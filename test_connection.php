<?php
// ไฟล์ทดสอบการเชื่อมต่อฐานข้อมูล
require_once 'includes/Database.php';

try {
    echo "กำลังทดสอบการเชื่อมต่อฐานข้อมูล...<br>";
    
    $db = new Database();
    echo "✅ เชื่อมต่อฐานข้อมูลสำเร็จ<br>";
    
    // ทดสอบการ query ตารางหมวดหมู่
    echo "<br>ทดสอบ query ตาราง budget_categories:<br>";
    $categories = $db->fetchAll("SELECT * FROM budget_categories LIMIT 3");
    if (!empty($categories)) {
        echo "✅ พบข้อมูลหมวดหมู่ " . count($categories) . " รายการ<br>";
        foreach ($categories as $cat) {
            echo "- " . htmlspecialchars($cat['name']) . "<br>";
        }
    } else {
        echo "⚠️ ไม่พบข้อมูลในตาราง budget_categories<br>";
    }
    
    // ทดสอบการ query ตารางกิจกรรม
    echo "<br>ทดสอบ query ตาราง activities:<br>";
    $activities = $db->fetchAll("SELECT * FROM activities LIMIT 3");
    if (!empty($activities)) {
        echo "✅ พบข้อมูลกิจกรรม " . count($activities) . " รายการ<br>";
        foreach ($activities as $act) {
            echo "- " . htmlspecialchars($act['name']) . "<br>";
        }
    } else {
        echo "⚠️ ไม่พบข้อมูลในตาราง activities<br>";
    }
    
    // ทดสอบการ query ตารางค่าใช้จ่าย
    echo "<br>ทดสอบ query ตาราง expenses:<br>";
    $expenses = $db->fetchAll("SELECT * FROM expenses LIMIT 3");
    if (!empty($expenses)) {
        echo "✅ พบข้อมูลค่าใช้จ่าย " . count($expenses) . " รายการ<br>";
        foreach ($expenses as $exp) {
            echo "- " . htmlspecialchars($exp['description']) . " (" . number_format($exp['amount'], 2) . " ฿)<br>";
        }
    } else {
        echo "⚠️ ไม่พบข้อมูลในตาราง expenses<br>";
    }
    
    // ทดสอบ method ใหม่
    echo "<br>ทดสอบ fetchWithLimit method:<br>";
    $recentExpenses = $db->fetchWithLimit(
        "SELECT e.*, a.name as activity_name 
         FROM expenses e 
         JOIN activities a ON e.activity_id = a.id 
         ORDER BY e.created_at DESC", 
        [], 
        2
    );
    
    if (!empty($recentExpenses)) {
        echo "✅ fetchWithLimit ทำงานสำเร็จ - พบ " . count($recentExpenses) . " รายการ<br>";
        foreach ($recentExpenses as $exp) {
            echo "- " . htmlspecialchars($exp['description']) . " (" . htmlspecialchars($exp['activity_name']) . ")<br>";
        }
    } else {
        echo "⚠️ fetchWithLimit ไม่ส่งข้อมูลกลับ<br>";
    }
    
    echo "<br>🎉 การทดสอบเสร็จสิ้น - ระบบพร้อมใช้งาน!<br>";
    echo "<br>📌 <strong>การเข้าใช้งาน:</strong><br>";
    echo "• <a href='login.php' class='text-blue-600'>🔐 เข้าสู่ระบบ</a><br>";
    echo "• <a href='index.php' class='text-blue-600'>🏠 ไปหน้าหลัก</a><br>";
    echo "<br>📌 <strong>API Endpoints:</strong><br>";
    echo "• GET <a href='api/activities' class='text-green-600'>api/activities</a> - ดึงรายการกิจกรรม<br>";
    echo "• GET <a href='api/categories' class='text-green-600'>api/categories</a> - ดึงรายการหมวดหมู่<br>";
    echo "• GET <a href='api/summary' class='text-green-600'>api/summary</a> - ดึงข้อมูลสรุป<br>";
    echo "<br>📌 <strong>บัญชีทดสอบ:</strong><br>";
    echo "• Admin: admin / admin123<br>";
    echo "• User: teacher1 / user123<br>";
    
} catch (Exception $e) {
    echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "<br>";
    echo "<br>แนะนำการแก้ไข:<br>";
    echo "1. ตรวจสอบว่า Apache และ MySQL เปิดใช้งานใน XAMPP<br>";
    echo "2. Import ไฟล์ database/schema.sql ใน phpMyAdmin<br>";
    echo "3. ตรวจสอบการตั้งค่าใน includes/Database.php<br>";
}
?>
