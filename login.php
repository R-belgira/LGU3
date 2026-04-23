<?php
// actions/login.php
session_start();
include('../config/db.php');
include('../includes/functions.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $password = sanitize($conn, $_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // 🚫 1. Check if account is rejected
            if ($user['approved_by_admin'] == 2) {
                echo "<script>alert('❌ Your account has been rejected by the administrator.'); window.history.back();</script>";
                exit;
            }

            // 🚫 2. Check if account is pending admin approval (for vendors)
            if ($user['role'] === 'vendor' && $user['approved_by_admin'] == 0) {
                echo "<script>alert('⏳ Your account is pending admin approval. Please wait for confirmation.'); window.history.back();</script>";
                exit;
            }

            // 🚫 3. Check if email is verified
            if ($user['email_verified'] == 0) {
                echo "<script>alert('⚠️ Please verify your email before logging in.'); window.history.back();</script>";
                exit;
            }

            // 🚫 4. Check if account is active
            if (strtolower($user['status']) !== 'active') {
                echo "<script>alert('⚠️ Your account is inactive. Please contact the administrator.'); window.history.back();</script>";
                exit;
            }

            // ✅ 5. TRUST DEVICE check
            if (isset($_COOKIE['trusted_device_' . $user['id']])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['profile_picture'] = $user['profile_picture']; // <-- IDAGDAG ITO
                redirectToDashboard($user['role']);
                exit;
            }

            // ✅ 6. Generate OTP
            $otp = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
            $update = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
            $update->bind_param("ssi", $otp, $expiry, $user['id']);
            $update->execute();

            // ✅ 7. Send OTP Email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = "localgovernmentunit3@gmail.com";
                $mail->Password   = "xyhjhtbdfwiwhwnf"; // App password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom("localgovernmentunit3@gmail.com", "LGU3 Economic Enterprise System");
                $mail->addAddress($user['email'], $user['name']);
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP Code - LGU3 Economic Enterprise System';
                $mail->Body = "
                    <div style='font-family:Poppins,sans-serif;'>
                        <h2>Hello, {$user['name']}!</h2>
                        <p>Your One-Time Password (OTP) is:</p>
                        <h1 style='color:#2563eb;'>$otp</h1>
                        <p>This code will expire in 10 minutes.</p>
                    </div>
                ";
                $mail->send();

                // ✅ Save pending login session
                $_SESSION['pending_user_id'] = $user['id'];
                header("Location: ../actions/verify_otp.php");
                exit;
            } catch (Exception $e) {
                echo "<script>alert('Failed to send OTP: {$mail->ErrorInfo}'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Invalid password.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Email not found.'); window.history.back();</script>";
    }
}

function redirectToDashboard($role) {
    if ($role === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($role === 'finance') { // <-- IDINAGDAG NATIN ITO!
        header("Location: ../finance/dashboard.php");
    } elseif ($role === 'staff') {
        header("Location: ../staff/dashboard.php");
    } else { // 'vendor' ang matitira dito
        header("Location: ../vendor/dashboard.php");
    }
    exit();
}
