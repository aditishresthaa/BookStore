<?php

$conn = mysqli_connect('localhost','root','','shop_db') or die('connection failed');

// Base URL for payment return callbacks (no trailing slash). Change for production.
if (!defined('SITE_BASE_URL')) {
   define('SITE_BASE_URL', 'http://localhost/project');
}

// Khalti secret key (same as used in pay_now.php Authorization header, without "Key " prefix).
if (!defined('KHALTI_SECRET_KEY')) {
   define('KHALTI_SECRET_KEY', 'live_secret_key_68791341fdd94846a146f0457ff7b455');
}

// Set to true while debugging payment/order SQL issues (logs to PHP error log only).
if (!defined('ORDER_SQL_DEBUG')) {
   define('ORDER_SQL_DEBUG', false);
}

?>