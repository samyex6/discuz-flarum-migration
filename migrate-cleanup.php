<?php

include_once __DIR__ . '/common.php';

Tools::println('Cleaning up...');

$db->resetTables(['flarum.flarum_access_tokens']);

