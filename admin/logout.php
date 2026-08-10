<?php require_once __DIR__.'/../includes/config.php';unset($_SESSION['admin']);header('Location: '.APP_URL.'/admin/login.php');exit;
