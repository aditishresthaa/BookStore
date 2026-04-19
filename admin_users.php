<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `users` WHERE id = '$delete_id'") or die('query failed');
   header('location:admin_users.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>users</title>

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

      .users{
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

<body>
<!-- <?php include 'admin_header.php'; ?> -->
<div class="sidebar">
      <h2>Admin Panel</h2>
      <a href="admin_page.php"><i class="fas fa-chart-line"></i> Dashboard</a>
      <a href="admin_products.php"><i class="fas fa-box"></i> Products</a>
      <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
      <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
      <a href="admin_contacts.php"><i class="fas fa-envelope"></i> Messages</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
   </div>
<section class="users">

   <h1 class="title"> user accounts </h1>

   <div class="box-container">
      <?php
         $select_users = mysqli_query($conn, "SELECT * FROM `users`") or die('query failed');
         while($fetch_users = mysqli_fetch_assoc($select_users)){
      ?>
      <div class="box">
         <p> user id : <span><?php echo $fetch_users['id']; ?></span> </p>
         <p> username : <span><?php echo $fetch_users['name']; ?></span> </p>
         <p> email : <span><?php echo $fetch_users['email']; ?></span> </p>
         <p> user type : <span style="color:<?php if($fetch_users['user_type'] == 'admin'){ echo 'var(--orange)'; } ?>"><?php echo $fetch_users['user_type']; ?></span> </p>
         <a href="admin_users.php?delete=<?php echo $fetch_users['id']; ?>" onclick="return confirm('delete this user?');" class="delete-btn">delete user</a>
      </div>
      <?php
         };
      ?>
   </div>

</section>









<!-- custom admin js file link  -->
<script src="js/admin_script.js"></script>

</body>
</html>