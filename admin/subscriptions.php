<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = (string)($_POST['action'] ?? '');
    $sub = $id ? query_one("SELECT * FROM subscriptions WHERE id=? AND status='pending'", 'i', [$id]) : null;

    if (!$sub || !in_array($action, ['approve', 'reject'], true)) {
        flash('error', 'This payment is no longer pending. Refresh the page and try again.');
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
$rows = db()->query('SELECT s.*,u.full_name,u.username,p.name FROM subscriptions s JOIN users u ON u.id=s.user_id JOIN subscription_plans p ON p.id=s.plan_id ORDER BY s.id DESC');
?>
<h1>Subscription requests</h1>
<section class="card scroll"><table><tr><th>Student</th><th>Reference</th><th>Amount</th><th>Status</th><th>Action</th></tr>
<?php while($r=$rows->fetch_assoc()):?><tr><td><?=e($r['full_name'])?> (@<?=e($r['username'])?>)</td><td><?=e($r['payment_method'])?><br><?=e($r['payment_reference'])?></td><td>LKR <?=e($r['amount_lkr'])?></td><td><?=e($r['status'])?></td><td><?php if($r['status']==='pending'):?><form method="post" class="row"><?=csrf_field()?><input type="hidden" name="id" value="<?=$r['id']?>"><button class="good" name="action" value="approve">Approve</button><button class="danger" name="action" value="reject">Reject</button></form><?php endif;?></td></tr><?php endwhile;?>
</table></section>
<?php include __DIR__.'/../includes/footer.php';?>
