<?php
require_once '../../config/database.php';
require_once '../includes/staff-auth.php';
requireStaffAuth();

if (!$_SESSION['staff_permissions']['view_feedback']) {
    header("Location: ../dashboard.php");
    exit();
}

// Handle inline status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'], $_POST['status'])) {
    $feedbackId = (int) $_POST['feedback_id'];
    $newStatus = $_POST['status'];

    $validStatuses = ['pending', 'reviewed', 'resolved'];
    if (in_array($newStatus, $validStatuses)) {
        $stmt = $conn->prepare("UPDATE feedback SET status = ? WHERE feedback_id = ?");
        $stmt->bind_param("si", $newStatus, $feedbackId);
        $stmt->execute();
    }

    // Refresh the page to prevent form resubmission
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Filter parameters
$status = $_GET['status'] ?? 'pending';
$rating = $_GET['rating'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Build query
$query = "SELECT f.*, p.first_name, p.last_name 
          FROM feedback f
          JOIN patients p ON f.patient_id = p.patient_id";

$countQuery = "SELECT COUNT(*) as total 
               FROM feedback f
               JOIN patients p ON f.patient_id = p.patient_id";

$where = [];
$params = [];
$types = '';

// Status filter
if ($status !== 'all') {
    $where[] = "f.status = ?";
    $params[] = $status;
    $types .= 's';
}

// Rating filter
if ($rating && is_numeric($rating)) {
    $where[] = "f.rating = ?";
    $params[] = $rating;
    $types .= 'i';
}

// Combine where clauses
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
    $countQuery .= " WHERE " . implode(" AND ", $where);
}

// Ordering and pagination
$query .= " ORDER BY f.feedback_date DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

// Fetch feedback
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$feedback = $result->fetch_all(MYSQLI_ASSOC);

// Fetch total for pagination
$stmt = $conn->prepare($countQuery);
if (count($params) > 2) {
    $countParams = array_slice($params, 0, count($params) - 2);
    $countTypes = substr($types, 0, -2);
    $stmt->bind_param($countTypes, ...$countParams);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Portal - Patient Feedback</title>
    <link rel="stylesheet" href="../assets/css/staff.css">
    <style>
        .star.filled { color: gold; }
        .status-pending { color: orange; }
        .status-reviewed { color: blue; }
        .status-resolved { color: green; }
        .feedback-item { border-bottom: 1px solid #ddd; padding: 15px 0; }
        .feedback-header { display: flex; justify-content: space-between; align-items: center; }
        .feedback-content { margin: 10px 0; }
        .feedback-actions { display: flex; gap: 10px; align-items: center; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Patient Feedback</h2>
        </div>

        <form method="GET" class="filter-form" style="padding: 15px;">
            <label>Status:
                <select name="status">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="reviewed" <?= $status === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                    <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                </select>
            </label>

            <label>Rating:
                <select name="rating">
                    <option value="">All</option>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= $rating == $i ? 'selected' : '' ?>>
                            <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </label>

            <button type="submit">Filter</button>
            <a href="index.php" class="btn">Reset</a>
        </form>

        <?php if (count($feedback) > 0): ?>
            <div class="feedback-list" style="padding: 0 15px;">
                <?php foreach ($feedback as $item): ?>
                    <div class="feedback-item">
                        <div class="feedback-header">
                            <div>
                                <strong><?= htmlspecialchars($item['last_name'] . ', ' . $item['first_name']) ?></strong><br>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= $item['rating'] ? 'filled' : '' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <div>
                                <small><?= date('M j, Y', strtotime($item['feedback_date'])) ?></small><br>
                                <span class="status-<?= $item['status'] ?>"><?= ucfirst($item['status']) ?></span>
                            </div>
                        </div>

                        <div class="feedback-content">
                            <?= nl2br(htmlspecialchars($item['feedback_text'])) ?>
                        </div>

                        <div class="feedback-actions">
                            <form method="POST">
                                <input type="hidden" name="feedback_id" value="<?= $item['feedback_id'] ?>">
                                <select name="status">
                                    <option value="pending" <?= $item['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="reviewed" <?= $item['status'] === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                    <option value="resolved" <?= $item['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                </select>
                                <button type="submit" class="btn btn-sm">Update</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination" style="padding: 15px;">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn">Previous</a>
                <?php endif; ?>
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn">Next</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p style="padding: 15px;">No feedback found.</p>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/staff.js"></script>
</body>
</html>
