<?php
// admin/slaughterhouse.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../config/db.php');
$page_title = "Slaughterhouse Management";

// Access control
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}

// Pagination
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page  = isset($_GET['page'])  ? (int)$_GET['page']  : 1;
$offset = ($page - 1) * $limit;

// ---------------------------------------------------
// KUNIN ANG DATA NG LAHAT NG ENTRIES
// ---------------------------------------------------
$entries_query = $conn->query("
    SELECT * FROM slaughterhouse_entries
    WHERE status != 'Cancelled'
    ORDER BY entry_date DESC
    LIMIT $limit OFFSET $offset
");

// Para sa pagination
$total_records_query = $conn->query("SELECT COUNT(*) as total FROM slaughterhouse_entries");
$total_records = $total_records_query->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

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

                <!-- Header with "Add" button -->
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-3">
                    <h2 class="text-xl font-semibold text-gray-800">Slaughterhouse Entries</h2>
                    <button onclick="openModal('addEntryModal')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium w-full sm:w-auto">
                        <i class="fa fa-plus mr-1"></i> Add New Entry
                    </button>
                </div>

                <!-- Table Container -->
                <div class="overflow-x-auto rounded-lg border">
                    <?php include('partials/slaughterhouse_entries_table.php'); ?>
                </div>
                
                <!-- Pagination -->
                <div class="mt-6">
                     <?php include('includes/pagination.php'); ?>
                </div>

            </div>
        </main>
    </div>

    <?php // Dito ilalagay ang modals sa hinaharap ?>
    <!-- MODALS -->
    <?php include('modals/slaughterhouse_modals.php'); ?>

    <!-- Scripts -->
    <script src="js/global.js?v=<?= time() ?>"></script>
    <script src="js/slaughterhouse.js"></script>
    <?php include('includes/footer.php'); ?>

</body>
</html>