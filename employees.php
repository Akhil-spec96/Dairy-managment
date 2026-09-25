<?php
$page_title = 'Employees';
require '../includes/db.php';
 
$msg = '';
$edit_emp = null;
$action = $_GET['action'] ?? '';
 
if (!is_admin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || in_array($action, ['add','edit']) || isset($_GET['delete'])) {
        $action = '';
        $msg = '<div class="view-only-notice">👁️ You are in <strong>view-only mode</strong>. <a href="../login.php">Login as Admin</a> to add, edit or delete records.</div>';
    }
}
 
if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $conn->real_escape_string($_POST['name']);
    $phone   = $conn->real_escape_string($_POST['phone']);
    $email   = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);
    $role    = $conn->real_escape_string($_POST['role']);
    $salary  = (float)$_POST['salary'];
    $date    = $conn->real_escape_string($_POST['join_date']);
    $status  = $conn->real_escape_string($_POST['status']);
 
    if (isset($_POST['employee_id']) && $_POST['employee_id']) {
        $id = (int)$_POST['employee_id'];
        $conn->query("UPDATE employees SET name='$name',phone='$phone',email='$email',address='$address',role='$role',salary=$salary,join_date='$date',status='$status' WHERE employee_id=$id");
        $msg = '<div class="alert alert-success">✅ Employee updated successfully.</div>';
    } else {
        $conn->query("INSERT INTO employees (name,phone,email,address,role,salary,join_date,status) VALUES ('$name','$phone','$email','$address','$role',$salary,'$date','$status')");
        $msg = '<div class="alert alert-success">✅ Employee added successfully.</div>';
    }
    $action = '';
}
 
if (is_admin() && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM employees WHERE employee_id=$id");
    $msg = '<div class="alert alert-success">✅ Employee deleted.</div>';
}
 
if (is_admin() && $action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $edit_emp = $conn->query("SELECT * FROM employees WHERE employee_id=$id")->fetch_assoc();
}
 
$search = $conn->real_escape_string($_GET['search'] ?? '');
$where = $search ? "WHERE name LIKE '%$search%' OR role LIKE '%$search%' OR phone LIKE '%$search%'" : '';
$employees = $conn->query("SELECT * FROM employees $where ORDER BY employee_id DESC");
$roles = ['Manager','Supervisor','Lab Technician','Driver','Helper','Accountant'];
 
require '../includes/header.php';
?>
<div class="page-header">
    <h1>👷 Employee Management</h1>
    <p>Track all staff members, their roles and salary details</p>
</div>
<div class="page-content">
    <?= $msg ?>
 
    <div class="card">
        <div class="card-header">
            <h2><?= $action==='edit'?'Edit Employee':'Add New Employee' ?></h2>
            <?php if (is_admin() && $action !== 'add' && $action !== 'edit'): ?>
            <a href="?action=add" class="btn btn-primary btn-sm">➕ Add Employee</a>
            <?php endif; ?>
        </div>
        <?php if (is_admin() && ($action === 'add' || $action === 'edit')): ?>
        <div class="card-body">
            <form method="POST">
                <?php if ($edit_emp): ?>
                <input type="hidden" name="employee_id" value="<?= $edit_emp['employee_id'] ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required value="<?= $edit_emp['name'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="text" name="phone" required value="<?= $edit_emp['phone'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= $edit_emp['email'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role" required>
                            <?php foreach ($roles as $r): ?>
                            <option value="<?= $r ?>" <?= (($edit_emp['role'] ?? '') === $r) ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Salary (₹) *</label>
                        <input type="number" name="salary" step="0.01" required value="<?= $edit_emp['salary'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Join Date *</label>
                        <input type="date" name="join_date" required value="<?= $edit_emp['join_date'] ?? date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?= (($edit_emp['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (($edit_emp['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Address</label>
                        <textarea name="address" rows="2"><?= $edit_emp['address'] ?? '' ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success"><?= $edit_emp ? '💾 Update' : '✅ Add Employee' ?></button>
                    <a href="employees.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
 
    <div class="card">
        <div class="card-header">
            <h2>All Employees (<?= $employees->num_rows ?>)</h2>
            <form method="GET" style="display:flex;gap:0.5rem;">
                <input type="text" name="search" placeholder="Search name, role, phone..." value="<?= htmlspecialchars($search) ?>" style="padding:7px 12px;border:1.5px solid var(--border);border-radius:6px;font-family:var(--font-body);font-size:0.88rem;">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <?php if ($search): ?><a href="employees.php" class="btn btn-secondary btn-sm">Clear</a><?php endif; ?>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Role</th><th>Salary</th><th>Joined</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if ($employees->num_rows === 0): ?>
                    <tr><td colspan="9" class="no-data">No employees found.</td></tr>
                <?php else: ?>
                    <?php while ($e = $employees->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $e['employee_id'] ?></strong></td>
                        <td><?= htmlspecialchars($e['name']) ?></td>
                        <td><?= $e['phone'] ?></td>
                        <td><?= $e['email'] ?: '—' ?></td>
                        <td><span class="badge badge-amber"><?= $e['role'] ?></span></td>
                        <td>₹<?= number_format($e['salary'], 2) ?></td>
                        <td><?= date('d M Y', strtotime($e['join_date'])) ?></td>
                        <td><span class="badge <?= $e['status']==='active'?'badge-green':'badge-red' ?>"><?= $e['status'] ?></span></td>
                        <td>
                            <?php if (is_admin()): ?>
                            <a href="?action=edit&id=<?= $e['employee_id'] ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
                            <a href="?delete=<?= $e['employee_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this employee?')">🗑️</a>
                            <?php else: ?>
                            <span style="font-size:0.78rem;color:var(--text-muted);">🔒 Login to edit</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require '../includes/footer.php'; ?>