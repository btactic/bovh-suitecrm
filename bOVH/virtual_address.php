<?php

require_once('bOVH/bean_utils.php');

class VirtualAddress {

    public function __construct($ovh_api_instance) {
        $this->ovh = $ovh_api_instance;
    }

    public function sync_all_virtual_addresses() {
        try {
            $servers = $this->ovh->get('/dedicated/server');
            foreach ($servers as $server) {
                $this->sync_dedicated_server_virtual_addresses($server);
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("[bOVH] Error retrieving dedicated servers list.");
        }
    }

    public function sync_dedicated_server_virtual_addresses($servername) {
        try {
            $virtualMacs = $this->ovh->get('/dedicated/server/'.$servername.'/virtualMac');
            foreach ($virtualMacs as $virtualMac) {
                $this->sync_virtualmac_addresses($servername, $virtualMac);
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("[bOVH] Error retrieving virtual macs list of '"
                    .$servername."' server.");
        }
    }

    private function sync_virtualmac_addresses($servername, $virtualMac) {
        try {
            $virtualAddresses = $this->ovh->get('/dedicated/server/'.$servername
                    .'/virtualMac/'.$virtualMac.'/virtualAddress');
            foreach ($virtualAddresses as $virtualAddress) {
                $keys_values = array();
                $keys_values['name'] = $virtualAddress;
                $bean = retrieve_record_bean('btc_IP', $keys_values);
                $bean->name = $virtualAddress;
                $bean->mac = $virtualMac;
                $bean->save();
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("[bOVH] Error retrieving virtual addresses list.");
        }
    }

}

?>
