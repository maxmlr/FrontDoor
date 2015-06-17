<?php

require('config.php');
require('SQLiteHandler.php');
require('GMCHandler.php');

$db = new SQLiteHandler($CONFIG['db']);
echo "...................................\n";
echo "API-Key:\n" . $CONFIG['gmc_browser_apikey'] ."\n";
echo "...................................\n";
echo "Devices:\n";
var_dump($db->getGMCDevice($argv[1]));
echo "...................................\n";

$gmc = new GCMPushMessage($CONFIG['gmc_browser_apikey']);
$gmc->setDevices($db->getGMCDevice($argv[1]));
$gmc_response = $gmc->send($argv[2]);

echo "Response:\n";
echo $gmc_response;
echo "\n...................................\n";
?>
