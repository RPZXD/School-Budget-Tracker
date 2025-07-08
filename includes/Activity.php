<?php
require_once 'Database.php';

class Activity {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllActivities($search = '', $category_filter = '', $status_filter = '') {
        $sql = "SELECT a.*, c.name as category_name 
                FROM activities a 
                LEFT JOIN budget_categories c ON a.category_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (a.name LIKE ? OR a.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($category_filter)) {
            $sql .= " AND a.category_id = ?";
            $params[] = $category_filter;
        }
        
        if (!empty($status_filter)) {
            $sql .= " AND a.status = ?";
            $params[] = $status_filter;
        }
        
        $sql .= " ORDER BY a.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getActivityById($id) {
        $sql = "SELECT a.*, c.name as category_name 
                FROM activities a 
                LEFT JOIN budget_categories c ON a.category_id = c.id 
                WHERE a.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function createActivity($data) {
        $sql = "INSERT INTO activities (name, description, category_id, planned_budget, start_date, end_date, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['name'],
            $data['description'],
            $data['category_id'],
            $data['planned_budget'],
            $data['start_date'],
            $data['end_date'],
            $data['created_by'] ?? null
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    public function updateActivity($id, $data) {
        $sql = "UPDATE activities 
                SET name = ?, description = ?, category_id = ?, planned_budget = ?, 
                    start_date = ?, end_date = ?, status = ?, updated_by = ?
                WHERE id = ?";
        
        $params = [
            $data['name'],
            $data['description'],
            $data['category_id'],
            $data['planned_budget'],
            $data['start_date'],
            $data['end_date'],
            $data['status'],
            $data['updated_by'] ?? null,
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }

    public function deleteActivity($id) {
        $sql = "DELETE FROM activities WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    public function updateActualExpense($id) {
        $sql = "UPDATE activities 
                SET actual_expense = (
                    SELECT COALESCE(SUM(amount), 0) 
                    FROM expenses 
                    WHERE activity_id = ?
                ) 
                WHERE id = ?";
        return $this->db->execute($sql, [$id, $id]);
    }

    public function getBudgetSummary() {
        $sql = "SELECT 
                    COUNT(*) as total_activities,
                    SUM(planned_budget) as total_planned,
                    SUM(actual_expense) as total_spent,
                    SUM(remaining_budget) as total_remaining,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_count,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count
                FROM activities";
        return $this->db->fetch($sql);
    }

    public function getBudgetByCategory() {
        $sql = "SELECT 
                    c.name as category_name,
                    COUNT(a.id) as activity_count,
                    SUM(a.planned_budget) as total_planned,
                    SUM(a.actual_expense) as total_spent,
                    SUM(a.remaining_budget) as total_remaining
                FROM budget_categories c
                LEFT JOIN activities a ON c.id = a.category_id
                GROUP BY c.id, c.name
                ORDER BY total_planned DESC";
        return $this->db->fetchAll($sql);
    }

    public function getStatusDistribution() {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(planned_budget) as total_budget
                FROM activities 
                GROUP BY status";
        return $this->db->fetchAll($sql);
    }
}
?>
