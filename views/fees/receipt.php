<?php
/**
 * Fee Receipt View
 * Renders a formatted receipt for a paid fee.
 */
$view = 'fees/receipt';
ob_start();
?>
<div class="card">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2><?=APP_NAME?></h2>
        <p>Fee Receipt</p>
    </div>
    <?php if(!empty($feeData)): ?>
        <table>
            <tr><th>Receipt ID</th><td><?=escapeOutput($feeData['id'])?></td></tr>
            <tr><th>Student</th><td><?=escapeOutput($feeData['student_name'] ?? '')?> (<?=escapeOutput($feeData['roll_number'] ?? '')?>)</td></tr>
            <tr><th>Room</th><td><?=escapeOutput($feeData['room_number'] ?? '')?> - <?=escapeOutput($feeData['hostel_name'] ?? '')?></td></tr>
            <tr><th>Fee Type</th><td><?=escapeOutput(ucfirst($feeData['fee_type'] ?? ''))?></td></tr>
            <tr><th>Amount</th><td><?=formatCurrency($feeData['amount'] ?? 0)?></td></tr>
            <tr><th>Due Date</th><td><?=formatDate($feeData['due_date'] ?? '')?></td></tr>
            <tr><th>Paid Date</th><td><?=formatDate($feeData['paid_date'] ?? '')?></td></tr>
            <tr><th>Payment Method</th><td><?=escapeOutput(ucfirst(str_replace('_', ' ', $feeData['payment_method'] ?? '')))?></td></tr>
            <tr><th>Transaction ID</th><td><?=escapeOutput($feeData['transaction_id'] ?? '-')?></td></tr>
            <tr><th>Status</th><td><span class="badge badge-paid"><?=escapeOutput(ucfirst($feeData['status'] ?? ''))?></span></td></tr>
            <?php if(!empty($feeData['notes'])): ?>
                <tr><th>Notes</th><td><?=escapeOutput($feeData['notes'])?></td></tr>
            <?php endif ?>
        </table>
    <?php else: ?>
        <p>No fee data available.</p>
    <?php endif ?>
</div>
<?php
$content = ob_get_clean();
require APP_ROOT . '/views/app.php';
