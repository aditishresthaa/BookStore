<?php
include 'config.php';
require_once 'cart_helpers.php';
session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
}

$message = [];

// ================= ADD TO CART =================
if (isset($_POST['add_to_cart'])) {
   $r = add_product_to_cart($conn, $user_id ?? null, (int) ($_POST['product_id'] ?? 0), (int) ($_POST['product_quantity'] ?? 1));
   $message[] = $r['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Home</title>

   <!-- font awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<!-- ================= HERO ================= -->
<section class="home">
   <div class="content">
      <h3>Your Book Adventure Awaits.</h3>
      <p>Come explore the world of books with us!</p>
      <a href="about.php" class="white-btn">discover more</a>
   </div>
</section>
<!-- ================= PRODUCTS ================= -->
<section class="products">

   <h1 class="title">Latest Products</h1>

   <?php $category = $_GET['category'] ?? ''; ?>

   <!-- CATEGORY FILTER -->
   <form method="GET" class="category-filter">
      <select name="category" onchange="this.form.submit()">
         <option value="" >All Categories</option>
         <?php
         $catQuery = mysqli_query($conn, "SELECT DISTINCT category FROM products");
         while ($cat = mysqli_fetch_assoc($catQuery)) {
            $selected = ($category == $cat['category']) ? 'selected' : '';
            echo "<option value='{$cat['category']}' $selected>{$cat['category']}</option>";
         }
         ?>
      </select>
   </form>

   <div class="box-container">

      <?php
      if (!empty($category)) {
         $select_products = mysqli_query($conn, "SELECT * FROM products WHERE category='$category'");
      } else {
         $select_products = mysqli_query($conn, "SELECT * FROM products");
      }

      if (mysqli_num_rows($select_products) > 0) {
         while ($fetch_products = mysqli_fetch_assoc($select_products)) {
      ?>
      <form action="" method="POST" class="box">
         <a href="view_page.php?id=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
         
         <!-- IMAGE FIXED WITH ASPECT RATIO -->
         <div class="img-container">
            <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="<?= $fetch_products['name']; ?>">
         </div>

         <div class="name"><?= $fetch_products['name']; ?></div>
         <div class="price">Rs.<?= $fetch_products['price']; ?>/-</div>

         <input type="number" min="1" name="product_quantity" value="1" class="qty">
         <input type="hidden" name="product_id" value="<?= (int) $fetch_products['id']; ?>">
         <input type="hidden" name="product_name" value="<?= $fetch_products['name']; ?>">
         <input type="hidden" name="product_price" value="<?= $fetch_products['price']; ?>">
         <input type="hidden" name="product_image" value="<?= $fetch_products['image']; ?>">

         <input type="submit" value="add to cart" name="add_to_cart" class="btn">
      </form>
      <?php
         }
      } else {
         echo '<p class="empty">No products found!</p>';
      }
      ?>
   </div>

   <div class="load-more" style="margin-top: 2rem; text-align:center">
      <a href="shop.php" class="option-btn">load more</a>
   </div>

</section>




<!-- ================= ABOUT ================= -->
<section class="about">
   <div class="flex">
      <div class="image">
         <img src="images/boook.png" alt="">
      </div>
      <div class="content">
         <h3>about us</h3>
         <p>
            Welcome to Bookly, your neighbourhood bookstore. We offer a curated
            selection of books for all ages and interests.
         </p>
         <a href="about.php" class="btn">read more</a>
      </div>
   </div>
</section>

<!-- ================= CONTACT ================= -->
<section class="home-contact">
   <div class="content">
      <h3>have any questions?</h3>
      <p>Feel free to reach out to us.</p>
      <a href="contact.php" class="white-btn">contact us</a>
   </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
