<?php
require_once __DIR__ . '/../config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { die('Invalid ID'); }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { die('User not found'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = trim($_POST['employee_id'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $role       = trim($_POST['role'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($employee_id === '') $errors[] = 'Employee ID is required';
    if ($full_name === '')  $errors[] = 'Full name is required';
    if ($username === '')   $errors[] = 'Username is required';
    if ($role === '')       $errors[] = 'Role is required';

    if (!$errors) {
        try {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE users SET employee_id=?, full_name=?, username=?, role=?, password=? WHERE id=?";
                $params = [$employee_id, $full_name, $username, $role, $hash, $id];
            } else {
                $sql = "UPDATE users SET employee_id=?, full_name=?, username=?, role=? WHERE id=?";
                $params = [$employee_id, $full_name, $username, $role, $id];
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../header.php';
?>
<h2>Edit User</h2>
<?php foreach ($errors as $e): ?>
  <p style="color:red;"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<form method="post">
  <label>Employee ID
    <input type="text" name="employee_id" value="<?= htmlspecialchars($user['employee_id']) ?>" required>
  </label>
  <label>Full Name
    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
  </label>
  <label>Username
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
  </label>
  <label>Role
    <input type="text" name="role" value="<?= htmlspecialchars($user['role']) ?>" required>
  </label>
  <label>New Password (leave blank to keep existing)
    <input type="password" name="password">
  </label>
  <button type="submit">Update</button>
  <a href="index.php">Cancel</a>
</form>
<?php include __DIR__ . '/../footer.php'; ?>