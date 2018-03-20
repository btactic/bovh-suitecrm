<?php

require_once('bOVH/ovh.php');
require_once('bOVH/domain.php');
require_once('bOVH/dedicated_server.php');
require_once('bOVH/ip.php');
require_once('bOVH/virtual_address.php');

function main() {
    $GLOBALS['log']->fatal("[bOVH] Entering bOVH synchronization.");
    $GLOBALS['log']->fatal("[bOVH] --> Syncing domain zones...");
    $domain = new Domain(OVH::get_api_instance());
    $domain->sync_all_domain_zones();
    $GLOBALS['log']->fatal("[bOVH] --> Syncing dedicated servers...");
    $server = new DedicatedServer(OVH::get_api_instance());
    $server->sync_all_dedicated_servers();
    $GLOBALS['log']->fatal("[bOVH] --> Syncing IPs...");
    $ip = new IP(OVH::get_api_instance());
    $ip->sync_all_ips();
    $GLOBALS['log']->fatal("[bOVH] --> Syncing virtual address...");
    $virtualAddress = new VirtualAddress(OVH::get_api_instance());
    $virtualAddress->sync_all_virtual_addresses();
    $GLOBALS['log']->fatal("[bOVH] bOVH synchronization finished.");
    return true;
}

?>
