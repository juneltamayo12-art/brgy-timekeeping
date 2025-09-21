<?php
include __DIR__ . '/../db.php';
include __DIR__ . '/../header.php';

$res = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<div class="card p-3 shadow">
  <h2 class="fw-bold text-danger">⚙ User Settings</h2>
  <a href="create.php" class="btn btn-primary mb-3">➕ Add User</a>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Username</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['fullname'] ?? $row['username']) ?></td>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td>
            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏ Edit</a>
            <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete user?')">🗑 Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
