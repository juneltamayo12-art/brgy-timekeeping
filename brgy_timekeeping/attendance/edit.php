<?php
require_once __DIR__ . '/../config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { die('Invalid ID'); }

$stmt = $pdo->prepare("SELECT al.*, u.full_name, u.ser_id
                       FROM attendance_logs al
                       JOIN users u ON al.employee_id = u.id
                       WHERE al.id = ?");
$stmt->execute([$id]);
$log = $stmt->fetch();
if (!$log) { die('Log not found'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = trim($_POST['start_date'] ?? '');
    $status     = strtoupper(trim($_POST['status'] ?? ''));

    if ($start_date === '') $errors[] = 'Log time is required';
    if (!in_array($status, ['IN','OUT'])) $errors[] = 'Status must be IN or OUT';

    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE attendance_logs SET start_date=?, status=? WHERE id=?");
        $stmt->execute([$start_date, $status, $id]);
        header('Location: index.php');
        exit;
    }
}

include __DIR__ . '/../header.php';
?>
<h2>Edit Attendance Log</h2>
<p><strong><?= htmlspecialchars($log['full_name']) ?></strong>
   (Employee ID: <?= htmlspecialchars($log['employee_id']) ?>)</p>

<?php foreach ($errors as $e): ?>
  <p style="color:red;"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<form method="post">
  <label>Log Time
    <input type="datetime-local" name="start_date"
           value="<?= $log['start_date'] ? date('Y-m-d\TH:i', strtotime($log['start_date'])) : '' ?>" required>
  </label>
  <label>Status
    <select name="status" required>
      <option value="IN"  <?= $log['status']==='IN'  ? 'selected' : '' ?>>IN</option>
      <option value="OUT" <?= $log['status']==='OUT' ? 'selected' : '' ?>>OUT</option>
    </select>
  </label>
  <button type="submit">Update</button>
  <a href="index.php">Cancel</a>
</form>
<?php include __DIR__ . '/../footer.php'; ?>