<?php

include 'includes/session.php';

// AES encryption class
class AES {
    private $key;

    public function __construct($key) {
        $this->key = $key;
    }
    // Encrypt a string using AES
    public function encrypt($data) {
        $iv = random_bytes(16); // Generate a random IV
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted); // Concatenate IV and encrypted data
    }

    // Decrypt a string using AES
    public function decrypt($data) {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16); // Extract the IV
        $encrypted = substr($data, 16); // Extract the encrypted data
        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $iv);
    }
}
// AES key
$aesKey = 'sabin'; 
$aes = new AES($aesKey);

if (isset($_POST['add'])) {
    $firstname = $aes->encrypt($_POST['firstname']); // Encrypt firstname
    $lastname = $aes->encrypt($_POST['lastname']);   // Encrypt lastname
    $voterid = $aes->encrypt($_POST['voterid']);     // Encrypt voterid
    $dob = $aes->encrypt($_POST['dob']);             // Encrypt date of birth
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $filename = $_FILES['photo']['name'];
    if (!empty($filename)) {
        // Validate file upload
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($fileExtension, $allowedExtensions)) {
            if ($_FILES['photo']['size'] <= 2000000) { // Max size 2MB
                move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $filename);
            } else {
                $_SESSION['error'] = 'File size is too large. Max 2MB.';
            }
        } else {
            $_SESSION['error'] = 'Invalid file type. Only JPG, JPEG, and PNG allowed.';
        }
    }
    // Generate random voters ID Algorithm
    function generateVoterID() {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $length = 7; // Voter ID length
        $voterID = '';
        for ($i = 0; $i < $length; $i++) {
            $voterID .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $voterID;
    }

    // Ensure unique voter ID in database
    do {
        $voter = generateVoterID();
        $sql_check = "SELECT * FROM voters WHERE voters_id = '$voter'";
        $result_check = $conn->query($sql_check);
    } while ($result_check->num_rows > 0);

    // Insert encrypted data into the database
    $sql = "INSERT INTO voters (voters_id, password, firstname, lastname, photo, voterid, dob) 
            VALUES ('$voter', '$password', '$firstname', '$lastname', '$filename', '$voterid', '$dob')";

    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Voter added successfully';
    } else {
        $_SESSION['error'] = 'Error: ' . $conn->error . ' - Query: ' . $sql;
    }
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: voters.php');
?>
