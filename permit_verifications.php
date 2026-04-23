<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db.php');
$page_title = "Permit Verifications";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php"); exit();
}

// Palitan ang lumang query nito:
    $apps_query = $conn->query("
    SELECT ba.*, u.name as vendor_name
    FROM business_applications ba
    JOIN users u ON ba.vendor_id = u.id
    ORDER BY ba.submitted_at DESC
");

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
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Pending Business Permit Applications</h2>
                <div class="overflow-x-auto rounded-lg border">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-600 text-white"><tr>
                            <th class="py-2 px-4 text-left">Date</th>
                            <th class="py-2 px-4 text-left">Business Name</th>
                            <th class="py-2 px-4 text-left">Applicant (Vendor)</th>
                            <th class="py-2 px-4 text-left">Status</th>
                            <th class="py-2 px-4 text-center">Actions</th>
                        </tr></thead>
                        <tbody>
                            <?php if ($apps_query->num_rows === 0): ?>
                                <tr><td colspan="5" class="text-center py-4">No applications submitted yet.</td></tr>
                            <?php else: ?>
                                <?php while ($app = $apps_query->fetch_assoc()): ?>
    <?php
        // LOGIC PARA SA KULAY NG BADGE
        $status = $app['status'];
        $status_color = 'bg-gray-100 text-gray-800'; // Default color
        
        switch ($status) {
            case 'Pending':
                $status_color = 'bg-yellow-100 text-yellow-800';
                break;
            case 'Approved':
                $status_color = 'bg-green-100 text-green-800';
                break;
            case 'Rejected':
                $status_color = 'bg-red-100 text-red-800';
                break;
        }
    ?>
    <tr class="border-b hover:bg-gray-50">
        <td class="py-2 px-4"><?= date('M d, Y', strtotime($app['submitted_at'])) ?></td>
        <td class="py-2 px-4 font-medium"><?= htmlspecialchars($app['business_name']) ?></td>
        <td class="py-2 px-4"><?= htmlspecialchars($app['vendor_name']) ?></td>
        
        <!-- ITO ANG INAYOS NA STATUS COLUMN -->
        <td class="py-2 px-4">
            <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $status_color ?>">
                <?= htmlspecialchars($app['status']) ?>
            </span>
        </td>
        
        <td class="py-2 px-4 text-center">
            <a href="view_application.php?id=<?= $app['id'] ?>" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-xs hover:bg-blue-200">
                Review
            </a>
        </td>
    </tr>
<?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script src="js/global.js?v=<?= time() ?>"></script>
</body>
</html>