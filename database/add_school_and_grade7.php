<?php
require_once __DIR__.'/../includes/db.php';$db=db();
if(!$db->query("SHOW COLUMNS FROM users LIKE 'school_name'")->num_rows){if(!$db->query("ALTER TABLE users ADD school_name VARCHAR(190) NULL AFTER email"))throw new RuntimeException($db->error);}
$db->query("INSERT INTO grades(grade_number,name,status) SELECT 7,'Grade 7','active' WHERE NOT EXISTS(SELECT 1 FROM grades WHERE grade_number=7)");echo "School field and Grade 7 are ready.\n";
