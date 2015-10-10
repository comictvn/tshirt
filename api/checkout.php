<?php
/**
 *  
 *  This call charges the user's card 
 *  and marks the order as complete
 *  
 */
require __DIR__.'/autoload.php';
use Symfony\Component\Yaml\Yaml;
use Carbon\Carbon;

$settings = include __DIR__ . "/../data/settings.php";

// Set your secret key: remember to change this to your live secret key in production
// See your keys here https://dashboard.stripe.com/account
Stripe::setApiKey($settings['stripe_secret_key']);

// Retrieve the request's body and parse it as JSON
$input = @file_get_contents("php://input");
$event_json = json_decode($input);

$order = Order::find($event_json->orderId);
if(!$order)
	die();
$order->payment_details = json_encode($event_json->token);
$order->save();

// Do something with $event_json
$token = $event_json->token->id;
// Create the charge on Stripe's servers - this will charge the user's card
try {
	$charge = Stripe_Charge::create(array(
	  "amount" => $order->amount*1000, // amount in cents, again
	  "currency" => $order->currency,
	  "card" => $token,
	  "description" => $event_json->token->email)
	);
	
	$order->charge = json_encode($charge->__toArray(true));
	$order->transaction_id = $charge->id;
	$order->confirmed = 1;
	$order->save();
} catch(Stripe_Error $e) {
  // The card has been declined
  $order->info = json_encode($e);
  $order->confirmed = -1;
  $order->save();
}

//now show the order details
$result = [];
$result['status'] = true;
$data_dir = __DIR__ . "/../data/";
$pricing = Yaml::parse($data_dir . "pricing.yml");

$order = Order::find($event_json->orderId);
$payment_details = json_decode($order->payment_details);
$checkout_details = json_decode($order->checkout_details);
$result['amount'] = number_format($order->amount, 2);
$result['currency'] = $order->currency;
$result['date'] = Carbon::parse($order->created_at)->toDateTimeString();
$result['transaction_id'] = str_replace("ch_", "", $order->transaction_id);
$result['postage'] = $pricing['delivery_types'][$checkout_details->postage]['name'];
$result['description'] = $checkout_details->title;
$result['email'] = $order->email;


$_SESSION['uuid'] = generate_session()->toString();	 //reset session

//send an email
$mail = new MyMailer;
#$mail->isSendmail();
$mail->setFrom($settings['email'], $settings['site_name']);	//Set who the message is to be sent from
$mail->addAddress($order->email, $order->firstname .' '. $order->lastname); //Set who the message is to be sent to
$mail->Subject = 'Your '.$settings['site_name'].' order';
$message = file_get_contents(__DIR__ .'/../data/emails/order_placed.txt');
$message = str_replace("{name}", $order->firstname .' '. $order->lastname, $message);
$message = str_replace("{transaction_id}", $order->transaction_id, $message);
$message = str_replace("{site_name}", $settings['site_name'], $message);
$mail->Body = $message;  
if(!$mail->send()) {
	#echo 'Message could not be sent.';
	#echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
	#echo 'Message has been sent';
}


header('Content-Type: application/json');
echo json_encode($result);

