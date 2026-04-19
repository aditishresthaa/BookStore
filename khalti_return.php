<?php
/**
 * Khalti ePayment return handler: user lands here via GET after payment.
 * Verifies payment with Khalti lookup API, then updates orders.payment_status.
 */
include 'config.php';
session_start();

$pidx = isset($_GET['pidx']) ? trim((string) $_GET['pidx']) : '';
$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : (int) ($_SESSION['khalti_order_id'] ?? 0);

if ($pidx === '') {
   header('Location: orders.php?payment=incomplete');
   exit();
}

$payload = json_encode(['pidx' => $pidx]);

$ch = curl_init('https://a.khalti.com/api/v2/epayment/lookup/');
curl_setopt_array($ch, [
   CURLOPT_POST => true,
   CURLOPT_POSTFIELDS => $payload,
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_HTTPHEADER => [
      'Authorization: Key ' . KHALTI_SECRET_KEY,
      'Content-Type: application/json',
   ],
]);

$response = curl_exec($ch);
$curl_err = curl_error($ch);
curl_close($ch);

$data = json_decode($response, true);
if (ORDER_SQL_DEBUG) {
   error_log('Khalti lookup response: ' . $response);
}

$status = '';
if (is_array($data)) {
   if (isset($data['status'])) {
      $status = (string) $data['status'];
   } elseif (isset($data['data']['status'])) {
      $status = (string) $data['data']['status'];
   } elseif (isset($data['state']['status'])) {
      $status = (string) $data['state']['status'];
   }
}

$resp_order = 0;
if (is_array($data)) {
   if (isset($data['purchase_order_id'])) {
      $resp_order = (int) $data['purchase_order_id'];
   } elseif (isset($data['data']['purchase_order_id'])) {
      $resp_order = (int) $data['data']['purchase_order_id'];
   }
}

if ($order_id <= 0 && $resp_order > 0) {
   $order_id = $resp_order;
}

$completed = (strcasecmp($status, 'Completed') === 0);

if (!$completed || $order_id <= 0 || $curl_err) {
   if (ORDER_SQL_DEBUG) {
      error_log('Khalti lookup failed or not completed. curl_err=' . $curl_err . ' status=' . $status);
   }
   header('Location: orders.php?payment=failed');
   exit();
}

$oid_esc = mysqli_real_escape_string($conn, (string) $order_id);
$upd = mysqli_query($conn, "UPDATE `orders` SET `payment_status` = 'completed' WHERE `id` = '$oid_esc' LIMIT 1");

if (!$upd) {
   if (ORDER_SQL_DEBUG) {
      error_log('Order update failed: ' . mysqli_error($conn));
   }
   header('Location: orders.php?payment=update_error');
   exit();
}

unset($_SESSION['khalti_order_id']);
header('Location: orders.php?payment=success');
exit();
