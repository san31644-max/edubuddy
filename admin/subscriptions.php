<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    $action = trim((string)($_POST['action'] ?? ''));
    if (!$id || !in_array($action, ['approve', 'reject'], true)) {
        flash('error', 'Invalid payment action. Refresh the page and try again.');
        redirect('admin/subscriptions.php');
    }
    $sub = query_one('SELECT * FROM subscriptions WHERE id=?', 'i', [(int)$id]);
    if (!$sub) {
        flash('error', 'Payment record was not found.');
        redirect('admin/subscriptions.php');
    }
    if ($sub['status'] !== 'pending') {
        flash('warning', 'This payment was already '.($sub['status']==='active'?'approved':$sub['status']).'.');
        redirect('admin/subscriptions.php');
    }

    $db = db();
    $db->begin_transaction();
    try {
        if ($action === 'approve') {
            $userId = (int)$sub['user_id'];
            $currentExpiry = query_one('SELECT subscription_expires_at FROM users WHERE id=? FOR UPDATE', 'i', [$userId]);
            if (!$currentExpiry) throw new RuntimeException('Student account was not found.');
            $base = !empty($currentExpiry['subscription_expires_at']) && strtotime($currentExpiry['subscription_expires_at']) > time()
                ? strtotime($currentExpiry['subscription_expires_at']) : time();
            $start = date('Y-m-d H:i:s');
            $end = date('Y-m-d H:i:s', strtotime('+30 days', $base));

            $s = $db->prepare("UPDATE subscriptions SET status='active',starts_at=?,expires_at=? WHERE id=? AND status='pending'");
            $s->bind_param('ssi', $start, $end, $id);
            if (!$s->execute() || $s->affected_rows !== 1) throw new RuntimeException('Payment status was not updated.');

            $u = $db->prepare('UPDATE users SET subscription_expires_at=? WHERE id=?');
            $u->bind_param('si', $end, $userId);
            if (!$u->execute() || $u->affected_rows < 1) throw new RuntimeException('Student Premium access was not updated.');
            $message = 'Payment approved. Premium is active for 30 days.';
        } else {
            $s = $db->prepare("UPDATE subscriptions SET status='rejected' WHERE id=? AND status='pending'");
            $s->bind_param('i', $id);
            if (!$s->execute() || $s->affected_rows !== 1) throw new RuntimeException('Payment status was not updated.');
            $message = 'Payment rejected.';
        }
        $db->commit();
        flash('success', $message);
    } catch (Throwable $e) {
        $db->rollback();
        error_log('Subscription review failed for payment '.$id.': '.$e->getMessage());
        flash('error', 'Payment update failed: '.$e->getMessage());
    }
    redirect('admin/subscriptions.php');
}

$pageTitle = 'Subscriptions';
include __DIR__.'/_top.php';
$rows = db()->query('SELECT s.*,u.full_name,u.username,p.name,r.referral_code FROM subscriptions s JOIN users u ON u.id=s.user_id JOIN subscription_plans p ON p.id=s.plan_id LEFT JOIN referral_promoters r ON r.id=s.referral_promoter_id ORDER BY s.id DESC');
?>
<h1>Subscription requests</h1>
<section class="card scroll"><table><tr><th>Student</th><th>Reference</th><th>Amount</th><th>Referral</th><th>Status</th><th>Action</th></tr>
<?php while($r=$rows->fetch_assoc()):?><tr><td><?=e($r['full_name'])?> (@<?=e($r['username'])?>)</td><td><?=e($r['payment_method'])?><br><?=e($r['payment_reference'])?><?php if(!empty($r['receipt_path'])):?><br><a href="<?=url($r['receipt_path'])?>" target="_blank" rel="noopener">View receipt photo</a><?php endif;?></td><td>LKR <?=e($r['amount_lkr'])?><?php if((float)$r['discount_lkr']>0):?><br><small>Rs. <?=e($r['discount_lkr'])?> discount</small><?php endif;?></td><td><?=e($r['referral_code']?:'—')?></td><td><?=e($r['status'])?></td><td><?php if($r['status']==='pending'):?><div class="row"><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="action" value="approve"><button type="submit" class="good">Approve</button></form><form method="post" onsubmit="return confirm('Reject this payment?')"><?=csrf_field()?><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="action" value="reject"><button type="submit" class="danger">Reject</button></form></div><?php endif;?></td></tr><?php endwhile;?>
</table></section>
<?php include __DIR__.'/../includes/footer.php';?>
