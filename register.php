<?php
// actions/register.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load dependencies
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
include('../config/db.php');
include('../includes/functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize input
    $name = sanitize($conn, $_POST['name']);
    $email = sanitize($conn, $_POST['email']);
    $password = sanitize($conn, $_POST['password']);
    $confirm_password = sanitize($conn, $_POST['confirm_password']);

    // ✅ 1. Validate password match
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit;
    }

    // ✅ 2. Check if email already exists (prepared statement)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email already registered'); window.history.back();</script>";
        $stmt->close();
        exit;
    }
    $stmt->close();

    // ✅ 3. Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // ✅ 4. Create email verification token
    $verification_token = md5(uniqid(rand(), true));

    // ✅ 5. Insert new user securely
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, email_verified, approved_by_admin, verification_token)
                            VALUES (?, ?, ?, 'vendor', 0, 0, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashed, $verification_token);

    if ($stmt->execute()) {
        $stmt->close();

        // ✅ 6. Send verification email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Gmail SMTP config (⚠️ store password securely, not in plain code)
            $smtp_username = "localgovernmentunit3@gmail.com";
            $smtp_password = "xyhjhtbdfwiwhwnf"; // Replace with environment variable for production
            $sender_name   = "LGU3 Economic Enterprise System";

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_username;
            $mail->Password   = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender & recipient
            $mail->setFrom($smtp_username, $sender_name);
            $mail->addAddress($email, $name);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - LGU3 Economic Enterprise System';

            $verification_link = "http://localhost/lgu3/actions/verify_email.php?token=$verification_token";

            $mail->Body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f4f7fb;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 35px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header {
            color: #2563eb;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>Welcome to LGU3 Economic Enterprise System</div>
        <p>Hello, <b>$name</b>!</p>
        <p>Thank you for registering with the <b>LGU3 Economic Enterprise System</b>.</p>
        <p>Please confirm your email address by clicking the button below:</p>
        <div style='text-align:center;'>
            <a href='$verification_link' class='button'>Verify Email Address</a>
        </div>
        <p>If you didn’t create this account, you can safely ignore this email.</p>
        <div class='footer'>
            <p>© " . date('Y') . " LGU3 Economic Enterprise System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";


            $mail->send();
            echo "<script>alert('Registration successful! Please check your email to verify your account.'); window.location.href='../index.php';</script>";

        } catch (Exception $e) {
            echo "<script>alert('Error sending verification email: {$mail->ErrorInfo}'); window.history.back();</script>";
        }

    } else {
        echo "<script>alert('Database error: " . $conn->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
