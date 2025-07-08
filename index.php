<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบติดตามงบประมาณโรงเรียน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .chart-container {
            position: relative;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
        }
        .stat-icon {
            background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
        }
        .stat-icon-2 {
            background: linear-gradient(135deg, #48cae4 0%, #0077b6 100%);
        }
        .stat-icon-3 {
            background: linear-gradient(135deg, #f72585 0%, #b5179e 100%);
        }
        .stat-icon-4 {
            background: linear-gradient(135deg, #06ffa5 0%, #2fb344 100%);
        }
        .progress-glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }
        .table-hover:hover {
            background: linear-gradient(90deg, #f0f9ff 0%, #e0f2fe 100%);
            transform: translateX(5px);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100">
    <?php
    require_once 'includes/Auth.php';
    require_once 'includes/Activity.php';
    require_once 'includes/BudgetCategory.php';
    require_once 'includes/Expense.php';
    require_once 'includes/functions.php';

    // ตรวจสอบการ Login
    $auth = new Auth();
    $auth->requireLogin();
    $currentUser = $auth->getCurrentUser();

    $activity = new Activity();
    $category = new BudgetCategory();
    $expense = new Expense();

    // ดึงข้อมูลสรุป
    $summary = $activity->getBudgetSummary();
    $categoryData = $activity->getBudgetByCategory();
    $statusData = $activity->getStatusDistribution();
    $recentExpenses = $expense->getRecentExpenses(5);
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

    <div class="container mx-auto p-6 fade-in-up">
        <!-- Header -->
        <div class="mb-8 text-center animate__animated animate__fadeInDown">
            <h2 class="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent mb-4">
                <i class="fas fa-chart-line mr-3 text-blue-600"></i>Dashboard
            </h2>
            <p class="text-gray-600 text-xl">ภาพรวมการใช้งบประมาณของโรงเรียน</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <div class="bg-white rounded-2xl shadow-xl p-8 card-hover animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl stat-icon text-white mr-6 floating">
                        <i class="fas fa-tasks text-3xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">จำนวนกิจกรรม</p>
                        <p class="text-3xl font-bold text-gray-800 pulse-slow"><?= $summary['total_activities'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8 card-hover animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl stat-icon-2 text-white mr-6 floating">
                        <i class="fas fa-wallet text-3xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">งบที่วางแผน</p>
                        <p class="text-2xl font-bold text-gray-800"><?= formatCurrency($summary['total_planned']) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8 card-hover animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl stat-icon-3 text-white mr-6 floating">
                        <i class="fas fa-money-bill-wave text-3xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">ใช้จ่ายแล้ว</p>
                        <p class="text-2xl font-bold text-gray-800"><?= formatCurrency($summary['total_spent']) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-8 card-hover animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                <div class="flex items-center">
                    <div class="p-4 rounded-2xl stat-icon-4 text-white mr-6 floating">
                        <i class="fas fa-piggy-bank text-3xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">งบคงเหลือ</p>
                        <p class="text-2xl font-bold text-gray-800"><?= formatCurrency($summary['total_remaining']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Budget by Category Chart -->
            <div class="bg-white rounded-2xl shadow-xl p-8 card-hover animate__animated animate__fadeInLeft">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-pink-500 to-rose-500 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-chart-pie text-white"></i>
                    </div>
                    งบประมาณตามหมวดหมู่
                </h3>
                <div class="chart-container p-4">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Activity Status Chart -->
            <div class="bg-white rounded-2xl shadow-xl p-8 card-hover animate__animated animate__fadeInRight">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-chart-bar text-white"></i>
                    </div>
                    สถานะกิจกรรม
                </h3>
                <div class="chart-container p-4">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Budget Progress by Category -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-12 animate__animated animate__fadeInUp">
            <h3 class="text-2xl font-bold text-gray-800 mb-8 flex items-center">
                <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                ความคืบหน้างบประมาณตามหมวดหมู่
            </h3>
            <div class="space-y-6">
                <?php foreach ($categoryData as $index => $cat): ?>
                    <?php 
                    $percentage = calculatePercentage($cat['total_spent'], $cat['total_planned']);
                    $progressClass = getProgressBarClass($percentage);
                    ?>
                    <div class="animate__animated animate__fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg font-semibold text-gray-700 flex items-center">
                                <i class="fas fa-folder-open mr-2 text-blue-500"></i>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </span>
                            <span class="text-lg font-bold text-gray-600 bg-gray-100 px-3 py-1 rounded-full"><?= $percentage ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden shadow-inner">
                            <div class="<?= $progressClass ?> h-4 rounded-full progress-glow transition-all duration-1000 ease-out" 
                                 style="width: <?= min($percentage, 100) ?>%"></div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500 mt-2">
                            <span class="flex items-center">
                                <i class="fas fa-arrow-up mr-1 text-orange-500"></i>
                                ใช้: <?= formatCurrency($cat['total_spent']) ?>
                            </span>
                            <span class="flex items-center">
                                <i class="fas fa-target mr-1 text-green-500"></i>
                                แผน: <?= formatCurrency($cat['total_planned']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="bg-white rounded-2xl shadow-xl p-8 animate__animated animate__fadeInUp">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-history text-white"></i>
                    </div>
                    รายการใช้จ่ายล่าสุด
                </h3>
                <a href="expenses.php" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-6 py-3 rounded-full transition-all duration-300 transform hover:scale-105 shadow-lg">
                    ดูทั้งหมด <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            
            <?php if (empty($recentExpenses)): ?>
                <div class="text-center py-12 animate__animated animate__fadeIn">
                    <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-xl">ยังไม่มีรายการใช้จ่าย</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-xl">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">วันที่</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">รายการ</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">กิจกรรม</th>
                                <th class="px-6 py-4 text-right text-sm font-bold text-gray-700 uppercase tracking-wider">จำนวนเงิน</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recentExpenses as $index => $exp): ?>
                                <tr class="table-hover animate__animated animate__fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s">
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                        <i class="fas fa-calendar-day mr-2 text-blue-500"></i>
                                        <?= formatDate($exp['expense_date']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                        <i class="fas fa-file-invoice mr-2 text-green-500"></i>
                                        <?= htmlspecialchars($exp['description']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <i class="fas fa-tasks mr-2 text-purple-500"></i>
                                        <?= htmlspecialchars($exp['activity_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                        <span class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-3 py-1 rounded-full font-bold">
                                            <?= formatCurrency($exp['amount']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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

        // Chart.js configurations
        Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
        
        // Budget by Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($categoryData, 'category_name')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($categoryData, 'total_planned')) ?>,
                    backgroundColor: [
                        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FECA57',
                        '#FF9FF3', '#54A0FF', '#5F27CD', '#00D2D3', '#FF9F43'
                    ],
                    borderWidth: 4,
                    borderColor: '#ffffff',
                    hoverBorderWidth: 6,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + new Intl.NumberFormat('th-TH').format(context.parsed) + ' ฿';
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff'
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 2000
                }
            }
        });

        // Activity Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusLabels = <?= json_encode(array_column($statusData, 'status')) ?>;
        const statusCounts = <?= json_encode(array_column($statusData, 'count')) ?>;
        
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels.map(status => {
                    const statusMap = {
                        'pending': 'ยังไม่ดำเนินการ',
                        'in_progress': 'ดำเนินการบางส่วน',
                        'completed': 'เสร็จสิ้น'
                    };
                    return statusMap[status] || status;
                }),
                datasets: [{
                    data: statusCounts,
                    backgroundColor: ['#FF6B6B', '#4ECDC4', '#45B7D1'],
                    borderWidth: 4,
                    borderColor: '#ffffff',
                    hoverBorderWidth: 6,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: {
                                size: 14
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    duration: 2000
                }
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
