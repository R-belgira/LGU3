<?php
session_start();
include('../config/db.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

if (!isset($_SESSION['pending_user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['pending_user_id'];

// ✅ Handle OTP Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];
    $trust_device = isset($_POST['trust_device']);

    $stmt = $conn->prepare("SELECT otp_code, otp_expiry, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['otp_code'] == $entered_otp && strtotime($user['otp_expiry']) > time()) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_picture'] = $user['profile_picture']; // <-- IDAGDAG DIN DITO

        $clear = $conn->prepare("UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE id = ?");
        $clear->bind_param("i", $user_id);
        $clear->execute();

        if ($trust_device) {
            setcookie('trusted_device_' . $user_id, '1', time() + (86400 * 30), "/", "", false, true);
        }

        unset($_SESSION['pending_user_id']);
        unset($_SESSION['last_otp_sent']);

        if ($user['role'] === 'admin') header("Location: ../admin/dashboard.php");
        elseif ($user['role'] === 'staff') header("Location: ../staff/dashboard.php");
        else header("Location: ../vendor/dashboard.php");
        exit;
    } else {
        echo "<script>alert('Invalid or expired OTP.'); window.history.back();</script>";
        exit;
    }
}

// ✅ Handle Resend OTP (limited to 2 minutes)
if (isset($_POST['resend_otp'])) {
    if (isset($_SESSION['last_otp_sent']) && time() - $_SESSION['last_otp_sent'] < 120) {
        $remaining = 120 - (time() - $_SESSION['last_otp_sent']);
        echo "<script>alert('Please wait {$remaining} seconds before requesting a new OTP.'); window.history.back();</script>";
        exit;
    }

    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $update = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
    $update->bind_param("ssi", $otp, $expiry, $user_id);
    $update->execute();

    $_SESSION['last_otp_sent'] = time();

    $userQuery = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $user = $userQuery->get_result()->fetch_assoc();

    // Send new OTP
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "localgovernmentunit3@gmail.com";
        $mail->Password = "xyhjhtbdfwiwhwnf";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("localgovernmentunit3@gmail.com", "LGU3 Economic Enterprise System");
        $mail->addAddress($user['email'], $user['name']);
        $mail->isHTML(true);
        $mail->Subject = 'Your One-Time Password (OTP)';

        // 📨 Nicer email design + smaller OTP display
        $mail->Body = "
<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
body {font-family:'Poppins',Arial,sans-serif;background-color:#f4f7fb;color:#333;margin:0;padding:0;}
.container {max-width:500px;margin:40px auto;background:#ffffff;border-radius:10px;padding:35px 40px;
box-shadow:0 4px 15px rgba(0,0,0,0.1);}
.header {color:#2563eb;font-size:22px;font-weight:700;margin-bottom:20px;text-align:center;}
.otp-box {text-align:center;background-color:#2563eb;color:#fff;font-size:22px;font-weight:600;
letter-spacing:3px;padding:10px 0;border-radius:8px;margin:25px auto;width:180px;}
p {font-size:15px;line-height:1.6;margin:10px 0;}
.footer {margin-top:30px;font-size:13px;color:#777;text-align:center;}
</style>
</head>
<body>
<div class='container'>
    <div class='header'>Your One-Time Password (OTP)</div>
    <p>Hello, <b>{$user['name']}</b>!</p>
    <p>We received a login attempt for your account on the <b>LGU3 Economic Enterprise System</b>.</p>
    <p>Use the following One-Time Password (OTP) to complete your login:</p>
    <div class='otp-box'>$otp</div>
    <p>This code will expire in <b>10 minutes</b>. Please do not share it with anyone for your security.</p>
    <div class='footer'>
        <p>© " . date('Y') . " LGU3 Economic Enterprise System. All rights reserved.</p>
    </div>
</div>
</body>
</html>";

        $mail->send();
        echo "<script>alert('A new OTP has been sent to your email.');</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error sending OTP: {$mail->ErrorInfo}');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify OTP — LGU3 Economic Enterprise</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../assets/css/index.css">
<style>
.watermark-centered {
    position: absolute;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0.05;
    pointer-events: none;
    z-index: 5;
}
.watermark-centered img {
    width: 720px;
    height: auto;
}
</style>
<script>
window.onload = () => {
  const resendBtn = document.getElementById('resendBtn');
  const timer = document.getElementById('timer');

  // PHP value: last OTP sent timestamp (or 0 if not set)
  const lastSent = <?php echo isset($_SESSION['last_otp_sent']) ? $_SESSION['last_otp_sent'] : 0; ?>;
  const now = Math.floor(Date.now() / 1000);
  let remaining = 120 - (now - lastSent);

  function updateTimer() {
    if (remaining > 0) {
      resendBtn.disabled = true;
      timer.innerText = 'You can resend a new OTP in ' + remaining + 's';
      remaining--;
    } else {
      resendBtn.disabled = false;
      timer.innerText = '';
      clearInterval(countdown);
    }
  }

  if (remaining > 0) {
    updateTimer();
    var countdown = setInterval(updateTimer, 1000);
  } else {
    resendBtn.disabled = false;
    timer.innerText = '';
  }
};
</script>
</head>
<body class="antialiased font-sans relative min-h-screen overflow-hidden">

  <!-- Background layers -->
  <div class="bg-layer"></div>
  <div class="light-band"></div>
  <div class="light-band band2"></div>
  <div class="particles"></div>

  <!-- Centered watermark -->
  <div class="watermark-centered">
    <img src="../assets/img/logo.png" alt="LGU3 Logo" />
  </div>

  <!-- MAIN CONTENT -->
  <div class="relative min-h-screen flex items-center justify-center z-20">
    <div class="w-full max-w-md glass rounded-2xl p-1">
      <div class="card-inner text-center">

        <img src="../assets/img/logo.png" alt="LGU Logo" class="w-20 h-20 object-contain mx-auto mb-3 drop-shadow-lg" />
        <h2 class="text-2xl font-bold text-slate-900">Two-Step Verification</h2>
        <p class="text-sm text-slate-600 mt-1">Enter the 6-digit code sent to your email</p>

        <form class="mt-6 space-y-4" method="POST">
          <input type="text" name="otp" maxlength="6" required placeholder="Enter 6-digit OTP"
                 class="w-full text-center tracking-widest text-lg rounded-xl border border-slate-200 py-3 input-focus" />

          <div class="flex items-center justify-center gap-2 text-sm mt-2">
            <input type="checkbox" id="trust_device" name="trust_device" class="rounded border-slate-300" />
            <label for="trust_device" class="text-slate-700">Trust this device for 30 days</label>
          </div>

          <button type="submit" name="verify_otp"
            class="w-full rounded-xl py-3 text-white font-semibold btn-prim transition transform">
            Verify OTP
          </button>

          <div class="mt-4">
            <p id="timer" class="text-sm text-slate-500"></p>
            <button id="resendBtn" type="submit" name="resend_otp"
              class="w-full rounded-xl py-3 bg-slate-400 text-white font-semibold transition disabled:opacity-60">
              Resend OTP
            </button>
          </div>
        </form>

        <p class="text-xs text-slate-500 mt-6">
          Didn’t receive the code? Check your spam folder or wait 2 minutes to resend.
        </p>
      </div>
    </div>

    <footer class="absolute bottom-6 left-0 right-0 text-center z-30">
      <div class="text-xs text-white/80">© <span id="year"></span> LGU3 Local Economic Enterprise • All Rights Reserved</div>
    </footer>
  </div>

  <script>document.getElementById('year').textContent = new Date().getFullYear();</script>
</body>
</html>
