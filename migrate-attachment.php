<?php

include_once __DIR__ . '/common.php';

use Ramsey\Uuid\Uuid;

const OLD_ATTACH_PATH = '/var/www/html/pokeuniv-legacy/bbs/data/attachment/forum';
const NEW_ATTACH_PATH = '/var/www/html/flarum/assets/files';

$db->resetTables(['flarum.flarum_flagrow_files', 'flarum.flarum_flagrow_file_downloads']);


// remove attachments
// TODO replace by shell script
foreach (glob('/var/www/html/flarum/assets/files/*') as $dir)
    Tools::removeDirectory($dir);

// TODO - abandoned forums attachments

$attach      = [];
$uuids       = [];
$attach_id   = 1;
$attachments = [];
$query  = $db->query('SELECT aid, tid, pid, downloads, uid FROM pokeuniv_legacy.pre_forum_attachment');
while ($info = $query->fetch(PDO::FETCH_ASSOC)) {
    $attach[$info['aid']] = $info;
}
$query = $db->query('SELECT * FROM (
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_0 UNION ALL
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_1 UNION ALL
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_2 UNION ALL
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_3 UNION ALL
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_4 UNION ALL
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_5 UNION ALL
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_6 UNION ALL
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_7 UNION ALL
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_8 UNION ALL
        SELECT aid, tid, pid, dateline, filename, filesize, attachment, remote, isimage FROM pokeuniv_legacy.pre_forum_attachment_9
    ) t ORDER BY t.aid ASC
');
while ($info = $query->fetch(PDO::FETCH_ASSOC)) {

    Tools::println('Processing attachment#%d', TRUE, [$info['aid']]);

    foreach (['tid', 'pid'] as $prop) {
        if ($info[$prop] != $attach[$info['aid']][$prop])
            Tools::println('Bad attachment %s#%d,%d', FALSE, [$prop, $info[$prop], $attach[$info['aid']][$prop]], 'red');
    }
    $attach[$info['aid']] = $attach[$info['aid']] + $info;
    $info = &$attach[$info['aid']];

    do {
        $uuid = Uuid::uuid4()->toString();
    } while (isset($uuids[$uuid]));
    $uuids[$uuid] = 1;

    $old_abs_path  = OLD_ATTACH_PATH . '/' . $info['attachment'];
    $ext           = explode('.', $info['attachment'])[1];
    $base_name     = $uuid . '.' . $ext;
    $folder        = date('Y-m', $info['dateline']);
    $new_rel_path  = $folder . '/' . $info['dateline'] . '-' . $base_name;
    $new_abs_path  = NEW_ATTACH_PATH . '/' . $new_rel_path;
    $url           = 'http://127.0.0.1/flarum/assets/files/' . $new_rel_path; // TODO https
    if (!file_exists($old_abs_path)) {
        Tools::println('Invalid attachment#%d', FALSE, [$info['aid']], 'red');
        continue;
    }
    if (!file_exists($tmp = dirname($new_abs_path)) && !mkdir($tmp)) {
        Tools::println('Cannot create folder#%s', FALSE, [$tmp], 'red');
        continue;
    }
    if (!copy($old_abs_path, $new_abs_path)) {
        Tools::println('Cannot copy file#%s', FALSE, [$old_abs_path], 'red');
        continue;
    }

    $attachments[$info['aid']] = [
        'id'            => $attach_id++,
        'actor_id'      => $info['uid'],
        'discussion_id' => NULL,
        'post_id'       => NULL,
        'base_name'     => $base_name,
        'path'          => $new_rel_path,
        'url'           => $url,
        'type'          => mime_content_type($old_abs_path),
        'size'          => filesize($old_abs_path),
        'upload_method' => 'local',
        'created_at'    => Tools::timestampToDatetime($info['dateline']),
        'remote_id'     => NULL,
        'uuid'          => $uuid,
        'tag'           => $info['isimage'] ? 'image-preview' : 'file',
    ];
    unset($info);
}

Tools::println('');
Tools::println('Migrating attachments...');
if (!$db->insertData('flarum.flarum_flagrow_files', $attachments))
    Tools::println('Failed to generate attachments. Bye.', FALSE, [], 'red');
Tools::generateData('attachments', $attachments);
