#!/usr/bin/php -q
<?php
require("SQLiteHandler.php");
require("config.php");

$db = new SQLiteHandler($CONFIG['db']);

$expires = "-";
if ($argc <= 1)
  $code = $db->addBurnKey();
else if ($argc == 2)
{
  $user = $argv[1];
  $code = $db->addBurnKey($argv[1]);
}
else if ($argc == 3)
{                                   
  $user = $argv[1];
  $code = $db->addBurnKey($argv[1], $argv[2]);
}
else if ($argc == 4)
{                                   
  $user = $argv[1];
  $code = $db->addBurnKey($argv[1], $argv[2], $argv[3]);
  $expires = $argv[3];
}
echo "User: " . $user  . "\nCode: " . $code . "\nExpires: " . $expires  ."\n";
?>
