<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../includes/Auth.php';
require_once '../includes/Activity.php';
require_once '../includes/BudgetCategory.php';
require_once '../includes/Expense.php';
require_once '../includes/functions.php';

// Initialize classes
$auth = new Auth();
$activity = new Activity();
$category = new BudgetCategory();
$expense = new Expense();

// Check authentication for write operations
$requireAuth = ['POST', 'PUT', 'DELETE'];
if (in_array($_SERVER['REQUEST_METHOD'], $requireAuth)) {
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit();
    }
    
    if (!$auth->hasPermission('write')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit();
    }
}

// Get current user for logging
$currentUser = $auth->getCurrentUser();

// Parse URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));
$endpoint = end($pathParts);

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetRequest($endpoint);
            break;
        case 'POST':
            handlePostRequest($endpoint);
            break;
        case 'PUT':
            handlePutRequest($endpoint);
            break;
        case 'DELETE':
            handleDeleteRequest($endpoint);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function handleGetRequest($endpoint) {
    global $activity, $category, $expense;
    
    switch ($endpoint) {
        case 'activities':
            $search = $_GET['search'] ?? '';
            $categoryFilter = $_GET['category'] ?? '';
            $statusFilter = $_GET['status'] ?? '';
            
            $activities = $activity->getAllActivities($search, $categoryFilter, $statusFilter);
            
            // Calculate additional fields
            foreach ($activities as &$act) {
                $act['remaining_budget'] = $act['planned_budget'] - $act['actual_expense'];
                $act['budget_percentage'] = $act['planned_budget'] > 0 ? 
                    round(($act['actual_expense'] / $act['planned_budget']) * 100, 2) : 0;
                $act['status_text'] = getStatusText($act['status']);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $activities,
                'total' => count($activities)
            ]);
            break;
            
        case 'activity':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Activity ID required']);
                return;
            }
            
            $activityData = $activity->getActivityById($id);
            if (!$activityData) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Activity not found']);
                return;
            }
            
            $activityData['remaining_budget'] = $activityData['planned_budget'] - $activityData['actual_expense'];
            $activityData['budget_percentage'] = $activityData['planned_budget'] > 0 ? 
                round(($activityData['actual_expense'] / $activityData['planned_budget']) * 100, 2) : 0;
            
            echo json_encode(['success' => true, 'data' => $activityData]);
            break;
            
        case 'categories':
            $categories = $category->getAllCategories();
            echo json_encode(['success' => true, 'data' => $categories]);
            break;
            
        case 'expenses':
            $activityId = $_GET['activity_id'] ?? null;
            if ($activityId) {
                $expenses = $expense->getExpensesByActivity($activityId);
            } else {
                $limit = $_GET['limit'] ?? 50;
                $expenses = $expense->getRecentExpenses($limit);
            }
            echo json_encode(['success' => true, 'data' => $expenses]);
            break;
            
        case 'summary':
            $summary = $activity->getBudgetSummary();
            $categoryData = $activity->getBudgetByCategory();
            $statusData = $activity->getStatusDistribution();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'by_category' => $categoryData,
                    'by_status' => $statusData
                ]
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
}

function handlePostRequest($endpoint) {
    global $activity, $category, $expense, $currentUser;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($endpoint) {
        case 'activities':
            // Validate required fields
            $required = ['name', 'category_id', 'planned_budget'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                    return;
                }
            }
            
            $data = [
                'name' => sanitizeInput($input['name']),
                'description' => sanitizeInput($input['description'] ?? ''),
                'category_id' => $input['category_id'],
                'planned_budget' => $input['planned_budget'],
                'start_date' => $input['start_date'] ?? null,
                'end_date' => $input['end_date'] ?? null,
                'created_by' => $currentUser['id']
            ];
            
            $activityId = $activity->createActivity($data);
            $newActivity = $activity->getActivityById($activityId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Activity created successfully',
                'data' => $newActivity
            ]);
            break;
            
        case 'expenses':
            // Validate required fields
            $required = ['activity_id', 'description', 'amount', 'expense_date'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                    return;
                }
            }
            
            $data = [
                'activity_id' => $input['activity_id'],
                'description' => sanitizeInput($input['description']),
                'amount' => $input['amount'],
                'expense_date' => $input['expense_date'],
                'receipt_number' => sanitizeInput($input['receipt_number'] ?? ''),
                'notes' => sanitizeInput($input['notes'] ?? '')
            ];
            
            $expenseId = $expense->createExpense($data);
            $newExpense = $expense->getExpenseById($expenseId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Expense created successfully',
                'data' => $newExpense
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
}

function handlePutRequest($endpoint) {
    global $activity, $expense, $currentUser;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    
    switch ($endpoint) {
        case 'activities':
            $data = [
                'name' => sanitizeInput($input['name']),
                'description' => sanitizeInput($input['description'] ?? ''),
                'category_id' => $input['category_id'],
                'planned_budget' => $input['planned_budget'],
                'start_date' => $input['start_date'] ?? null,
                'end_date' => $input['end_date'] ?? null,
                'status' => $input['status'] ?? 'pending',
                'updated_by' => $currentUser['id']
            ];
            
            $result = $activity->updateActivity($id, $data);
            
            if ($result) {
                $updatedActivity = $activity->getActivityById($id);
                echo json_encode([
                    'success' => true,
                    'message' => 'Activity updated successfully',
                    'data' => $updatedActivity
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update activity']);
            }
            break;
            
        case 'expenses':
            $data = [
                'description' => sanitizeInput($input['description']),
                'amount' => $input['amount'],
                'expense_date' => $input['expense_date'],
                'receipt_number' => sanitizeInput($input['receipt_number'] ?? ''),
                'notes' => sanitizeInput($input['notes'] ?? '')
            ];
            
            $result = $expense->updateExpense($id, $data);
            
            if ($result) {
                $updatedExpense = $expense->getExpenseById($id);
                echo json_encode([
                    'success' => true,
                    'message' => 'Expense updated successfully',
                    'data' => $updatedExpense
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update expense']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
}

function handleDeleteRequest($endpoint) {
    global $activity, $expense;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        return;
    }
    
    switch ($endpoint) {
        case 'activities':
            $result = $activity->deleteActivity($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Activity deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete activity']);
            }
            break;
            
        case 'expenses':
            $result = $expense->deleteExpense($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Expense deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete expense']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
}
?>
