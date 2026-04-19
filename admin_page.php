

<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
   header('location:login.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Panel</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
         margin: 500px;
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

   <div class="sidebar">
      <h2>Admin Panel</h2>
      <a href="admin_page.php"><i class="fas fa-chart-line"></i> Dashboard</a>
      <a href="admin_products.php"><i class="fas fa-box"></i> Products</a>
      <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
      <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
      <a href="admin_contacts.php"><i class="fas fa-envelope"></i> Messages</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
   </div>

   <div class="main-content">
      <h1 class="title">DASHBOARD</h1>

      <div class="box-container">

         <?php
            $total_pendings = 0;
            $select_pending = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE payment_status = 'pending'");
            while($row = mysqli_fetch_assoc($select_pending)){
               $total_pendings += $row['total_price'];
            }

            $total_completed = 0;
            $select_completed = mysqli_query($conn, "SELECT total_price FROM `orders` WHERE payment_status = 'completed'");
            while($row = mysqli_fetch_assoc($select_completed)){
               $total_completed += $row['total_price'];
            }

            $orders = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `orders`"));
            $products = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `products`"));
            $users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `users` WHERE user_type='user'"));
            $admins = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `users` WHERE user_type='admin'"));
            $accounts = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `users`"));
            $messages = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `message`"));
         ?>

         <div class="box">
            <h3>Rs.<?php echo $total_pendings; ?>/-</h3>
            <p>Total Pendings</p>
         </div>
         <div class="box">
            <h3>Rs.<?php echo $total_completed; ?>/-</h3>
            <p>Completed Payments</p>
         </div>
         <div class="box">
            <h3><?php echo $orders; ?></h3>
            <p>Orders Placed</p>
         </div>
         <div class="box">
            <h3><?php echo $products; ?></h3>
            <p>Products Added</p>
         </div>
         <div class="box">
            <h3><?php echo $users; ?></h3>
            <p>Normal Users</p>
         </div>
         <div class="box">
            <h3><?php echo $admins; ?></h3>
            <p>Admin Users</p>
         </div>
         <div class="box">
            <h3><?php echo $accounts; ?></h3>
            <p>Total Accounts</p>
         </div>
         <div class="box">
            <h3><?php echo $messages; ?></h3>
            <p>New Messages</p>
         </div>
      </div>

   

   <!-- Charts Section -->
<div style="margin-top: 50px;">
   <h2 style="text-align:center; margin-bottom: 30px;">Statistics Overview</h2>

   <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 40px;">
      <!-- Pie Chart 1: Payments -->
      <div style="width: 300px;">
         <h3 style="text-align:center;">Payments (Pending vs Completed)</h3>
         <canvas id="paymentChart"></canvas>
      </div>

      <!-- Pie Chart 2: Users -->
      <div style="width: 300px;">
         <h3 style="text-align:center;">Users (Admin vs Normal)</h3>
         <canvas id="userChart"></canvas>
      </div>

      <!-- Bar Chart: Orders vs Products -->
      <div style="width: 400px;">
         <h3 style="text-align:center;">Orders vs Products</h3>
         <canvas id="orderProductChart"></canvas>
      </div>
   </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
   // === Pie Chart 1: Payment Stats ===
   const paymentCtx = document.getElementById('paymentChart');
   new Chart(paymentCtx, {
      type: 'pie',
      data: {
         labels: ['Pending Payments', 'Completed Payments'],
         datasets: [{
            data: [<?php echo $total_pendings; ?>, <?php echo $total_completed; ?>],
            backgroundColor: ['#ff9f1c', '#2ec4b6']
         }]
      },
      options: {
         responsive: true,
         plugins: {
            legend: { position: 'bottom' }
         }
      }
   });

   // === Pie Chart 2: User Types ===
   const userCtx = document.getElementById('userChart');
   new Chart(userCtx, {
      type: 'pie',
      data: {
         labels: ['Normal Users', 'Admin Users'],
         datasets: [{
            data: [<?php echo $users; ?>, <?php echo $admins; ?>],
            backgroundColor: ['#8338ec', '#ff006e']
         }]
      },
      options: {
         responsive: true,
         plugins: {
            legend: { position: 'bottom' }
         }
      }
   });

   // === Bar Chart: Orders vs Products ===
   const orderProductCtx = document.getElementById('orderProductChart');
   new Chart(orderProductCtx, {
      type: 'bar',
      data: {
         labels: ['Orders', 'Products'],
         datasets: [{
            label: 'Count',
            data: [<?php echo $orders; ?>, <?php echo $products; ?>],
            backgroundColor: ['#3a86ff', '#ffbe0b']
         }]
      },
      options: {
         responsive: true,
         scales: {
            y: { beginAtZero: true }
         },
         plugins: {
            legend: { display: false }
         }
      }
   });
</script>
</div>

</body>
</html>
