<?php
require_once 'Database.php';

class Expense {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getExpensesByActivity($activity_id) {
        $sql = "SELECT * FROM expenses WHERE activity_id = ? ORDER BY expense_date DESC";
        return $this->db->fetchAll($sql, [$activity_id]);
    }

    public function getExpenseById($id) {
        $sql = "SELECT e.*, a.name as activity_name 
                FROM expenses e
                JOIN activities a ON e.activity_id = a.id
                WHERE e.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function createExpense($data) {
        $sql = "INSERT INTO expenses (activity_id, description, amount, expense_date, receipt_number, notes) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['activity_id'],
            $data['description'],
            $data['amount'],
            $data['expense_date'],
            $data['receipt_number'],
            $data['notes']
        ];
        
        $this->db->execute($sql, $params);
        $expense_id = $this->db->lastInsertId();
        
        // อัพเดทยอดค่าใช้จ่ายจริงในกิจกรรม
        $this->updateActivityExpense($data['activity_id']);
        
        return $expense_id;
    }

    public function updateExpense($id, $data) {
        // ดึงข้อมูลเก่าก่อนอัพเดท
        $old_expense = $this->getExpenseById($id);
        
        $sql = "UPDATE expenses 
                SET description = ?, amount = ?, expense_date = ?, receipt_number = ?, notes = ?
                WHERE id = ?";
        
        $params = [
            $data['description'],
            $data['amount'],
            $data['expense_date'],
            $data['receipt_number'],
            $data['notes'],
            $id
        ];
        
        $result = $this->db->execute($sql, $params);
        
        // อัพเดทยอดค่าใช้จ่ายจริงในกิจกรรม
        if ($old_expense) {
            $this->updateActivityExpense($old_expense['activity_id']);
        }
        
        return $result;
    }

    public function deleteExpense($id) {
        // ดึงข้อมูลก่อนลบ
        $expense = $this->getExpenseById($id);
        
        $sql = "DELETE FROM expenses WHERE id = ?";
        $result = $this->db->execute($sql, [$id]);
        
        // อัพเดทยอดค่าใช้จ่ายจริงในกิจกรรม
        if ($expense) {
            $this->updateActivityExpense($expense['activity_id']);
        }
        
        return $result;
    }

    private function updateActivityExpense($activity_id) {
        $sql = "UPDATE activities 
                SET actual_expense = (
                    SELECT COALESCE(SUM(amount), 0) 
                    FROM expenses 
                    WHERE activity_id = ?
                ) 
                WHERE id = ?";
        return $this->db->execute($sql, [$activity_id, $activity_id]);
    }

    public function getTotalExpensesByMonth() {
        $sql = "SELECT 
                    DATE_FORMAT(expense_date, '%Y-%m') as month,
                    SUM(amount) as total_amount,
                    COUNT(*) as expense_count
                FROM expenses 
                WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
                ORDER BY month";
        return $this->db->fetchAll($sql);
    }

    public function getRecentExpenses($limit = 10) {
        $sql = "SELECT e.*, a.name as activity_name, c.name as category_name
                FROM expenses e
                JOIN activities a ON e.activity_id = a.id
                LEFT JOIN budget_categories c ON a.category_id = c.id
                ORDER BY e.created_at DESC";
        return $this->db->fetchWithLimit($sql, [], $limit);
    }
}
?>
