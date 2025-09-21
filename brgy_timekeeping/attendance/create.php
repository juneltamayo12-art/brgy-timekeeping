<?php
// attendance/create.php
// Put this file at: C:\xampp\htdocs\brgy_timekeeping\attendance\create.php

// include header and DB (one level up)
include __DIR__ . '/../db.php';  // para makuha yung connection
include __DIR__ . '/../header.php';


date_default_timezone_set('Asia/Manila');

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize inputs
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    $role   = isset($_POST['role']) ? trim($_POST['role']) : '';

    if (!$userId || !$action) {
        $error = "Missing required fields.";
    } else {
        // define today's range
        $today = date('Y-m-d');
        $startOfDay = $today . ' 00:00:00';
        $endOfDay   = $today . ' 23:59:59';

        // check last action for the user today
        $stmt = $conn->prepare("
            SELECT action, start_date
            FROM attendance_logs
            WHERE user_id = ?
              AND start_date BETWEEN ? AND ?
            ORDER BY id DESC
            LIMIT 1
        ");

        if ($stmt) {
            $stmt->bind_param("iss", $userId, $startOfDay, $endOfDay);
            $stmt->execute();
            $res = $stmt->get_result();
            $last = $res->fetch_assoc();
            $stmt->close();

            if ($last && $last['action'] === $action) {
                $error = "Invalid: user already '{$action}' today.";
            } else {
                // insert new attendance log
                $insert = $conn->prepare("
                    INSERT INTO attendance_logs (user_id, start_date, action, role, status)
                    VALUES (?, NOW(), ?, ?, 'active')
                ");
                if ($insert) {
                    $insert->bind_param("iss", $userId, $action, $role);
                    if ($insert->execute()) {
                        $success = "Attendance saved successfully.";
                    } else {
                        $error = "Insert failed: " . $insert->error;
                    }
                    $insert->close();
                } else {
                    $error = "Prepare (insert) failed: " . $conn->error;
                }
            }
        } else {
            $error = "Prepare (select last) failed: " . $conn->error;
        }
    }
}

// fetch users for dropdown (safe: retrieve all columns and build label in PHP)
$users = [];
$res = $conn->query("SELECT * FROM users ORDER BY id");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        // build a friendly name using available columns
        $fullname = '';
        if (!empty($r['fullname'])) $fullname = $r['fullname'];
        elseif (!empty($r['name'])) $fullname = $r['name'];
        elseif (!empty($r['username'])) $fullname = $r['username'];
        else {
            $f = trim((isset($r['firstname']) ? $r['firstname'] : '') . ' ' . (isset($r['lastname']) ? $r['lastname'] : ''));
            $fullname = $f !== '' ? $f : ('User #' . $r['id']);
        }
        $users[] = ['id' => $r['id'], 'fullname' => $fullname];
    }
}
?>

<!-- main card content (header.php already opened the main-content area) -->
<div class="card">
  <h2 class="fw-bold text-danger">Add Attendance Log</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success mt-3"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post" class="mt-3">
    <div class="mb-3">
      <label class="form-label">User</label>
      <select name="user_id" class="form-select" required>
        <option value="">-- Select user --</option>
        <?php foreach ($users as $u): ?>
          <option value="<?= htmlspecialchars($u['id']) ?>"><?= htmlspecialchars($u['fullname']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Action</label>
      <select name="action" class="form-select" required>
        <option value="Time In">Time In</option>
        <option value="Time Out">Time Out</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Role <small class="text-muted">(optional)</small></label>
      <input type="text" name="role" class="form-control" />
    </div>

    <button class="btn btn-primary" type="submit">Save</button>
    <a href="<?= htmlspecialchars(dirname(__DIR__)) ?>/index.php" class="btn btn-secondary ms-2">Back</a>
  </form>
</div>

<?php include dirname(__DIR__) . '/footer.php'; ?>