<?php
$view = 'leave requests';
ob_start();
?>
<div class="actions">
    <?php if (hasRole(ROLE_STUDENT)): ?><a class="button" href="<?=APP_URL?>/index.php?action=leaves&amp;subaction=add">Request leave</a><?php endif; ?>
</div>
<div class="card table-card">
    <table>
        <thead><tr><th>Student</th><th>From</th><th>To</th><th>Reason</th><th>Status</th><th>Remarks</th><?php if (hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?><th>Decision</th><?php endif; ?></tr></thead>
        <tbody><?php foreach ($leaves as $leave): ?><tr>
            <td><?=escapeOutput($leave['student_name'])?> <small><?=escapeOutput($leave['roll_number'])?></small></td>
            <td><?=formatDate($leave['from_date'])?></td><td><?=formatDate($leave['to_date'])?></td>
            <td><?=escapeOutput($leave['reason'])?></td><td><span class="badge badge-<?=escapeOutput($leave['status'])?>"><?=escapeOutput(ucfirst($leave['status']))?></span></td>
            <td><?=escapeOutput($leave['remarks'] ?: '—')?></td>
            <?php if (hasAnyRole([ROLE_ADMIN, ROLE_WARDEN])): ?><td><?php if ($leave['status'] === 'pending'): ?><form method="post" action="<?=APP_URL?>/index.php?action=leaves&amp;subaction=decision&amp;id=<?=(int)$leave['id']?>" class="inline-form"><input type="hidden" name="csrf_token" value="<?=escapeOutput(generateCsrfToken())?>"><button name="status" value="approved">Approve</button><button class="secondary" name="status" value="rejected">Reject</button></form><?php else: ?><?=escapeOutput($leave['reviewer_name'] ?: '—')?><?php endif; ?></td><?php endif; ?>
        </tr><?php endforeach; ?><?php if (empty($leaves)): ?><tr><td colspan="7">No leave requests found.</td></tr><?php endif; ?></tbody>
    </table>
</div>
<?php $content = ob_get_clean(); require APP_ROOT . '/views/app.php';
