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


    $interfaces = array();
    foreach (preg_split("/\n\n/", $out) as $int) {

    preg_match("/^([A-z]*\d)\s+Link\s+encap:([A-z]*)\s+HWaddr\s+([A-z0-9:]*).*" .
            "inet addr:([0-9.]+).*Bcast:([0-9.]+).*Mask:([0-9.]+).*" .
            "MTU:([0-9.]+).*Metric:([0-9.]+).*" .
            "RX packets:([0-9.]+).*errors:([0-9.]+).*dropped:([0-9.]+).*overruns:([0-9.]+).*frame:([0-9.]+).*" .
            "TX packets:([0-9.]+).*errors:([0-9.]+).*dropped:([0-9.]+).*overruns:([0-9.]+).*carrier:([0-9.]+).*" .
            "RX bytes:([0-9.]+).*\((.*)\).*TX bytes:([0-9.]+).*\((.*)\)" .
            "/ims", $int, $regex);

    if (!empty($regex)) {

        $interface = array();
        $interface['name'] = $regex[1];
        $interface['type'] = $regex[2];
        $interface['mac'] = $regex[3];
        $interface['ip'] = $regex[4];
        $interface['broadcast'] = $regex[5];
        $interface['netmask'] = $regex[6];
        $interface['mtu'] = $regex[7];
        $interface['metric'] = $regex[8];

        $interface['rx']['packets'] = (int) $regex[9];
        $interface['rx']['errors'] = (int) $regex[10];
        $interface['rx']['dropped'] = (int) $regex[11];
        $interface['rx']['overruns'] = (int) $regex[12];
        $interface['rx']['frame'] = (int) $regex[13];
        $interface['rx']['bytes'] = (int) $regex[19];
        $interface['rx']['hbytes'] = (int) $regex[20];

        $interface['tx']['packets'] = (int) $regex[14];
        $interface['tx']['errors'] = (int) $regex[15];
        $interface['tx']['dropped'] = (int) $regex[16];
        $interface['tx']['overruns'] = (int) $regex[17];
        $interface['tx']['carrier'] = (int) $regex[18];
        $interface['tx']['bytes'] = (int) $regex[21];
        $interface['tx']['hbytes'] = (int) $regex[22];

        $interfaces[$interface['name']] = $interface;
    }
    }

    $bcast = ip2long($interfaces['eth0']['broadcast']);
    $smask = ip2long($interfaces['eth0']['netmask']);
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
