<?php
// admin/view_application.php

// 1. Umpisahan ang Session at Database
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db.php');

// Make sure functions.php exists
if(file_exists('includes/functions.php')) {
    include('includes/functions.php'); 
}

// 2. Proteksyon at Pagkuha ng ID
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff']) || !isset($_GET['id'])) {
    header("Location: ../index.php"); exit();
}
$application_id = (int)$_GET['id'];

// 3. Kunin ang lahat ng Data mula sa Database
$app_stmt = $conn->prepare("SELECT * FROM business_applications WHERE id = ?");
$app_stmt->bind_param("i", $application_id);
$app_stmt->execute();
$application = $app_stmt->get_result()->fetch_assoc();

if (!$application) { die("Application not found."); }

$req_stmt = $conn->prepare("SELECT * FROM application_requirements WHERE application_id = ?");
$req_stmt->bind_param("i", $application_id);
$req_stmt->execute();
$req_query = $req_stmt->get_result();

$page_title = "Review Application: " . ($application['business_name'] ?? '');

$current_user = $_SESSION["name"] ?? 'Guest';
$current_role = $_SESSION["role"] ?? "User";

// --- DAGDAG LOGIC: CHECK STATUS ---
// Kung Approved o Rejected na, gagawin nating TRUE ang $is_locked variable
$current_status = $application['status']; // Kunin ang status mula sa DB
$is_locked = in_array($current_status, ['Approved', 'Rejected']); 
// ----------------------------------
?>

<!-- ======================= HTML VIEW ========================= -->
<?php include('includes/header.php'); ?>
<body class="bg-slate-50 flex h-screen overflow-hidden text-slate-800">
    <?php include('includes/navbar.php'); ?>
    <div class="flex flex-col flex-1 overflow-hidden">
        <?php include('includes/top.php'); ?>
        
        <main class="flex-1 overflow-y-auto p-6 scroll-smooth">
            <a href="permit_verifications.php" class="text-blue-600 hover:text-blue-800 hover:underline text-sm mb-4 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Applications List
            </a>
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b pb-6 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">Reviewing Application</h2>
                        <div class="text-lg text-slate-500 mt-1">
                            For Business: <strong class="text-blue-700"><?= htmlspecialchars($application['business_name']) ?></strong>
                        </div>
                    </div>
                    <!-- Status Badge sa Taas -->
                    <div class="mt-4 md:mt-0 px-4 py-2 rounded-lg font-bold uppercase text-sm tracking-wide
                        <?php 
                            if($current_status == 'Approved') echo 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                            elseif($current_status == 'Rejected') echo 'bg-rose-100 text-rose-700 border border-rose-200';
                            else echo 'bg-yellow-100 text-yellow-700 border border-yellow-200';
                        ?>">
                        Current Status: <?= $current_status ?>
                    </div>
                </div>

                <div class="space-y-8">
                    <?php if ($req_query->num_rows === 0): ?>
                        <div class="bg-gray-50 rounded-xl p-8 text-center border border-dashed border-gray-300">
                             <p class="text-gray-500">No requirement files were uploaded for this application.</p>
                        </div>
                    <?php else: ?>
                       <?php while ($req = $req_query->fetch_assoc()): ?>
    
                        <?php
                            // Check function exists bago tawagin para iwas error
                            $analysis = ['percentage' => 0, 'document_type' => 'Unknown', 'found_keywords' => [], 'required_keywords' => []];
                            if(function_exists('analyze_permit_text')){
                                $analysis = analyze_permit_text($req['scanned_text'] ?? '');
                            }
                            
                            $percentage = $analysis['percentage'];
                            $doc_type = $analysis['document_type'];
                            
                            // Adjust colors
                            $progress_color = 'bg-red-500';
                            $text_color = 'text-red-600';
                            if ($percentage >= 80) { $progress_color = 'bg-emerald-500'; $text_color = 'text-emerald-600'; }
                            elseif ($percentage >= 50) { $progress_color = 'bg-yellow-500'; $text_color = 'text-yellow-600'; }
                            
                            $doc_verified_status = $req['verification_status'] ?? 'Pending';
                        ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-6 border rounded-xl bg-slate-50/50 hover:bg-white transition hover:shadow-md">
                            
                            <!-- KALIWA: Picture -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                     <h3 class="font-bold text-slate-700 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <?= htmlspecialchars($req['file_name']) ?>
                                     </h3>
                                     <!-- Document Specific Status Badge -->
                                     <span class="text-xs px-2 py-1 rounded border font-bold uppercase
                                        <?= ($doc_verified_status == 'Approved') ? 'bg-green-100 text-green-700 border-green-200' : 
                                           (($doc_verified_status == 'Rejected') ? 'bg-red-100 text-red-700 border-red-200' : 'bg-gray-100 text-gray-600 border-gray-200') ?>">
                                        <?= $doc_verified_status ?>
                                     </span>
                                </div>
                                <a href="../<?= htmlspecialchars($req['file_path']) ?>" target="_blank" title="Click to view full image">
                                    <div class="border rounded-xl overflow-hidden shadow-sm bg-white relative group">
                                        <img src="../<?= htmlspecialchars($req['file_path']) ?>" alt="..." class="w-full h-auto max-h-[450px] object-contain">
                                        <div class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center text-white font-bold opacity-0 group-hover:opacity-100 transition">
                                            Click to Zoom 🔍
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- KANAN: Analysis -->
                            <div class="flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-slate-700 border-b pb-2 mb-4">AI Analysis Results</h3>
                                    
                                    <!-- Detected Document Type -->
                                    <div class="mb-4">
                                        <p class="text-xs font-bold uppercase text-slate-400 mb-1">Detected Document Type:</p>
                                        <p class="text-lg font-bold text-blue-600 flex items-center gap-2">
                                            <?= htmlspecialchars($doc_type) ?>
                                        </p>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mb-5 bg-white p-3 rounded-lg border shadow-sm">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs font-bold uppercase text-slate-500">Confidence Score</span>
                                            <span class="font-bold text-lg <?= $text_color ?>"><?= $percentage ?>%</span>
                                        </div>
                                        <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                                            <div class="<?= $progress_color ?> h-3 rounded-full transition-all duration-1000" style="width: <?= $percentage ?>%;"></div>
                                        </div>
                                    </div>

                                    <!-- B. Keyword Checklist -->
                                    <div class="text-sm bg-white p-4 rounded-lg border shadow-sm mb-4">
                                        <h4 class="font-bold text-slate-600 mb-2">Data Checklist:</h4>
                                        <div class="grid grid-cols-2 gap-2">
                                            <?php foreach ($analysis['required_keywords'] as $keyword): ?>
                                                <?php $is_found = in_array($keyword, $analysis['found_keywords']); ?>
                                                <div class="flex items-center gap-2 <?= $is_found ? 'text-emerald-600' : 'text-slate-300' ?>">
                                                    <span class="text-base"><?= $is_found ? '☑' : '☐' ?></span>
                                                    <span class="<?= $is_found ? 'font-medium' : 'line-through' ?>"><?= $keyword ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- C. Scanned Text Toggle -->
                                    <details class="text-sm">
                                        <summary class="font-bold text-slate-500 cursor-pointer hover:text-blue-600 select-none">Show Raw OCR Text</summary>
                                        <div class="mt-2 border rounded p-2 bg-slate-800 text-slate-300 h-[100px] overflow-y-auto font-mono text-xs">
                                            <?= htmlspecialchars($req['scanned_text']) ?>
                                        </div>
                                    </details>
                                </div>

                                <!-- D. Final Verification Actions for this Document -->
                                <div class="mt-6 pt-4 border-t">
                                    <div class="flex items-center justify-between mb-2">
                                         <span class="text-sm font-bold text-slate-600">Verification Decision:</span>
                                    </div>

                                    <!-- *** LOGIC START: Check kung Approved/Rejected na ang MAIN application *** -->
                                    <?php if ($is_locked): ?>
                                        <!-- LOCKED MODE: Status nalang ipapakita -->
                                        <div class="w-full text-center py-3 bg-slate-100 rounded-lg border border-slate-200 text-slate-400 font-bold text-sm italic cursor-not-allowed select-none">
                                            Feature Disabled (Application is <?= $current_status ?>)
                                        </div>
                                    <?php else: ?>
                                        
                                    <?php endif; ?>
                                    <!-- *** LOGIC END *** -->
                                </div>
                            </div>
                        </div>
                       <?php endwhile; ?>
                    <?php endif; ?>
                </div>

                <!-- OVERALL ACTION SA DULO -->
                <div class="mt-10 border-t pt-8">
                    
                    <!-- *** FINAL STATUS SECTION CHANGE *** -->
                    <?php if (!$is_locked): ?>
                        
                        <!-- PENDING: Show Actions -->
                        <div class="text-center">
                            <h3 class="text-lg font-bold text-slate-700 mb-6">Final Application Decision</h3>
                            <div class="flex flex-col sm:flex-row justify-center gap-4">
                                <a href="actions/update_application_status.php?app_id=<?= $application_id ?>&status=Approved" 
                                   onclick="return confirm('Confirm Approval? This will enable the Permit Generation.');" 
                                   class="px-8 py-4 bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-200 text-white font-bold rounded-xl text-lg transition transform hover:-translate-y-1">
                                    APPROVE APPLICATION
                                </a>
                                <a href="actions/update_application_status.php?app_id=<?= $application_id ?>&status=Rejected" 
                                   onclick="return confirm('Confirm Rejection? User will be notified.');" 
                                   class="px-8 py-4 bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-200 text-white font-bold rounded-xl text-lg transition transform hover:-translate-y-1">
                                    REJECT APPLICATION
                                </a>
                            </div>
                            <p class="text-slate-400 text-sm mt-4">Make sure to verify all documents above before deciding.</p>
                        </div>

                    <?php else: ?>
                        
                        <!-- LOCKED (VIEW ONLY): Show Status Banner -->
                        <div class="max-w-3xl mx-auto text-center p-8 rounded-2xl border-2 border-dashed 
                            <?= ($current_status == 'Approved') ? 'bg-emerald-50 border-emerald-300' : 'bg-rose-50 border-rose-300' ?>">
                            
                            <!-- Icon -->
                            <div class="mb-4 inline-flex p-4 rounded-full <?= ($current_status == 'Approved') ? 'bg-emerald-200 text-emerald-800' : 'bg-rose-200 text-rose-800' ?>">
                                <?php if($current_status == 'Approved'): ?>
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <?php else: ?>
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <?php endif; ?>
                            </div>

                            <h2 class="text-3xl font-bold <?= ($current_status == 'Approved') ? 'text-emerald-700' : 'text-rose-700' ?>">
                                Application <?= $current_status ?>
                            </h2>
                            <p class="text-slate-500 mt-2 text-lg">
                                This application has been finalized. Actions are now disabled.
                            </p>
                        </div>

                    <?php endif; ?>
                    <!-- *** END CHANGE *** -->

                </div>
            </div>
        </main>
    </div>
    <script src="js/global.js?v=<?= time() ?>"></script>
</body>
</html>