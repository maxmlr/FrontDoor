<?php
require('php/route.php');
$ip = checkIPv();

if ($ip == "local")
{
    header("Location: http://localhost:5000");
    exit;
}
else
{
    header("Location: remote.php");
    exit;
}

?>
