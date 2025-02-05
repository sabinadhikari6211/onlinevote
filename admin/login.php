<?php
session_start();
include 'includes/conn.php';
// Rate limiting variables
$max_attempts = 5;  // Max login attempts before blocking
$time_window = 300; // Time window in seconds (5 minutes)
$ip_address = $_SERVER['REMOTE_ADDR']; // Get the user's IP address
// Function to check login attempts
function checkLoginAttempts($conn, $ip, $max_attempts, $time_window) {
    $query = "SELECT * FROM login_attempts WHERE ip_address = '$ip'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $attempts = $row['attempt_count'];
        $last_attempt = strtotime($row['last_attempt']);

        if ($attempts >= $max_attempts) {
            if (time() - $last_attempt < $time_window) {
                return false; // Block login attempt
            } else {
                // Reset login attempts after the time window
                $conn->query("UPDATE login_attempts SET attempt_count = 1, last_attempt = NOW() WHERE ip_address = '$ip'");
            }
        } else {
            $conn->query("UPDATE login_attempts SET attempt_count = attempt_count + 1, last_attempt = NOW() WHERE ip_address = '$ip'");
        }
    } else {
        $conn->query("INSERT INTO login_attempts (ip_address, attempt_count) VALUES ('$ip', 1)");
    }

    return true;
}
// Handle login request
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if rate limit is exceeded
    if (!checkLoginAttempts($conn, $ip_address, $max_attempts, $time_window)) {
        $_SESSION['error'] = "Too many failed login attempts. Try again in 5 minutes.";
        header("location: index.php");
        exit();
    }
    // Fetch admin details
    $sql = "SELECT * FROM admin WHERE username = '$username'";
    $query = $conn->query($sql);
    if($query->num_rows < 1){
        $_SESSION['error'] = 'Cannot find user with that username. Please try again!';
    } else {
        $row = $query->fetch_assoc();
        // Secure password verification 
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin'] = $row['id'];

            // Reset login attempts on successful login
            $conn->query("DELETE FROM login_attempts WHERE ip_address = '$ip_address'");
            
            header("location: home.php");
            exit();
        } else {
            $_SESSION['error'] = 'Incorrect password. Please try again!';
        }
    }
} else {
    $_SESSION['error'] = 'Please provide your Username and Password to continue!';
}
header('location: index.php');
?>
