#!/usr/bin/php -q
<?php
require("SQLiteHandler.php");
require("config.php");

$db = new SQLiteHandler($CONFIG['db']);
$db->dumpLogins();
?>
