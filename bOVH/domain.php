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
        $bean = retrieve_record_bean('btc_Dominios', $keys_values);
        $bean->name = $domain;
        $has_dns = false;
        $is_domain = false;
        try {
            $zones = $this->ovh->get('/domain/zone/'.$domain.'/export');
            $bean->dns = $zones;
            $has_dns = true;
        } catch (Exception $e) {}
        try {
            $domain_info = $this->ovh->get('/domain/'.$domain.'/serviceInfos');
            $bean->alta = $domain_info['creation'];
            $bean->caducidad = $domain_info['expiration'];
            $is_domain = true;
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
        if ($has_dns && $is_domain) {
            $bean->tipo_c = 'dominio_con_zona_dns';
        } else if ($has_dns) {
            $bean->tipo_c = 'zona_dns';
        } else {
            $bean->tipo_c = 'dominio';
        }
        $bean->save();
    }

}

?>
