<?php

include_once __DIR__ . '/common.php';

/*$users = Tools::retrieveData('users');
$tags  = Tools::retrieveData('forums');*/
$query = $db->query('SELECT a.fid, a.name, b.threadtypes 
                     FROM pokeuniv_legacy.pre_forum_forum a 
                     LEFT JOIN pokeuniv_legacy.pre_forum_forumfield b ON a.fid = b.fid WHERE b.threadtypes <> \'\'');
while ($info = $query->fetch(PDO::FETCH_ASSOC)) {
    print_r(unserialize($info['threadtypes'])['types']);
}
