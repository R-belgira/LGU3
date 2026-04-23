<?php
session_start();
include('../config/db.php');

$page_title = "User Approval";

// Access control
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}
// Allowed status tabs
$valid_tabs = ['all', 'pending', 'approved', 'rejected'];
$status_filter = $_GET['tab'] ?? 'all';

if (!in_array($status_filter, $valid_tabs)) {
    $status_filter = 'all';
}


// Pagination
$limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page   = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
$offset = ($page - 1) * $limit;

// Count total users
$where_clause = "";

switch ($status_filter) {
    case 'pending':
        $where_clause = "WHERE approved_by_admin = 0 AND role != 'admin'";
        break;

    case 'approved':
        $where_clause = "WHERE approved_by_admin = 1 AND role != 'admin'";
        break;

    case 'rejected':
        $where_clause = "WHERE approved_by_admin = 2 AND role != 'admin'";
        break;

    default:
        // all users
        $where_clause = "WHERE role != 'admin'";
}

// COUNT users
$total_query = $conn->query("SELECT COUNT(*) AS total FROM users $where_clause");
$total_users = $total_query->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// FETCH users
$users = $conn->query("
    SELECT * FROM users 
    $where_clause 
    ORDER BY created_at DESC 
    LIMIT $limit OFFSET $offset
");


$current_user = htmlspecialchars($_SESSION["name"] ?? 'Admin');
$current_role = htmlspecialchars($_SESSION["role"] ?? 'admin');
?>

<?php include('includes/header.php'); ?>
<?php include('includes/navbar.php'); ?>

<div class="flex flex-col flex-1 overflow-hidden">
<?php include('includes/top.php'); ?>

<main class="flex-1 overflow-y-auto p-3">

    <div class="bg-white rounded-lg shadow border p-6">

        <div id="activeTabValue" data-value="<?= $status_filter ?>"></div>

        <!-- ✅ Tabs (status filter via JS) -->
        <div class="flex flex-wrap gap-2 border-b border-gray-300 mb-4 pb-1">

            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600"
                    data-tab="all" onclick="changeTab('all')">
                All
            </button>

            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600"
                    data-tab="pending" onclick="changeTab('pending')">
                Pending
            </button>

            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600"
                    data-tab="approved" onclick="changeTab('approved')">
                Approved
            </button>

            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600"
                    data-tab="rejected" onclick="changeTab('rejected')">
                Rejected
            </button>

        </div>

        <!-- ✅ Search -->
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
        </div>

        <div class="overflow-x-auto rounded-lg border">
            <?php include('partials/user_approval_table.php'); ?>
        </div>

        <!-- ✅ Pagination -->
        <div class="mt-6">
            <?php include('includes/pagination.php'); ?>
        </div>

    </div>

</main>

<?php include('modals/user_approval.php'); ?>

<!-- JS -->
<script src="js/global.js?v=<?= time() ?>"></script>
<script src="js/user_approval.js"></script>

<?php include('includes/footer.php'); ?>
