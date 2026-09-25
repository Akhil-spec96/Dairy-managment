<?php
$page_title = 'Products';
require '../includes/db.php';
 
$msg = '';
$edit_prod = null;
$action = $_GET['action'] ?? '';
 
if (!is_admin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || in_array($action, ['add','edit']) || isset($_GET['delete'])) {
        $action = '';
        $msg = '<div class="view-only-notice">👁️ You are in <strong>view-only mode</strong>. <a href="../login.php">Login as Admin</a> to add, edit or delete records.</div>';
    }
}
 
if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = $conn->real_escape_string($_POST['product_name']);
    $unit   = $conn->real_escape_string($_POST['unit']);
    $price  = (float)$_POST['price_per_unit'];
    $stock  = (float)$_POST['stock_quantity'];
    $desc   = $conn->real_escape_string($_POST['description'] ?? '');
    $status = $conn->real_escape_string($_POST['status']);
 
    if (isset($_POST['product_id']) && $_POST['product_id']) {
        $id = (int)$_POST['product_id'];
        $conn->query("UPDATE products SET product_name='$name',unit='$unit',price_per_unit=$price,stock_quantity=$stock,description='$desc',status='$status' WHERE product_id=$id");
        $msg = '<div class="alert alert-success">✅ Product updated.</div>';
    } else {
        $conn->query("INSERT INTO products (product_name,unit,price_per_unit,stock_quantity,description,status) VALUES ('$name','$unit',$price,$stock,'$desc','$status')");
        $msg = '<div class="alert alert-success">✅ Product added.</div>';
    }
    $action = '';
}
 
if (is_admin() && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE product_id=$id");
    $msg = '<div class="alert alert-success">✅ Product deleted.</div>';
}
 
if (is_admin() && $action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $edit_prod = $conn->query("SELECT * FROM products WHERE product_id=$id")->fetch_assoc();
}
 
$products = $conn->query("SELECT * FROM products ORDER BY status ASC, product_name ASC");
$units = ['Litre','Kg','Gram','Packet','Piece','Dozen'];
 
require '../includes/header.php';
?>
<div class="page-header">
    <h1>🧀 Dairy Products</h1>
    <p>Manage product catalogue, pricing and available stock</p>
</div>
<div class="page-content">
    <?= $msg ?>
 
    <div class="card">
        <div class="card-header">
            <h2><?= $action==='edit'?'Edit Product':'Add New Product' ?></h2>
            <?php if (is_admin() && $action !== 'add' && $action !== 'edit'): ?>
            <a href="?action=add" class="btn btn-primary btn-sm">➕ Add Product</a>
            <?php endif; ?>
        </div>
        <?php if (is_admin() && ($action === 'add' || $action === 'edit')): ?>
        <div class="card-body">
            <form method="POST">
                <?php if ($edit_prod): ?>
                <input type="hidden" name="product_id" value="<?= $edit_prod['product_id'] ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="product_name" required value="<?= $edit_prod['product_name'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Unit *</label>
                        <select name="unit" required>
                            <?php foreach ($units as $u): ?>
                            <option value="<?= $u ?>" <?= (($edit_prod['unit'] ?? '') === $u) ? 'selected' : '' ?>><?= $u ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price per Unit (₹) *</label>
                        <input type="number" name="price_per_unit" step="0.01" required value="<?= $edit_prod['price_per_unit'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_quantity" step="0.01" value="<?= $edit_prod['stock_quantity'] ?? '0' ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="available" <?= (($edit_prod['status'] ?? '') === 'available') ? 'selected' : '' ?>>Available</option>
                            <option value="unavailable" <?= (($edit_prod['status'] ?? '') === 'unavailable') ? 'selected' : '' ?>>Unavailable</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Description</label>
                        <textarea name="description" rows="2"><?= $edit_prod['description'] ?? '' ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success"><?= $edit_prod ? '💾 Update' : '✅ Add Product' ?></button>
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
 
    <!-- Products Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.25rem;margin-bottom:2rem;">
        <?php $products->data_seek(0); while ($p = $products->fetch_assoc()): $is_low = $p['stock_quantity'] < 10; ?>
        <div class="service-card" style="position:relative;cursor:default;">
            <?php if ($is_low && $p['status']==='available'): ?>
            <span class="badge badge-red" style="position:absolute;top:14px;right:14px;">Low Stock</span>
            <?php endif; ?>
            <div class="service-icon"><?php
                $icons = ['Milk'=>'🥛','Butter'=>'🧈','Ghee'=>'🫙','Paneer'=>'🧀','Curd'=>'🥣','Cream'=>'🍶'];
                $icon = '🧀';
                foreach ($icons as $k=>$v) { if (stripos($p['product_name'],$k)!==false) { $icon=$v; break; } }
                echo $icon;
            ?></div>
            <h3><?= htmlspecialchars($p['product_name']) ?></h3>
            <p style="font-size:0.85rem;margin-bottom:0.8rem;"><?= htmlspecialchars($p['description'] ?: 'No description.') ?></p>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem;">
                <span style="font-size:1.3rem;font-weight:700;color:var(--amber);font-family:var(--font-display);">₹<?= $p['price_per_unit'] ?></span>
                <span style="font-size:0.82rem;color:var(--text-muted);">per <?= $p['unit'] ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <span style="font-size:0.85rem;color:var(--text-muted);">Stock: <strong style="color:<?= $is_low?'var(--red)':'var(--green)' ?>"><?= $p['stock_quantity'] ?> <?= $p['unit'] ?></strong></span>
                <span class="badge <?= $p['status']==='available'?'badge-green':'badge-red' ?>"><?= $p['status'] ?></span>
            </div>
            <?php if (is_admin()): ?>
            <div style="display:flex;gap:0.5rem;">
                <a href="?action=edit&id=<?= $p['product_id'] ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
                <a href="?delete=<?= $p['product_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">🗑️ Delete</a>
            </div>
            <?php else: ?>
            <span style="font-size:0.78rem;color:var(--text-muted);">🔒 Login as admin to edit</span>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
 
    <!-- Products Table -->
    <div class="card">
        <div class="card-header"><h2>All Products — Table View</h2></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>ID</th><th>Product Name</th><th>Unit</th><th>Price/Unit</th><th>Stock</th><th>Status</th><?php if(is_admin()): ?><th>Actions</th><?php endif; ?></tr>
                </thead>
                <tbody>
                <?php $products->data_seek(0); while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $p['product_id'] ?></td>
                    <td><?= htmlspecialchars($p['product_name']) ?></td>
                    <td><?= $p['unit'] ?></td>
                    <td>₹<?= number_format($p['price_per_unit'],2) ?></td>
                    <td><?= $p['stock_quantity'] ?> <?= $p['unit'] ?></td>
                    <td><span class="badge <?= $p['status']==='available'?'badge-green':'badge-red' ?>"><?= $p['status'] ?></span></td>
                    <?php if (is_admin()): ?>
                    <td>
                        <a href="?action=edit&id=<?= $p['product_id'] ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
                        <a href="?delete=<?= $p['product_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">🗑️</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require '../includes/footer.php'; ?>