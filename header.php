<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/cart_helpers.php';

// Check if the user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if($user_id){
    $user_id_header_esc = mysqli_real_escape_string($conn, (string) $user_id);
    prune_stale_cart_items($conn, $user_id, false);
    $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id_header_esc'") or die('query failed');
    $cart_rows_number = mysqli_num_rows($select_cart_number);
} else {
    prune_guest_cart_stale($conn, false);
    $cart_rows_number = guest_cart_item_count();
}

// Handle messages if any
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>


<header class="header">

   <div class="header-1">
      <div class="flex">
         <div class="share">
            <a href="#" class="fab fa-facebook-f"></a>
            <a href="#" class="fab fa-twitter"></a>
            <a href="#" class="fab fa-instagram"></a>
            <a href="#" class="fab fa-linkedin"></a>
         </div>
         <?php if($user_id): ?>
            <p> Welcome, <?php echo $_SESSION['user_name']; ?> | <a href="logout.php">Logout</a> </p>
         <?php else: ?>
            <p> new <a href="login.php">login</a> | <a href="register.php">register</a> </p>
         <?php endif; ?>
      </div>
   </div>

   <div class="header-2">
      <div class="flex">
         <a href="home.php" class="logo">Booklfy.</a>

         <nav class="navbar">
            <a href="home.php">home</a>
            <a href="about.php">about</a>
            <a href="shop.php">shop</a>
            <a href="contact.php">contact</a>
            <a href="orders.php">orders</a>
         </nav>

         <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <a href="search_page.php" class="fas fa-search"></a>
            <div id="user-btn" class="fas fa-user"></div>
            <a href="cart.php"> <i class="fas fa-shopping-cart"></i> <span>(<?php echo $cart_rows_number; ?>)</span> </a>
         </div>

         <?php if($user_id): ?>
            <div class="user-box">
               <p>username : <span><?php echo $_SESSION['user_name']; ?></span></p>
               <p>email : <span><?php echo $_SESSION['user_email']; ?></span></p>
               <a href="logout.php" class="delete-btn">logout</a>
            </div>
         <?php endif; ?>
      </div>
   </div>

</header>
