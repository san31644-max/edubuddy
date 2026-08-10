<?php require_once __DIR__.'/../includes/auth.php';unset($_SESSION['parent']);session_regenerate_id(true);redirect('parent/login.php');
