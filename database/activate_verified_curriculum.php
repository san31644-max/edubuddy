<?php
declare(strict_types=1);require_once __DIR__.'/../includes/db.php';$db=db();$value='Verified Grade 6 textbook learning content is active.';$s=$db->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('curriculum_notice',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");$s->bind_param('s',$value);$s->execute();echo "Verified curriculum notice activated.\n";
