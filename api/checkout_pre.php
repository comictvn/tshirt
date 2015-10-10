<?php
/**
 *  
 *  Before going to stripe we store the order details
 *  such as email, name, products, designs and address
 *  
 */
require __DIR__ . '/autoload.php';
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\PayPalAPI\SetExpressCheckoutReq;
use PayPal\PayPalAPI\SetExpressCheckoutRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

if (!function_exists('json_last_error_msg')) {
    function json_last_error_msg() {
        static $errors = array(
            JSON_ERROR_NONE             => null,
            JSON_ERROR_DEPTH            => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR        => 'Unexpected control character found',
            JSON_ERROR_SYNTAX           => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
        $error = json_last_error();
        return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error})";
    }
}

// Retrieve the request's body and parse it as JSON
$input = @file_get_contents("php://input");
$event_json = json_decode(utf8_encode($input));
$storage_dir = __DIR__ . "/../storage/";
$settings = include __DIR__ . "/../data/settings.php";

$order = Order::where('uuid', $_SESSION['uuid'])->first();
if(!$order) {
	$order = new Order();	
} else {
	if($order->confirmed !== 0) {
		$_SESSION['uuid'] = generate_session()->toString();
		$order = new Order();
	}
}

function parse_text_layer($layer) {
	$colors = [];
	$colors[] = $layer->fill;
	$colors[] = $layer->stroke;
	return $colors;
}

function parse_graphic_layer($layer) {
	
	$colors = [];
	foreach($layer->paths as $path) {
		$colors[] = $path->fill;
		$colors[] = $path->stroke;
	}
	
	return $colors;
}

function clean_colors($colors) {
	//get rid of duplicates
	$colors = array_unique($colors);
	$color_list = [];
	foreach($colors as $color) {
		if($color && $color[0] == '#') {
			$color_list[] = $color;
		}
	}
	return $color_list;
}

function calculate_orientation_price($colors, $has_photo) {
	global $pricing;
	
	$total_colors = count($colors);
	$max_count_set = count($pricing['colors']);
	if($total_colors > $max_count_set || $has_photo) {
		$total_colors = 'INF';
	}
	
	$price = 0;
	if( isset($pricing['colors'][$total_colors]) ) {
		$price = $pricing['colors'][$total_colors];
	}
	return (float) $price;
}

function calculate_layer_cost($item) {
	
	$colors = [];
	$orientation_pricing = [];
	foreach($item->canvases as $orientation => $layers) {
		$colors[$orientation] = [];
		$has_photo = false;
		foreach($layers->objects as $layer) {
			if($layer->type == 'text' || $layer->type == 'i-text') {
				$colors[$orientation] = array_merge($colors[$orientation], parse_text_layer($layer));
			}			
			if($layer->type == 'path-group') {
				$colors[$orientation] = array_merge($colors[$orientation], parse_graphic_layer($layer));
			}			
			if($layer->type == 'image') {
				$has_photo = true;
			}
		}
		$colors[$orientation] = clean_colors($colors[$orientation]);
		$orientation_pricing[$orientation] = calculate_orientation_price($colors[$orientation], $has_photo);
	}
	
	$orientation_pricing = array_sum($orientation_pricing);
	return $orientation_pricing;
	
}

$pricing = Yaml::parse(file_get_contents(__DIR__ . '/../data/pricing.yml'));

function calculate_subtotal() {
	global $pricing, $event_json;
	
	$total = 0;
	$single_price = 0;
	foreach($event_json->cart as $k => $item) {

		$base_price = $item->product->price;
		$quantity = array_sum((array) $item->quantities);
		$single_price = $base_price;
		
		$single_price += calculate_layer_cost($item); //add layers pricing
		
		$event_json->cart[$k]->single_price = $single_price;		
		$total += $single_price * $quantity; //per item
	}
	
	return $total;
}

$subtotal = calculate_subtotal($event_json);
if(!$subtotal) {
	die('ERROR');	
}
$postage = $pricing['delivery_types'][$event_json->checkout_details->postage]['price'];
$total_price = $subtotal + $postage; //add postage

$order->checkout_details = json_encode($event_json->checkout_details);
$order->customer_details = json_encode($event_json->customer_details);
$order->cart = json_encode($event_json->cart);
$order->firstname = @$event_json->customer_details->firstname;
$order->lastname = @$event_json->customer_details->lastname;
$order->email = @$event_json->customer_details->email;
$order->currency = $event_json->checkout_details->currency;
$order->uuid = $_SESSION['uuid'];
$order->subtotal = $subtotal;
$order->postage = $postage;
$order->amount = $total_price;
$order->dispatched = false;
$order->confirmed = 0;
$order->payment_method = $event_json->payment_method;
$order->save();

//if it's paypal we need to get a token
if($event_json->payment_method == 'paypal') {

	$config = array (
		'mode' 				=> $settings['paypal_environment'], 
		'acct1.UserName' 	=> $settings['paypal_username'],
		'acct1.Password' 	=> $settings['paypal_password'],
		'acct1.Signature' 	=> $settings['paypal_signature']
	);

	$paypalService = new PayPalAPIInterfaceServiceService($config);
	$paymentDetails= new PaymentDetailsType();

	$itemDetails = new PaymentDetailsItemType();
	$itemDetails->Name = count($event_json->cart) . ' items';
	$itemAmount = $order->amount;
	$itemDetails->Amount = $itemAmount;
	$itemQuantity = '1';
	$itemDetails->Quantity = $itemQuantity;

	$itemDetails->ItemCategory =  'Physical';

	$paymentDetails->PaymentDetailsItem[0] = $itemDetails;

	$orderTotal = new BasicAmountType();
	$orderTotal->currencyID = $order->currency;
	$orderTotal->value = $order->amount; 

	$paymentDetails->OrderTotal = $orderTotal;
	$paymentDetails->PaymentAction = 'Sale';

	$setECReqDetails = new SetExpressCheckoutRequestDetailsType();
	$setECReqDetails->PaymentDetails[0] = $paymentDetails;
	$setECReqDetails->CancelURL = $settings['site_url'] . '/api/paypal/checkout_cancel.php';
	$setECReqDetails->ReturnURL = $settings['site_url'] . '/api/paypal/checkout_success.php';
	$setECReqDetails->SolutionType = 'Sole';
	$setECReqDetails->LandingPage = 'Billing';

	$setECReqType = new SetExpressCheckoutRequestType();
	$setECReqType->Version = '104.0';
	$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;

	$setECReq = new SetExpressCheckoutReq();
	$setECReq->SetExpressCheckoutRequest = $setECReqType;
	
	$setECResponse = $paypalService->SetExpressCheckout($setECReq);
	
	/* check the return status */
	if($setECResponse->Ack == 'Success') {
		$token = $setECResponse->Token;
		$order->token = $token;
		$order->save();
	} else {
		$token = "none";
	}

}


$folder = md5($order->id);
$hash_folder = $folder[0]."/".$folder[1]."/";
$hash_path = $storage_dir.$hash_folder;
if(!is_dir($hash_path)) {
	mkdir($hash_path, 0777, true);
}

//dump images into storage
/*
foreach($event_json->orientation_data as $orientation => $values) {
	if(!$values->image)
		continue;
	list($type, $data) = explode(';', $values->image);
	list(, $data)      = explode(',', $data);
	
	$data = base64_decode($data);

	file_put_contents($hash_path.$orientation.'.png', $data);
}
*/
$result = [];
$result['id'] = $order->id;
$result['amount'] = $order->amount;
$result['payment_method'] = $event_json->payment_method;
if($event_json->payment_method == 'paypal') {
	$result['token'] = $token;
}
echo json_encode(['order' => $result]);

