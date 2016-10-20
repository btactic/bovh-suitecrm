<?php

require_once('bOVH/bean_utils.php');

class Domain {

    public function __construct($ovh_api_instance) {
        $this->ovh = $ovh_api_instance;
    }

    public function sync_all_domain_zones() {
        try {
            $domains = $this->ovh->get('/domain/zone');
            foreach ($domains as $domain) {
                $this->sync_domain_zone($domain);
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("[bOVH] Error retrieving domain zones list.");
        }
    }

    public function sync_domain_zone($domain) {
        $keys_values = array();
        $keys_values['name'] = $domain;
        $bean = retrieve_record_bean('btc_dominios', $keys_values);
        $bean->name = $domain;
        try {
            $zones = $this->ovh->get('/domain/zone/'.$domain.'/export');
            $bean->dns = $zones;
        } catch (Exception $e) {}
        try {
            $domain_info = $this->ovh->get('/domain/'.$domain.'/serviceInfos');
            $bean->alta = $domain_info['creation'];
            $bean->caducidad = $domain_info['expiration'];
        } catch (Exception $e) {}
        try {
            $name_servers = $this->ovh->get('/domain/'.$domain.'/nameServer');
            $DNS1 = (isset($name_servers[0])) ? $this->ovh->get('/domain/'
                    .$domain.'/nameServer/'.$name_servers[0])['host'] : '';
            $bean->dns_server = $DNS1;
            $DNS2 = (isset($name_servers[1])) ? $this->ovh->get('/domain/'
                    .$domain.'/nameServer/'.$name_servers[1])['host'] : '';
            $bean->dns_server2 = $DNS2;
        } catch (Exception $e) {}
        $bean->save();
    }

}

?>
