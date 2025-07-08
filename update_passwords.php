<?php
require_once 'includes/Database.php';

try {
    $db = new Database();
    
    echo "<h2>อัพเดทรหัสผ่านผู้ใช้</h2>";
    
    // สร้างรหัสผ่านใหม่
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $userPassword = password_hash('user123', PASSWORD_DEFAULT);
    
    // อัพเดทรหัสผ่าน admin
    $result1 = $db->execute(
        "UPDATE users SET password = ? WHERE username = 'admin'",
        [$adminPassword]
    );
    
    // อัพเดทรหัสผ่าน teacher1
    $result2 = $db->execute(
        "UPDATE users SET password = ? WHERE username = 'teacher1'",
        [$userPassword]
    );
    
    // อัพเดทรหัสผ่าน teacher2
    $result3 = $db->execute(
        "UPDATE users SET password = ? WHERE username = 'teacher2'",
        [$userPassword]
    );
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3>✅ อัพเดทรหัสผ่านสำเร็จ!</h3>";
    echo "<p><strong>บัญชีที่อัพเดท:</strong></p>";
    echo "<ul>";
    echo "<li>👤 <strong>admin</strong> → รหัสผ่าน: <code>admin123</code> (อัพเดท: " . ($result1 ? "สำเร็จ" : "ล้มเหลว") . ")</li>";
    echo "<li>👤 <strong>teacher1</strong> → รหัสผ่าน: <code>user123</code> (อัพเดท: " . ($result2 ? "สำเร็จ" : "ล้มเหลว") . ")</li>";
    echo "<li>👤 <strong>teacher2</strong> → รหัสผ่าน: <code>user123</code> (อัพเดท: " . ($result3 ? "สำเร็จ" : "ล้มเหลว") . ")</li>";
    echo "</ul>";
    echo "</div>";
    
    // ทดสอบการ login
    echo "<br><h3>🧪 ทดสอบการยืนยันรหัสผ่าน:</h3>";
    
    $users = $db->fetchAll("SELECT username, password FROM users WHERE username IN ('admin', 'teacher1', 'teacher2')");
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>Username</th><th>Password Test</th><th>Result</th></tr>";
    
    foreach ($users as $user) {
        $testPassword = ($user['username'] === 'admin') ? 'admin123' : 'user123';
        $verified = password_verify($testPassword, $user['password']);
        
        echo "<tr>";
        echo "<td><strong>" . $user['username'] . "</strong></td>";
        echo "<td><code>" . $testPassword . "</code></td>";
        echo "<td>" . ($verified ? 
            "<span style='color: green; font-weight: bold;'>✅ ถูกต้อง</span>" : 
            "<span style='color: red; font-weight: bold;'>❌ ไม่ถูกต้อง</span>") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><div style='background: #cce5ff; padding: 15px; border-radius: 5px; border: 1px solid #99d6ff;'>";
    echo "<h3>🔗 ลิงก์ที่เกี่ยวข้อง:</h3>";
    echo "<p>";
    echo "<a href='login.php' style='margin-right: 15px; color: #0066cc;'>🔐 หน้า Login</a>";
    echo "<a href='test_connection.php' style='margin-right: 15px; color: #0066cc;'>🧪 ทดสอบระบบ</a>";
    echo "<a href='index.php' style='color: #0066cc;'>🏠 หน้าหลัก</a>";
    echo "</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>❌ เกิดข้อผิดพลาด:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>แนะนำ:</strong> ตรวจสอบว่าได้ import ไฟล์ database/schema.sql แล้วหรือยัง</p>";
    echo "</div>";
}
?>

<style>
body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    margin: 20px; 
    background-color: #f8f9fa;
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
    border-collapse: collapse;
}
th, td { 
    padding: 12px; 
    text-align: left;
}
th { 
    background-color: #e9ecef; 
    font-weight: 600;
}
a { 
    text-decoration: none; 
    padding: 8px 15px; 
    background-color: #007bff; 
    color: white; 
    border-radius: 4px; 
    display: inline-block;
    margin: 5px;
}
a:hover { 
    background-color: #0056b3; 
}
</style>
