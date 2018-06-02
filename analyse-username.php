<?php

include 'common.php';

$query = $db->query('SELECT uid, username FROM pre_ucenter_members');
while ($info = $query->fetch(PDO::FETCH_ASSOC)) {
    if (preg_match('/[^a-zA-Z0-9_\-\x{0800}-\x{9fa5}]/u', $info['username'])) {
        echo $info['username'] . PHP_EOL;
    }
}

