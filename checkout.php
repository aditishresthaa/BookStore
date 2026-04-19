<?php
include 'config.php';
require_once 'cart_helpers.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:login.php');
   exit();
}

$user_id_esc = mysqli_real_escape_string($conn, (string) $user_id);
$checkout_removed_names = prune_stale_cart_items($conn, $user_id, true);
$message = [];

// Fetch user data to pre-fill the form
$user_query = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$user_id_esc'") or die('query failed');
$user_data = mysqli_fetch_assoc($user_query);

if(isset($_POST['order_btn'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $number = mysqli_real_escape_string($conn, $_POST['number']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $method = mysqli_real_escape_string($conn, $_POST['method']);
   $address = mysqli_real_escape_string($conn, $_POST['address']);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = [];

   // Calculate cart total and gather product list
   $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id_esc'") or die('query failed');
   if(mysqli_num_rows($cart_query) > 0){
      while($cart_item = mysqli_fetch_assoc($cart_query)){
         $cart_products[] = $cart_item['name'].' ('.$cart_item['quantity'].') ';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      }
   }

   $total_products = implode(', ', $cart_products);

   if($cart_total == 0){
      $message[] = 'Your cart is empty';
   } else {
      // Check if order was already placed (to prevent refreshing page duplicates)
      $order_check = mysqli_query($conn, "SELECT * FROM `orders` WHERE name = '$name' AND number = '$number' AND email = '$email' AND method = '$method' AND address = '$address' AND total_products = '$total_products' AND total_price = '$cart_total' AND placed_on = '$placed_on'") or die('query failed');

      if(mysqli_num_rows($order_check) > 0){
         $message[] = 'Order already placed!';
      } else {
         // Insert order into database (explicit pending; relies on DB default if column omitted elsewhere)
         mysqli_query($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status) VALUES('$user_id_esc', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on', 'pending')") or die('query failed: ' . mysqli_error($conn));
         
         $new_order_id = (int) mysqli_insert_id($conn);

         // Clear the cart
         mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id_esc'") or die('query failed');

         // PAYMENT LOGIC
         if ($method == 'Khalti'){
            // Convert to Paisa (Rs 1 = 100 Paisa)
            $amount_paisa = $cart_total * 100;
            $_SESSION['khalti_order_id'] = $new_order_id;
            // Redirect to pay_now.php (Ensure no "echo" happened before this)
            header('location: pay_now.php?amount=' . $amount_paisa . '&name=' . urlencode($name) . '&number=' . urlencode($number) . '&order_id=' . $new_order_id);
            exit();
         } else {
            // If Cash on Delivery
            header('location:orders.php');
            exit();
         }
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
   <h3>Checkout</h3>
   <p> <a href="home.php">Home</a> / Checkout </p>
</div>

<?php if (!empty($checkout_removed_names)) { ?>
<div class="cart-removal-alert" role="alert" style="max-width:900px;margin:0 auto 2rem;">
   <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
   <div class="cart-removal-alert__body">
      <strong>Items updated</strong>
      <p><?php echo format_cart_removal_notice_lines($checkout_removed_names); ?></p>
   </div>
</div>
<?php } ?>

<section class="display-order">
   <?php  
      $grand_total = 0;
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id_esc'") or die('query failed');
      if(mysqli_num_rows($select_cart) > 0){
         while($fetch_cart = mysqli_fetch_assoc($select_cart)){
            $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
            $grand_total += $total_price;
   ?>
   <p> <?php echo $fetch_cart['name']; ?> <span>(<?php echo 'Rs.'.$fetch_cart['price'].'/-'.' x '. $fetch_cart['quantity']; ?>)</span> </p>
   <?php
         }
      } else {
         echo '<p class="empty">Your cart is empty</p>';
      }
   ?>
   <div class="grand-total"> Grand total: <span>Rs.<?php echo $grand_total; ?>/-</span> </div>
</section>

<section class="checkout">
   <form action="" method="post">
      <h3>Place Your Order</h3>
      <div class="flex">
         <div class="inputBox">
            <span>Your Name:</span>
            <input type="text" name="name" required placeholder="Enter your name" value="<?php echo $user_data['name']; ?>">
         </div>
         <div class="inputBox">
            <span>Your Number:</span>
            <input type="number" name="number" required placeholder="Enter your number" value="<?php echo $user_data['number'] ?? ''; ?>">
         </div>
         <div class="inputBox">
            <span>Your Email:</span>
            <input type="email" name="email" required placeholder="Enter your email" value="<?php echo $user_data['email']; ?>">
         </div>
         <div class="inputBox">
            <span>Payment Method:</span>
            <select name="method">
               <option value="cash on delivery">Cash on Delivery</option>
               <option value="Khalti">Khalti (E-Payment)</option>
            </select>
         </div>
         <div class="inputBox">
           <span>Full Address:</span>
            <input type="text" name="address" required placeholder="e.g. Street name, City, Nepal">
        </div>
      </div>
      
      <input type="submit" value="Order Now" class="btn" name="order_btn">
   </form>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>