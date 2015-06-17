#!/usr/bin/php -q
<?php
require("SQLiteHandler.php");
require("config.php");

$db = new SQLiteHandler($CONFIG['db']);

$expires = "-";
if ($argc <= 1)
{
  echo "Usage: <Username> [<key> <valid until: YYYY-MM-DD HH:MM:SS>]\n";
  exit;
}
else if ($argc == 2)
  $code = $db->addUser($argv[1]);
else if ($argc == 3)
  $code = $db->addUser($argv[1], $argv[2]);
else if ($argc == 4)
{
  $code = $db->addUser($argv[1], $argv[2], $argv[3]);
  $expires = $argv[3];
}
echo "User: " . $argv[1]  . "\nCode: " . $code . "\nExpires: " . $expires  ."\n";
?>
