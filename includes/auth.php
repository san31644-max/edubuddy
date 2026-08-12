<?php
declare(strict_types=1);
require_once __DIR__.'/db.php';require_once __DIR__.'/helpers.php';require_once __DIR__.'/referrals.php';require_once __DIR__.'/gamification.php';require_once __DIR__.'/activity.php';
function user():?array{return $_SESSION['user']??null;}
function medium_language(string $medium):string{return ['Sinhala'=>'si','Tamil'=>'ta','English'=>'en'][$medium]??'en';}
function user_grade_number():int{static $number=null;if($number!==null)return $number;$row=user()?query_one('SELECT grade_number FROM grades WHERE id=?','i',[(int)user()['grade_id']]):null;return $number=(int)($row['grade_number']??6);}
function require_login():void{if(!user()){flash('warning','Please log in to continue.');redirect('login.php');}}
function require_guest():void{if(user())redirect('student/dashboard.php');}
function refresh_user():void{if(!user())return;$u=query_one('SELECT id,full_name,username,email,phone,phone_verified_at,school_name,district,grade_id,medium,preferred_language,profile_image,subscription_expires_at,referral_promoter_id,referral_code_used,status FROM users WHERE id=?','i',[user()['id']]);if($u){$u['preferred_language']=medium_language((string)$u['medium']);$_SESSION['user']=$u;$_SESSION['lang']=$u['preferred_language'];}}
function require_admin():void{if(empty($_SESSION['admin']))redirect('admin/login.php');}
function parent_user():?array{return $_SESSION['parent']??null;}
function require_parent():void{if(!parent_user()){flash('warning','Please log in as a parent.');redirect('parent/login.php');}}
function require_parent_guest():void{if(parent_user())redirect('parent/dashboard.php');}
