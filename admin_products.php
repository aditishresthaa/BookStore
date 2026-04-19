<?php
include 'config.php';
require_once 'cart_helpers.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit();
};

// --- ADD PRODUCT ---
if(isset($_POST['add_product'])){
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = $_POST['price'];
   $details = mysqli_real_escape_string($conn, $_POST['details']);
   $category = mysqli_real_escape_string($conn, $_POST['category']);
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   $select_product_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name = '$name'") or die('query failed');

   if(mysqli_num_rows($select_product_name) > 0){
      $message[] = 'Product name already added';
   }else{
      $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, details, price, category, image) VALUES('$name', '$details', '$price', '$category', '$image')") or die('query failed');

      if($add_product_query){
         if($image_size > 2000000){
            $message[] = 'Image size is too large';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'Product added successfully!';
         }
      }else{
         $message[] = 'Product could not be added!';
      }
   }
}

// --- DELETE PRODUCT ---
if(isset($_GET['delete'])){
   $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
   $delete_row_query = mysqli_query($conn, "SELECT `id`, `name`, `price`, `image` FROM `products` WHERE id = '$delete_id' LIMIT 1") or die('query failed');
   $fetch_delete_row = mysqli_fetch_assoc($delete_row_query);
   if ($fetch_delete_row) {
      if (!empty($fetch_delete_row['image'])) {
         $img_path = 'uploaded_img/'.$fetch_delete_row['image'];
         if (is_file($img_path)) {
            @unlink($img_path);
         }
      }
      // Remove cart lines first, or MySQL blocks product delete (fk_product / RESTRICT-style FKs).
      if (cart_has_product_id_column($conn)) {
         mysqli_query($conn, "DELETE FROM `cart` WHERE `product_id` = '$delete_id'") or die('query failed: ' . mysqli_error($conn));
      } else {
         $n = mysqli_real_escape_string($conn, (string) $fetch_delete_row['name']);
         $img_esc = mysqli_real_escape_string($conn, (string) $fetch_delete_row['image']);
         $pr = (int) $fetch_delete_row['price'];
         mysqli_query($conn, "DELETE FROM `cart` WHERE `name` = '$n' AND `price` = '$pr' AND `image` = '$img_esc'") or die('query failed: ' . mysqli_error($conn));
      }
   }
   mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed: ' . mysqli_error($conn));
   header('location:admin_products.php');
   exit();
}

// --- UPDATE PRODUCT ---
if(isset($_POST['update_product'])){
   $update_p_id = $_POST['update_p_id'];
   $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
   $update_price = $_POST['update_price'];
   $update_category = mysqli_real_escape_string($conn, $_POST['update_category']);
   $update_details = mysqli_real_escape_string($conn, $_POST['update_details']);

   mysqli_query($conn, "UPDATE `products` SET name = '$update_name', price = '$update_price', category = '$update_category', details = '$update_details' WHERE id = '$update_p_id'") or die('query failed');

   $update_image = $_FILES['update_image']['name'];
   $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
   $update_image_size = $_FILES['update_image']['size'];
   $update_folder = 'uploaded_img/'.$update_image;
   $update_old_image = $_POST['update_old_image'];

   if(!empty($update_image)){
      if($update_image_size > 2000000){
         $message[] = 'Image file size is too large';
      }else{
         mysqli_query($conn, "UPDATE `products` SET image = '$update_image' WHERE id = '$update_p_id'") or die('query failed');
         move_uploaded_file($update_image_tmp_name, $update_folder);
         unlink('uploaded_img/'.$update_old_image);
      }
   }
   header('location:admin_products.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Products Management</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">
   <style>
      *{ font-family: 'Poppins', sans-serif; margin:0; padding:0; box-sizing: border-box; }
      body{ display: flex; background: #f7f7f7; }
      .sidebar { width: 230px; background: #3c1361; color: white; height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; padding: 25px; }
      .sidebar h2 { text-align: center; margin-bottom: 25px; color: #d0bdf4; font-size: 22px; }
      .sidebar a { text-decoration: none; color: white; margin: 15px 0; padding: 12px 10px; border-radius: 8px; display: flex; align-items: center; transition: 0.3s; }
      .sidebar a:hover { background: #5e239d; transform: translateX(5px); }
      .sidebar a i { margin-right: 12px; }
      .main-content { margin-left: 230px; padding: 30px; width: 100%; }
      .title { text-align: center; margin-bottom: 30px; font-size: 28px; color: #333; text-transform: uppercase; }
   </style>
</head>
<body>

   <div class="sidebar">
      <h2>Admin Panel</h2>
      <a href="admin_page.php"><i class="fas fa-chart-line"></i> Dashboard</a>
      <a href="admin_products.php"><i class="fas fa-box"></i> Products</a>
      <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
      <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
      <a href="admin_messages.php"><i class="fas fa-envelope"></i> Messages</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
   </div>

   <div class="main-content">
      <h1 class="title">Shop Products</h1>

      <section class="add-products">
         <form action="" method="post" enctype="multipart/form-data">
            <h3>Add New Book</h3>
            <input type="text" name="name" class="box" placeholder="Enter book name" required>
            <input type="number" min="0" name="price" class="box" placeholder="Enter price" required>
            <select name="category" class="box" required>
               <option value="" disabled selected>Select Category</option>
               <option value="Fiction">Fiction</option>
               <option value="Romance">Romance</option>
               <option value="Thriller">Thriller</option>
               <option value="Poetry">Poetry</option>
               <option value="Biography">Biography</option>
            </select>
            <textarea name="details" class="box" placeholder="Enter book details" required cols="30" rows="10"></textarea>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
            <input type="submit" value="Add Product" name="add_product" class="btn">
         </form>
      </section>

      <section class="show-products">
         <div class="box-container">
            <?php
               $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
               if(mysqli_num_rows($select_products) > 0){
                  while($fetch_products = mysqli_fetch_assoc($select_products)){
            ?>
            <div class="box">
               <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
               <div class="name"><?php echo $fetch_products['name']; ?></div>
               <div class="category">Category: <?php echo $fetch_products['category']; ?></div>
               <div class="price">Rs.<?php echo $fetch_products['price']; ?>/-</div>
               <a href="admin_products.php?update=<?php echo $fetch_products['id']; ?>" class="option-btn">Update</a>
               <a href="admin_products.php?delete=<?php echo $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
            </div>
            <?php
                  }
               }else{
                  echo '<p class="empty">No products added yet!</p>';
               }
            ?>
         </div>
      </section>

      <section class="edit-product-form">
         <?php
            if(isset($_GET['update'])){
               $update_id = $_GET['update'];
               $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id'") or die('query failed');
               if(mysqli_num_rows($update_query) > 0){
                  while($fetch_update = mysqli_fetch_assoc($update_query)){
         ?>
         <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['id']; ?>">
            <input type="hidden" name="update_old_image" value="<?php echo $fetch_update['image']; ?>">
            <img src="uploaded_img/<?php echo $fetch_update['image']; ?>" alt="">
            <input type="text" name="update_name" value="<?php echo $fetch_update['name']; ?>" class="box" required placeholder="Enter name">
            <input type="number" name="update_price" value="<?php echo $fetch_update['price']; ?>" min="0" class="box" required placeholder="Enter price">
            <select name="update_category" class="box" required>
               <option value="<?php echo $fetch_update['category']; ?>" selected><?php echo $fetch_update['category']; ?></option>
               <option value="Fiction">Fiction</option>
               <option value="Romance">Romance</option>
               <option value="Thriller">Thriller</option>
               <option value="Poetry">Poetry</option>
               <option value="Biography">Biography</option>
            </select>
            <textarea name="update_details" class="box" required placeholder="Enter details" cols="30" rows="10"><?php echo $fetch_update['details']; ?></textarea>
            <input type="file" class="box" name="update_image" accept="image/jpg, image/jpeg, image/png">
            <input type="submit" value="Update" name="update_product" class="btn">
            <input type="reset" value="Cancel" id="close-update" class="option-btn" onclick="window.location.href='admin_products.php'">
         </form>
         <?php
                  }
               }
            }else{
               echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
            }
         ?>
      </section>
   </div>

   <script src="js/admin_script.js"></script>
</body>
</html>