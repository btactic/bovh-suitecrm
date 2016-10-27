<?php

require_once('bOVH/bean_utils.php');

class IP {

    public function __construct($ovh_api_instance) {
        $this->ovh = $ovh_api_instance;
    }

    public function sync_all_ips() {
        try {
            $ips = $this->ovh->get('/ip');
            foreach ($ips as $ip) {
                $this->sync_ip($ip);
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("[bOVH] Error retrieving ip list.");
        }
    }

    public function sync_ip($ip) {
        $keys_values = array();
        $keys_values['name'] = str_replace("/32", "", $ip);
        $bean = retrieve_record_bean('btc_IP', $keys_values);
        $bean->name = $keys_values['name'];
        $ip = str_replace("/", "%2F", $ip);
        try {
            $ip_info = $this->ovh->get('/ip/'.$ip);
            $bean->tipoip = $ip_info['type'];
            $bean->description = $ip_info['description'];
            //Routed to (nombre servidor dedicado): $ip_info['routedTo']['serviceName']
        } catch (Exception $e) {}
        try {
            $reverses = $this->ovh->get('/ip/'.$ip.'/reverse');
            if (isset($reverses[0])) {
                $ipReverse = $this->ovh->get('/ip/'.$ip.'/reverse/'.$reverses[0]);
                $bean->dns = $ipReverse['reverse'];
            }
        } catch (Exception $e) {}
        $bean->save();
    }

}

?>
