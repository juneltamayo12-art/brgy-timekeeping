<?php
// attendance/index.php (History tab)
include __DIR__ . '/../db.php';
include __DIR__ . '/../header.php';

date_default_timezone_set('Asia/Manila');

// Filter: today or all
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if ($filter === 'today') {
    $today = date('Y-m-d');
    $startOfDay = $today . ' 00:00:00';
    $endOfDay   = $today . ' 23:59:59';
    $stmt = $conn->prepare("
        SELECT a.*, u.*
        FROM attendance_logs a
        LEFT JOIN users u ON u.id = a.user_id
        WHERE a.start_date BETWEEN ? AND ?
        ORDER BY a.start_date DESC
    ");
    $stmt->bind_param("ss", $startOfDay, $endOfDay);
    $stmt->execute();
    $res = $stmt->get_result();
    $logs = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $res = $conn->query("
        SELECT a.*, u.*
        FROM attendance_logs a
        LEFT JOIN users u ON u.id = a.user_id
        ORDER BY a.start_date DESC
    ");
    $logs = $res->fetch_all(MYSQLI_ASSOC);
}

// Helper function to get display name
function getDisplayName($row) {
    if (!empty($row['fullname'])) {
        return $row['fullname'];
    } elseif (!empty($row['firstname']) || !empty($row['lastname'])) {
        return trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));
    } elseif (!empty($row['username'])) {
        return $row['username'];
    }
    return "User #" . $row['user_id'];
}
?>

<div class="card p-3 shadow">
  <h2 class="fw-bold text-danger">📜 Attendance History</h2>

  <!-- Filter Buttons -->
  <div class="mb-3">
    <a href="?filter=all" class="btn btn-sm <?= $filter==='all'?'btn-primary':'btn-outline-primary' ?>">All Logs</a>
    <a href="?filter=today" class="btn btn-sm <?= $filter==='today'?'btn-primary':'btn-outline-primary' ?>">Today's Logs</a>
  </div>

  <!-- Logs Table -->
  <div class="table-responsive">
    <table class="table table-striped">
      <thead class="table-dark">
        <tr>
          <th>User</th>
          <th>Action</th>
          <th>Role</th>
          <th>Date/Time</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($logs) > 0): ?>
          <?php foreach ($logs as $row): ?>
            <tr>
              <td><?= htmlspecialchars(getDisplayName($row)) ?></td>
              <td><?= htmlspecialchars($row['action']) ?></td>
              <td><?= htmlspecialchars($row['role'] ?? '') ?></td>
              <td><?= htmlspecialchars($row['start_date']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="text-center text-muted">No logs found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
