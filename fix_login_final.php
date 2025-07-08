<?php
require_once 'includes/Database.php';
require_once 'includes/User.php';

try {
    $db = new Database();
    $user = new User($db);
    
    echo "<h2>แก้ไขปัญหารหัสผ่าน และทดสอบ Login</h2>";
    
    // 1. ดูรหัสผ่านปัจจุบันในฐานข้อมูล
    echo "<h3>1. ตรวจสอบข้อมูลผู้ใช้ในฐานข้อมูล:</h3>";
    $allUsers = $user->getAllUsers();
    foreach ($allUsers as $u) {
        echo "User: {$u['username']} (ID: {$u['id']})<br>";
    }
    
    // 2. สร้างรหัสผ่านใหม่
    echo "<h3>2. สร้างรหัสผ่านใหม่:</h3>";
    $passwords = [
        'admin' => 'admin123',
        'teacher1' => 'user123',
        'teacher2' => 'user123'
    ];
    
    foreach ($passwords as $username => $plainPassword) {
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        echo "Username: $username<br>";
        echo "Plain Password: $plainPassword<br>";
        echo "Hashed Password: $hashedPassword<br>";
        
        // อัพเดทรหัสผ่านใหม่
        $sql = "UPDATE users SET password = ? WHERE username = ?";
        $result = $db->execute($sql, [$hashedPassword, $username]);
        
        if ($result) {
            echo "<span style='color: green;'>✓ อัพเดทรหัสผ่านสำเร็จ</span><br>";
        } else {
            echo "<span style='color: red;'>✗ อัพเดทรหัสผ่านไม่สำเร็จ</span><br>";
        }
        echo "<br>";
    }
    
    // 3. ทดสอบการ login
    echo "<h3>3. ทดสอบการ login:</h3>";
    
    foreach ($passwords as $username => $plainPassword) {
        echo "<strong>ทดสอบ: $username / $plainPassword</strong><br>";
        
        // ดึงข้อมูลผู้ใช้
        $userData = $user->getUserByUsername($username);
        if ($userData) {
            echo "พบผู้ใช้: {$userData['username']} (ID: {$userData['id']})<br>";
            echo "Password Hash ในฐานข้อมูล: {$userData['password']}<br>";
            
            // ทดสอบ password_verify
            $isValid = password_verify($plainPassword, $userData['password']);
            if ($isValid) {
                echo "<span style='color: green;'>✓ รหัสผ่านถูกต้อง</span><br>";
            } else {
                echo "<span style='color: red;'>✗ รหัสผ่านไม่ถูกต้อง</span><br>";
            }
        } else {
            echo "<span style='color: red;'>✗ ไม่พบผู้ใช้</span><br>";
        }
        echo "<br>";
    }
    
    // 4. ทดสอบ Auth class
    echo "<h3>4. ทดสอบ Auth class:</h3>";
    require_once 'includes/Auth.php';
    $auth = new Auth();
    
    foreach ($passwords as $username => $plainPassword) {
        echo "<strong>ทดสอบ Auth->login: $username / $plainPassword</strong><br>";
        
        $result = $auth->login($username, $plainPassword, false);
        if ($result['success']) {
            echo "<span style='color: green;'>✓ Login สำเร็จ: {$result['message']}</span><br>";
            echo "User Data: ";
            print_r($result['user']);
            echo "<br>";
            
            // ล้าง session สำหรับการทดสอบครั้งถัดไป
            session_destroy();
            session_start();
        } else {
            echo "<span style='color: red;'>✗ Login ไม่สำเร็จ: {$result['message']}</span><br>";
        }
        echo "<br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; }
h3 { color: #666; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
</style>
