<?php

include_once __DIR__ . '/common.php';

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

const AVATAR_PATH = '/var/www/html/flarum/assets/avatars';

$db->addColumns('flarum.flarum_users', [
    'is_migrated'  => 'TINYINT (1 ) UNSIGNED NOT NULL DEFAULT 0',
    'old_password' => 'CHAR    (32) NOT NULL DEFAULT \'\'',
    'old_salt'     => 'CHAR    (6 ) NOT NULL DEFAULT \'\'',
    'old_uid'      => 'INT     (6 ) UNSIGNED NOT NULL DEFAULT 0'
]);

$db->resetTables(['flarum.flarum_users', 'flarum.flarum_notifications',
                  'flarum.flarum_email_tokens']);

$files = glob(AVATAR_PATH . '/*');
foreach($files as $file) {
    if(is_file($file)) @unlink($file);
}

// 经验、学分、贡献、人气、EXP、馒头、弹珠

$i         = 1;
$uid       = 1;
$sql       = '';
$users     = [];
$pwd       = password_hash('PokeUnivCommonPassword', PASSWORD_DEFAULT);
$count     = $db->query('SELECT COUNT(*) FROM pokeuniv_legacy.pre_ucenter_members')->fetch(PDO::FETCH_NUM)[0];
$read_time = Tools::timestampToDatetime(time());
$query     = $db->query('
                SELECT a.uid, a.username, a.email, a.password, a.regip, a.regdate, a.lastloginip, a.lastlogintime, a.salt, 
                       b.extcredits1, b.extcredits2, b.extcredits3, b.extcredits4, b.extcredits5, b.extcredits6, b.extcredits7, b.extcredits8, b.oltime 
                FROM pre_ucenter_members a 
                LEFT JOIN pre_common_member_count b
                ON a.uid = b.uid
           ');
while ($info = $query->fetch(PDO::FETCH_ASSOC)) {

    $pre_text = '(' . $i . '/' . $count . ') Processing user ' . $info['username'] . '...              ';

    Tools::println($pre_text, TRUE);

    // reproduce avatars
    $new_avatar_name = NULL;
    $old_avatar_path = '/var/www/html/pokeuniv-legacy/bbs/uc_server/data/avatar/' . Tools::getOldAvatarPath($info['uid']);
    if (file_exists($old_avatar_path)) {
        $new_avatar_name = Str::lower(Str::quickRandom()) . '.png';
        $new_avatar_path = AVATAR_PATH . '/' . $new_avatar_name;
        file_put_contents($new_avatar_path, (new ImageManager)->make($old_avatar_path)->fit(100, 100)->encode('png', 100));
    }

    $users[$info['uid']] = [
        'id'                      => $uid++,
        'username'                => $info['username'], 
        'email'                   => 'placeholder_' . $i . '@pokeuniv.com', //$info['email'],
        'is_activated'            => 0,
        'password'                => $pwd,
        'bio'                     => NULL,
        'avatar_path'             => $new_avatar_name,
        'preferences'             => NULL,
        'join_time'               => Tools::timestampToDatetime($info['regdate']),
        'last_seen_time'          => NULL,
        'read_time'               => $read_time,
        'notifications_read_time' => NULL,
        'discussions_count'       => 0,
        'comments_count'          => 0,
        'flags_read_time'         => NULL,
        'suspend_until'           => NULL,
        'money'                   => 0, // TODO
        'is_migrated'             => 0,
        'old_password'            => $info['password'],
        'old_salt'                => $info['salt'],
        'old_uid'                 => $info['uid']
    ];

    $i++;
}

Tools::println('');
Tools::println('Migrating users...');
if (!$db->insertData('flarum.flarum_users', $users))
    Tools::println('Failed to generate users. Bye.');
Tools::generateData('users', $users);



