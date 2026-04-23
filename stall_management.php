<?php
session_start();
include('../config/db.php');
include('includes/fetch_summary_data.php');
$page_title = "Stalls Management";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}

$valid_tabs = ['stalls','requests','records','repairs'];
$tab_filter = $_GET['tab'] ?? 'stalls';
if (!in_array($tab_filter, $valid_tabs)) { $tab_filter = 'stalls'; }

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page  = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
$offset = ($page - 1) * $limit;

// Fetch Stalls
$stalls = $conn->query("SELECT s.*, u.name AS vendor_name FROM stalls s LEFT JOIN users u ON s.assigned_to = u.id ORDER BY s.created_at DESC LIMIT $limit OFFSET $offset");
$total_stalls = $conn->query("SELECT COUNT(*) AS total FROM stalls")->fetch_assoc()['total'];

// Fetch Requests
$requests = $conn->query("SELECT r.*, u.name AS vendor_name, s.stall_number, s.type FROM vendor_request r LEFT JOIN users u ON r.vendor_id = u.id LEFT JOIN stalls s ON r.stall_id = s.id ORDER BY r.request_date DESC LIMIT $limit OFFSET $offset");
$total_requests = $conn->query("SELECT COUNT(*) AS total FROM vendor_request")->fetch_assoc()['total'];

// Fetch Records
$records = $conn->query("SELECT rec.*, u.name AS vendor_name, COALESCE(rec.stall_number, s.stall_number) AS stall_number FROM records rec LEFT JOIN users u ON rec.vendor_id = u.id LEFT JOIN stalls s ON rec.stall_id = s.id ORDER BY rec.created_at DESC LIMIT $limit OFFSET $offset");
$total_records = $conn->query("SELECT COUNT(*) AS total FROM records")->fetch_assoc()['total'];

// Fetch Repair Requests (ITO YUNG DAGDAG)
$repairs = $conn->query("SELECT rr.*, s.stall_number, u.name AS vendor_name FROM stall_repair_requests rr LEFT JOIN stalls s ON rr.stall_id = s.id LEFT JOIN users u ON rr.vendor_id = u.id WHERE rr.status != 'Resolved' ORDER BY rr.request_date DESC LIMIT $limit OFFSET $offset");
$total_repairs = $conn->query("SELECT COUNT(*) AS total FROM stall_repair_requests WHERE status != 'Resolved'")->fetch_assoc()['total'];

// TUKUYIN KUNG ANONG TOTAL PAGES ANG GAGAMITIN
$total_pages = 1;
if ($tab_filter === 'stalls') { $total_pages = ceil($total_stalls / $limit); }
if ($tab_filter === 'requests') { $total_pages = ceil($total_requests / $limit); }
if ($tab_filter === 'records') { $total_pages = ceil($total_records / $limit); }
if ($tab_filter === 'repairs') { $total_pages = ceil($total_repairs / $limit); }

$current_user = $_SESSION["name"] ?? 'Guest';
$current_role = $_SESSION["role"] ?? "User";
?>

<?php include('includes/header.php'); ?>
<?php include('includes/navbar.php'); ?>
<div class="flex flex-col flex-1 overflow-hidden">
<?php include('includes/top.php'); ?>


<main class="flex-1 overflow-y-auto p-3">

  <?php include('partials/summary_cards_view.php'); ?>

    <div class="bg-white rounded-lg shadow border p-6">

       <!-- ✅ Active Tab Holder -->
        <div id="activeTabValue" data-value="<?= $tab_filter ?>"></div>

        <!-- ✅ Responsive Tabs (MUST match global.js structure) -->
        <div class="flex flex-wrap gap-2 border-b border-gray-300 mb-4 pb-1">

            <button class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap text-gray-600 hover:text-blue-600"
                    data-tab="stalls" onclick="changeTab('stalls')">
                Stalls
            </button>

            <button class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap text-gray-600 hover:text-blue-600"
                    data-tab="requests" onclick="changeTab('requests')">
                Requests
            </button>

            <button class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap text-gray-600 hover:text-blue-600"
                    data-tab="records" onclick="changeTab('records')">
                Records
            </button>

            <button class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap text-gray-600 hover:text-blue-600"
                    data-tab="repairs" onclick="changeTab('repairs')">
                Repair Request
            </button>

        </div>

        <!-- ✅ Search + Add Button -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-3">
            <div class="flex items-center gap-2 w-full sm:w-1/3">
                <input 
                    type="text" 
                    id="searchInput" 
                    onkeyup="filterTable()" 
                    placeholder="Search..."
                    class="w-full border rounded px-3 py-2 text-sm focus:ring-blue-500 focus:outline-none"
                >
            </div>

            <?php if ($tab_filter === 'stalls'): ?>
            <button onclick="openModal('addStallModal')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium w-full sm:w-auto">
                <i class="fa fa-plus mr-1"></i> Add Stall
            </button>
            <?php endif; ?>
        </div>

        <!-- ✅ Table Container -->
        <div class="overflow-x-auto rounded-lg border">

            <?php include('partials/stall_stalls_table.php'); ?>
            <?php include('partials/stall_requests_table.php'); ?>
            <?php include('partials/stall_records_table.php'); ?>
            <?php include('partials/stall_repairs_table.php'); ?>

        </div>

        <!-- ✅ Pagination -->
        <div class="mt-6">
            <?php include('includes/pagination.php'); ?>
        </div>

    </div>

</main>

<!-- ✅ MODALS -->
<?php include('modals/stall_management.php'); ?>

<!-- ✅ Scripts -->
<script src="js/global.js"></script>
<script src="js/stall_management.js"></script>

<?php include('includes/footer.php'); ?>
