#!/usr/bin/php -q
<?php
require("SQLiteHandler.php");
require("config.php");

$db = new SQLiteHandler($CONFIG['db']);

$db->createTable_logins();
$db->createTable_users();
$db->createTable_burn_keys();

$db->addUser("Gast", "0815");

var_dump($db->listUsers());
?>
