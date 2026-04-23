<?php
// admin/edit_profile.php

// 1. Umpisahan ang session
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 2. Isama ang database (dito nabubuo ang $conn)
include('../config/db.php');

// 3. Ngayon, pwede na tayong magpatuloy sa iba
$page_title = "Edit My Profile";

// Security: Tiyakin na may naka-log in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); exit();
}
$user_id = (int)$_SESSION['user_id']; // Cast to integer for security

// Ngayon, LIGTAS nang gamitin ang $conn
$stmt = $conn->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
if (!$stmt) { die("Prepare failed: " . $conn->error); } // Magandang maglagay ng error check
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();


// Variables para sa layout
$current_user = htmlspecialchars($_SESSION["name"] ?? 'User');
$current_role = htmlspecialchars($_SESSION["role"] ?? 'Unknown');
?>

<?php include('includes/header.php'); ?>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <?php include('includes/navbar.php'); ?>
    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>
        <main class="flex-1 overflow-y-auto p-6">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow border p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Edit Profile</h2>

                    <!-- GINAWA NATING `enctype` DITO PARA TANGGAPIN ANG FILES -->
                    <form action="actions/update_profile.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="flex flex-col md:flex-row gap-8">
                            <!-- KALIWA: Profile Picture Upload -->
                            <div class="text-center">
                                <?php 
                                    // Kung may picture, ipakita ito. Kung wala, placeholder.
                                    $avatar = !empty($user['profile_picture']) ? '../' . $user['profile_picture'] : '../assets/img/default_avatar.png';
                                ?>
                                <img id="profilePreview" src="<?= $avatar ?>" class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-gray-200">
                                <label for="profile_picture" class="cursor-pointer text-blue-600 hover:underline mt-2 inline-block text-sm">
                                    Change Picture
                                </label>
                                <input type="file" name="profile_picture" id="profile_picture" class="hidden" onchange="previewImage(event)">
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF up to 2MB</p>
                            </div>

                            <!-- KANAN: Form Fields -->
                            <div class="flex-1 space-y-4">
                                <div>
                                    <label for="name">Full Name</label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="mt-1 w-full p-2 border rounded-md">
                                </div>
                                <div>
                                    <label>Email Address</label>
                                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly class="mt-1 w-full p-2 border bg-gray-100 rounded-md">
                                </div>
                                
                                <div class="border-t pt-4 space-y-3">
                                  <h3 class="font-semibold">Change Password</h3>
                                  <div>
                                      <label class="text-sm">Current Password</label>
                                      <input type="password" name="current_password" class="mt-1 w-full p-2 border rounded-md">
                                  </div>
                                  <div>
                                      <label class="text-sm">New Password</label>
                                      <input type="password" name="new_password" class="mt-1 w-full p-2 border rounded-md">
                                  </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-3 mt-6 border-t pt-4">
                            <a href="dashboard.php" class="px-4 py-2 border rounded-lg">Cancel</a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript para sa live image preview -->
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('profilePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>