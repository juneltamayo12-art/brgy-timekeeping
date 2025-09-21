<?php
require_once __DIR__ . '/../config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = trim($_POST['employee_id'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $role       = trim($_POST['role'] ?? '');

    if ($employee_id === '') $errors[] = 'Employee ID is required';
    if ($full_name === '')  $errors[] = 'Full name is required';
    if ($username === '')   $errors[] = 'Username is required';
    if ($password === '')   $errors[] = 'Password is required';
    if ($role === '')       $errors[] = 'Role is required';

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (employee_id, full_name, username, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$employee_id, $full_name, $username, $hash, $role]);
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../header.php';
?>
<h2>Add User</h2>
<?php foreach ($errors as $e): ?>
  <p style="color:red;"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<form method="post">
  <label>Employee ID
    <input type="text" name="employee_id" required>
  </label>
  <label>Full Name
    <input type="text" name="full_name" required>
  </label>
  <label>Username
    <input type="text" name="username" required>
  </label>
  <label>Password
    <input type="password" name="password" required>
  </label>
  <label>Role
    <input type="text" name="role" required>
  </label>
  <button type="submit">Save</button>
  <a href="index.php">Cancel</a>
</form>
<?php include __DIR__ . '/../footer.php'; ?>