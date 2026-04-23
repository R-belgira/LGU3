<?php
// admin/manage_participants.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db.php');

if (!isset($_GET['program_id']) || empty($_GET['program_id'])) {
    die("Error: No Program ID specified.");
}
$program_id = (int)$_GET['program_id'];

// Kunin ang info ng program
$prog_stmt = $conn->prepare("SELECT program_name FROM programs WHERE id = ?");
$prog_stmt->bind_param("i", $program_id);
$prog_stmt->execute();
$program = $prog_stmt->get_result()->fetch_assoc();
$page_title = "Participants for: " . ($program['program_name'] ?? 'Unknown Program');

// Kunin ang listahan ng participants
$participants_query = $conn->prepare("SELECT * FROM program_participants WHERE program_id = ? ORDER BY participant_name ASC");
$participants_query->bind_param("i", $program_id);
$participants_query->execute();
$participants = $participants_query->get_result();

$current_user = $_SESSION["name"] ?? 'Guest';
$current_role = $_SESSION["role"] ?? "User";
?>
<?php include('includes/header.php'); ?>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <?php include('includes/navbar.php'); ?>
    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>
        <main class="flex-1 overflow-y-auto p-6">
            
            <a href="program_management.php?tab=programs" class="text-blue-600 hover:underline text-sm mb-4 inline-block">&larr; Back to Programs List</a>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Kaliwa: Add Participant Form -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-lg shadow border p-6">
                        <h3 class="font-semibold text-lg mb-4">Add New Participant</h3>
                        <form action="actions/add_participant.php" method="POST" class="space-y-4">
                            <input type="hidden" name="program_id" value="<?= $program_id ?>">
                            <div>
                                <label class="block text-sm">Full Name</label>
                                <input type="text" name="participant_name" required class="mt-1 w-full p-2 border rounded-md">
                            </div>
                            <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-lg">Add Participant</button>
                        </form>
                    </div>
                </div>
                <!-- Kanan: Participants List -->
                <div class="md:col-span-2">
                    <div class="bg-white rounded-lg shadow border p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4"><?= htmlspecialchars($page_title) ?></h2>
                        <div class="overflow-x-auto rounded-lg border">
                            <?php include('partials/participants_table.php'); ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
    <script src="js/global.js?v=<?= time() ?>"></script>
</body>
</html>