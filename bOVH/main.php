<?php

require_once('bOVH/ovh.php');
require_once('bOVH/domain.php');
require_once('bOVH/dedicated_server.php');
require_once('bOVH/ip.php');

function main() {
    $GLOBALS['log']->fatal("[bOVH] Entering bOVH synchronization.");
    $domain = new Domain(OVH::get_api_instance());
    $domain->sync_all_domain_zones();
    $server = new DedicatedServer(OVH::get_api_instance());
    $server->sync_all_dedicated_servers();
    $ip = new IP(OVH::get_api_instance());
    $ip->sync_all_ips();
    return true;
}

?>
