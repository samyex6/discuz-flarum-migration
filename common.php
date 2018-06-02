<?php

include_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('UTC');

spl_autoload_register(function ($class_name) {
    include 'library/' . $class_name . '.php';
});

const IS_MIGRATE = TRUE;

set_time_limit(0);
error_reporting(E_ALL);

$db = new Database('mysql:dbname=pokeuniv_legacy;host=localhost', 'root', file_get_contents('./password'));

$d = [
    'identification' => '嘟嘟之魂',
    'password'       => 'PokeUnivCommonPassword'
];

$response = Tools::post('token', $d);
$cookie   = 'flarum_remember=' . $response['token'];
