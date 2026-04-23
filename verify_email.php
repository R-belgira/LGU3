<?php
// actions/verify_email.php
include('../config/db.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ? AND email_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token found and not yet verified
        $user = $result->fetch_assoc();

        // Update verification status
        $update = $conn->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?");
        $update->bind_param("i", $user['id']);
        $update->execute();

        if ($update->affected_rows > 0) {
            echo "<script>alert('✅ Email verified successfully! You can now log in.'); window.location.href='../index.php';</script>";
        } else {
            echo "<script>alert('Something went wrong while updating verification status.'); window.location.href='../index.php';</script>";
        }

        $update->close();
    } else {
        echo "<script>alert('Invalid or expired verification link.'); window.location.href='../index.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php");
    exit;
}
?>
