<?php
// admin/asset_management.php (FINAL CLEAN VERSION)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db.php');
$page_title = "Asset Management";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}

$assets_query = $conn->query("SELECT * FROM assets ORDER BY asset_name ASC LIMIT 100");

$current_user = $_SESSION["name"] ?? 'Guest';
$current_role = $_SESSION["role"] ?? "User";
?>
<?php include('includes/header.php'); ?>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include('includes/navbar.php'); ?>
    
    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>
        
        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white rounded-lg shadow border p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">LGU Asset Inventory</h2>
                    <button onclick="openModal('addAssetModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        <i class="fa fa-plus mr-1"></i> Add New Asset
                    </button>
                </div>

                <!-- DITO DAPAT NAKALAGAY ANG MGA FILTERS, HINDI SA LABAS -->
                <!-- Example Filter Section -->
                <div class="mb-4">
                    <!-- Maaari tayong magdagdag ng search at filter dito sa hinaharap -->
                </div>
                
                <div class="overflow-x-auto rounded-lg border">
                    <?php include('partials/assets_table.php'); ?>
                </div>
            </div>
        </main>
    </div>
    
    <?php include('modals/asset_modals.php'); ?>
    <script src="js/global.js?v=<?= time() ?>"></script>
    <script src="js/asset_management.js?v=<?= time() ?>"></script>
    
</body>
</html>