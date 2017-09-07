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
        try {
            $domain_properties = $this->ovh->get('/domain/'.$domain);
            if (!empty($domain_properties['whoisOwner'])) {
                $owner_info = $this->ovh->get('/me/contact/'.$domain_properties['whoisOwner']);
                $this->set_domain_owner($bean, $owner_info);
            }
        } catch (Exception $e) {}
        $bean->save();
    }

    private function set_domain_owner(&$domain_bean, $owner_info) {
        $keys_values = array();
        $keys_values['nif_c'] = $owner_info['vat'];
        $account_bean = retrieve_record_bean('Accounts', $keys_values);
        if (empty($account_bean->id)) {
            $account_bean->name = $owner_info['organisationName'];
            $account_bean->nif_c = $owner_info['vat'];
            $account_bean->email1 = $owner_info['email'];
            $account_bean->phone_work = $owner_info['phone'];
            $account_bean->billing_address_street = $owner_info['address']['line1'];
            $account_bean->billing_address_city = $owner_info['address']['city'];
            $account_bean->billing_address_state = $owner_info['address']['province'];
            $account_bean->billing_address_postalcode = $owner_info['address']['zip'];
            $account_bean->billing_address_country = $owner_info['address']['country'];
            $account_bean->save();
        }
        $domain_bean->account_id_c = $account_bean->id;
    }

}

?>
