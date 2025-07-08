<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบติดตามงบประมาณโรงเรียน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center">
    <?php
    require_once 'includes/Auth.php';

    $auth = new Auth();
    $message = '';
    $messageType = '';

    // ถ้าเข้าสู่ระบบแล้ว redirect ไปหน้าหลัก
    if ($auth->isLoggedIn()) {
        $redirect = $_GET['redirect'] ?? 'index.php';
        header('Location: ' . $redirect);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        if (empty($username) || empty($password)) {
            $message = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
            $messageType = 'error';
        } else {
            $result = $auth->login($username, $password, $rememberMe);
            
            if ($result['success']) {
                $redirect = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect);
                exit();
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
    }
    ?>

    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="bg-white rounded-full w-20 h-20 mx-auto flex items-center justify-center mb-4 shadow-lg">
                <i class="fas fa-school text-3xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">ระบบติดตามงบประมาณ</h1>
            <p class="text-blue-100">เข้าสู่ระบบเพื่อจัดการงบประมาณโรงเรียน</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <form method="POST" class="space-y-6">
                <!-- Message -->
                <?php if ($message): ?>
                    <div class="<?= $messageType === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas <?= $messageType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle' ?> mr-2"></i>
                            <span><?= htmlspecialchars($message) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Username/Email Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2"></i>ชื่อผู้ใช้หรืออีเมล
                    </label>
                    <input type="text" name="username" required
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="กรอกชื่อผู้ใช้หรือที่อยู่อีเมล">
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>รหัสผ่าน
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pr-12"
                               placeholder="กรอกรหัสผ่าน">
                        <button type="button" onclick="togglePassword()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember_me" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">จดจำการเข้าสู่ระบบ</span>
                    </label>
                    <a href="forgot-password.php" class="text-sm text-blue-600 hover:text-blue-800">
                        ลืมรหัสผ่าน?
                    </a>
                </div>

                <!-- Login Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all transform hover:scale-105 font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i>เข้าสู่ระบบ
                </button>

                <!-- Demo Accounts -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600 mb-2">บัญชีทดสอบ:</p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-white p-2 rounded border">
                            <strong>ผู้ดูแลระบบ:</strong><br>
                            admin / admin123
                        </div>
                        <div class="bg-white p-2 rounded border">
                            <strong>ครู:</strong><br>
                            teacher1 / user123
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-blue-100 text-sm">
                © 2025 ระบบติดตามงบประมาณโรงเรียน
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-fill demo account
        function fillDemo(username, password) {
            document.getElementsByName('username')[0].value = username;
            document.getElementsByName('password')[0].value = password;
        }

        // เพิ่ม event listener สำหรับ demo accounts
        document.addEventListener('DOMContentLoaded', function() {
            const demoAccounts = document.querySelectorAll('.bg-white.p-2');
            demoAccounts.forEach((account, index) => {
                account.style.cursor = 'pointer';
                account.addEventListener('click', function() {
                    if (index === 0) {
                        fillDemo('admin', 'admin123');
                    } else {
                        fillDemo('teacher1', 'user123');
                    }
                });
            });
        });
    </script>
</body>
</html>
