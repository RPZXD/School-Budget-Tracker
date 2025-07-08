<?php
require_once 'Database.php';

class BudgetCategory {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM budget_categories ORDER BY name";
        return $this->db->fetchAll($sql);
    }

    public function getCategoryById($id) {
        $sql = "SELECT * FROM budget_categories WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function createCategory($data) {
        $sql = "INSERT INTO budget_categories (name, description) VALUES (?, ?)";
        $params = [$data['name'], $data['description']];
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    public function updateCategory($id, $data) {
        $sql = "UPDATE budget_categories SET name = ?, description = ? WHERE id = ?";
        $params = [$data['name'], $data['description'], $id];
        return $this->db->execute($sql, $params);
    }

    public function deleteCategory($id) {
        // ตรวจสอบว่ามีกิจกรรมที่ใช้หมวดหมู่นี้หรือไม่
        $check_sql = "SELECT COUNT(*) as count FROM activities WHERE category_id = ?";
        $result = $this->db->fetch($check_sql, [$id]);
        
        if ($result['count'] > 0) {
            throw new Exception("ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากมีกิจกรรมที่ใช้หมวดหมู่นี้อยู่");
        }
        
        $sql = "DELETE FROM budget_categories WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    public function getCategoriesWithStats() {
        $sql = "SELECT 
                    c.*,
                    COUNT(a.id) as activity_count,
                    COALESCE(SUM(a.planned_budget), 0) as total_planned,
                    COALESCE(SUM(a.actual_expense), 0) as total_spent,
                    COALESCE(SUM(a.remaining_budget), 0) as total_remaining
                FROM budget_categories c
                LEFT JOIN activities a ON c.id = a.category_id
                GROUP BY c.id, c.name, c.description
                ORDER BY c.name";
        return $this->db->fetchAll($sql);
    }
}
?>
