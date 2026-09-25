<?php
$page_title = 'Farmers';
require '../includes/db.php';
 
$msg = '';
$edit_farmer = null;
$action = $_GET['action'] ?? '';
 
// Block non-admin write operations
if (!is_admin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || in_array($action, ['add','edit']) || isset($_GET['delete'])) {
        $action = '';
        $msg = '<div class="view-only-notice">👁️ You are in <strong>view-only mode</strong>. <a href="../login.php">Login as Admin</a> to add, edit or delete records.</div>';
    }
}
 
// Handle form submissions (admin only)
if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $conn->real_escape_string($_POST['name']);
    $phone   = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $village = $conn->real_escape_string($_POST['village']);
    $bank    = $conn->real_escape_string($_POST['bank_account']);
    $ifsc    = $conn->real_escape_string($_POST['ifsc_code']);
    $date    = $conn->real_escape_string($_POST['join_date']);
    $status  = $conn->real_escape_string($_POST['status']);
 
    if (isset($_POST['farmer_id']) && $_POST['farmer_id']) {
        $id = (int)$_POST['farmer_id'];
        $conn->query("UPDATE farmers SET name='$name',phone='$phone',address='$address',village='$village',bank_account='$bank',ifsc_code='$ifsc',join_date='$date',status='$status' WHERE farmer_id=$id");
        $msg = '<div class="alert alert-success">✅ Farmer updated successfully.</div>';
    } else {
        $conn->query("INSERT INTO farmers (name,phone,address,village,bank_account,ifsc_code,join_date,status) VALUES ('$name','$phone','$address','$village','$bank','$ifsc','$date','$status')");
        $msg = '<div class="alert alert-success">✅ Farmer added successfully.</div>';
    }
    $action = '';
}
 
// Handle delete (admin only)
if (is_admin() && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM farmers WHERE farmer_id=$id");
    $msg = '<div class="alert alert-success">✅ Farmer deleted.</div>';
}
 
// Handle edit load (admin only)
if (is_admin() && $action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $edit_farmer = $conn->query("SELECT * FROM farmers WHERE farmer_id=$id")->fetch_assoc();
}
 
// Search
$search = $conn->real_escape_string($_GET['search'] ?? '');
$where  = $search ? "WHERE name LIKE '%$search%' OR phone LIKE '%$search%' OR village LIKE '%$search%'" : '';
$farmers = $conn->query("SELECT * FROM farmers $where ORDER BY farmer_id DESC");
 
require '../includes/header.php';
?>
<div class="page-header">
    <h1>👨‍🌾 Farmer Management</h1>
    <p>Register, update and manage all dairy farmers in the cooperative</p>
</div>
<div class="page-content">
    <?= $msg ?>
 
    <div class="card">
        <div class="card-header">
            <h2><?= ($action === 'edit') ? 'Edit Farmer' : 'Add New Farmer' ?></h2>
            <?php if (is_admin() && $action !== 'add' && $action !== 'edit'): ?>
            <a href="?action=add" class="btn btn-primary btn-sm">➕ Add Farmer</a>
            <?php endif; ?>
        </div>
 
        <?php if (is_admin() && ($action === 'add' || $action === 'edit')): ?>
        <div class="card-body">
            <form method="POST">
                <?php if ($edit_farmer): ?>
                <input type="hidden" name="farmer_id" value="<?= $edit_farmer['farmer_id'] ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required value="<?= $edit_farmer['name'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="text" name="phone" required maxlength="15" value="<?= $edit_farmer['phone'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Village</label>
                        <input type="text" name="village" value="<?= $edit_farmer['village'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Join Date *</label>
                        <input type="date" name="join_date" required value="<?= $edit_farmer['join_date'] ?? date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Bank Account No.</label>
                        <input type="text" name="bank_account" value="<?= $edit_farmer['bank_account'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>IFSC Code</label>
                        <input type="text" name="ifsc_code" value="<?= $edit_farmer['ifsc_code'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?= (($edit_farmer['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (($edit_farmer['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Address *</label>
                        <textarea name="address" rows="2" required><?= $edit_farmer['address'] ?? '' ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success"><?= $edit_farmer ? '💾 Update' : '✅ Add Farmer' ?></button>
                    <a href="farmers.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
 
    <div class="card">
        <div class="card-header">
            <h2>All Farmers (<?= $farmers->num_rows ?>)</h2>
            <form method="GET" style="display:flex;gap:0.5rem;">
                <input type="text" name="search" placeholder="Search name, phone, village..." value="<?= htmlspecialchars($search) ?>" style="padding:7px 12px;border:1.5px solid var(--border);border-radius:6px;font-family:var(--font-body);font-size:0.88rem;">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <?php if ($search): ?><a href="farmers.php" class="btn btn-secondary btn-sm">Clear</a><?php endif; ?>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Phone</th><th>Village</th><th>Bank Account</th><th>Joined</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($farmers->num_rows === 0): ?>
                    <tr><td colspan="8" class="no-data">No farmers found.</td></tr>
                <?php else: ?>
                    <?php while ($f = $farmers->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $f['farmer_id'] ?></strong></td>
                        <td><?= htmlspecialchars($f['name']) ?></td>
                        <td><?= $f['phone'] ?></td>
                        <td><?= htmlspecialchars($f['village']) ?></td>
                        <td><?= $f['bank_account'] ? '****'.substr($f['bank_account'],-4) : '—' ?></td>
                        <td><?= date('d M Y', strtotime($f['join_date'])) ?></td>
                        <td><span class="badge <?= $f['status']==='active'?'badge-green':'badge-red' ?>"><?= $f['status'] ?></span></td>
                        <td>
                            <?php if (is_admin()): ?>
                            <a href="?action=edit&id=<?= $f['farmer_id'] ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
                            <a href="bills.php?farmer_id=<?= $f['farmer_id'] ?>" class="btn btn-primary btn-sm">🧾 Bill</a>
                            <a href="?delete=<?= $f['farmer_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this farmer?')">🗑️</a>
                            <?php else: ?>
                            <a href="bills.php?farmer_id=<?= $f['farmer_id'] ?>" class="btn btn-primary btn-sm">🧾 Bill</a>
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