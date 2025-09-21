<?php
// index.php (Home Tab with QR Scanner + Manual Input)
include __DIR__ . '/db.php';
include __DIR__ . '/header.php';

date_default_timezone_set('Asia/Manila');

$success = $error = null;

// Reusable function para mag-log
function logAttendance($conn, $userId) {
    $today = date('Y-m-d');
    $startOfDay = $today . ' 00:00:00';
    $endOfDay   = $today . ' 23:59:59';

    // Last log today
    $stmt = $conn->prepare("
        SELECT action FROM attendance_logs
        WHERE user_id = ? 
          AND start_date BETWEEN ? AND ?
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->bind_param("iss", $userId, $startOfDay, $endOfDay);
    $stmt->execute();
    $res = $stmt->get_result();
    $last = $res->fetch_assoc();
    $stmt->close();

    // Decide next action
    $nextAction = ($last && $last['action'] === "Time In") ? "Time Out" : "Time In";

    $insert = $conn->prepare("
        INSERT INTO attendance_logs (user_id, start_date, action, status)
        VALUES (?, NOW(), ?, 'active')
    ");
    $insert->bind_param("is", $userId, $nextAction);
    if ($insert->execute()) {
        return "✅ User #$userId logged as <b>$nextAction</b>";
    } else {
        return "Insert failed: " . $insert->error;
    }
}

// Process QR Scanner
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_code'])) {
    $qrCode = trim($_POST['qr_code']);
    $userId = intval($qrCode);

    if ($userId > 0) {
        $success = logAttendance($conn, $userId);
    } else {
        $error = "Invalid QR Code!";
    }
}

// Process Manual Input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_user'])) {
    $userId = intval($_POST['manual_user']);
    if ($userId > 0) {
        $success = logAttendance($conn, $userId);
    } else {
        $error = "Please select a valid user!";
    }
}

// Fetch users for manual input
$users = [];
$res = $conn->query("SELECT * FROM users ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
    $fullname = '';
    if (!empty($row['fullname'])) {
        $fullname = $row['fullname'];
    } elseif (!empty($row['firstname']) || !empty($row['lastname'])) {
        $fullname = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''));
    } elseif (!empty($row['username'])) {
        $fullname = $row['username'];
    } else {
        $fullname = "User #" . $row['id'];
    }
    $row['display_name'] = $fullname;
    $users[] = $row;
}
?>

<div class="card p-4 shadow">
  <h2 class="fw-bold text-danger">🏠 Home - QR Timekeeping</h2>
  <p>Scan your QR code or use the manual form to <b>Time In / Time Out</b>.</p>

  <?php if ($error): ?>
    <div class="alert alert-danger mt-2"><?= $error ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success mt-2"><?= $success ?></div>
  <?php endif; ?>

  <!-- QR Input -->
  <div class="mb-4">
    <h5>🔍 QR Scanner</h5>
    <form method="post" id="qrForm">
      <input type="text" name="qr_code" id="qrInput" 
             class="form-control form-control-lg"
             placeholder="Scan QR Code here..." autofocus>
    </form>
  </div>

  <script>
    // Auto-submit kapag may scan
    document.getElementById("qrInput").addEventListener("input", function() {
      if (this.value.length > 0) {
        document.getElementById("qrForm").submit();
      }
    });
  </script>

  <!-- Manual Input -->
  <div class="mt-4">
    <h5>✍ Manual Input</h5>
    <form method="post" class="row g-2">
      <div class="col-md-8">
        <select name="manual_user" class="form-select" required>
          <option value="">-- Select User --</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['display_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary w-100">Log Attendance</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
