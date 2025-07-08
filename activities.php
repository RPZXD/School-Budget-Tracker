<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการกิจกรรม - ระบบติดตามงบประมาณโรงเรียน</title>
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
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.2);
        }
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .table-row-hover:hover {
            background: linear-gradient(90deg, #f0f9ff 0%, #e0f2fe 100%);
            transform: translateX(8px);
            transition: all 0.3s ease;
        }
        .modal-animate {
            animation: slideInDown 0.3s ease-out;
        }
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
    <?php
    require_once 'includes/Auth.php';
    require_once 'includes/Activity.php';
    require_once 'includes/BudgetCategory.php';
    require_once 'includes/functions.php';

    // ตรวจสอบการ Login
    $auth = new Auth();
    $auth->requireLogin();
    $currentUser = $auth->getCurrentUser();

    $activity = new Activity();
    $category = new BudgetCategory();

    // Handle form submissions (เฉพาะ Admin เท่านั้น)
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
                                'name' => sanitizeInput($_POST['name']),
                                'description' => sanitizeInput($_POST['description']),
                                'category_id' => $_POST['category_id'],
                                'planned_budget' => $_POST['planned_budget'],
                                'start_date' => $_POST['start_date'],
                                'end_date' => $_POST['end_date'],
                                'created_by' => $currentUser['id']
                            ];
                            
                            $activity->createActivity($data);
                            $message = 'เพิ่มกิจกรรมเรียบร้อยแล้ว';
                            $messageType = 'success';
                        } catch (Exception $e) {
                            $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                            $messageType = 'error';
                        }
                        break;

                    case 'update':
                        try {
                            $data = [
                                'name' => sanitizeInput($_POST['name']),
                                'description' => sanitizeInput($_POST['description']),
                                'category_id' => $_POST['category_id'],
                                'planned_budget' => $_POST['planned_budget'],
                                'start_date' => $_POST['start_date'],
                                'end_date' => $_POST['end_date'],
                                'status' => $_POST['status'],
                                'updated_by' => $currentUser['id']
                            ];
                            
                            $activity->updateActivity($_POST['activity_id'], $data);
                            $message = 'อัพเดทกิจกรรมเรียบร้อยแล้ว';
                            $messageType = 'success';
                        } catch (Exception $e) {
                            $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                            $messageType = 'error';
                        }
                        break;

                    case 'delete':
                        try {
                            $activity->deleteActivity($_POST['activity_id']);
                            $message = 'ลบกิจกรรมเรียบร้อยแล้ว';
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
    $search = $_GET['search'] ?? '';
    $categoryFilter = $_GET['category'] ?? '';
    $statusFilter = $_GET['status'] ?? '';

    // Get data
    $activities = $activity->getAllActivities($search, $categoryFilter, $statusFilter);
    $categories = $category->getAllCategories();
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
                            <div class="text-gray-500"><?= htmlspecialchars($currentUser['username']) ?></div>
                            <div class="text-xs text-blue-600 capitalize"><?= $currentUser['role'] ?></div>
                        </div>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <a href="users.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-users mr-2"></i>จัดการผู้ใช้
                            </a>
                        <?php endif; ?>
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user-edit mr-2"></i>แก้ไขโปรไฟล์
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

    <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 animate__animated animate__fadeInDown">
            <div>
                <h2 class="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent mb-4">
                    <i class="fas fa-tasks mr-3 text-blue-600"></i>จัดการกิจกรรม
                </h2>
                <p class="text-gray-600 text-xl">เพิ่ม แก้ไข และติดตามกิจกรรมต่างๆ ของโรงเรียน</p>
            </div>
            <?php if ($auth->hasPermission('write')): ?>
                <button onclick="openModal('addModal')" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-4 rounded-2xl shadow-lg transform hover:scale-105 transition-all duration-300 animate__animated animate__pulse animate__infinite">
                    <i class="fas fa-plus mr-2"></i>เพิ่มกิจกรรมใหม่
                </button>
            <?php endif; ?>
        </div>

        <!-- Message -->
        <?php if ($message): ?>
            <?= alert($message, $messageType) ?>
        <?php endif; ?>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 card-hover animate__animated animate__fadeInUp">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-filter text-white text-sm"></i>
                </div>
                กรองข้อมูล
            </h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">ค้นหา</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="ชื่อกิจกรรม..."
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
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
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">สถานะ</label>
                    <select name="status" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="">ทั้งหมด</option>
                        <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>ยังไม่ดำเนินการ</option>
                        <option value="in_progress" <?= $statusFilter == 'in_progress' ? 'selected' : '' ?>>ดำเนินการบางส่วน</option>
                        <option value="completed" <?= $statusFilter == 'completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-search mr-2"></i>ค้นหา
                    </button>
                </div>
            </form>
        </div>

        <!-- Activities Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden animate__animated animate__fadeInUp">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-list text-white text-sm"></i>
                    </div>
                    รายการกิจกรรม
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">กิจกรรม</th>
                            <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">หมวดหมู่</th>
                            <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">งบวางแผน</th>
                            <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">ใช้จ่ายแล้ว</th>
                            <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">คงเหลือ</th>
                            <th class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider">สถานะ</th>
                            <th class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($activities)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-6xl mb-4 animate__animated animate__fadeIn"></i>
                                    <p class="text-xl">ไม่พบข้อมูลกิจกรรม</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($activities as $index => $act): ?>
                                <tr class="table-row-hover animate__animated animate__fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($act['name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?= htmlspecialchars($act['description']) ?>
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">
                                                <?= formatDate($act['start_date']) ?> - <?= formatDate($act['end_date']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= htmlspecialchars($act['category_name'] ?? 'ไม่ระบุ') ?>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900">
                                        <?= formatCurrency($act['planned_budget']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900">
                                        <?= formatCurrency($act['actual_expense']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <?php $status = getBudgetStatus($act['planned_budget'], $act['actual_expense']); ?>
                                        <span class="<?= $status['class'] ?> font-medium">
                                            <?= formatCurrency($act['remaining_budget']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= getStatusClass($act['status']) ?>">
                                            <?= getStatusText($act['status']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-medium">
                                        <?php if ($auth->hasPermission('write')): ?>
                                            <button onclick="editActivity(<?= htmlspecialchars(json_encode($act)) ?>)" 
                                                    class="text-blue-600 hover:text-blue-900 mr-3" title="แก้ไข">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="expenses.php?activity_id=<?= $act['id'] ?>" 
                                           class="text-green-600 hover:text-green-900 mr-3" title="ดูค่าใช้จ่าย">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <?php if ($auth->hasPermission('write')): ?>
                                            <button onclick="deleteActivity(<?= $act['id'] ?>, '<?= htmlspecialchars($act['name']) ?>')" 
                                                    class="text-red-600 hover:text-red-900" title="ลบ">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
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
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full modal-animate">
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-white bg-opacity-30 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h3 class="text-xl font-bold">เพิ่มกิจกรรมใหม่</h3>
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อกิจกรรม *</label>
                                <input type="text" name="name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">รายละเอียด</label>
                                <textarea name="description" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมวดหมู่ *</label>
                                <select name="category_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">เลือกหมวดหมู่</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">งบประมาณที่วางแผน (฿) *</label>
                                <input type="number" name="planned_budget" step="0.01" min="0" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">วันที่เริ่ม</label>
                                <input type="date" name="start_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">วันที่สิ้นสุด</label>
                                <input type="date" name="end_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full modal-animate">
                <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-white bg-opacity-30 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-edit"></i>
                            </div>
                            <h3 class="text-xl font-bold">แก้ไขกิจกรรม</h3>
                        </div>
                        <button onclick="closeModal('editModal')" class="text-white hover:text-gray-300 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <div class="p-8">
                    <form method="POST" class="space-y-4" id="editForm">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="activity_id" id="edit_activity_id">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อกิจกรรม *</label>
                                <input type="text" name="name" id="edit_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">รายละเอียด</label>
                                <textarea name="description" id="edit_description" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">หมวดหมู่ *</label>
                                <select name="category_id" id="edit_category_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">เลือกหมวดหมู่</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">งบประมาณที่วางแผน (฿) *</label>
                                <input type="number" name="planned_budget" id="edit_planned_budget" step="0.01" min="0" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">วันที่เริ่ม</label>
                                <input type="date" name="start_date" id="edit_start_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">วันที่สิ้นสุด</label>
                                <input type="date" name="end_date" id="edit_end_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">สถานะ</label>
                                <select name="status" id="edit_status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="pending">ยังไม่ดำเนินการ</option>
                                    <option value="in_progress">ดำเนินการบางส่วน</option>
                                    <option value="completed">เสร็จสิ้น</option>
                                </select>
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
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full modal-animate">
                <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white p-6 rounded-t-2xl">
                    <div class="flex items-center justify-center">
                        <div class="w-16 h-16 bg-white bg-opacity-30 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-exclamation-triangle text-3xl"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-center">ยืนยันการลบกิจกรรม</h3>
                </div>
                
                <div class="p-8">
                    <p class="text-sm text-gray-500 mb-6 text-center">
                        คุณแน่ใจหรือไม่ที่จะลบกิจกรรม "<span id="delete_activity_name"></span>"<br>
                        การดำเนินการนี้ไม่สามารถย้อนกลับได้
                    </p>
                    
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="activity_id" id="delete_activity_id">
                        
                        <div class="flex justify-center space-x-3">
                            <button type="button" onclick="closeModal('deleteModal')" 
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                ยกเลิก
                            </button>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                ลบ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function editActivity(activity) {
            document.getElementById('edit_activity_id').value = activity.id;
            document.getElementById('edit_name').value = activity.name;
            document.getElementById('edit_description').value = activity.description || '';
            document.getElementById('edit_category_id').value = activity.category_id || '';
            document.getElementById('edit_planned_budget').value = activity.planned_budget;
            document.getElementById('edit_start_date').value = activity.start_date || '';
            document.getElementById('edit_end_date').value = activity.end_date || '';
            document.getElementById('edit_status').value = activity.status;
            
            openModal('editModal');
        }

        function deleteActivity(id, name) {
            document.getElementById('delete_activity_id').value = id;
            document.getElementById('delete_activity_name').textContent = name;
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
    </script>
</body>
</html>
