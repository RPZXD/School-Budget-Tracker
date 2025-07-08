<?php
require_once 'includes/Auth.php';

$auth = new Auth();
$result = $auth->logout();

// Redirect ไปหน้า login พร้อมข้อความ
header('Location: login.php?message=' . urlencode($result['message']) . '&type=success');
exit();
?>
