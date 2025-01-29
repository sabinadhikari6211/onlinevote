<?php
include 'includes/session.php';

if (isset($_POST['edit'])) {
    // Validate inputs
    $id = intval($_POST['id']); // Convert ID to an integer for security
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $password = trim($_POST['password']);

    // Retrieve existing voter data
    $sql = "SELECT * FROM voters WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // Bind $id as an integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if password is being updated
        if (empty($password)) {
            $hashedPassword = $row['password']; // Keep the old password
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the new password
        }

        // Update voter data securely
        $updateSql = "UPDATE voters SET firstname = ?, lastname = ?, password = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssi", $firstname, $lastname, $hashedPassword, $id);

        if ($updateStmt->execute()) {
            $_SESSION['success'] = 'Voter updated successfully';
        } else {
            $_SESSION['error'] = $conn->error;
        }
    } else {
        $_SESSION['error'] = 'Voter not found';
    }
} else {
    $_SESSION['error'] = 'Fill up the edit form first';
}

header('location: voters.php');
?>
