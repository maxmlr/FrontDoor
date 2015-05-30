<?php
/**
* Check if a client IP is in our Server subnet
*
* @param string $client_ip
* @param string $server_ip
* @return boolean
*/
function clientInSameSubnet($client_ip=false,$server_ip=false) {
    if (!$client_ip)
        $client_ip = $_SERVER['REMOTE_ADDR'];
    if (!$server_ip)
        $server_ip = $_SERVER['SERVER_ADDR'];
    // Extract broadcast and netmask from ifconfig
    if (!($p = popen("ifconfig","r"))) return false;
    $out = "";
    while(!feof($p))
        $out .= fread($p,1024);
    fclose($p);
    // This is because the php.net comment function does not
    // allow long lines.
    $match  = "/^.*".$server_ip;
    $match .= ".*Bcast:(\d{1,3}\.\d{1,3}i\.\d{1,3}\.\d{1,3}).*";
    $match .= "Mask:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/im";
    if (!preg_match($match,$out,$regs))
        return false;
    $bcast = ip2long($regs[1]);
    $smask = ip2long($regs[2]);
    $ipadr = ip2long($client_ip);
    $nmask = $bcast & $smask;
    return (($ipadr & $smask) == ($nmask & $smask));
}

function checkIPv($client_ip=false,$server_ip=false) {
    if (!$client_ip)
        $client_ip = $_SERVER['REMOTE_ADDR'];
    if (!$server_ip)
        $server_ip = $_SERVER['SERVER_ADDR'];

    $validv4 = filter_var($client_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);

    $validv6 = filter_var($client_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

    if (is_string($validv4) && $validv6 == FALSE)
    {
        if (clientInSameSubnet($client_ip, $server_ip))
            return("local");
        else
            return("extern");
    }

    if (is_string($validv6) && $validv4 == FALSE)
        return("extern");
}
?>
