<?php
include 'config.php';
include 'order_helpers.php';
require_once 'cart_helpers.php';
session_start();
$user_id = $_SESSION['user_id'];
if(!isset($user_id)){ header('location:login.php'); exit(); }

$message = [];
if (isset($_POST['add_to_cart'])) {
   $r = add_product_to_cart($conn, $user_id, (int) ($_POST['product_id'] ?? 0), (int) ($_POST['product_quantity'] ?? 1));
   $message[] = $r['message'];
}

$user_id_esc = mysqli_real_escape_string($conn, (string) $user_id);
prune_stale_cart_items($conn, $user_id, false);

$payment_notice = '';
if (isset($_GET['payment'])) {
   switch ($_GET['payment']) {
      case 'success':
         $payment_notice = 'Payment completed. Your order status has been updated.';
         break;
      case 'failed':
      case 'incomplete':
         $payment_notice = 'Payment was not completed. Your order status will update when payment succeeds or an admin updates it.';
         break;
      case 'update_error':
         $payment_notice = 'Payment may have succeeded, but we could not update your order. Please contact support with your order details.';
         break;
      default:
         break;
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Your Orders</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Your Orders</h3>
   <p> <a href="home.php">home</a> / orders </p>
</div>

<section class="placed-orders">
   <h1 class="title">Placed Orders</h1>

   <?php if ($payment_notice !== '') { ?>
   <p class="empty" style="max-width:900px;margin:0 auto 2rem;"><?php echo htmlspecialchars($payment_notice, ENT_QUOTES, 'UTF-8'); ?></p>
   <?php } ?>

   <div class="box-container">
      <?php
         $order_query = mysqli_query($conn, "SELECT `id`, `user_id`, `name`, `number`, `email`, `method`, `address`, `total_products`, `total_price`, `placed_on`, `payment_status` FROM `orders` WHERE user_id = '$user_id_esc'");
         if (!$order_query) {
            die('query failed: ' . mysqli_error($conn));
         }
         if(mysqli_num_rows($order_query) > 0){
            while($fetch_orders = mysqli_fetch_assoc($order_query)){
            $pay_disp = resolve_order_payment_status_display($fetch_orders);
      ?>
      <div class="box">
         <p> placed on : <span><?php echo htmlspecialchars($fetch_orders['placed_on'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> name : <span><?php echo htmlspecialchars($fetch_orders['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> number : <span><?php echo htmlspecialchars($fetch_orders['number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> email : <span><?php echo htmlspecialchars($fetch_orders['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> address : <span><?php echo htmlspecialchars($fetch_orders['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> payment method : <span><?php echo htmlspecialchars($fetch_orders['method'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p class="order-products-label"> your orders : </p>
         <div class="order-products-wrap" style="color: #8e44ad;"><?php echo format_order_products_for_display($conn, $fetch_orders['total_products'] ?? ''); ?></div>
         <p> total price : <span>Rs.<?php echo htmlspecialchars((string)($fetch_orders['total_price'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>/-</span> </p>
         <?php
            if (defined('ORDER_SQL_DEBUG') && ORDER_SQL_DEBUG) {
               $dbg_raw = $pay_disp['raw'];
               $dbg_json = json_encode($dbg_raw, JSON_UNESCAPED_UNICODE);
               if ($dbg_json === false) {
                  $dbg_json = '(encode error)';
               }
               echo '<!-- order id=' . (int)($fetch_orders['id'] ?? 0) . ' row[payment_status]=' . htmlspecialchars($dbg_json, ENT_QUOTES, 'UTF-8') . ' display=' . htmlspecialchars($pay_disp['label'], ENT_QUOTES, 'UTF-8') . ' -->' . "\n";
            }
         ?>
         <p> payment status : <span style="color:<?php echo htmlspecialchars($pay_disp['color'], ENT_QUOTES, 'UTF-8'); ?>;"><?php echo htmlspecialchars($pay_disp['label'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      </div>
      <?php
       }
      }else{ echo '<p class="empty">no orders placed yet!</p>'; }
      ?>
   </div>
</section>

<!-- Recommendation Section -->
<?php
include 'recommend.php';
echo "<section class='recommend'>";
echo "<h1 class='title'>You may also like</h1>";
$randomProduct = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM products ORDER BY RAND() LIMIT 1"));
if ($randomProduct) {
    displayRecommendations($randomProduct['id'], $conn);
}
echo "</section>";
?>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>