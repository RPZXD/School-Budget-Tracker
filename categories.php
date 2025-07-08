<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหมวดหมู่งบประมาณ - ระบบติดตามงบประมาณโรงเรียน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .progress-glow {
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php
    require_once 'includes/Auth.php';
    require_once 'includes/BudgetCategory.php';
    require_once 'includes/functions.php';

    // ตรวจสอบการ Login
    $auth = new Auth();
    $auth->requireLogin();
    $currentUser = $auth->getCurrentUser();

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
                            'description' => sanitizeInput($_POST['description'])
                        ];
                        
                        $category->createCategory($data);
                        $message = 'เพิ่มหมวดหมู่เรียบร้อยแล้ว';
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
                            'description' => sanitizeInput($_POST['description'])
                        ];
                        
                        $category->updateCategory($_POST['category_id'], $data);
                        $message = 'อัพเดทหมวดหมู่เรียบร้อยแล้ว';
                        $messageType = 'success';
                    } catch (Exception $e) {
                        $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                    break;

                case 'delete':
                    try {
                        $category->deleteCategory($_POST['category_id']);
                        $message = 'ลบหมวดหมู่เรียบร้อยแล้ว';
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

    // Get data
    $categories = $category->getCategoriesWithStats();
    ?>

    <!-- Navigation -->
    <nav class="gradient-bg text-white p-4 shadow-lg">
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

    <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 animate__animated animate__fadeInDown">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2 bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    <i class="fas fa-folder-open mr-3 text-blue-600"></i>จัดการหมวดหมู่งบประมาณ
                </h2>
                <p class="text-gray-600 text-lg">เพิ่ม แก้ไข และจัดการหมวดหมู่งบประมาณของโรงเรียน</p>
            </div>
            <?php if ($auth->hasPermission('write')): ?>
            <button onclick="openModal('addModal')" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-6 py-3 rounded-lg shadow-lg transform hover:scale-105 transition-all duration-300 animate__animated animate__pulse animate__infinite">
                <i class="fas fa-plus mr-2"></i>เพิ่มหมวดหมู่ใหม่
            </button>
            <?php endif; ?>
        </div>

        <!-- Message -->
        <?php if ($message): ?>
            <?= alert($message, $messageType) ?>
        <?php endif; ?>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate__animated animate__fadeInUp">
            <?php foreach ($categories as $index => $cat): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 card-hover animate__animated animate__fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center mr-4 shadow-lg">
                                    <i class="fas fa-folder text-white text-lg"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </h3>
                            </div>
                            <p class="text-gray-600 text-sm mb-4 leading-relaxed">
                                <?= htmlspecialchars($cat['description']) ?>
                            </p>
                        </div>
                        <?php if ($auth->hasPermission('write')): ?>
                        <div class="flex space-x-3">
                            <button onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)" 
                                    class="w-10 h-10 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name']) ?>')" 
                                    class="w-10 h-10 bg-red-100 hover:bg-red-200 text-red-600 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Statistics -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600"><?= $cat['activity_count'] ?></div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">กิจกรรม</div>
                            </div>
                            <div class="text-center">
                                <?php $percentage = $cat['total_planned'] > 0 ? calculatePercentage($cat['total_spent'], $cat['total_planned']) : 0; ?>
                                <div class="text-2xl font-bold text-purple-600"><?= $percentage ?>%</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">ใช้ไป</div>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 flex items-center">
                                    <i class="fas fa-bullseye mr-2 text-green-500"></i>งบที่วางแผน
                                </span>
                                <span class="font-bold text-green-600"><?= formatCurrency($cat['total_planned']) ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 flex items-center">
                                    <i class="fas fa-money-bill-wave mr-2 text-orange-500"></i>ใช้จ่ายแล้ว
                                </span>
                                <span class="font-bold text-orange-600"><?= formatCurrency($cat['total_spent']) ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 flex items-center">
                                    <i class="fas fa-piggy-bank mr-2 text-blue-500"></i>งบคงเหลือ
                                </span>
                                <?php $status = getBudgetStatus($cat['total_planned'], $cat['total_spent']); ?>
                                <span class="font-bold <?= $status['class'] ?>"><?= formatCurrency($cat['total_remaining']) ?></span>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <?php if ($cat['total_planned'] > 0): ?>
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-2">
                                    <span>ความคืบหน้า</span>
                                    <span><?= $percentage ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="<?= getProgressBarClass($percentage) ?> h-3 rounded-full progress-glow transition-all duration-1000 ease-out" 
                                         style="width: <?= min($percentage, 100) ?>%"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex space-x-2">
                        <a href="activities.php?category=<?= $cat['id'] ?>" 
                           class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-center py-3 px-4 rounded-lg text-sm font-medium transition-all duration-300 transform hover:scale-105 shadow-md">
                            <i class="fas fa-tasks mr-2"></i>กิจกรรม
                        </a>
                        <a href="expenses.php?category=<?= $cat['id'] ?>" 
                           class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-center py-3 px-4 rounded-lg text-sm font-medium transition-all duration-300 transform hover:scale-105 shadow-md">
                            <i class="fas fa-receipt mr-2"></i>ค่าใช้จ่าย
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Add New Category Card -->
            <?php if ($auth->hasPermission('write')): ?>
            <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl border-2 border-dashed border-gray-300 p-8 flex items-center justify-center cursor-pointer hover:from-blue-50 hover:to-purple-50 hover:border-blue-300 transition-all duration-300 card-hover animate__animated animate__fadeInUp"
                 onclick="openModal('addModal')" style="animation-delay: <?= count($categories) * 0.1 ?>s">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-4 floating">
                        <i class="fas fa-plus text-white text-2xl"></i>
                    </div>
                    <p class="text-gray-600 font-medium text-lg">เพิ่มหมวดหมู่ใหม่</p>
                    <p class="text-gray-500 text-sm mt-1">คลิกเพื่อเพิ่มหมวดหมู่</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Summary Cards -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-4 gap-6 animate__animated animate__fadeInUp">
            <?php
            $totalCategories = count($categories);
            $totalActivities = array_sum(array_column($categories, 'activity_count'));
            $totalPlanned = array_sum(array_column($categories, 'total_planned'));
            $totalSpent = array_sum(array_column($categories, 'total_spent'));
            ?>
            
            <div class="bg-blue-500 text-white rounded-xl shadow-lg p-6 text-center card-hover">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">
                        <i class="fas fa-folder text-2xl"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold mb-2"><?= $totalCategories ?></div>
                <div class="text-sm opacity-90">หมวดหมู่ทั้งหมด</div>
            </div>

            <div class="bg-green-500 text-white rounded-xl shadow-lg p-6 text-center card-hover">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">
                        <i class="fas fa-tasks text-2xl"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold mb-2"><?= $totalActivities ?></div>
                <div class="text-sm opacity-90">กิจกรรมทั้งหมด</div>
            </div>

            <div class="bg-purple-500 text-white rounded-xl shadow-lg p-6 text-center card-hover">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">
                        <i class="fas fa-wallet text-2xl"></i>
                    </div>
                </div>
                <div class="text-xl font-bold mb-2"><?= formatCurrency($totalPlanned) ?></div>
                <div class="text-sm opacity-90">งบรวมที่วางแผน</div>
            </div>

            <div class="bg-red-500 text-white rounded-xl shadow-lg p-6 text-center card-hover">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-2xl"></i>
                    </div>
                </div>
                <div class="text-xl font-bold mb-2"><?= formatCurrency($totalSpent) ?></div>
                <div class="text-sm opacity-90">ใช้จ่ายรวม</div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 animate__animated animate__zoomIn">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6 rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white bg-opacity-30 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3 class="text-xl font-bold">เพิ่มหมวดหมู่ใหม่</h3>
                    </div>
                    <button onclick="closeModal('addModal')" class="text-white hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="create">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2 text-blue-500"></i>ชื่อหมวดหมู่ *
                        </label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-file-alt mr-2 text-purple-500"></i>รายละเอียด
                        </label>
                        <textarea name="description" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-500 transition-colors resize-none"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeModal('addModal')" 
                                class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                            <i class="fas fa-times mr-2"></i>ยกเลิก
                        </button>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-save mr-2"></i>บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
                                บันทึก
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 animate__animated animate__zoomIn">
            <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white p-6 rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white bg-opacity-30 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h3 class="text-xl font-bold">แก้ไขหมวดหมู่</h3>
                    </div>
                    <button onclick="closeModal('editModal')" class="text-white hover:text-gray-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <form method="POST" class="space-y-6" id="editForm">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2 text-orange-500"></i>ชื่อหมวดหมู่ *
                        </label>
                        <input type="text" name="name" id="edit_name" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-orange-500 transition-colors">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-file-alt mr-2 text-red-500"></i>รายละเอียด
                        </label>
                        <textarea name="description" id="edit_description" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-red-500 transition-colors resize-none"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeModal('editModal')" 
                                class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                            <i class="fas fa-times mr-2"></i>ยกเลิก
                        </button>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 text-white rounded-lg hover:from-orange-600 hover:to-red-700 transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-save mr-2"></i>อัพเดท
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 animate__animated animate__zoomIn">
            <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white p-6 rounded-t-2xl">
                <div class="flex items-center justify-center">
                    <div class="w-16 h-16 bg-white bg-opacity-30 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-exclamation-triangle text-3xl"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-center">ยืนยันการลบหมวดหมู่</h3>
            </div>
            
            <div class="p-6">
                <div class="text-center mb-6">
                    <p class="text-gray-700 text-lg mb-2">
                        คุณแน่ใจหรือไม่ที่จะลบหมวดหมู่
                    </p>
                    <p class="text-xl font-bold text-red-600 mb-4">
                        "<span id="delete_category_name"></span>"
                    </p>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-red-700 text-sm">
                            <i class="fas fa-warning mr-2"></i>
                            การดำเนินการนี้จะลบหมวดหมู่และข้อมูลที่เกี่ยวข้องทั้งหมด<br>
                            และไม่สามารถย้อนกลับได้
                        </p>
                    </div>
                </div>
                
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="category_id" id="delete_category_id">
                    
                    <div class="flex justify-center space-x-3">
                        <button type="button" onclick="closeModal('deleteModal')" 
                                class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                            <i class="fas fa-times mr-2"></i>ยกเลิก
                        </button>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-lg hover:from-red-600 hover:to-pink-700 transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-trash mr-2"></i>ลบหมวดหมู่
                        </button>
                    </div>
                </form>
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
            
            // เพิ่มเอฟเฟคต์เสียง (ถ้าต้องการ)
            // playSound('open');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            const modalContent = modal.querySelector('.animate__animated');
            
            // เพิ่มแอนิเมชันการปิด
            modalContent.classList.remove('animate__zoomIn');
            modalContent.classList.add('animate__zoomOut');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                modalContent.classList.remove('animate__zoomOut');
                modalContent.classList.add('animate__zoomIn');
            }, 300);
        }

        function editCategory(category) {
            document.getElementById('edit_category_id').value = category.id;
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_description').value = category.description || '';
            
            openModal('editModal');
        }

        function deleteCategory(id, name) {
            document.getElementById('delete_category_id').value = id;
            document.getElementById('delete_category_name').textContent = name;
            openModal('deleteModal');
        }

        // เพิ่มเอฟเฟคต์เมื่อโหลดหน้า
        document.addEventListener('DOMContentLoaded', function() {
            // เพิ่มแอนิเมชันให้กับ cards
            const cards = document.querySelectorAll('.card-hover');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // เพิ่มเอฟเฟคต์ hover สำหรับ progress bars
            const progressBars = document.querySelectorAll('[class*="bg-"]');
            progressBars.forEach(bar => {
                if (bar.style.width) {
                    bar.addEventListener('mouseenter', function() {
                        this.style.transform = 'scaleY(1.2)';
                        this.style.transition = 'transform 0.3s ease';
                    });
                    bar.addEventListener('mouseleave', function() {
                        this.style.transform = 'scaleY(1)';
                    });
                }
            });

            // เพิ่มเอฟเฟคต์ parallax เบา ๆ
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const parallax = document.querySelector('.gradient-bg');
                const speed = scrolled * 0.5;
                if (parallax) {
                    parallax.style.transform = `translateY(${speed}px)`;
                }
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('bg-opacity-50') || e.target.classList.contains('bg-black')) {
                const modals = ['addModal', 'editModal', 'deleteModal'];
                modals.forEach(modalId => {
                    if (!document.getElementById(modalId).classList.contains('hidden')) {
                        closeModal(modalId);
                    }
                });
            }
        });

        // เพิ่มเอฟเฟคต์การพิมพ์
        function typeWriter(element, text, speed = 50) {
            let i = 0;
            element.innerHTML = '';
            function type() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            type();
        }

        // เพิ่ม floating animation สำหรับไอคอน
        setInterval(() => {
            const floatingElements = document.querySelectorAll('.floating');
            floatingElements.forEach(element => {
                element.style.transform = `translateY(${Math.sin(Date.now() * 0.001) * 10}px)`;
            });
        }, 16);
    </script>
</body>
</html>
