<?php
declare(strict_types=1);

ini_set('session.save_path', __DIR__.'/../includes/runtime');
require_once __DIR__.'/../includes/auth.php';
$db=db();
function referral_assert(bool $condition,string $message):void{if(!$condition)throw new RuntimeException($message);echo "PASS: $message\n";}

$plan=query_one('SELECT price_lkr,duration_days FROM subscription_plans WHERE id=1');
referral_assert((float)($plan['price_lkr']??0)===300.0,'Premium database price is Rs. 300');
referral_assert((int)($plan['duration_days']??0)===30,'Premium duration remains 30 days');
referral_assert(active_referral_promoter('NO-SUCH-REFERRAL')===null,'Unknown referral codes are rejected');

$db->begin_transaction();
try{
 $suffix=(string)random_int(100000,999999);$code='TEST-'.$suffix;$email='ref-'.$suffix.'@example.invalid';$hash=password_hash('TestPassword123!',PASSWORD_DEFAULT);
 $s=$db->prepare("INSERT INTO referral_promoters(full_name,email,referral_code,password_hash,status) VALUES('Referral Test Teacher',?,?,?,'active')");$s->bind_param('sss',$email,$code,$hash);referral_assert($s->execute(),'Promoter account can be created');$promoterId=(int)$s->insert_id;
 $promoter=active_referral_promoter(strtolower($code));referral_assert((int)($promoter['id']??0)===$promoterId,'Referral code validation is case-insensitive');
 $grade=query_one("SELECT id FROM grades WHERE status='active' ORDER BY grade_number LIMIT 1");$gradeId=(int)$grade['id'];$username='ref_test_'.$suffix;$studentHash=password_hash('TestStudent123!',PASSWORD_DEFAULT);
 $s=$db->prepare("INSERT INTO users(full_name,username,email,phone,phone_verified_at,school_name,district,password_hash,grade_id,medium,preferred_language,profile_image,subscription_expires_at,referral_promoter_id,referral_code_used,status,created_at,updated_at) VALUES('Referral Test Student',?,NULL,NULL,NOW(),'Test School','Colombo',?,?,'Sinhala','si',NULL,NULL,?,?,'active',NOW(),NOW())");$s->bind_param('ssiis',$username,$studentHash,$gradeId,$promoterId,$code);referral_assert($s->execute(),'Student can register with a referral code');$userId=(int)$s->insert_id;
 $db->query("UPDATE referral_promoters SET registration_limit=1 WHERE id=$promoterId");$fullPromoter=active_referral_promoter($code);referral_assert(!referral_promoter_has_capacity($fullPromoter),'Referral code expires when its registration limit is reached');$db->query("UPDATE referral_promoters SET registration_limit=50 WHERE id=$promoterId");
 $offer=premium_offer_for_user($userId);referral_assert((float)$offer['base']===300.0,'Referred student base price is Rs. 300');referral_assert((float)$offer['discount']===20.0,'Referred student discount is Rs. 20');referral_assert((float)$offer['total']===280.0,'Referred student payable price is Rs. 280');
 $username2='plain_test_'.$suffix;$s=$db->prepare("INSERT INTO users(full_name,username,email,phone,phone_verified_at,school_name,district,password_hash,grade_id,medium,preferred_language,profile_image,subscription_expires_at,referral_promoter_id,referral_code_used,status,created_at,updated_at) VALUES('Plain Test Student',?,NULL,NULL,NOW(),'Test School','Colombo',?,?,'Sinhala','si',NULL,NULL,NULL,NULL,'active',NOW(),NOW())");$s->bind_param('ssi',$username2,$studentHash,$gradeId);referral_assert($s->execute(),'Student can register without a referral code');$plainId=(int)$s->insert_id;$plainOffer=premium_offer_for_user($plainId);referral_assert((float)$plainOffer['total']===300.0,'Student without a referral pays Rs. 300');
 $plainReference='PLAINTEST'.$suffix;$plainTotal=(float)$plainOffer['total'];$plainBase=(float)$plainOffer['base'];$plainDiscount=(float)$plainOffer['discount'];$plainPromoterId=$plainOffer['promoter_id'];$method='bank_transfer';$s=$db->prepare("INSERT INTO subscriptions(user_id,plan_id,amount_lkr,base_amount_lkr,discount_lkr,referral_promoter_id,payment_method,payment_reference,status) VALUES(?,1,?,?,?,?,?,?,'pending')");$s->bind_param('idddiss',$plainId,$plainTotal,$plainBase,$plainDiscount,$plainPromoterId,$method,$plainReference);referral_assert($s->execute(),'Regular Rs. 300 payment stores without a referral');
 $reference='REFTEST'.$suffix;$total=(float)$offer['total'];$base=(float)$offer['base'];$discount=(float)$offer['discount'];
 $s=$db->prepare("INSERT INTO subscriptions(user_id,plan_id,amount_lkr,base_amount_lkr,discount_lkr,referral_promoter_id,payment_method,payment_reference,status) VALUES(?,1,?,?,?,?,?,?,'pending')");$s->bind_param('idddiss',$userId,$total,$base,$discount,$promoterId,$method,$reference);referral_assert($s->execute(),'Discounted payment submission stores server-side pricing');$subscriptionId=(int)$s->insert_id;
 $pending=query_one("SELECT COUNT(*) total FROM subscriptions WHERE referral_promoter_id=? AND status='pending'",'i',[$promoterId]);referral_assert((int)$pending['total']===1,'Promoter report sees the pending purchase');
 $s=$db->prepare("UPDATE subscriptions SET status='active',starts_at=NOW(),expires_at=NOW()+INTERVAL 30 DAY WHERE id=?");$s->bind_param('i',$subscriptionId);$s->execute();$paid=query_one("SELECT COUNT(*) total,COALESCE(SUM(amount_lkr),0) value FROM subscriptions WHERE referral_promoter_id=? AND status='active'",'i',[$promoterId]);referral_assert((int)$paid['total']===1&&(float)$paid['value']===280.0,'Promoter report sees the verified Rs. 280 purchase');
 $_SESSION['referral_promoter']=query_one("SELECT id,full_name,email,phone,referral_code,registration_limit,status,created_at FROM referral_promoters WHERE id=?",'i',[$promoterId]);$_SERVER['SCRIPT_NAME']='/educhat/referral/dashboard.php';$_SERVER['HTTP_HOST']='localhost';ob_start();include __DIR__.'/../referral/dashboard.php';$portalHtml=ob_get_clean();referral_assert(str_contains($portalHtml,'Referral Test Student')&&str_contains($portalHtml,'Paid Rs. 280')&&str_contains($portalHtml,'Student registration QR'),'Promoter dashboard renders the referred student, verified purchase and QR');unset($_SESSION['referral_promoter']);
 $db->rollback();echo "Referral program integration test passed; temporary data was rolled back.\n";
}catch(Throwable $e){$db->rollback();fwrite(STDERR,"FAIL: ".$e->getMessage()."\n");exit(1);}
