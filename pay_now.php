<?php
include 'config.php';

$amountInPaisa = isset($_GET['amount']) ? (int) $_GET['amount'] : 0;
$name = isset($_GET['name']) ? urldecode((string) $_GET['name']) : '';
$number = isset($_GET['number']) ? urldecode((string) $_GET['number']) : '';
$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

$return_url = SITE_BASE_URL . '/khalti_return.php';
if ($order_id > 0) {
   $return_url .= '?order_id=' . $order_id;
}

$purchase_order_id = $order_id > 0 ? (string) $order_id : 'Order-' . time();
$purchase_order_name = $name !== '' ? $name : 'Bookstore order';

$postData = [
   'return_url' => $return_url,
   'website_url' => SITE_BASE_URL,
   'amount' => $amountInPaisa,
   'purchase_order_id' => $purchase_order_id,
   'purchase_order_name' => $purchase_order_name,
   'customer_info' => [
      'name' => $name,
      'email' => 'test@khalti.com',
      'phone' => $number,
   ],
];

$curl = curl_init();

curl_setopt_array($curl, array(
   CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_ENCODING => '',
   CURLOPT_MAXREDIRS => 10,
   CURLOPT_TIMEOUT => 0,
   CURLOPT_FOLLOWLOCATION => true,
   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
   CURLOPT_CUSTOMREQUEST => 'POST',
   CURLOPT_POSTFIELDS => json_encode($postData),
   CURLOPT_HTTPHEADER => array(
      'Authorization: Key ' . KHALTI_SECRET_KEY,
      'Content-Type: application/json',
   ),
));

$response = curl_exec($curl);
curl_close($curl);

$response = is_string($response) ? json_decode($response, true) : null;

if (is_array($response) && isset($response['payment_url'])) {
   $payment_url = $response['payment_url'];
   header('location: ' . $payment_url);
   exit();
}

echo 'Error initiating payment: ' . htmlspecialchars(json_encode($response), ENT_QUOTES, 'UTF-8');

?>