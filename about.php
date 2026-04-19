<?php
   include 'config.php';
   session_start();

   if (isset($_SESSION['user_id'])) {
      $user_id = $_SESSION['user_id'];
   } 

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>about</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>about us</h3>
   <p> <a href="home.php">home</a> / about </p>
</div>

<section class="about">

   <div class="flex">

      <div class="image">
         <img src="images/boook.png" alt="">
      </div>

      <div class="content">
         <h3>why choose us?</h3>
         <p>We're not just a bookstore-we're a literary destination crafted with love and passion for books.</p>
         <p>Step into our cozy corner of the literary world and discover a curated collection of books that cater to every taste and interest.From bestsellers to hidden gems,classic literature to contemporary fiction and everything in between,ourshelves are stocked with treasures waiting to be explored.</p>
         <a href="contact.php" class="btn">contact us</a>
      </div>

   </div>

</section>

<section class="reviews">

   <h1 class="title">client's reviews</h1>

   <div class="box-container">

      <div class="box">
         <img src="images/photoooo1.jpg" alt="">
         <p>This bookstore has an impressive selection of books,I always find something interesting to read here. One of my favourite bookstore of all  time.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Aditi Shrestha</h3>
      </div>

      <div class="box">
         <img src="images/photoooo2.jpg" alt="">
         <p>I've been a loyal customer of this bookstore for years,and it never dissapoints.This is my favourite go-to-go place. </p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Urgen Munikar</h3>
      </div>

      <div class="box">
         <img src="images/photoooo3.jpg" alt="">
         <p>Absolutely love this bookstore! I could apend hours exploring their shelves filled with unique finds and hidden treasures.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Rubina Kunwar</h3>
      </div>
     
   </div>

</section>

<section class="authors">

   <h1 class="title">Authors</h1>

   <div class="box-container">

      <div class="box">
         <img src="images/author-1.jpeg" alt="">
         <h3>Laxmi Prasad Devkota</h3>
      </div>

      <div class="box">
         <img src="images/author-2.jpeg" alt="">
         <h3>Colleen Hoover</h3>
      </div>

      <div class="box">
         <img src="images/author-3.jpg" alt="">
         <h3>William Peter Blatty</h3>
      </div>

      <div class="box">
         <img src="images/author-4.jpeg" alt="">
         <h3>Subin Bhattarai</h3>
      </div>

      <div class="box">
         <img src="images/author-5.jpg" alt="">
         <h3>Bhagwati Charan Verma</h3>
      </div>

      <div class="box">
         <img src="images/author-6.png" alt="">
         <h3>William Shakespeare</h3>
      </div>

   </div>

</section>







<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>