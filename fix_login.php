<?php
require_once 'includes/Database.php';

try {
    $db = new Database();
    
    echo "<h2>🔧 แก้ไขปัญหา Login - อัพเดทรหัสผ่านด้วย Hash ที่ถูกต้อง</h2>";
    
    // ใช้ Hash ที่สร้างจาก generate_passwords.php
    $adminHash = '$2y$10$IWU3Zh0IwPzLRN4VJCgoP.w5JY7Wq9SoY9J4to8OowoOI0pmLM3ha'; // admin123
    $userHash = '$2y$10$8vuFaaAi/gh4vAoM70I/NuCNMTuCrLGeanuXarkr7pKpcd8oD9U5O'; // user123
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7; margin-bottom: 20px;'>";
    echo "<h3>⚡ กำลังอัพเดทรหัสผ่าน...</h3>";
    echo "<p>ใช้ Hash ที่สร้างจาก generate_passwords.php</p>";
    echo "</div>";
    
    // อัพเดทรหัสผ่าน admin
    $result1 = $db->execute(
        "UPDATE users SET password = ? WHERE username = 'admin'",
        [$adminHash]
    );
    
    // อัพเดทรหัสผ่าน teacher1
    $result2 = $db->execute(
        "UPDATE users SET password = ? WHERE username = 'teacher1'",
        [$userHash]
    );
    
    // อัพเดทรหัสผ่าน teacher2
    $result3 = $db->execute(
        "UPDATE users SET password = ? WHERE username = 'teacher2'",
        [$userHash]
    );
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb; margin-bottom: 20px;'>";
    echo "<h3>✅ อัพเดทรหัสผ่านเสร็จสิ้น!</h3>";
    echo "<ul>";
    echo "<li>👤 <strong>admin</strong> → " . ($result1 ? "✅ สำเร็จ" : "❌ ล้มเหลว") . "</li>";
    echo "<li>👤 <strong>teacher1</strong> → " . ($result2 ? "✅ สำเร็จ" : "❌ ล้มเหลว") . "</li>";
    echo "<li>👤 <strong>teacher2</strong> → " . ($result3 ? "✅ สำเร็จ" : "❌ ล้มเหลว") . "</li>";
    echo "</ul>";
    echo "</div>";
    
    // ทดสอบรหัสผ่านที่อัพเดทแล้ว
    echo "<h3>🧪 ทดสอบการยืนยันรหัสผ่านในฐานข้อมูล:</h3>";
    
    $users = $db->fetchAll("SELECT username, password FROM users WHERE username IN ('admin', 'teacher1', 'teacher2')");
    
    echo "<table style='border-collapse: collapse; width: 100%; border: 1px solid #ddd;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>Username</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>Test Password</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>Hash ในฐานข้อมูล</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>ผลการทดสอบ</th>";
    echo "</tr>";
    
    foreach ($users as $user) {
        $testPassword = ($user['username'] === 'admin') ? 'admin123' : 'user123';
        $verified = password_verify($testPassword, $user['password']);
        
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 12px;'><strong>" . $user['username'] . "</strong></td>";
        echo "<td style='border: 1px solid #ddd; padding: 12px;'><code>" . $testPassword . "</code></td>";
        echo "<td style='border: 1px solid #ddd; padding: 12px; font-family: monospace; font-size: 11px;'>" . substr($user['password'], 0, 30) . "...</td>";
        echo "<td style='border: 1px solid #ddd; padding: 12px;'>" . ($verified ? 
            "<span style='color: green; font-weight: bold;'>✅ ถูกต้อง</span>" : 
            "<span style='color: red; font-weight: bold;'>❌ ไม่ถูกต้อง</span>") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // แสดงข้อมูล Hash ที่ใช้
    echo "<br><h3>📋 รายละเอียด Hash ที่ใช้:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;'>";
    echo "<p><strong>Admin Hash:</strong><br><code style='font-size: 11px; word-break: break-all;'>$adminHash</code></p>";
    echo "<p><strong>User Hash:</strong><br><code style='font-size: 11px; word-break: break-all;'>$userHash</code></p>";
    echo "</div>";
    
    echo "<br><div style='background: #cce5ff; padding: 15px; border-radius: 5px; border: 1px solid #99d6ff;'>";
    echo "<h3>🚀 ทดสอบการ Login:</h3>";
    echo "<p>ตอนนี้คุณสามารถ login ได้ด้วยบัญชีเหล่านี้:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: <code>admin</code> / password: <code>admin123</code></li>";
    echo "<li><strong>Teacher 1:</strong> username: <code>teacher1</code> / password: <code>user123</code></li>";
    echo "<li><strong>Teacher 2:</strong> username: <code>teacher2</code> / password: <code>user123</code></li>";
    echo "</ul>";
    echo "<p>";
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 ไปหน้า Login</a>";
    echo "<a href='test_connection.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🧪 ทดสอบระบบ</a>";
    echo "<a href='index.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 หน้าหลัก</a>";
    echo "</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ เกิดข้อผิดพลาด:</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>แนะนำ:</strong></p>";
    echo "<ul>";
    echo "<li>ตรวจสอบว่า XAMPP Apache และ MySQL เปิดใช้งานอยู่</li>";
    echo "<li>ตรวจสอบว่าได้ import ไฟล์ database/schema.sql แล้ว</li>";
    echo "<li>ตรวจสอบการตั้งค่าในไฟล์ includes/Database.php</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<style>
body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    margin: 20px; 
    background-color: #f8f9fa;
    line-height: 1.6;
}
h2, h3 { color: #343a40; }
code { 
    background-color: #f8f9fa; 
    padding: 2px 6px; 
    border-radius: 4px; 
    border: 1px solid #dee2e6;
    font-family: 'Courier New', monospace;
}
table { 
    width: 100%; 
    margin: 15px 0; 
}
th { 
    background-color: #e9ecef; 
    font-weight: 600;
}
</style>
