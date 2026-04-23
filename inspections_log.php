<?php
// admin/inspections_log.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db.php');
$page_title = "Slaughterhouse Inspections Log";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}

// Query para kunin ang LAHAT ng inspeksyon, kasama ang detalye ng entry
$inspections_query = $conn->query("
    SELECT 
        i.*, 
        e.owner_name, 
        e.animal_type
    FROM slaughterhouse_inspections i
    JOIN slaughterhouse_entries e ON i.entry_id = e.id
    ORDER BY i.inspection_date DESC
    LIMIT 100 -- Limit muna natin para hindi bumigat
");

$current_user = htmlspecialchars($_SESSION["name"] ?? 'Guest');
$current_role = htmlspecialchars($_SESSION["role"] ?? "User");
?>
<?php include('includes/header.php'); ?>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <?php include('includes/navbar.php'); ?>
    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>
        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white rounded-lg shadow border p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Inspections Log</h2>
                <div class="overflow-x-auto rounded-lg border">
                    <?php include('partials/inspections_log_table.php'); ?>
                </div>
            </div>
        </main>
    </div>
    <script src="js/global.js?v=<?= time() ?>"></script>
    <?php include('includes/footer.php'); ?>
</body>
</html>