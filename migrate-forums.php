<?php

include_once __DIR__ . '/common.php';

$db->query('DELETE FROM flarum.flarum_tags');
$db->query('ALTER TABLE flarum.flarum_tags AUTO_INCREMENT = 1');

$generator = function ($id, $name, $slug, $position, $parent_id = NULL) {
    return [
        'id'                 => $id,
        'name'               => $name,
        'slug'               => $slug,
        'description'        => '',
        'color'              => '',
        'background_path'    => NULL,
        'background_mode'    => NULL,
        'position'           => $position,
        'parent_id'          => $parent_id,
        'default_sort'       => NULL,
        'is_restricted'      => 0,
        'is_hidden'          => 0,
        'discussions_count'  => 0,
        'last_time'          => NULL,
        'last_discussion_id' => NULL
    ];
};

$parent_pos = 0;
$tags       = [];
foreach (Mapping::$tags_info as $parent_id => $parent) {
    $tags[] = $generator($parent_id, $parent['name'], $parent['slug'], $parent_pos++);
    $child_pos = 0;
    foreach ($parent['children'] as $child_id => $child)
        $tags[] = $generator($child_id, $child['name'], $child['slug'], $child_pos++, $parent_id);
}

Tools::println('Generating tags...');
if (!$db->insertData('flarum.flarum_tags', $tags))
    Tools::println('Failed to generate tags. Bye.');
Tools::generateData('tags', $tags);

