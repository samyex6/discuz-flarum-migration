<?php

include 'common.php';

$query = $db->query('SELECT pid, tid, message FROM pre_forum_post WHERE tid = 7190 AND pid = 67307');

$patterns = [
    ['/\[img=(\d+),(\d+)\]/', '[img width=$1 height=$2]'],
    ['/\[td=(\d+),(\d+)\]/', '[td colspan=$1 rowspan=$2]'],
    ['/\[table=(\d+)%\]/', '[table width=$1]']
];

$converted_tags = ['img', 'url', 'a', 'b', 'u', 's', 'code', 'quote', 'color'];

while ($info = $query->fetch(PDO::FETCH_ASSOC)) {
    $c = $info['message'];
    //preg_match_all('/\[[a-zA-Z]+.*?\]/', $c, $m);
    //print_r($m);

    foreach ($patterns as $p)
        $c = preg_replace($p[0], $p[1], $c);

    $data_post = [
        'data' => [
            'type' => 'posts',
            'attributes' => ['content' => $c],
            'relationships' => [
                'discussion' => [
                    'data' => ['type' => 'discussions', 'id' => 2]
                ]
            ]
        ]
    ];

    $data_discussion = [
        'data' => [
            'type' => 'discussions',
            'attributes' => ['title': '哈哈哈', 'content' => '佩佩佩'],
            'relationships' => [
                'tags' => [
                    'data' => [['type' => 'tags', 'id' => 1]]
                ]
            ]
        ]
    ];
    //$response = post('http://127.0.0.1/flarum/api/posts', $d, $cookie);
}

