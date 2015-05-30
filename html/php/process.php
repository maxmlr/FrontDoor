<?php
#if(!$_SERVER['HTTP_X_REQUESTED_WITH'])
if( !isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) || ( $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' ) )
{
   //header("HTTP/1.0 403 Forbidden");
   //exit;
}

require('config.php');
require('SQLiteHandler.php');

$db = new SQLiteHandler($CONFIG['db']);

$key  = trim($_POST['key']);
$ip = $_SERVER['REMOTE_ADDR'];
$time = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']);

$valid = $db->validate($key);

if ($valid == 1)
{
    $res = shell_exec($CONFIG['send_command']);
    $user = $db->whichUser($key);
    $db->addLogin($user,$time,$ip);
} else {
    $user = "DENIED";
    $db->addLogin($user,$time,$ip);
}

if ($CONFIG['send_notification'])
{
	$subject = '[FrontDoor] ' . $CONFIG['door_name'] . ' Access: ' . $user;
	$message = "<div>\n<b>" . $user . "</b><br/>\n" . $time . "<br/>\n" . $ip . "<br/>\n</div>";
	$header  = 'From: ' . $CONFIG['email_from'] . "\r\n";
	$header .= 'Reply-To: ' . $CONFIG['email_from'] . "\r\n";
	$header .= 'X-Mailer: PHP/' . phpversion();
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-type: text/html; charset=iso-8859-1\r\n";

	shell_exec("/usr/bin/php sendmail.php \"" . $CONFIG['recipient'] . "\" \"" . $subject . "\" \"" . $message . "\" \"" . $header . "\" > /dev/null 2>/dev/null &");
}

echo($user);
exit;
?>
