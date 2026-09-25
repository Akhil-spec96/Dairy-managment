<?php
$page_title = 'Daily Production';
require '../includes/db.php';

$msg    = '';
$action = $_GET['action'] ?? '';
$edit_prod = null;

// Block non-admin writes
if (!is_admin()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || in_array($action, ['add','edit']) || isset($_GET['delete'])) {
        $action = '';
        $msg = '<div class="view-only-notice">👁️ You are in <strong>view-only mode</strong>. <a href="../login.php">Login as Admin</a> to add, edit or delete records.</div>';
    }
}

// ── SAVE / UPDATE ──────────────────────────────────────────────
if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmer_id  = (int)$_POST['farmer_id'];
    $product_id = (int)$_POST['product_id'];
    $date       = $conn->real_escape_string($_POST['production_date']);
    $morning    = (float)$_POST['morning_qty'];
    $evening    = (float)$_POST['evening_qty'];
    $new_total  = $morning + $evening;
    $fat        = (float)$_POST['fat_percentage'];
    $snf        = (float)$_POST['snf_percentage'];
    $rate       = (float)$_POST['rate_per_unit'];
    $remarks    = $conn->real_escape_string($_POST['remarks'] ?? '');

    if (isset($_POST['production_id']) && $_POST['production_id']) {
        // ── EDIT: fetch old record to compute stock difference ──
        $id  = (int)$_POST['production_id'];
        $old = $conn->query("SELECT total_qty, product_id FROM daily_production WHERE production_id=$id")->fetch_assoc();
        $old_total      = (float)($old['total_qty'] ?? 0);
        $old_product_id = (int)($old['product_id'] ?? $product_id);

        $conn->query("UPDATE daily_production
            SET farmer_id=$farmer_id, product_id=$product_id,
                production_date='$date', morning_qty=$morning, evening_qty=$evening,
                fat_percentage=$fat, snf_percentage=$snf,
                rate_per_unit=$rate, remarks='$remarks'
            WHERE production_id=$id");

        // Product changed → reverse stock on old, add to new
        if ($old_product_id !== $product_id) {
            $conn->query("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity - $old_total) WHERE product_id=$old_product_id");
            $conn->query("UPDATE products SET stock_quantity = stock_quantity + $new_total WHERE product_id=$product_id");
        } else {
            // Same product → apply net difference
            $diff = $new_total - $old_total;
            $conn->query("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity + $diff) WHERE product_id=$product_id");
        }
        $msg = '<div class="alert alert-success">✅ Record updated and product stock adjusted automatically.</div>';

    } else {
        // ── INSERT or UPSERT: check for existing record ──
        $existing = $conn->query("
            SELECT production_id, total_qty FROM daily_production
            WHERE farmer_id=$farmer_id AND product_id=$product_id AND production_date='$date'
        ")->fetch_assoc();

        if ($existing) {
            $old_total = (float)($existing['total_qty'] ?? 0);
            $diff      = $new_total - $old_total;

            $conn->query("UPDATE daily_production
                SET morning_qty=$morning, evening_qty=$evening,
                    fat_percentage=$fat, snf_percentage=$snf,
                    rate_per_unit=$rate, remarks='$remarks'
                WHERE production_id={$existing['production_id']}");

            // Adjust stock by difference only (handles both increase and decrease)
            $conn->query("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity + $diff) WHERE product_id=$product_id");
            $sign = $diff >= 0 ? '+' : '';
            $msg = '<div class="alert alert-success">✅ Daily record updated — product stock adjusted by <strong>' . $sign . number_format($diff, 2) . '</strong>.</div>';
        } else {
            $conn->query("INSERT INTO daily_production
                (farmer_id, product_id, production_date, morning_qty, evening_qty,
                 fat_percentage, snf_percentage, rate_per_unit, remarks)
                VALUES ($farmer_id, $product_id, '$date', $morning, $evening,
                        $fat, $snf, $rate, '$remarks')");

            // Add full quantity to product stock
            $conn->query("UPDATE products SET stock_quantity = stock_quantity + $new_total WHERE product_id=$product_id");
            $msg = '<div class="alert alert-success">✅ Production saved — product stock increased by <strong>+' . number_format($new_total, 2) . '</strong>.</div>';
        }
    }
    $action = '';
}

// ── DELETE ─────────────────────────────────────────────────────
if (is_admin() && isset($_GET['delete'])) {
    $id  = (int)$_GET['delete'];
    // Fetch the record before deleting so we can reverse the stock
    $rec = $conn->query("SELECT total_qty, product_id FROM daily_production WHERE production_id=$id")->fetch_assoc();
    if ($rec) {
        $conn->query("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity - {$rec['total_qty']}) WHERE product_id={$rec['product_id']}");
    }
    $conn->query("DELETE FROM daily_production WHERE production_id=$id");
    $msg = '<div class="alert alert-success">✅ Record deleted and product stock reversed.</div>';
}

// ── EDIT LOAD ──────────────────────────────────────────────────
if (is_admin() && $action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $edit_prod = $conn->query("SELECT * FROM daily_production WHERE production_id=$id")->fetch_assoc();
}

// ── FILTERS ────────────────────────────────────────────────────
$filter_date   = $conn->real_escape_string($_GET['filter_date'] ?? date('Y-m-d'));
$filter_farmer = (int)($_GET['filter_farmer'] ?? 0);
$where_clauses = ["dp.production_date='$filter_date'"];
if ($filter_farmer) $where_clauses[] = "dp.farmer_id=$filter_farmer";
$where = 'WHERE ' . implode(' AND ', $where_clauses);

// One row per farmer per day — GROUP BY ensures no duplicates in display
$records = $conn->query("
    SELECT dp.*, f.name AS farmer_name, p.product_name, p.unit
    FROM daily_production dp
    JOIN farmers f ON dp.farmer_id = f.farmer_id
    JOIN products p ON dp.product_id = p.product_id
    $where
    ORDER BY f.name ASC
");

$totals = $conn->query("
    SELECT SUM(total_qty) as total_qty, SUM(amount) as total_amount, COUNT(*) as total_farmers
    FROM daily_production dp $where
")->fetch_assoc();

$farmers_list  = $conn->query("SELECT farmer_id, name FROM farmers WHERE status='active' ORDER BY name");
$products_list = $conn->query("SELECT product_id, product_name, price_per_unit, unit FROM products WHERE status='available' ORDER BY product_name");

require '../includes/header.php';
?>
<div class="page-header">
    <h1>🥛 Daily Production Log</h1>
    <p>One record per farmer per day — enter morning and evening quantities together</p>
</div>
<div class="page-content">
    <?= $msg ?>

    <!-- ── FORM ── -->
    <div class="card">
        <div class="card-header">
            <h2><?= $action==='edit' ? '✏️ Edit Production Record' : '➕ Log Daily Production' ?></h2>
            <?php if (is_admin() && $action !== 'add' && $action !== 'edit'): ?>
            <a href="?action=add" class="btn btn-primary btn-sm">➕ Add Record</a>
            <?php endif; ?>
        </div>

        <?php if (is_admin() && ($action === 'add' || $action === 'edit')): ?>
        <div class="card-body">
            <?php if (!$edit_prod): ?>
            <div class="alert alert-info" style="margin-bottom:1.2rem;">
                ℹ️ If a record already exists for the selected farmer, product and date, it will be <strong>updated</strong> automatically — no duplicate entries.
            </div>
            <?php endif; ?>
            <form method="POST">
                <?php if ($edit_prod): ?>
                <input type="hidden" name="production_id" value="<?= $edit_prod['production_id'] ?>">
                <?php endif; ?>

                <!-- Row 1: Farmer / Product / Date -->
                <div class="form-grid">
                    <div class="form-group">
                        <label>Farmer *</label>
                        <select name="farmer_id" required id="farmerSel">
                            <option value="">— Select Farmer —</option>
                            <?php $farmers_list->data_seek(0); while ($f = $farmers_list->fetch_assoc()): ?>
                            <option value="<?= $f['farmer_id'] ?>" <?= (($edit_prod['farmer_id'] ?? '') == $f['farmer_id']) ? 'selected' : '' ?>>#<?= $f['farmer_id'] ?> — <?= htmlspecialchars($f['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Product *</label>
                        <select name="product_id" required id="productSel">
                            <option value="">— Select Product —</option>
                            <?php $products_list->data_seek(0); while ($p = $products_list->fetch_assoc()): ?>
                            <option value="<?= $p['product_id'] ?>" data-rate="<?= $p['price_per_unit'] ?>" <?= (($edit_prod['product_id'] ?? '') == $p['product_id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['product_name']) ?> (<?= $p['unit'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="production_date" required value="<?= $edit_prod['production_date'] ?? date('Y-m-d') ?>">
                    </div>
                </div>

                <!-- Row 2: Quantities side by side with visual separator -->
                <div style="background:var(--warm-bg);border:1px solid var(--border);border-radius:var(--radius);padding:1.2rem;margin:1rem 0;">
                    <div style="font-size:0.8rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:0.9rem;">🥛 Milk Collection Quantities</div>
                    <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:1rem;align-items:center;">
                        <div class="form-group" style="margin:0;">
                            <label style="color:var(--amber);">🌅 Morning Qty (L/Kg)</label>
                            <input type="number" name="morning_qty" id="morningQty" step="0.01" min="0" value="<?= $edit_prod['morning_qty'] ?? '0' ?>" style="font-size:1.05rem;font-weight:600;">
                        </div>
                        <div style="text-align:center;font-size:1.5rem;color:var(--border);padding-top:1.5rem;">+</div>
                        <div class="form-group" style="margin:0;">
                            <label style="color:var(--brown);">🌙 Evening Qty (L/Kg)</label>
                            <input type="number" name="evening_qty" id="eveningQty" step="0.01" min="0" value="<?= $edit_prod['evening_qty'] ?? '0' ?>" style="font-size:1.05rem;font-weight:600;">
                        </div>
                    </div>
                    <div style="margin-top:0.9rem;padding:0.7rem 1rem;background:white;border-radius:6px;display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:0.85rem;color:var(--text-muted);font-weight:600;">= Total for the day</span>
                        <span id="totalDisplay" style="font-size:1.2rem;font-weight:700;color:var(--green);font-family:var(--font-display);">
                            <?= number_format(($edit_prod['morning_qty'] ?? 0) + ($edit_prod['evening_qty'] ?? 0), 2) ?> L/Kg
                        </span>
                    </div>
                </div>

                <!-- Row 3: Quality + Rate -->
                <div class="form-grid">
                    <div class="form-group">
                        <label>Fat % (CLR)</label>
                        <input type="number" name="fat_percentage" step="0.01" min="0" max="10" value="<?= $edit_prod['fat_percentage'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>SNF %</label>
                        <input type="number" name="snf_percentage" step="0.01" min="0" max="15" value="<?= $edit_prod['snf_percentage'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Rate per Unit (₹) *</label>
                        <input type="number" name="rate_per_unit" id="rateInput" step="0.01" required value="<?= $edit_prod['rate_per_unit'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <input type="text" name="remarks" placeholder="Optional notes..." value="<?= $edit_prod['remarks'] ?? '' ?>">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success"><?= $edit_prod ? '💾 Update Record' : '✅ Save Daily Record' ?></button>
                    <a href="production.php?filter_date=<?= $filter_date ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── RECORDS TABLE ── -->
    <div class="card">
        <div class="card-header">
            <h2>Production Records</h2>
            <form method="GET" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
                <input type="date" name="filter_date" value="<?= $filter_date ?>" style="padding:7px 12px;border:1.5px solid var(--border);border-radius:6px;font-family:var(--font-body);font-size:0.88rem;">
                <select name="filter_farmer" style="padding:7px 12px;border:1.5px solid var(--border);border-radius:6px;font-family:var(--font-body);font-size:0.88rem;">
                    <option value="">All Farmers</option>
                    <?php $farmers_list->data_seek(0); while ($f = $farmers_list->fetch_assoc()): ?>
                    <option value="<?= $f['farmer_id'] ?>" <?= $filter_farmer == $f['farmer_id'] ? 'selected' : '' ?>><?= htmlspecialchars($f['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
            </form>
        </div>

        <!-- Day Summary Bar -->
        <?php if ($totals['total_qty'] > 0): ?>
        <div style="display:flex;gap:0;flex-wrap:wrap;border-bottom:1px solid var(--border);">
            <div style="flex:1;min-width:140px;padding:1rem 1.6rem;border-right:1px solid var(--border);">
                <span style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;letter-spacing:0.5px;display:block;">Farmers Today</span>
                <strong style="color:var(--brown);font-size:1.4rem;font-family:var(--font-display);"><?= $totals['total_farmers'] ?></strong>
            </div>
            <div style="flex:1;min-width:140px;padding:1rem 1.6rem;border-right:1px solid var(--border);">
                <span style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;letter-spacing:0.5px;display:block;">Total Collected</span>
                <strong style="color:var(--green);font-size:1.4rem;font-family:var(--font-display);"><?= number_format($totals['total_qty'],2) ?> L</strong>
            </div>
            <div style="flex:1;min-width:140px;padding:1rem 1.6rem;">
                <span style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;letter-spacing:0.5px;display:block;">Total Payable</span>
                <strong style="color:var(--amber);font-size:1.4rem;font-family:var(--font-display);">₹<?= number_format($totals['total_amount'],2) ?></strong>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Farmer</th>
                        <th>Product</th>
                        <th>🌅 Morning</th>
                        <th>🌙 Evening</th>
                        <th>Total</th>
                        <th>Fat%</th>
                        <th>SNF%</th>
                        <th>Rate/Unit</th>
                        <th>Amount</th>
                        <?php if (is_admin()): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if ($records->num_rows === 0): ?>
                    <tr><td colspan="<?= is_admin()?11:10 ?>" class="no-data">No production records for <?= date('d M Y', strtotime($filter_date)) ?>.</td></tr>
                <?php else: ?>
                    <?php while ($r = $records->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $r['production_id'] ?></strong></td>
                        <td><?= htmlspecialchars($r['farmer_name']) ?></td>
                        <td><?= htmlspecialchars($r['product_name']) ?></td>
                        <td><?= number_format($r['morning_qty'],2) ?> <?= $r['unit'] ?></td>
                        <td><?= number_format($r['evening_qty'],2) ?> <?= $r['unit'] ?></td>
                        <td><strong style="color:var(--green);"><?= number_format($r['total_qty'],2) ?> <?= $r['unit'] ?></strong></td>
                        <td><?= $r['fat_percentage'] ?>%</td>
                        <td><?= $r['snf_percentage'] ?>%</td>
                        <td>₹<?= number_format($r['rate_per_unit'],2) ?></td>
                        <td><strong>₹<?= number_format($r['amount'],2) ?></strong></td>
                        <?php if (is_admin()): ?>
                        <td>
                            <a href="?action=edit&id=<?= $r['production_id'] ?>&filter_date=<?= $filter_date ?>" class="btn btn-secondary btn-sm">✏️ Edit</a>
                            <a href="?delete=<?= $r['production_id'] ?>&filter_date=<?= $filter_date ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this record?')">🗑️</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Auto-fill rate when product is selected
document.getElementById('productSel')?.addEventListener('change', function() {
    const rate = this.options[this.selectedIndex].dataset.rate;
    if (rate) document.getElementById('rateInput').value = rate;
});

// Live total calculator
function updateTotal() {
    const m = parseFloat(document.getElementById('morningQty')?.value) || 0;
    const e = parseFloat(document.getElementById('eveningQty')?.value) || 0;
    const t = document.getElementById('totalDisplay');
    if (t) t.textContent = (m + e).toFixed(2) + ' L/Kg';
}
document.getElementById('morningQty')?.addEventListener('input', updateTotal);
document.getElementById('eveningQty')?.addEventListener('input', updateTotal);
</script>
<?php require '../includes/footer.php'; ?>