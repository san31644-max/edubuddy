<?php
require_once __DIR__.'/includes/auth.php';
redirect(user()?'student/dashboard.php':'login.php');
