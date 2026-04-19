<?php
include 'config.php';
require_once 'cart_helpers.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$user_id_esc = $user_id !== null ? mysqli_real_escape_string($conn, (string) $user_id) : '';
$message = [];

$cart_removed_names = [];
if ($user_id !== null) {
    $cart_removed_names = prune_stale_cart_items($conn, $user_id, true);
} else {
    $cart_removed_names = prune_guest_cart_stale($conn, true);
}

if ($user_id !== null) {

    if (isset($_POST['update_cart'])) {
        $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
        $cart_quantity = (int) $_POST['cart_quantity'];
        mysqli_query($conn, "UPDATE `cart` SET quantity = '$cart_quantity' WHERE id = '$cart_id' AND user_id = '$user_id_esc'") or die('query failed');
        $message[] = 'cart quantity updated!';
    }

    if (isset($_GET['delete'])) {
        $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
        mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$delete_id' AND user_id = '$user_id_esc'") or die('query failed');
        header('location:cart.php');
        exit();
    }

    if (isset($_GET['delete_all'])) {
        mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id_esc'") or die('query failed');
        header('location:cart.php');
        exit();
    }
} else {

    if (isset($_GET['guest_remove']) && is_numeric($_GET['guest_remove'])) {
        $idx = (int) $_GET['guest_remove'];
        $items = guest_cart_get_items();
        if (isset($items[$idx])) {
            array_splice($items, $idx, 1);
            guest_cart_set_items($items);
        }
        header('location:cart.php');
        exit();
    }

    if (isset($_GET['delete_all'])) {
        guest_cart_set_items([]);
        header('location:cart.php');
        exit();
    }

    if (isset($_POST['update_guest_cart']) && isset($_POST['guest_line_index'], $_POST['guest_quantity'])) {
        $idx = (int) $_POST['guest_line_index'];
        $q = max(1, (int) $_POST['guest_quantity']);
        $items = guest_cart_get_items();
        if (isset($items[$idx])) {
            $items[$idx]['quantity'] = $q;
            guest_cart_set_items($items);
            $message[] = 'cart quantity updated!';
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
   <title>cart</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>shopping cart</h3>
   <p> <a href="home.php">home</a> / cart </p>
</div>

<section class="shopping-cart">

   <h1 class="title">products added</h1>

   <?php if (!empty($cart_removed_names)) { ?>
   <div class="cart-removal-alert" role="alert">
      <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
      <div class="cart-removal-alert__body">
         <strong>Items updated</strong>
         <p><?php echo format_cart_removal_notice_lines($cart_removed_names); ?></p>
      </div>
   </div>
   <?php } ?>

   <div class="box-container">
      <?php
         $grand_total = 0;
         if ($user_id !== null) {
            $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id_esc'") or die('query failed');
            if (mysqli_num_rows($select_cart) > 0) {
               while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
      ?>
      <div class="box">
         <a href="cart.php?delete=<?php echo (int) $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this from cart?');"></a>
         <img src="uploaded_img/<?php echo htmlspecialchars($fetch_cart['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" >
         <div class="name"><?php echo htmlspecialchars($fetch_cart['name'], ENT_QUOTES, 'UTF-8'); ?></div>
         <div class="price">Rs.<?php echo (int) $fetch_cart['price']; ?>/-</div>
         <form action="" method="post">
            <input type="hidden" name="cart_id" value="<?php echo (int) $fetch_cart['id']; ?>">
            <input type="number" min="1" name="cart_quantity" value="<?php echo (int) $fetch_cart['quantity']; ?>">
            <input type="submit" name="update_cart" value="update" class="option-btn">
         </form>
         <div class="sub-total"> sub total : <span>Rs.<?php echo $sub_total = ((int) $fetch_cart['quantity'] * (int) $fetch_cart['price']); ?>/-</span> </div>
      </div>
      <?php
                  $grand_total += $sub_total;
               }
            } else {
               echo '<p class="empty">your cart is empty</p>';
            }
         } else {
            $guest_items = guest_cart_get_items();
            if ($guest_items !== []) {
               foreach ($guest_items as $gi => $fetch_cart) {
                  $sub_total = (int) $fetch_cart['quantity'] * (int) $fetch_cart['price'];
                  $grand_total += $sub_total;
      ?>
      <div class="box">
         <a href="cart.php?guest_remove=<?php echo (int) $gi; ?>" class="fas fa-times" onclick="return confirm('delete this from cart?');"></a>
         <img src="uploaded_img/<?php echo htmlspecialchars($fetch_cart['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" >
         <div class="name"><?php echo htmlspecialchars($fetch_cart['name'], ENT_QUOTES, 'UTF-8'); ?></div>
         <div class="price">Rs.<?php echo (int) $fetch_cart['price']; ?>/-</div>
         <form action="" method="post">
            <input type="hidden" name="guest_line_index" value="<?php echo (int) $gi; ?>">
            <input type="number" min="1" name="guest_quantity" value="<?php echo (int) $fetch_cart['quantity']; ?>">
            <input type="submit" name="update_guest_cart" value="update" class="option-btn">
         </form>
         <div class="sub-total"> sub total : <span>Rs.<?php echo $sub_total; ?>/-</span> </div>
      </div>
      <?php
               }
            } else {
               echo '<p class="empty">your cart is empty</p>';
            }
         }
      ?>
   </div>

   <div style="margin-top: 2rem; text-align:center;">
      <a href="cart.php?delete_all" class="delete-btn <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>" onclick="return confirm('delete all from cart?');">delete all</a>
   </div>

   <?php if ($user_id === null && $grand_total > 0) { ?>
   <p class="cart-guest-hint"><i class="fas fa-info-circle"></i> You are not logged in. <a href="login.php">Sign in</a> to save your cart across devices and checkout.</p>
   <?php } ?>

   <div class="cart-total">
      <p>grand total : <span>Rs.<?php echo $grand_total; ?>/-</span></p>
      <div class="flex">
         <a href="shop.php" class="option-btn">continue shopping</a>
         <?php if ($user_id !== null) { ?>
         <a href="checkout.php" class="btn <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>">proceed to checkout</a>
         <?php } else { ?>
         <a href="login.php" class="btn <?php echo ($grand_total > 0) ? '' : 'disabled'; ?>">login to checkout</a>
         <?php } ?>
      </div>
   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
