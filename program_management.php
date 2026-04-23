<?php
// admin/program_management.php (FINAL CLEAN VERSION)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db.php');
$page_title = "Program & Project Management";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}

// Tab filtering
$valid_tabs = ['projects', 'programs'];
$tab_filter = $_GET['tab'] ?? 'projects';
if (!in_array($tab_filter, $valid_tabs)) { $tab_filter = 'projects'; }

// Itago ang mga naka-archive/cancelled
$projects_query = $conn->query("SELECT * FROM projects WHERE status != 'Archived' ORDER BY start_date DESC");
$programs_query = $conn->query("SELECT * FROM programs WHERE status != 'Cancelled' ORDER BY start_datetime DESC");

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
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center">
                        <div id="activeTabValue" data-value="<?= $tab_filter ?>"></div>
                        <div class="flex flex-wrap gap-2 border-b border-gray-300">
                            <button class="tab-btn px-4 py-2 text-sm" data-tab="projects" onclick="changeTab('projects')">Projects</button>
                            <button class="tab-btn px-4 py-2 text-sm" data-tab="programs" onclick="changeTab('programs')">Programs & Trainings</button>
                        </div>
                    </div>
                    <div>
                        <?php if ($tab_filter === 'projects'): ?>
                           <button onclick="openModal('addProjectModal')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm"><i class="fa fa-plus mr-1"></i> Add New Project</button>
                        <?php else: ?>
                           <button onclick="openModal('addProgramModal')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm"><i class="fa fa-plus mr-1"></i> Add New Program</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-lg border">
                    <?php include('partials/projects_table.php'); ?>
                    <?php include('partials/programs_table.php'); ?>
                </div>
            </div>
        </main>
    </div>
    <?php include('modals/program_modals.php'); ?>
    <script src="js/global.js?v=<?= time() ?>"></script>
    <script src="js/program_management.js?v=FINAL"></script>
</body>
</html>