<?php

require __DIR__ . '/../autoload.php';
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\BillingAgreementDetailsType;
use PayPal\EBLBaseComponents\PaymentDetailsItemType;
use PayPal\EBLBaseComponents\PaymentDetailsType;
use PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsReq;
use PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentReq;
use PayPal\Service\PayPalAPIInterfaceServiceService;
#use PayPal\Type\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType;
use PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType;

$settings = include __DIR__ . "/../../data/settings.php";

$token = $_REQUEST['token'];
$payer_id = $_REQUEST['PayerID'];

$config = array (
	'mode' 				=> $settings['paypal_environment'],
	'acct1.UserName' 	=> $settings['paypal_username'],
	'acct1.Password' 	=> $settings['paypal_password'],
	'acct1.Signature' 	=> $settings['paypal_signature']
);

/* Create GetExpressCheckoutDetails request with token*/
$getExpressCheckoutDetailsRequest = new GetExpressCheckoutDetailsRequestType($token);
$getExpressCheckoutDetailsRequest->Version = 85.0;
$getExpressCheckoutReq = new GetExpressCheckoutDetailsReq();
$getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;

/* execute the GetExpressCheckoutDetails API call */
$paypalService = new PayPalAPIInterfaceServiceService($config);
$getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);

/* obtain the payerId */
if($getECResponse->Ack =='Success')
{
	#$payerId = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PayerInfo->PayerID;
	#$amount = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails->OrderTotal;
	/* TODO: Make sure amount and other information matches what you've asked for */
	$order = Order::where('token', $token)->first();
	if(!$order)
		die();

	/* setup payment details */
	$orderTotal = new BasicAmountType($order->currency, $order->amount);
	$PaymentDetails= new PaymentDetailsType();
	$PaymentDetails->OrderTotal = $orderTotal;

	/* create DoExpressCheckout request details */
	$DoECRequestDetails = new DoExpressCheckoutPaymentRequestDetailsType();
	$DoECRequestDetails->PayerID = $payer_id;
	$DoECRequestDetails->Token = $token;
	$DoECRequestDetails->PaymentDetails[0] = $PaymentDetails;
	
	/* create DoExpressCheckoutPayment Request */
	$DoECRequest = new DoExpressCheckoutPaymentRequestType();
	$DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
	$DoECRequest->Version = '85.0';
	$DoECReq = new DoExpressCheckoutPaymentReq();
	$DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;

	/* Execute the API call */
	$paypalService = new PayPalAPIInterfaceServiceService($config);
	$DoECResponse = $paypalService->DoExpressCheckoutPayment($DoECReq);

	/* check the return status */
	#print_r($getECResponse);
	$order->payment_details = json_encode($getECResponse);

	if($DoECResponse->Ack =='Success') {
		/* verify your payment info - before providing the item/resource to the consumer */
		$paymentStatus = $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->PaymentStatus;
		
		$order->charge = json_encode($DoECResponse);
		$order->transaction_id = $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID;
		$order->confirmed = 1;
		$order->save();
		
	} else {
		// The card has been declined
		$order->info = json_encode($DoECResponse);
		$order->confirmed = -1;
		$order->save();
	}

}

$_SESSION['uuid'] = generate_session()->toString();	 //reset session

//send an email
$mail = new MyMailer;
$mail->setFrom($settings['email'], $settings['site_name']);	//Set who the message is to be sent from
$mail->addAddress($order->email, $order->firstname .' '. $order->lastname); //Set who the message is to be sent to
$mail->Subject = 'Your '.$settings['site_name'].' order';
$message = file_get_contents(__DIR__ .'/../../data/emails/order_placed.txt');
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
#die();
?>
<script type='text/javascript'>
window.onload = function() {
	
    if (window.opener) {
		window.opener.scopeCheckout.handlePaypalToken('<?= $token ?>');
        window.close();
    } else {
        if (top.dg.isOpen() == true) {
            top.scopeCheckout.handlePaypalToken('<?= $token ?>');
			top.dg.closeFlow();
            return true;
        }
    }
	
};
</script>