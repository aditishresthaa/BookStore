<?php
include 'config.php';
require_once 'cart_helpers.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$message = [];

if(isset($_POST['add_to_cart'])){
   $r = add_product_to_cart($conn, $user_id, (int) ($_POST['product_id'] ?? 0), (int) ($_POST['product_quantity'] ?? 1));
   $message[] = $r['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Book Details</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="quick-view">
    <h1 class="title">Product Details</h1>
    <?php
    if(isset($_GET['id'])){
        $id = mysqli_real_escape_string($conn, $_GET['id']);
        $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$id'") or die('query failed');
        if(mysqli_num_rows($select_products) > 0){
           while($fetch_products = mysqli_fetch_assoc($select_products)){
    ?>
     <form action="" method="post" class="box">
        <img class="image" src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
        <div class="name"><?php echo $fetch_products['name']; ?></div>
        <div class="price">Rs.<?php echo $fetch_products['price']; ?>/-</div>
        <div class="details"><?php echo $fetch_products['details']; ?></div>
        <input type="number" min="1" name="product_quantity" value="1" class="qty">
        <input type="hidden" name="product_id" value="<?php echo (int) $fetch_products['id']; ?>">
        <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
        <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
        <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
        <input type="submit" value="add to cart" name="add_to_cart" class="btn">
     </form>
    <?php
           }
        }else{
           echo '<p class="empty">Product not found!</p>';
        }
    }
    ?>
    <div class="more-btn" style="text-align: center; margin-top: 2rem;">
       <a href="home.php" class="option-btn">Go to Home Page</a>
    </div>
</section>

<!-- RECOMMENDATION SECTION -->
<section class="products" style="padding-top: 0;">
   <h1 class="title">Recommended for you</h1>
   <?php 
   include 'recommend.php'; 
   if(isset($_GET['id'])){
       displayRecommendations($_GET['id'], $conn);
   } 
   ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>