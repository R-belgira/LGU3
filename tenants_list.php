<?php
// admin/tenants_list.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');
$page_title = "Tenants List";

// Access control
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}

// -----------------------------------------------------
// KUNIN ANG DATA NG LAHAT NG VENDORS AT KANILANG STALL
// -----------------------------------------------------
// Gumagamit tayo ng LEFT JOIN para makuha ang LAHAT ng vendors,
// kahit yung mga wala pang stall (kung saan ang stall details ay magiging NULL).
$tenants_query = $conn->query("
    SELECT
        u.id AS user_id,
        u.name AS tenant_name,
        u.email,
        u.status AS user_status,
        s.id AS stall_id,
        s.stall_number,
        s.location AS stall_location,
        s.type AS stall_type
    FROM users u
    LEFT JOIN stalls s ON u.id = s.assigned_to
    WHERE u.role = 'vendor' AND u.approved_by_admin = 1
    ORDER BY u.name ASC
");

// Variables para sa top bar
$current_user = htmlspecialchars($_SESSION["name"] ?? 'Guest');
$current_role = htmlspecialchars($_SESSION["role"] ?? "User");
?>

<!-- ======================= VIEW ========================= -->
<?php include('includes/header.php'); ?>
<body class="bg-gray-100 flex h-screen overflow-hidden">

    <?php include('includes/navbar.php'); ?>

    <!-- Main Content Area -->
    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>

        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white rounded-lg shadow border p-6">

                <h2 class="text-xl font-semibold text-gray-800 mb-4">Tenants List</h2>

                <!-- Table Container -->
                <div class="overflow-x-auto rounded-lg border">
                    <?php include('partials/tenants_table.php'); ?>
                </div>

            </div>
        </main>
    </div>

    <?php include('modals/tenants_list.php'); ?>
    <?php include('modals/user_management.php'); ?>

    <!-- Scripts -->
    <script src="js/global.js?v=<?= time() ?>"></script>
    <script src="js/tenants_list.js"></script> <!-- a. -->
    
    <?php include('includes/footer.php'); ?>

</body>
</html>

