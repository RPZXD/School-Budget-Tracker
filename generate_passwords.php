<?php
// สคริปต์สำหรับสร้างรหัสผ่านที่เข้ารหัสถูกต้อง

echo "<h2>การสร้างรหัสผ่านที่เข้ารหัส</h2>";

// รหัสผ่านที่ต้องการ
$passwords = [
    'admin123' => 'สำหรับ admin',
    'user123' => 'สำหรับ teacher1, teacher2'
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>รหัสผ่าน</th><th>Hash</th><th>คำอธิบาย</th></tr>";

foreach ($passwords as $password => $description) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<tr>";
    echo "<td><strong>$password</strong></td>";
    echo "<td style='font-family: monospace; font-size: 12px;'>$hash</td>";
    echo "<td>$description</td>";
    echo "</tr>";
}

echo "</table>";

echo "<br><h3>SQL Statement สำหรับอัพเดทรหัสผ่าน:</h3>";
echo "<textarea rows='10' cols='80' style='font-family: monospace;'>";

$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
$userHash = password_hash('user123', PASSWORD_DEFAULT);

echo "-- อัพเดทรหัสผ่านในฐานข้อมูล\n";
echo "UPDATE users SET password = '$adminHash' WHERE username = 'admin';\n";
echo "UPDATE users SET password = '$userHash' WHERE username = 'teacher1';\n";
echo "UPDATE users SET password = '$userHash' WHERE username = 'teacher2';\n";

echo "</textarea>";

echo "<br><br><h3>ทดสอบการยืนยันรหัสผ่าน:</h3>";

// ทดสอบการยืนยัน
$testPassword = 'admin123';
$testHash = password_hash($testPassword, PASSWORD_DEFAULT);
$verified = password_verify($testPassword, $testHash);

echo "Password: <strong>$testPassword</strong><br>";
echo "Hash: <code style='font-size: 12px;'>$testHash</code><br>";
echo "Verification: " . ($verified ? "<span style='color: green;'>✅ ถูกต้อง</span>" : "<span style='color: red;'>❌ ไม่ถูกต้อง</span>");

echo "<br><br><a href='login.php'>🔗 ไปหน้า Login</a>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 20px 0; }
    th { background-color: #f0f0f0; }
    code { background-color: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
</style>
