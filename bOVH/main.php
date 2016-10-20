<?php

require_once('bOVH/ovh.php');
require_once('bOVH/domain.php');

function main() {
    $GLOBALS['log']->fatal("[bOVH] Entering bOVH synchronization.");
    $domain = new Domain(OVH::get_api_instance());
    $domain->sync_all_domain_zones();
    return true;
}

?>
