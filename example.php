<?php

//Can be run in a Browser or on the CLI!

require 'mojangapi.class.php';

if(php_sapi_name() === 'cli') {
    if(empty($argv[1])) {
        die("missing argv[1]!\n");
    } else {
        $_GET['name'] = $argv[1];
    }
} else {
    if(empty($_GET['name'])) {
        $form = '<form><input name="name" type="name"><input type="submit"></form>';
        die($form);
    }
}

$mapi = new mojangapi;
$data = $mapi->getbyName($_GET['name']);

header('Content-Type: application/json'); 
print json_encode($data, JSON_PRETTY_PRINT);
?>
