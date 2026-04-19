<?php

include 'config.php';
include 'order_helpers.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
}

$message = [];

if(isset($_POST['update_order'])){
   $order_update_id = isset($_POST['order_id']) ? mysqli_real_escape_string($conn, $_POST['order_id']) : '';
   $update_payment = isset($_POST['update_payment']) ? strtolower(trim((string) $_POST['update_payment'])) : '';
   $allowed_status = ['pending', 'processing', 'completed', 'cancelled'];

   if ($order_update_id === '' || !in_array($update_payment, $allowed_status, true)) {
      $message[] = 'Invalid order or status. Choose a status from the list and try again.';
   } else {
      $upd = mysqli_query($conn, "UPDATE `orders` SET `payment_status` = '$update_payment' WHERE `id` = '$order_update_id' LIMIT 1");
      if (!$upd) {
         $message[] = 'Update failed: ' . mysqli_error($conn);
      } else {
         if (mysqli_affected_rows($conn) === 0) {
            $message[] = 'No row updated (check order id exists).';
         } else {
            $message[] = 'Payment status has been updated!';
         }
      }
   }
}

if(isset($_GET['delete'])){
   $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
   mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$delete_id'") or die('query failed: ' . mysqli_error($conn));
   header('location:admin_orders.php');
   exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">
<style>
      *{
         font-family: 'Poppins', sans-serif;
         margin:0; padding:0;
         box-sizing: border-box;
      }
      body{
         display: flex;
         background: #f7f7f7;
      }

      .orders{
         margin-left: 480px;
      } 
      /* Sidebar */
.sidebar {
   width: 230px;
   background: #3c1361;
   color: white;
   height: 100vh;
   position: fixed;
   top: 0;
   left: 0;
   display: flex;
   flex-direction: column;
   padding: 25px;
   font-size: 16px; /* ✅ Increased base font size */
}

.sidebar h2 {
   text-align: center;
   margin-bottom: 25px;
   color: #d0bdf4;
   font-size: 22px; /* ✅ Bigger title */
   letter-spacing: 1px;
}

.sidebar a {
   text-decoration: none;
   color: white;
   margin: 15px 0;
   padding: 12px 10px;
   border-radius: 8px;
   display: flex;
   align-items: center;
   font-size: 16px; /* ✅ Larger menu font */
   transition: 0.3s;
}

.sidebar a:hover {
   background: #5e239d;
   transform: translateX(5px); /* ✅ Smooth hover effect */
}

.sidebar a i {
   margin-right: 12px;
   font-size: 18px; /* ✅ Slightly larger icons */
}


      /* Main content */
      .main-content{
         margin-left: 250px;
         padding: 30px;
         width: 100%;
      }
      h1.title{
         text-align: center;
         margin-bottom: 30px;
         font-size: 28px;
      }
      .box-container{
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
         gap: 20px;
      }
      .box{
         background: white;
         border-radius: 10px;
         box-shadow: 0 2px 6px rgba(0,0,0,0.1);
         text-align: center;
         padding: 20px;
         transition: transform 0.2s;
      }
      .box:hover{
         transform: translateY(-5px);
      }
      .box h3{
         font-size: 24px;
         color: #3c1361;
      }
      .box p{
         color: #777;
      }

      canvas{
         margin-top: 40px;
         background: white;
         padding: 20px;
         border-radius: 10px;
         box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      }
   </style>
</head>
<div class="sidebar">
      <h2>Admin Panel</h2>
      <a href="admin_page.php"><i class="fas fa-chart-line"></i> Dashboard</a>
      <a href="admin_products.php"><i class="fas fa-box"></i> Products</a>
      <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
      <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
      <a href="admin_messages.php"><i class="fas fa-envelope"></i> Messages</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
   </div>
<body>
   
<!-- <?php include 'admin_header.php'; ?> -->

<section class="orders">

   <h1 class="title">placed orders</h1>

   <?php
   if (!empty($message)) {
      foreach ($message as $msg) {
         echo '<p class="empty" style="max-width:800px;margin:1rem auto;">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>';
      }
   }
   ?>

   <div class="box-container">
      <?php
      $select_orders = mysqli_query($conn, "SELECT * FROM `orders`") or die('query failed');
      if(mysqli_num_rows($select_orders) > 0){
         while($fetch_orders = mysqli_fetch_assoc($select_orders)){
      ?>
      <div class="box">
         <p> user id : <span><?php echo htmlspecialchars((string)($fetch_orders['user_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> placed on : <span><?php echo htmlspecialchars($fetch_orders['placed_on'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> name : <span><?php echo htmlspecialchars($fetch_orders['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> number : <span><?php echo htmlspecialchars($fetch_orders['number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> email : <span><?php echo htmlspecialchars($fetch_orders['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p> address : <span><?php echo htmlspecialchars($fetch_orders['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <p class="order-products-label"> total products : </p>
         <div class="order-products-wrap admin-order-products"><?php echo format_order_products_for_display($conn, $fetch_orders['total_products'] ?? ''); ?></div>
         <p> total price : <span>Rs.<?php echo htmlspecialchars((string)($fetch_orders['total_price'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>/-</span> </p>
         <p> payment method : <span><?php echo htmlspecialchars($fetch_orders['method'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span> </p>
         <form action="" method="post">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars((string)($fetch_orders['id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            <?php
               $pay_status_adm = strtolower(trim((string)($fetch_orders['payment_status'] ?? 'pending')));
               if (!in_array($pay_status_adm, ['pending', 'processing', 'completed', 'cancelled'], true)) {
                  $pay_status_adm = 'pending';
               }
            ?>
            <select name="update_payment">
               <option value="pending"<?php echo $pay_status_adm === 'pending' ? ' selected' : ''; ?>>pending</option>
               <option value="processing"<?php echo $pay_status_adm === 'processing' ? ' selected' : ''; ?>>processing</option>
               <option value="completed"<?php echo $pay_status_adm === 'completed' ? ' selected' : ''; ?>>completed</option>
               <option value="cancelled"<?php echo $pay_status_adm === 'cancelled' ? ' selected' : ''; ?>>cancelled</option>
            </select>
            <input type="submit" value="update" name="update_order" class="option-btn">
            <a href="admin_orders.php?delete=<?php echo $fetch_orders['id']; ?>" onclick="return confirm('delete this order?');" class="delete-btn">delete</a>
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no orders placed yet!</p>';
      }
      ?>
   </div>

</section>










<!-- custom admin js file link  -->
<script src="js/admin_script.js"></script>

</body>
</html>