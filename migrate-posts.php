<?php

include_once __DIR__ . '/common.php';
include_once __DIR__ . '/library/TextBundle/TextFormatter.php';

$parser = TextFormatter::getParser();
$parser->disablePlugin('Emoticons');

function findPost ($pid) {
    global $posts;
    return [
        $posts[$pid]['discussion_id'],
        $posts[$pid]['number'],
        $posts[$pid]['id']
    ];
}

function addId($tag) {
    global $posts;
    $pid = $tag->getAttribute('id');
    if (!isset($posts[$pid])) 
        return FALSE;
    $tag->setAttribute('discussionid', $posts[$pid]['discussion_id']);
    $tag->setAttribute('number', $posts[$pid]['number']);
    $tag->setAttribute('id', $posts[$pid]['id']);
    return TRUE;
}


$db->addColumns('flarum.flarum_discussions', [
    'old_tid'    => 'INT     (6) UNSIGNED NOT NULL DEFAULT 0',
    'old_typeid' => 'TINYINT (2) UNSIGNED NOT NULL DEFAULT 0',
    'highlight'  => 'TINYINT (2) UNSIGNED NOT NULL DEFAULT 0'
]);

$db->resetTables(['flarum.flarum_discussions', 'flarum.flarum_posts', 
                  'flarum.flarum_discussions_tags', 'flarum.flarum_users_discussions',
                  'flarum.flarum_flags', 'flarum.flarum_mention_posts', 'flarum.mention_users']);

// build user & attachment index
$users         = Tools::retrieveData('users');
$users_by_new  = array_combine(array_column($users, 'id'), $users);
$users_by_name = array_combine(array_column($users, 'username'), $users);
$attachments   = Tools::retrieveData('attachments');

// displayorder: 1-sticky-1, 2-sticky-2, 3-sticky-3, -1-trash, -2-approving, -3-disapproved, -4=draft

$discussions     = [];
$discussion_tags = [];
$user_updates    = [];
$post_id         = 1;
$disc_id         = 1;
$query           = $db->query('SELECT tid, fid, authorid, dateline, subject, views, typeid, highlight, closed, displayorder, status FROM pokeuniv_legacy.pre_forum_thread ORDER BY tid');
while ($thread = $query->fetch(PDO::FETCH_ASSOC)) {

    Tools::println('Processing thread#%d... ', TRUE, [$thread['tid']]);

    // only process taggable forum posts
    if (empty(Mapping::$forum_to_tags[$thread['fid']]))
        continue;

    // check if user exists
    $author = $users[$thread['authorid']] ?? NULL;
    if (is_null($author)) {
        Tools::println('Author id %d in post %d doesn\'t exist!', FALSE, [$thread['authorid'], $thread['tid']], 'red');
        exit;
    }

    // generating tags for flarum.discussion_tags
    if (!isset(Mapping::$forum_to_tags[$thread['fid']])) {
        Tools::println('Forum %d doesn\'t exist for tid %d', FALSE, [$thread['fid'], $thread['tid']], 'red');
        exit;
    }
    // add tags based on thread type
    $type_to_tag = Mapping::$threadtype_to_tags[$thread['typeid']] ?? NULL;
    if (!is_null($type_to_tag)) {
        $discussion_tags[] = [
            'discussion_id' => $disc_id, 
            'tag_id'        => $type_to_tag
        ];
    }
    // add tags based on which forum it located at
    foreach (Mapping::$forum_to_tags[$thread['fid']] as $t) {
        $discussion_tags[] = [
            'discussion_id' => $disc_id, 
            'tag_id'        => $t
        ];
    }

    // fetch posts
    // to save memory, I made post fetching inside the loop, this is intended
    $posts  = [];
    $query2 = $db->query('SELECT * FROM (
                              SELECT 0 cid, pid, tid, authorid, dateline, message, useip, \'post\' method
                              FROM pokeuniv_legacy.pre_forum_post WHERE tid = ' . $thread['tid'] . ' UNION ALL
                              SELECT id cid, pid, tid, authorid, dateline, comment, useip, \'comment\' method
                              FROM pokeuniv_legacy.pre_forum_postcomment WHERE tid = ' . $thread['tid'] . '
                          ) t ORDER BY t.pid ASC, t.cid ASC, t.dateline ASC');
    $number = 1;
    $post_count = 0;
    $participants = [];
    while ($post = $query2->fetch(PDO::FETCH_ASSOC)) {

        // processing replacements
        $content = Tools::fixPost($post['message']);

        // invalid uid
        if (!isset($users[$post['authorid']])) {
            Tools::println('Invalid user#%d, ignored', FALSE, [$post['authorid']], 'red');
            continue;
        }

        // invalid parent for comment
        if ($post['method'] === 'comment' && !isset($posts[$post['pid']])) {
            Tools::println('Invalid post#%d, ignored', FALSE, [$post['pid']], 'red');
            continue;
        }

        // attach bbcode replace
        $content = preg_replace_callback('/\[(attach|attachimg)\](\d+)\[\/(attach|attachimg)\]/', function ($m) use ($attachments) {
            $file = $attachments[$m[2]] ?? NULL;
            if (!$file) 
                return $m[0];
            return $file['tag'] === 'image-preview' ? 
                        '[upl-image-preview url=' . $file['url'] . ']' : 
                        '[upl-file uuid=' . $file['uuid'] . ' size=' . Tools::human_filesize($file['size'], 0). ']' . $file['base_name'] . '[/upl-file]';
        }, $content);

        // extract & remove last edit info 
        $edit_time = $edit_id = NULL;
        $pattern = '/\[i=s\] 本帖最后由 (.*?) 于 (.*?) 编辑 \[\/i\]\n\n/s';
        if (preg_match($pattern, $content, $m)) {
            $content = preg_replace($pattern, '', $content);
            if (isset( $users_by_name[$m[1]])) {
                $edit_time = Tools::timestampToDatetime(strtotime($m[2]));
                $edit_id   = $users_by_name[$m[1]]['id'];
            }
        }

        // parse tags
        $content = $parser->parse(trim($content));
        $content = preg_replace_callback('/(@.+?#)(\d+)/', function ($m) use ($posts) {
            return isset($posts[$m[2]]) ? $m[1] . $posts[$m[2]]['id'] : $m[0];
        }, $content);

        // for in-post replies, replace template with pin tags
        if ($post['method'] === 'comment') {
            // set a lower bound for comments to prevent comment_time <= parent_time
            $post['dateline'] = max(strtotime($posts[$post['pid']]['time']) + 1, $post['dateline']);
            $content = sprintf('@%s#%d%s%s', 
                $users_by_new[$posts[$post['pid']]['user_id']]['username'], 
                $posts[$post['pid']]['id'], PHP_EOL, $content
            );
        }

        // TODO - separate long post into 2 posts
        // size limit
        if (($len = strlen($content)) >= (2 << 23)) {
            Tools::println('The content of the %s#%d is too long! Ignored. (char length: %d)', FALSE, [
                $post['method'], 
                $post[$post['method'] === 'post' ? 'pid' : 'cid'],
                $len
            ], 'red');
            continue;
            //exit;
        }

        // register as a participant
        $participants[$post['authorid']] = 1;


        // TODO - in-post reply & post has same pid
        $posts[$post['pid']] = $last_post = [
            'id'            => $post_id,
            'discussion_id' => $disc_id,
            'number'        => $number,
            'time'          => Tools::timestampToDatetime($post['dateline']),
            'user_id'       => $users[$post['authorid']]['id'],
            'type'          => 'comment',
            'content'       => $content,
            'edit_time'     => $edit_time,
            'edit_user_id'  => $edit_id,
            'hide_time'     => NULL,
            'hide_user_id'  => NULL,
            'ip_address'    => $post['useip'],
            'is_private'    => 0,
            'is_approved'   => 1,
            'is_spam'       => 0
        ];

        // generate user post count table
        if (!isset($user_updates[$post['authorid']])) {
            $user_updates[$post['authorid']] = [
                'id'                => $users[$post['authorid']]['id'],
                'discussions_count' => 0,
                'comments_count'    => 0
            ];
        }
        $user_updates[$post['authorid']]['comments_count'] += 1;

        $post_id++;
        $number++;
        $post_count++;
    }

    $user_updates[$thread['authorid']]['comments_count']    -= 1;
    $user_updates[$thread['authorid']]['discussions_count'] += 1;

    if ($posts && !$db->insertData('flarum.flarum_posts', $posts)) {
        Tools::println('Failed! (discussion#%d)', FALSE, [$disc_id], 'red');
        exit;
    }

    $discussions[] = [
        'id'                 => $disc_id,
        'title'              => htmlspecialchars_decode($thread['subject']),
        'comments_count'     => $post_count,
        'participants_count' => count($participants),
        'number_index'       => $disc_id,
        'start_time'         => Tools::timestampToDatetime($thread['dateline']),
        'start_user_id'      => $author['id'],
        'start_post_id'      => $post_id,
        'hide_time'          => NULL,
        'hide_user_id'       => NULL,
        'last_time'          => $last_post['time']    ?? NULL,
        'last_user_id'       => $last_post['user_id'] ?? NULL,
        'last_post_id'       => $last_post['id']      ?? NULL,
        'last_post_number'   => $last_post['number']  ?? NULL,
        'slug'               => preg_replace('/[^a-zA-Z0-9_\-\x{0800}-\x{9fa5}]/u', '', $thread['subject']),
        'is_private'         => 0,
        'is_approved'        => $thread['displayorder'] < 0 ? 0 : 1,
        'is_locked'          => $thread['closed'],
        'is_sticky'          => 0,//$thread['displayorder'] > 0 ? 1 : 0,
        'old_tid'            => $thread['tid'],
        'old_typeid'         => $thread['typeid'],
        'highlight'          => $thread['highlight']
    ];

    $disc_id++;
}

// TODO attachment

Tools::println('');
Tools::println('Migrating threads...');
if (!$db->insertData('flarum.flarum_discussions', $discussions))
    Tools::println('Failed to generate discussions. Bye.', FALSE, [], 'red');
if (!$db->insertData('flarum.flarum_discussions_tags', $discussion_tags))
    Tools::println('Failed to generate discussion tags. Bye.', FALSE, [], 'red');
if (!$db->insertData('flarum.flarum_users', $user_updates, TRUE))
    Tools::println('Failed to update user post count. Bye.', FALSE, [], 'red');

