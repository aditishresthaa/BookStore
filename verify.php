<?php
include 'config.php';  

if (isset($_GET['email']) && isset($_GET['verification_code'])) {
    $email = mysqli_real_escape_string($conn, $_GET['email']);  
    $verification_code = mysqli_real_escape_string($conn, $_GET['verification_code']);  

    $query = "SELECT * FROM `users` WHERE `email` = '$email' AND `verification_code` = '$verification_code'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $result_fetch = mysqli_fetch_assoc($result);

            if ($result_fetch['is_verified'] == 0) {
                $update = "UPDATE `users` SET `is_verified` = '1' WHERE `email` = '$email'";
                if (mysqli_query($conn, $update)) {
                    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                            <h2>Email Verified Successfully!</h2>
                            <p>You can now <a href='login.php'>Login here</a>.</p>
                          </div>";
                } else {
                    echo "Error updating status.";
                }
            } else {
                echo "Account already verified.";
            }
        } else {
            echo "Invalid verification link.";
        }
    }
} else {
    echo "Invalid request.";
}
?>