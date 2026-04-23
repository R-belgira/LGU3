<?php
session_start();
include('../config/db.php');
$page_title = "User Management";

// Access control
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}

// Allowed tabs
$valid_roles = ['all','admin','staff','vendor'];
$role_filter = $_GET['tab'] ?? 'all';
if (!in_array($role_filter, $valid_roles)) {
    $role_filter = 'all';
}

// Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page  = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
$offset = ($page - 1) * $limit;

// ---------- FETCHING USERS ----------
$where_clause = ($role_filter === 'all') ? "" : "WHERE role = '$role_filter'";

// Count total users
$total_query = $conn->query("SELECT COUNT(*) AS total FROM users $where_clause");
$total_users = $total_query->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Fetch visible users
$users = $conn->query("
    SELECT * FROM users
    $where_clause " . (empty($where_clause) ? "WHERE" : "AND") . "
    status != 'pending'
    AND approved_by_admin != 2
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
");

$current_user = htmlspecialchars($_SESSION["name"] ?? 'Guest');
$current_role = htmlspecialchars($_SESSION["role"] ?? "User");
?>

<?php include('includes/header.php'); ?>
<?php include('includes/navbar.php'); ?>
<div class="flex flex-col flex-1 overflow-hidden">
<?php include('includes/top.php'); ?>
<main class="flex-1 overflow-y-auto p-3">

    <div class="bg-white rounded-lg shadow border p-6">

        <!-- ✅ Active Tab Holder -->
        <div id="activeTabValue" data-value="<?= $role_filter ?>"></div>

        <!-- ✅ Responsive Tabs -->
        <div class="flex flex-wrap gap-2 border-b border-gray-300 mb-4 pb-1">

            <button class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap text-gray-600 hover:text-blue-600"
                    data-tab="all" onclick="changeTab('all')">
                All
            </button>

            <button class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap text-gray-600 hover:text-blue-600"
                    data-tab="admin" onclick="changeTab('admin')">
                Admin
            </button>

            <button class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap text-gray-600 hover:text-blue-600"
                    data-tab="staff" onclick="changeTab('staff')">
                Staff
            </button>

            <button class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap text-gray-600 hover:text-blue-600"
                    data-tab="vendor" onclick="changeTab('vendor')">
                Vendor
            </button>

        </div>

        <!-- ✅ Search + Add Button -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-3">

            <div class="flex items-center gap-2 w-full sm:w-1/3">
                <input 
                    type="text" 
                    id="searchInput" 
                    onkeyup="filterTable()" 
                    placeholder="Search by name or email..."
                    class="w-full border rounded px-3 py-2 text-sm focus:ring-blue-500 focus:outline-none"
                >
            </div>

            <button onclick="openModal('addUserModal')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium w-full sm:w-auto">
                <i class="fa fa-plus mr-1"></i> Add User
            </button>
        </div>

        <!-- ✅ Responsive Table Wrapper -->
        <div class="overflow-x-auto rounded-lg border">
            <?php include('partials/user_management_table.php'); ?>
        </div>

        <!-- ✅ Responsive Pagination Wrapper -->
        <div class="mt-6">
            <?php include('includes/pagination.php'); ?>
        </div>

    </div>

</main>

<!-- MODALS -->
<?php include('modals/user_management.php'); ?>

<!-- JS -->
<script src="js/global.js?v=<?= time() ?>"></script>
<script src="js/user_management.js"></script>

<?php include('includes/footer.php'); ?>
