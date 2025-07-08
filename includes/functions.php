<?php
// ฟังก์ชันช่วยเหลือต่างๆ

function formatCurrency($amount) {
    return number_format($amount, 2) . ' ฿';
}

function formatDate($date, $format = 'd/m/Y') {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    return date($format, strtotime($date));
}

function getStatusText($status) {
    $statuses = [
        'pending' => 'ยังไม่ดำเนินการ',
        'in_progress' => 'ดำเนินการบางส่วน',
        'completed' => 'เสร็จสิ้น'
    ];
    return $statuses[$status] ?? $status;
}

function getStatusClass($status) {
    $classes = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'in_progress' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}

function getBudgetStatus($planned, $actual) {
    $percentage = $planned > 0 ? ($actual / $planned) * 100 : 0;
    
    if ($percentage == 0) {
        return ['status' => 'not-started', 'text' => 'ยังไม่เริ่ม', 'class' => 'text-gray-500'];
    } elseif ($percentage < 50) {
        return ['status' => 'low', 'text' => 'ใช้น้อย', 'class' => 'text-green-600'];
    } elseif ($percentage < 80) {
        return ['status' => 'moderate', 'text' => 'ใช้ปานกลาง', 'class' => 'text-yellow-600'];
    } elseif ($percentage < 100) {
        return ['status' => 'high', 'text' => 'ใช้มาก', 'class' => 'text-orange-600'];
    } else {
        return ['status' => 'over', 'text' => 'เกินงบ', 'class' => 'text-red-600'];
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateRequired($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $errors[] = "กรุณากรอก " . $field;
        }
    }
    return $errors;
}

function validateNumber($value, $min = null, $max = null) {
    if (!is_numeric($value)) {
        return false;
    }
    
    $value = floatval($value);
    
    if ($min !== null && $value < $min) {
        return false;
    }
    
    if ($max !== null && $value > $max) {
        return false;
    }
    
    return true;
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function generateReceiptNumber() {
    return 'RCP' . date('Ymd') . sprintf('%04d', rand(1, 9999));
}

function calculatePercentage($value, $total) {
    if ($total == 0) return 0;
    return round(($value / $total) * 100, 2);
}

function getProgressBarClass($percentage) {
    if ($percentage < 50) {
        return 'bg-green-500';
    } elseif ($percentage < 80) {
        return 'bg-yellow-500';
    } elseif ($percentage < 100) {
        return 'bg-orange-500';
    } else {
        return 'bg-red-500';
    }
}

function alert($message, $type = 'info') {
    $classes = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700'
    ];
    
    $class = $classes[$type] ?? $classes['info'];
    
    return "<div class='border px-4 py-3 rounded mb-4 {$class}' role='alert'>
                <span class='block sm:inline'>{$message}</span>
            </div>";
}

function redirectTo($url) {
    header("Location: $url");
    exit();
}

function getCurrentUrl() {
    return $_SERVER['REQUEST_URI'];
}

function isCurrentPage($page) {
    return basename($_SERVER['PHP_SELF']) === $page;
}
?>
