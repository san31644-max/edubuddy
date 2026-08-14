<?php
declare(strict_types=1);

require_once __DIR__.'/includes/auth.php';

$error='';
$deleted=isset($_GET['deleted']);
if($_SERVER['REQUEST_METHOD']==='POST'){
    require_login();
    verify_csrf();
    $current=user();
    $password=(string)($_POST['password']??'');
    $record=query_one('SELECT password_hash,profile_image FROM users WHERE id=?','i',[(int)$current['id']]);
    if(!$record||!password_verify($password,(string)$record['password_hash'])){
        $error='The password is incorrect. Your account was not deleted.';
    }else{
        $database=db();
        $database->begin_transaction();
        try{
            $statement=$database->prepare('DELETE FROM users WHERE id=?');
            if(!$statement)throw new RuntimeException($database->error);
            $userId=(int)$current['id'];
            $statement->bind_param('i',$userId);
            if(!$statement->execute()||$statement->affected_rows!==1)throw new RuntimeException($statement->error?:'Account not found.');
            $database->commit();
            $profileImage=basename((string)($record['profile_image']??''));
            if($profileImage!==''){
                $profilePath=__DIR__.'/uploads/profiles/'.$profileImage;
                if(is_file($profilePath))@unlink($profilePath);
            }
            $_SESSION=[];
            if(ini_get('session.use_cookies')){
                $params=session_get_cookie_params();
                setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secure'],$params['httponly']);
            }
            session_destroy();
            redirect('delete-account.php?deleted=1');
        }catch(Throwable $exception){
            $database->rollback();
            error_log('Account deletion failed: '.$exception->getMessage());
            $error='We could not delete your account right now. Please contact support for assistance.';
        }
    }
}

$pageTitle='Delete K Education account';
include __DIR__.'/includes/header.php';
?>
<section class="card" style="max-width:760px;margin-inline:auto">
 <h1>Delete your K Education account</h1>
 <?php if($deleted):?>
  <div class="alert success">Your K Education account and associated data have been permanently deleted.</div>
 <?php else:?>
  <p>You can permanently delete your K Education student account using this page.</p>
  <h2>Data that will be deleted</h2>
  <p>Your profile and contact details, profile photo, learning progress, quiz results, points and emblems, AI Tutor conversations, activity history, parent links, and subscription records associated with the account will be deleted.</p>
  <p class="muted">Deletion is immediate in the active service. Limited copies may remain in encrypted system backups or security logs for up to 90 days before automatic removal, unless a longer period is required for security, fraud prevention, or legal compliance.</p>
  <?php if($error):?><div class="alert error" role="alert"><?=e($error)?></div><?php endif;?>
  <?php if(user()):?>
   <h2>Confirm deletion</h2>
   <p>Signed in as <strong><?=e((string)user()['full_name'])?></strong>. Enter your password to confirm. This action cannot be undone.</p>
   <form method="post">
    <?=csrf_field()?>
    <label>Current password<input type="password" name="password" autocomplete="current-password" required></label>
    <button class="btn danger" type="submit" onclick="return confirm('Permanently delete your K Education account and associated data?')">Permanently delete account</button>
   </form>
  <?php else:?>
   <h2>How to request deletion</h2>
   <ol>
    <li><a href="<?=url('login.php')?>">Sign in to K Education</a> using the account you want to delete.</li>
    <li>Return to this page from the Profile screen.</li>
    <li>Enter your password and select <strong>Permanently delete account</strong>.</li>
   </ol>
   <p>If you cannot sign in, use the support contact shown on the K Education Google Play listing to request assistance.</p>
  <?php endif;?>
 <?php endif;?>
</section>
<?php include __DIR__.'/includes/footer.php';
