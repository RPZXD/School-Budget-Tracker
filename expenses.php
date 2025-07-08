<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการค่าใช้จ่าย - ระบบติดตามงบประมาณโรงเรียน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        .fade-in {
            animation: fadeIn 1s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-animate-in {
            animation: slideInDown 0.3s ease-out;
        }
        .modal-animate-out {
            animation: slideOutUp 0.3s ease-in;
        }
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-50px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes slideOutUp {
            from { opacity: 1; transform: translateY(0) scale(1); }
            to { opacity: 0; transform: translateY(-50px) scale(0.9); }
        }
        .table-row-hover:hover {
            background: linear-gradient(90deg, #f0f9ff 0%, #e0f2fe 100%);
            transform: translateX(8px);
            transition: all 0.3s ease;
        }
        .expense-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-left: 4px solid #3b82f6;
        }
        .stat-card {
            background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
        }
        .stat-card-blue {
            background: linear-gradient(135deg, #48cae4 0%, #0077b6 100%);
        }
        .stat-card-green {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .stat-card-purple {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .progress-bar-glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }
        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
    <?php
    require_once 'includes/Auth.php';
    require_once 'includes/Activity.php';
    require_once 'includes/Expense.php';
    require_once 'includes/BudgetCategory.php';
    require_once 'includes/functions.php';

    // ตรวจสอบการ Login
    $auth = new Auth();
    $auth->requireLogin();
    $currentUser = $auth->getCurrentUser();

    $activity = new Activity();
    $expense = new Expense();
    $category = new BudgetCategory();

    // Handle form submissions
    $message = '';
    $messageType = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$auth->hasPermission('write')) {
            $message = 'คุณไม่มีสิทธิ์ในการดำเนินการนี้';
            $messageType = 'error';
        } else {
            if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    try {
                        $data = [
                            'activity_id' => $_POST['activity_id'],
                            'description' => sanitizeInput($_POST['description']),
                            'amount' => $_POST['amount'],
                            'expense_date' => $_POST['expense_date'],
                            'receipt_number' => sanitizeInput($_POST['receipt_number']),
                            'notes' => sanitizeInput($_POST['notes']),
                            'created_by' => $currentUser['id']
                        ];
                        
                        $expense->createExpense($data);
                        $message = 'เพิ่มรายการค่าใช้จ่ายเรียบร้อยแล้ว';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                    break;

                case 'update':
                    try {
                        $data = [
                            'description' => sanitizeInput($_POST['description']),
                            'amount' => $_POST['amount'],
                            'expense_date' => $_POST['expense_date'],
                            'receipt_number' => sanitizeInput($_POST['receipt_number']),
                            'notes' => sanitizeInput($_POST['notes']),
                            'updated_by' => $currentUser['id']
                        ];
                        
                        $expense->updateExpense($_POST['expense_id'], $data);
                        $message = 'อัพเดทรายการค่าใช้จ่ายเรียบร้อยแล้ว';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                    break;

                case 'delete':
                    try {
                        $expense->deleteExpense($_POST['expense_id']);
                        $message = 'ลบรายการค่าใช้จ่ายเรียบร้อยแล้ว';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                    break;
            }
        }
    }
    }

    // Get filters
    $activityFilter = $_GET['activity_id'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    
    // Get data
    $activities = $activity->getAllActivities();
    $categories = $category->getAllCategories();
    
    // Get expenses based on filter
    if ($activityFilter) {
        $expenses = $expense->getExpensesByActivity($activityFilter);
        $selectedActivity = $activity->getActivityById($activityFilter);
    } else {
        $expenses = $expense->getRecentExpenses(100); // Get more for general view
        $selectedActivity = null;
    }
    ?>

    <!-- Navigation -->
    <nav class="gradient-bg text-white p-4 shadow-2xl">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold animate__animated animate__fadeInLeft">
                <i class="fas fa-school mr-2 floating"></i>
                ระบบติดตามงบประมาณโรงเรียน
            </h1>
            <div class="flex items-center space-x-4">
                <div class="hidden md:flex space-x-4">
                    <a href="index.php" class="hover:text-blue-200 <?= isCurrentPage('index.php') ? 'text-blue-200' : '' ?>">
                        <i class="fas fa-chart-pie mr-1"></i>Dashboard
                    </a>
                    <a href="activities.php" class="hover:text-blue-200 <?= isCurrentPage('activities.php') ? 'text-blue-200' : '' ?>">
                        <i class="fas fa-tasks mr-1"></i>กิจกรรม
                    </a>
                    <a href="expenses.php" class="hover:text-blue-200 <?= isCurrentPage('expenses.php') ? 'text-blue-200' : '' ?>">
                        <i class="fas fa-receipt mr-1"></i>ค่าใช้จ่าย
                    </a>
                    <a href="categories.php" class="hover:text-blue-200 <?= isCurrentPage('categories.php') ? 'text-blue-200' : '' ?>">
                        <i class="fas fa-folder mr-1"></i>หมวดหมู่
                    </a>
                </div>
                
                <!-- User Menu -->
                <div class="relative" id="userMenu">
                    <button onclick="toggleUserMenu()" class="flex items-center space-x-2 hover:text-blue-200 focus:outline-none">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span class="hidden md:inline"><?= htmlspecialchars($currentUser['full_name']) ?></span>
                        <i class="fas fa-chevron-down text-sm"></i>
                    </button>
                    
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                        <div class="px-4 py-2 text-sm text-gray-700 border-b">
                            <div class="font-medium"><?= htmlspecialchars($currentUser['full_name']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($currentUser['username']) ?> (<?= $currentUser['role'] === 'admin' ? 'ผู้ดูแลระบบ' : 'ครู' ?>)</div>
                        </div>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                        <a href="admin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-cog mr-2"></i>จัดการระบบ
                        </a>
                        <?php endif; ?>
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i>โปรไฟล์
                        </a>
                        <div class="border-t">
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>ออกจากระบบ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 fade-in">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 animate__animated animate__fadeInDown">
            <div>
                <h2 class="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent mb-4">
                    <i class="fas fa-receipt mr-3 text-blue-600"></i>จัดการค่าใช้จ่าย
                </h2>
                <p class="text-gray-600 text-xl">บันทึกและติดตามรายการค่าใช้จ่ายของแต่ละกิจกรรม</p>
            </div>
            <button onclick="openModal('addModal')" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-4 rounded-2xl shadow-lg transform hover:scale-105 transition-all duration-300 animate__animated animate__pulse animate__infinite">
                <i class="fas fa-plus mr-2"></i>เพิ่มรายการใหม่
            </button>
        </div>

        <!-- Message -->
        <?php if ($message): ?>
            <?= alert($message, $messageType) ?>
        <?php endif; ?>

        <!-- Activity Summary (if activity selected) -->
        <?php if ($selectedActivity): ?>
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 card-hover animate__animated animate__fadeInUp expense-card">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-tasks text-white text-lg"></i>
                    </div>
                    กิจกรรม: <?= htmlspecialchars($selectedActivity['name']) ?>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center p-4 bg-white rounded-xl shadow-md">
                        <div class="w-16 h-16 stat-card-blue rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-wallet text-white text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">งบที่วางแผน</p>
                        <p class="text-2xl font-bold text-gray-800 pulse-slow"><?= formatCurrency($selectedActivity['planned_budget']) ?></p>
                    </div>
                    <div class="text-center p-4 bg-white rounded-xl shadow-md">
                        <div class="w-16 h-16 stat-card rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-money-bill-wave text-white text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">ใช้จ่ายแล้ว</p>
                        <p class="text-2xl font-bold text-orange-600"><?= formatCurrency($selectedActivity['actual_expense']) ?></p>
                    </div>
                    <div class="text-center p-4 bg-white rounded-xl shadow-md">
                        <div class="w-16 h-16 stat-card-green rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-piggy-bank text-white text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">งบคงเหลือ</p>
                        <?php $status = getBudgetStatus($selectedActivity['planned_budget'], $selectedActivity['actual_expense']); ?>
                        <p class="text-2xl font-bold <?= $status['class'] ?>"><?= formatCurrency($selectedActivity['remaining_budget']) ?></p>
                    </div>
                    <div class="text-center p-4 bg-white rounded-xl shadow-md">
                        <div class="w-16 h-16 stat-card-purple rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">สถานะ</p>
                        <span class="inline-block px-4 py-2 text-sm font-semibold rounded-full <?= getStatusClass($selectedActivity['status']) ?>">
                            <?= getStatusText($selectedActivity['status']) ?>
                        </span>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="mt-8">
                    <?php $percentage = calculatePercentage($selectedActivity['actual_expense'], $selectedActivity['planned_budget']); ?>
                    <div class="flex justify-between text-lg text-gray-600 mb-3">
                        <span class="font-semibold flex items-center">
                            <i class="fas fa-chart-bar mr-2 text-blue-500"></i>
                            ความคืบหน้าการใช้งบ
                        </span>
                        <span class="font-bold bg-blue-100 text-blue-800 px-3 py-1 rounded-full"><?= $percentage ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden shadow-inner">
                        <div class="<?= getProgressBarClass($percentage) ?> h-4 rounded-full progress-bar-glow transition-all duration-1000 ease-out" 
                             style="width: <?= min($percentage, 100) ?>%"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 card-hover animate__animated animate__fadeInUp">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-filter text-white text-sm"></i>
                </div>
                กรองข้อมูล
            </h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">กิจกรรม</label>
                    <select name="activity_id" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($activities as $act): ?>
                            <option value="<?= $act['id'] ?>" <?= $activityFilter == $act['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($act['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">หมวดหมู่</label>
                    <select name="category" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-filter mr-2"></i>กรองข้อมูล
                    </button>
                </div>
            </form>
        </div>

        <!-- Expenses Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate__animated animate__fadeInUp">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-list text-white text-sm"></i>
                    </div>
                    รายการค่าใช้จ่าย
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">วันที่</th>
                            <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">รายการ</th>
                            <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">กิจกรรม</th>
                            <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">จำนวนเงิน</th>
                            <th class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider">เลขที่ใบเสร็จ</th>
                            <th class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($expenses)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-6xl mb-4 animate__animated animate__fadeIn"></i>
                                    <p class="text-xl">ไม่พบข้อมูลรายการค่าใช้จ่าย</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($expenses as $index => $exp): ?>
                                <tr class="table-row-hover animate__animated animate__fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s">
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar-day mr-3 text-blue-500"></i>
                                            <?= formatDate($exp['expense_date']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 flex items-center">
                                                <i class="fas fa-file-invoice-dollar mr-2 text-green-500"></i>
                                                <?= htmlspecialchars($exp['description']) ?>
                                            </div>
                                            <?php if ($exp['notes']): ?>
                                                <div class="text-sm text-gray-500 mt-1 pl-6">
                                                    <i class="fas fa-sticky-note mr-1 text-yellow-500"></i>
                                                    <?= htmlspecialchars($exp['notes']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                        <div class="flex items-center">
                                            <i class="fas fa-tasks mr-2 text-purple-500"></i>
                                            <?= htmlspecialchars($exp['activity_name']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <span class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-4 py-2 rounded-full font-bold text-lg shadow-lg">
                                            <?= formatCurrency($exp['amount']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900 font-medium">
                                        <span class="bg-gray-100 px-3 py-1 rounded-full text-gray-700">
                                            <?= htmlspecialchars($exp['receipt_number']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-3">
                                            <button onclick="editExpense(<?= htmlspecialchars(json_encode($exp)) ?>)" 
                                                    class="w-10 h-10 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-md">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteExpense(<?= $exp['id'] ?>, '<?= htmlspecialchars($exp['description']) ?>')" 
                                                    class="w-10 h-10 bg-red-100 hover:bg-red-200 text-red-600 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-md">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full modal-animate-in">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-white bg-opacity-30 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h3 class="text-xl font-bold">เพิ่มรายการค่าใช้จ่าย</h3>
                        </div>
                        <button onclick="closeModal('addModal')" class="text-white hover:text-gray-300 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-8">
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">กิจกรรม *</label>
                                <select name="activity_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">เลือกกิจกรรม</option>
                                    <?php foreach ($activities as $act): ?>
                                        <option value="<?= $act['id'] ?>" <?= $activityFilter == $act['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($act['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">รายการค่าใช้จ่าย *</label>
                                <input type="text" name="description" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">จำนวนเงิน (฿) *</label>
                                <input type="number" name="amount" step="0.01" min="0" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">วันที่ใช้จ่าย *</label>
                                <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">เลขที่ใบเสร็จ</label>
                                <input type="text" name="receipt_number" value="<?= generateReceiptNumber() ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                                <textarea name="notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeModal('addModal')" 
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                ยกเลิก
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                บันทึก
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full modal-animate-in">
                <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-white bg-opacity-30 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-edit"></i>
                            </div>
                            <h3 class="text-xl font-bold">แก้ไขรายการค่าใช้จ่าย</h3>
                        </div>
                        <button onclick="closeModal('editModal')" class="text-white hover:text-gray-300 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-8">
                    <form method="POST" class="space-y-4" id="editForm">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="expense_id" id="edit_expense_id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">รายการค่าใช้จ่าย *</label>
                                <input type="text" name="description" id="edit_description" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">จำนวนเงิน (฿) *</label>
                                <input type="number" name="amount" id="edit_amount" step="0.01" min="0" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">วันที่ใช้จ่าย *</label>
                                <input type="date" name="expense_date" id="edit_expense_date" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">เลขที่ใบเสร็จ</label>
                                <input type="text" name="receipt_number" id="edit_receipt_number"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมายเหตุ</label>
                                <textarea name="notes" id="edit_notes" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" onclick="closeModal('editModal')" 
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                ยกเลิก
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                อัพเดท
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full modal-animate-in">
                <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white p-6 rounded-t-2xl">
                    <div class="flex items-center justify-center">
                        <div class="w-16 h-16 bg-white bg-opacity-30 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-exclamation-triangle text-3xl"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-center">ยืนยันการลบรายการ</h3>
                </div>
                
                <div class="p-8">
                    <div class="text-center mb-6">
                        <p class="text-gray-700 text-lg mb-2">
                            คุณแน่ใจหรือไม่ที่จะลบรายการ
                        </p>
                        <p class="text-xl font-bold text-red-600 mb-4">
                            "<span id="delete_expense_name"></span>"
                        </p>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-red-700 text-sm">
                                <i class="fas fa-warning mr-2"></i>
                                การดำเนินการนี้ไม่สามารถย้อนกลับได้
                            </p>
                        </div>
                    </div>
                    
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="expense_id" id="delete_expense_id">
                        
                        <div class="flex justify-center space-x-3">
                            <button type="button" onclick="closeModal('deleteModal')" 
                                    class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                                <i class="fas fa-times mr-2"></i>ยกเลิก
                            </button>
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-lg hover:from-red-600 hover:to-pink-700 transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-trash mr-2"></i>ลบรายการ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User menu toggle
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const dropdown = document.getElementById('userDropdown');
            
            if (!userMenu.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            const modalContent = modal.querySelector('.modal-animate-in');
            
            if (modalContent) {
                modalContent.classList.remove('modal-animate-in');
                modalContent.classList.add('modal-animate-out');
                
                setTimeout(() => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                    modalContent.classList.remove('modal-animate-out');
                    modalContent.classList.add('modal-animate-in');
                }, 300);
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        function editExpense(expense) {
            document.getElementById('edit_expense_id').value = expense.id;
            document.getElementById('edit_description').value = expense.description;
            document.getElementById('edit_amount').value = expense.amount;
            document.getElementById('edit_expense_date').value = expense.expense_date;
            document.getElementById('edit_receipt_number').value = expense.receipt_number || '';
            document.getElementById('edit_notes').value = expense.notes || '';
            
            openModal('editModal');
        }

        function deleteExpense(id, description) {
            document.getElementById('delete_expense_id').value = id;
            document.getElementById('delete_expense_name').textContent = description;
            openModal('deleteModal');
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('bg-opacity-50')) {
                const modals = ['addModal', 'editModal', 'deleteModal'];
                modals.forEach(modalId => {
                    if (!document.getElementById(modalId).classList.contains('hidden')) {
                        closeModal(modalId);
                    }
                });
            }
        });

        // Floating animation for icons
        setInterval(() => {
            const floatingElements = document.querySelectorAll('.floating');
            floatingElements.forEach(element => {
                element.style.transform = `translateY(${Math.sin(Date.now() * 0.001) * 10}px)`;
            });
        }, 16);

        // Parallax effect for navbar
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.gradient-bg');
            const speed = scrolled * 0.5;
            if (parallax) {
                parallax.style.transform = `translateY(${speed}px)`;
            }
        });

        // Add hover effects for table rows
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('.table-row-hover');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
                
                row.addEventListener('mouseenter', function() {
                    this.style.background = 'linear-gradient(90deg, #f0f9ff 0%, #e0f2fe 100%)';
                    this.style.transform = 'translateX(8px)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.background = '';
                    this.style.transform = '';
                });
            });
        });
    </script>
</body>
</html>
