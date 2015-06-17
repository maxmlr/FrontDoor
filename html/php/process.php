<?php
#if(!$_SERVER['HTTP_X_REQUESTED_WITH'])
if( !isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || ( $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' ) )
{
   //header("HTTP/1.0 403 Forbidden");
   //exit;
}

if (isset($_SERVER['PHP_AUTH_USER']))
  $usr = $_SERVER['PHP_AUTH_USER'];
if (isset($_SERVER['PHP_AUTH_PW']))
  $pwd = $_SERVER['PHP_AUTH_PW'];

require('config.php');
require('SQLiteHandler.php');
require('GMCHandler.php');

$db = new SQLiteHandler($CONFIG['db']);

$key = false;
if (isset($_POST['key']))
  $key  = trim($_POST['key']);

$callid = false;                                                                                                                                                                                             
if (isset($_POST['from']))
  $callid = trim($_POST['from']);

$callid_target = false;                   
if (isset($_POST['to']))         
  $callid_target = trim($_POST['to']);  

$token = false;
if (isset($_POST['token']))
  $token = trim($_POST['token']);

if ($key)
  $response = $db->validate($key);
else if ($callid)                                                                                                                                                                                                 
  $response = $db->validate($callid, $callid_target);
else
  $response['valid'] = 0;

if ($response['valid'] == 1)
  shell_exec($CONFIG['send_command'] . " > /dev/null 2>/dev/null &");

$return = array('valid' => $response['valid'], 'id' => $response['id'], 'name' => $response['name'], 'expires' => $response['expires']);

$ip = $_SERVER['REMOTE_ADDR'];
$db->log($response['id'],$key,$response['valid'],$ip);

if ($CONFIG['send_push_notification'])                                                                                                 
{
  if ($return['valid'] == 1)                                                
  {  
    $gmc = new GCMPushMessage($CONFIG['gmc_browser_apikey']);
    $gmc->setDevices($db->getGMCDevice($response['id']));              
    $gmc_response = $gmc->send($response['name']);  
  }
}

if ($CONFIG['send_email_notification'])
{
  $msg_short = "Access DENIED"; 
  $msg_long =  "Access was denied for code: <i>" . $key . "</i>:<br/>\n";  
  if ($return['valid'] == 1)
  {
    $msg_short = "Opened by " . $response['name'];
    $msg_long = "Door was opened by <i>" . $response['name'] . "</i>:<br/>\n";
  }
  $time = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']);
  $subject = '[FrontDoor] ' . $CONFIG['door_name'] . " " . $msg_short;
  $message = "<div>\n<b>" . $msg_long . "</b><br/>\n" . $time . "<br/>\n" . $ip . "<br/>\n</div>";
  $header  = 'From: ' . $CONFIG['email_from'] . "\r\n";
  $header .= 'Reply-To: ' . $CONFIG['email_from'] . "\r\n";
  $header .= 'X-Mailer: PHP/' . phpversion();
  $header .= "MIME-Version: 1.0\r\n";
  $header .= "Content-type: text/html; charset=iso-8859-1\r\n";
  
  shell_exec("/usr/bin/php sendmail.php \"" . $CONFIG['recipient'] . "\" \"" . $subject . "\" \"" . $message . "\" \"" . $header . "\" > /dev/null 2>/dev/null &");
}

header('Content-Type: application/json');
echo json_encode($return);

exit;
?>
