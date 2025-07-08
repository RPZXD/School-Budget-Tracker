<?php
class Database {
    private $host = 'localhost:3306';
    private $db_name = 'phichaia_schoolbudget';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
            exit();
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            // Log detailed error information for debugging
            error_log("SQL Error: " . $e->getMessage());
            error_log("SQL Query: " . $sql);
            error_log("Parameters: " . print_r($params, true));
            throw new Exception("Query Error: " . $e->getMessage());
        }
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function execute($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }

    /**
     * Safely execute query with LIMIT clause
     * @param string $sql SQL query without LIMIT
     * @param array $params Parameters for the query
     * @param int $limit Number of records to limit
     * @param int $offset Offset for pagination (optional)
     * @return array Results
     */
    public function fetchWithLimit($sql, $params = [], $limit = 10, $offset = 0) {
        // Validate and sanitize limit and offset
        $limit = (int) $limit;
        $offset = (int) $offset;
        
        if ($limit < 1) $limit = 10;
        if ($limit > 1000) $limit = 1000;
        if ($offset < 0) $offset = 0;
        
        // Append LIMIT clause
        $sql .= " LIMIT " . $limit;
        if ($offset > 0) {
            $sql .= " OFFSET " . $offset;
        }
        
        return $this->fetchAll($sql, $params);
    }

    /**
     * Validate integer value for safe use in SQL
     * @param mixed $value Value to validate
     * @param int $default Default value if invalid
     * @param int $min Minimum allowed value
     * @param int $max Maximum allowed value
     * @return int Validated integer
     */
    public function validateInt($value, $default = 0, $min = 0, $max = PHP_INT_MAX) {
        $value = (int) $value;
        if ($value < $min || $value > $max) {
            return $default;
        }
        return $value;
    }

    /**
     * Get total count for pagination
     * @param string $table Table name
     * @param string $whereClause Optional WHERE clause (without WHERE keyword)
     * @param array $params Parameters for WHERE clause
     * @return int Total count
     */
    public function getCount($table, $whereClause = '', $params = []) {
        $sql = "SELECT COUNT(*) as total FROM " . $table;
        if (!empty($whereClause)) {
            $sql .= " WHERE " . $whereClause;
        }
        
        $result = $this->fetch($sql, $params);
        return (int) $result['total'];
    }

    /**
     * Check if table exists
     * @param string $table Table name
     * @return bool True if table exists
     */
    public function tableExists($table) {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $result = $this->fetch($sql, [$table]);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
