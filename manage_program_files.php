<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db.php');

if (!isset($_GET['program_id'])) die("Missing Program ID.");
$program_id = (int)$_GET['program_id'];

// Kunin ang info ng program
$program = $conn->query("SELECT program_name FROM programs WHERE id = $program_id")->fetch_assoc();
$page_title = "Files for: " . ($program['program_name'] ?? 'Program');

// Kunin ang mga naka-upload na files
$files_query = $conn->query("SELECT * FROM program_files WHERE program_id = $program_id ORDER BY uploaded_at DESC");

$current_user = $_SESSION["name"] ?? 'Guest';
$current_role = $_SESSION["role"] ?? "User";
?>
<?php include('includes/header.php'); ?>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <?php include('includes/navbar.php'); ?>
    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>
        <main class="flex-1 overflow-y-auto p-6">
            <a href="program_management.php?tab=programs" class="text-blue-600 hover:underline text-sm mb-4 inline-block">&larr; Back to Programs</a>
            <div class="bg-white rounded-lg shadow border p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4"><?= htmlspecialchars($page_title) ?></h2>
                <!-- Upload Form -->
                <div class="bg-gray-50 p-4 rounded-lg border mb-6">
                    <h3 class="font-semibold mb-2">Upload New File</h3>
                    <form action="actions/upload_file.php" method="POST" enctype="multipart/form-data" class="flex items-end gap-4">
                        <input type="hidden" name="program_id" value="<?= $program_id ?>">
                        <div class="flex-1">
                            <label class="text-sm">Select File (PDF, DOCX, JPG, PNG)</label>
                            <input type="file" name="program_file" required class="mt-1 w-full p-2 border rounded-md text-sm">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg h-10">Upload</button>
                    </form>
                </div>

                <!-- Files List -->
                <h3 class="font-semibold mb-2">Uploaded Files</h3>
                <div class="space-y-2">
                    <?php if ($files_query->num_rows === 0): ?>
                        <p class="text-gray-500">No files uploaded for this program yet.</p>
                    <?php else: ?>
                        <?php while($file = $files_query->fetch_assoc()): ?>
                            <div class="p-2 border rounded-md flex justify-between items-center hover:bg-gray-50">
        <!-- File link (wala tayong binago dito) -->
        <a href="../<?= htmlspecialchars($file['file_path']) ?>" target="_blank" class="text-blue-600 font-medium flex items-center">
            <i class="fa fa-file-alt mr-2"></i>
            <div>
                <div><?= htmlspecialchars($file['file_name']) ?></div>
                <div class="text-xs text-gray-500 font-normal">Uploaded by <?= htmlspecialchars($file['uploaded_by']) ?></div>
            </div>
        </a>
        
        <!-- BAGONG DELETE BUTTON -->
        <a href="actions/delete_program_file.php?file_id=<?= $file['id'] ?>"
           onclick="return confirm('Are you sure you want to permanently delete this file?');"
           class="text-red-500 hover:text-red-700 px-3 py-1 text-sm" title="Delete File">
            <i class="fa fa-trash"></i>
        </a>
    </div>
<?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>