<?php
// admin/reports_slaughterhouse.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db.php');
$page_title = "Slaughterhouse Reports";

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../index.php");
    exit();
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

$report_data = false;
$total_animals_this_month = 0;

$stmt = $conn->prepare("
    SELECT animal_type, SUM(animal_count) AS total_count
    FROM slaughterhouse_entries
    WHERE entry_date BETWEEN ? AND ? AND status IN ('Passed', 'Processed', 'Released') 
    GROUP BY animal_type ORDER BY total_count DESC
");
if($stmt){
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $report_data = $stmt->get_result();
}

$current_user = $_SESSION["name"] ?? 'Guest';
$current_role = $_SESSION["role"] ?? "User";
?>

<?php include('includes/header.php'); ?>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include('includes/navbar.php'); ?>
    
    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>
        
        <main class="flex-1 overflow-y-auto p-6">
            <div id="report-page-container" class="bg-white rounded-lg shadow-md border p-6">
                
                <!-- FILTER FORM -->
                <div class="no-print mb-6">
                    <form method="GET" class="flex flex-col md:flex-row md:items-end gap-4 bg-gray-50 p-4 rounded-lg border">
                         <div class="flex-grow">
                            <h2 class="text-xl font-semibold text-gray-800">Generate Slaughterhouse Report</h2>
                            <p class="text-sm text-gray-500">Select a date range to generate a summary.</p>
                         </div>
                         <div class="flex gap-2">
                            <div>
                                <label for="start_date" class="text-sm font-medium text-gray-600">From</label>
                                <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>" class="mt-1 w-full p-2 border rounded-md">
                            </div>
                            <div>
                                <label for="end_date" class="text-sm font-medium text-gray-600">To</label>
                                <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>" class="mt-1 w-full p-2 border rounded-md">
                            </div>
                         </div>
                         <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md h-10 hover:bg-blue-700 transition">Generate</button>
                    </form>
                </div>

                <!-- REPORT TABLE AREA -->
                <div id="printable-content">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                             <h3 class="text-lg font-bold text-gray-900">Monthly Slaughterhouse Summary</h3>
                             <p class="text-sm text-gray-600">Period: <?= date('F d, Y', strtotime($start_date)) ?> to <?= date('F d, Y', strtotime($end_date)) ?></p>
                        </div>
                        <div class="flex gap-2 no-print">
                            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 flex items-center gap-2">
                                <i class="fa fa-print"></i> Print
                            </button>
                            <!-- TAWAGIN ANG OPEN MODAL FUNCTION -->
                            <button type="button" onclick="openSlaughterModal()" class="px-4 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700 flex items-center gap-2 transition-transform active:scale-95">
                                <i class="fas fa-file-pdf"></i> Export to PDF
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-blue-600 text-white font-bold uppercase tracking-wider">
                                <tr>
                                    <th class="py-3 px-4">Animal Type</th>
                                    <th class="py-3 px-4 text-right">Total Head Count</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y bg-white">
                                <?php if (!$report_data || $report_data->num_rows === 0): ?>
                                    <tr><td colspan="2" class="text-center py-6 text-gray-500">No transactions found for this period.</td></tr>
                                <?php else: ?>
                                    <?php while ($row = $report_data->fetch_assoc()): ?>
                                        <?php $total_animals_this_month += $row['total_count']; ?>
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="py-3 px-4 font-medium text-gray-800 uppercase"><?= htmlspecialchars($row['animal_type']) ?></td>
                                            <td class="py-3 px-4 text-right font-bold text-gray-900 text-lg"><?= number_format($row['total_count']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="bg-blue-50 font-bold border-t-2">
                                <tr>
                                    <td class="py-4 px-4 text-right text-gray-600">GRAND TOTAL:</td>
                                    <td class="py-4 px-4 text-right text-2xl text-blue-700 font-black"><?= number_format($total_animals_this_month) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- PURE TAILWIND MODAL (Siguradong center ito at sa ibabaw ng lahat) -->
    <div id="slaughterAuthModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all animate-fade-in">
            <div class="bg-slate-900 p-4 flex justify-between items-center text-white border-b border-white/10">
                <h5 class="text-sm font-bold uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-shield-alt text-yellow-400"></i> Verification Required
                </h5>
                <button onclick="closeSlaughterModal()" class="text-gray-400 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6">
                <div class="mb-4 text-center">
                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>
                    <p class="text-sm text-gray-600">Please enter Admin Password to authorize this export.</p>
                </div>
                
                <input type="password" id="slaughter_admin_pw" 
                       class="w-full border-2 border-gray-200 p-3 text-center rounded-lg focus:border-green-500 focus:ring-4 focus:ring-green-100 outline-none transition-all text-xl tracking-[0.5em]" 
                       placeholder="••••••••">
                
                <div id="slaughter_error" class="text-red-500 text-xs mt-3 text-center font-bold hidden italic bg-red-50 py-2 rounded-md border border-red-100 animate-pulse">
                    <i class="fas fa-times-circle"></i> Access Denied. Wrong Password.
                </div>
            </div>
            <div class="p-4 bg-gray-50 border-t flex gap-3">
                <button onclick="closeSlaughterModal()" class="flex-1 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-200 rounded-lg transition">Cancel</button>
                <button id="btn_confirm_slaughter_pdf" class="flex-1 py-2 text-sm font-bold bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-md transition active:scale-95">Verify & Export</button>
            </div>
        </div>
    </div>

    <!-- LOGIC SCRIPTS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    // FUNCTIONS TO SHOW/HIDE
    function openSlaughterModal() {
        // Alisin ang hidden, idagdag ang flex para ma-center
        const modal = document.getElementById('slaughterAuthModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('slaughter_admin_pw').value = '';
        document.getElementById('slaughter_admin_pw').focus();
    }

    function closeSlaughterModal() {
        const modal = document.getElementById('slaughterAuthModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('slaughter_error').classList.add('hidden');
    }

    // AJAX PROCESS
    $(document).ready(function() {
        $('#btn_confirm_slaughter_pdf').on('click', function() {
            var pw = $('#slaughter_admin_pw').val();
            var start = $('#start_date').val(); 
            var end = $('#end_date').val();

            if(pw == "") { 
                $('#slaughter_admin_pw').addClass('border-red-500');
                setTimeout(() => $('#slaughter_admin_pw').removeClass('border-red-500'), 1000);
                return; 
            }

            // AJAX CALL
            $.ajax({
                url: 'actions/verify_admin.php',
                method: 'POST',
                data: { admin_pw: pw },
                success: function(response) {
                    if(response.trim() === "authorized") {
                        // SUCCESS: Close popup and redirect to PDF
                        window.location.href = 'actions/generate_slaughterhouse_pdf.php?start_date=' + start + '&end_date=' + end;
                        closeSlaughterModal();
                    } else {
                        // FAILED: Show error message
                        $('#slaughter_error').removeClass('hidden');
                        $('#slaughter_admin_pw').val('').focus();
                    }
                },
                error: function() {
                    alert("Verification file (actions/verify_admin.php) not found.");
                }
            });
        });

        // Submit on "Enter" key press
        $('#slaughter_admin_pw').keypress(function(e) {
            if(e.which == 13) { $('#btn_confirm_slaughter_pdf').click(); }
        });
    });
    </script>
    
    <script src="js/global.js"></script>
    <?php include('includes/footer.php'); ?>
</body>
</html>