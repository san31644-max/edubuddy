<?php
require_once __DIR__.'/../includes/auth.php';unset($_SESSION['referral_promoter']);session_regenerate_id(true);redirect('referral/login.php');
