<?php
$page_title = 'Bill Generator';
require '../includes/db.php';
 
$farmer_id    = (int)($_GET['farmer_id'] ?? $_POST['farmer_id'] ?? 0);
$bill_month   = $conn->real_escape_string($_GET['bill_month'] ?? $_POST['bill_month'] ?? date('Y-m'));
$farmer       = null;
$bill_rows    = [];
$bill_summary = null;
$msg          = '';
 
// Mark bill as paid (admin only)
if (isset($_GET['mark_paid'])) {
    if (is_admin()) {
        $bid = (int)$_GET['mark_paid'];
        $conn->query("UPDATE bills SET status='paid' WHERE bill_id=$bid");
        $msg = '<div class="alert alert-success">✅ Bill marked as paid.</div>';
    } else {
        $msg = '<div class="view-only-notice">👁️ You are in <strong>view-only mode</strong>. <a href="../login.php">Login as Admin</a> to mark bills as paid.</div>';
    }
}
 
if ($farmer_id) {
    $farmer = $conn->query("SELECT * FROM farmers WHERE farmer_id=$farmer_id")->fetch_assoc();
 
    if ($farmer) {
        $month_start = $bill_month . '-01';
        $month_end   = date('Y-m-t', strtotime($month_start));
 
        $bill_rows_result = $conn->query("
            SELECT dp.production_date, p.product_name, p.unit,
                   dp.morning_qty, dp.evening_qty, dp.total_qty,
                   dp.fat_percentage, dp.snf_percentage, dp.rate_per_unit, dp.amount
            FROM daily_production dp
            JOIN products p ON dp.product_id = p.product_id
            WHERE dp.farmer_id = $farmer_id
              AND dp.production_date BETWEEN '$month_start' AND '$month_end'
            ORDER BY dp.production_date ASC
        ");
 
        while ($row = $bill_rows_result->fetch_assoc()) {
            $bill_rows[] = $row;
        }
 
        $summary = $conn->query("
            SELECT SUM(total_qty) as total_qty, SUM(amount) as total_amount,
                   AVG(fat_percentage) as avg_fat, AVG(snf_percentage) as avg_snf,
                   COUNT(*) as days_count
            FROM daily_production
            WHERE farmer_id=$farmer_id
              AND production_date BETWEEN '$month_start' AND '$month_end'
        ")->fetch_assoc();
 
        $bill_summary = $summary;
 
        // Save bill record (admin only)
        if (is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($bill_rows)) {
            $deductions = (float)($_POST['deductions'] ?? 0);
            $net = $summary['total_amount'] - $deductions;
            $existing = $conn->query("SELECT bill_id FROM bills WHERE farmer_id=$farmer_id AND bill_month='$bill_month'")->fetch_assoc();
            if ($existing) {
                $conn->query("UPDATE bills SET total_quantity={$summary['total_qty']},total_amount={$summary['total_amount']},deductions=$deductions,net_payable=$net WHERE bill_id={$existing['bill_id']}");
            } else {
                $conn->query("INSERT INTO bills (farmer_id,bill_month,total_quantity,total_amount,deductions,net_payable) VALUES ($farmer_id,'$bill_month',{$summary['total_qty']},{$summary['total_amount']},$deductions,$net)");
            }
            $msg = '<div class="alert alert-success">✅ Bill saved successfully.</div>';
        }
 
        // Fetch saved bill info
        $saved_bill = $conn->query("SELECT * FROM bills WHERE farmer_id=$farmer_id AND bill_month='$bill_month'")->fetch_assoc();
        $deductions = (float)($_POST['deductions'] ?? $saved_bill['deductions'] ?? 0);
    }
}
 
$all_farmers = $conn->query("SELECT farmer_id, name FROM farmers WHERE status='active' ORDER BY name");
 
require '../includes/header.php';
?>
<div class="page-header">
    <h1>🧾 Bill Generator</h1>
    <p>Generate monthly payment bills for farmers based on production records</p>
</div>
<div class="page-content">
    <?= $msg ?>
 
    <!-- Search Form -->
    <div class="card">
        <div class="card-header"><h2>Generate Bill</h2></div>
        <div class="card-body">
            <form method="GET">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Farmer *</label>
                        <select name="farmer_id" required>
                            <option value="">— Select Farmer by ID/Name —</option>
                            <?php while ($f = $all_farmers->fetch_assoc()): ?>
                            <option value="<?= $f['farmer_id'] ?>" <?= $farmer_id == $f['farmer_id'] ? 'selected' : '' ?>>#<?= $f['farmer_id'] ?> — <?= htmlspecialchars($f['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Bill Month *</label>
                        <input type="month" name="bill_month" value="<?= $bill_month ?>">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">🔍 Generate Bill</button>
                    <?php if ($farmer_id): ?>
                    <a href="bills.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
 
    <?php if ($farmer && !empty($bill_rows)): ?>
    <!-- Bill Output -->
    <div class="card" id="bill-output">
        <div class="card-body">
            <div class="bill-header">
                <div style="font-size:2rem;margin-bottom:0.3rem;">🐄</div>
                <h2>DairyFlow Cooperative</h2>
                <p style="color:var(--text-muted);font-size:0.9rem;">Dairy Colony, Karnataka, India | +91 98765 43210</p>
                <p style="margin-top:0.5rem;font-weight:600;color:var(--amber);font-size:1.05rem;">
                    FARMER PAYMENT BILL — <?= strtoupper(date('F Y', strtotime($bill_month.'-01'))) ?>
                </p>
            </div>
 
            <div class="bill-meta">
                <div>
                    <strong>Farmer Details</strong><br>
                    Name: <?= htmlspecialchars($farmer['name']) ?><br>
                    Farmer ID: #<?= $farmer['farmer_id'] ?><br>
                    Phone: <?= $farmer['phone'] ?><br>
                    Village: <?= htmlspecialchars($farmer['village']) ?>
                </div>
                <div>
                    <strong>Bank Details</strong><br>
                    Account: <?= $farmer['bank_account'] ? '****'.substr($farmer['bank_account'],-4) : 'Not provided' ?><br>
                    IFSC: <?= $farmer['ifsc_code'] ?: 'Not provided' ?><br>
                    Bill Date: <?= date('d M Y') ?><br>
                    Status: <span class="badge <?= isset($saved_bill) && $saved_bill['status']==='paid'?'badge-green':'badge-amber' ?>"><?= isset($saved_bill) ? $saved_bill['status'] : 'pending' ?></span>
                </div>
            </div>
 
            <!-- Production Table -->
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th><th>Product</th><th>Morning</th><th>Evening</th><th>Total</th><th>Fat%</th><th>SNF%</th><th>Rate/Unit</th><th>Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bill_rows as $row): ?>
                        <tr>
                            <td><?= date('d M', strtotime($row['production_date'])) ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= $row['morning_qty'] ?> <?= $row['unit'] ?></td>
                            <td><?= $row['evening_qty'] ?> <?= $row['unit'] ?></td>
                            <td><strong><?= $row['total_qty'] ?> <?= $row['unit'] ?></strong></td>
                            <td><?= $row['fat_percentage'] ?>%</td>
                            <td><?= $row['snf_percentage'] ?>%</td>
                            <td>₹<?= $row['rate_per_unit'] ?></td>
                            <td>₹<?= number_format($row['amount'],2) ?></td>
                        </tr>
                        <?php endforeach; ?>
 
                        <!-- Totals -->
                        <tr class="bill-total-row">
                            <td colspan="4"><strong>TOTAL</strong></td>
                            <td><strong><?= number_format($bill_summary['total_qty'],2) ?></strong></td>
                            <td><strong><?= number_format($bill_summary['avg_fat'],2) ?>%</strong></td>
                            <td><strong><?= number_format($bill_summary['avg_snf'],2) ?>%</strong></td>
                            <td>—</td>
                            <td><strong>₹<?= number_format($bill_summary['total_amount'],2) ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="8" style="text-align:right;color:var(--text-muted);">Deductions (loan / advance)</td>
                            <td>- ₹<?= number_format($deductions,2) ?></td>
                        </tr>
                        <tr class="bill-grand-total">
                            <td colspan="8"><strong>NET PAYABLE</strong></td>
                            <td><strong>₹<?= number_format($bill_summary['total_amount'] - $deductions, 2) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
 
            <!-- Summary Info -->
            <div style="margin-top:1.5rem;padding:1rem;background:var(--warm-bg);border-radius:var(--radius-sm);font-size:0.88rem;color:var(--text-muted);">
                📅 Collection Days: <strong><?= $bill_summary['days_count'] ?></strong> &nbsp;|&nbsp;
                🥛 Total Quantity: <strong><?= number_format($bill_summary['total_qty'],2) ?> L/Kg</strong> &nbsp;|&nbsp;
                🧪 Avg Fat: <strong><?= number_format($bill_summary['avg_fat'],2) ?>%</strong> &nbsp;|&nbsp;
                🧪 Avg SNF: <strong><?= number_format($bill_summary['avg_snf'],2) ?>%</strong>
            </div>
 
            <!-- Actions -->
            <div class="form-actions no-print" style="margin-top:1.5rem;">
                <?php if (is_admin()): ?>
                <form method="POST" style="display:inline-flex;align-items:center;gap:0.5rem;">
                    <input type="hidden" name="farmer_id" value="<?= $farmer_id ?>">
                    <input type="hidden" name="bill_month" value="<?= $bill_month ?>">
                    <label style="font-size:0.85rem;font-weight:600;color:var(--text-muted);">Deductions ₹:</label>
                    <input type="number" name="deductions" step="0.01" value="<?= $deductions ?>" style="width:120px;padding:8px;border:1.5px solid var(--border);border-radius:6px;font-family:var(--font-body);">
                    <button type="submit" class="btn btn-success">💾 Save Bill</button>
                </form>
                <?php else: ?>
                <div class="view-only-notice">👁️ <strong>View-only mode</strong> — <a href="../login.php">Login as Admin</a> to save bills or mark as paid.</div>
                <?php endif; ?>
                <button onclick="window.print()" class="btn btn-primary">🖨️ Print Bill</button>
                <?php if (is_admin() && isset($saved_bill) && $saved_bill['status'] === 'pending'): ?>
                <a href="?farmer_id=<?= $farmer_id ?>&bill_month=<?= $bill_month ?>&mark_paid=<?= $saved_bill['bill_id'] ?>" class="btn btn-success" onclick="return confirm('Mark as paid?')">✅ Mark Paid</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
 
    <?php elseif ($farmer && empty($bill_rows)): ?>
    <div class="alert alert-info">ℹ️ No production records found for <strong><?= htmlspecialchars($farmer['name']) ?></strong> in <?= date('F Y', strtotime($bill_month.'-01')) ?>. Please add production logs first.</div>
 
    <?php elseif ($farmer_id && !$farmer): ?>
    <div class="alert alert-error">❌ Farmer not found. Please select a valid farmer.</div>
    <?php endif; ?>
 
    <!-- Saved Bills History -->
    <?php
    $all_bills = $conn->query("
        SELECT b.*, f.name as farmer_name
        FROM bills b JOIN farmers f ON b.farmer_id = f.farmer_id
        ORDER BY b.generated_at DESC LIMIT 20
    ");
    ?>
    <?php if ($all_bills->num_rows > 0): ?>
    <div class="card no-print">
        <div class="card-header"><h2>Recent Bills</h2></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Bill ID</th><th>Farmer</th><th>Month</th><th>Total Qty</th><th>Total Amount</th><th>Deductions</th><th>Net Payable</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while ($b = $all_bills->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $b['bill_id'] ?></td>
                        <td><?= htmlspecialchars($b['farmer_name']) ?></td>
                        <td><?= date('M Y', strtotime($b['bill_month'].'-01')) ?></td>
                        <td><?= number_format($b['total_quantity'],2) ?></td>
                        <td>₹<?= number_format($b['total_amount'],2) ?></td>
                        <td>₹<?= number_format($b['deductions'],2) ?></td>
                        <td><strong>₹<?= number_format($b['net_payable'],2) ?></strong></td>
                        <td><span class="badge <?= $b['status']==='paid'?'badge-green':'badge-amber' ?>"><?= $b['status'] ?></span></td>
                        <td>
                            <a href="bills.php?farmer_id=<?= $b['farmer_id'] ?>&bill_month=<?= $b['bill_month'] ?>" class="btn btn-secondary btn-sm">👁️ View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require '../includes/footer.php'; ?>