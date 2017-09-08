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
        $keys_values['name'] = ($this->is_ipv4_block($ip)) 
               ? str_replace("/32", "", $ip) : $ip;
        $bean = retrieve_record_bean('btc_IP', $keys_values);
        $bean->name = $keys_values['name'];
        $ip_encoded = str_replace("/", "%2F", $ip);
        try {
            $ip_info = $this->ovh->get('/ip/'.$ip_encoded);
            $bean->tipoip = $ip_info['type'];
            $bean->description = $ip_info['description'];
            $assigned_servername = $ip_info['routedTo']['serviceName'];
        } catch (Exception $e) {}
        try {
            $reverses = $this->ovh->get('/ip/'.$ip_encoded.'/reverse');
            if (isset($reverses[0])) {
                $ipReverse = $this->ovh->get('/ip/'.$ip_encoded.'/reverse/'.$reverses[0]);
                $bean->dns = $ipReverse['reverse'];
            }
        } catch (Exception $e) {}
        $bean->save();
        if ($this->is_ipv4_block($ip)) {
            list($ipv4, $mask) = explode("/", "$ip");
            if ($mask < 32) $this->sync_ipv4_block($bean, $ipv4, $mask);
        }
        if (isset($assigned_servername)) {
            $this->relate_ip_with_server($bean, $assigned_servername);
        }
    }

    private function sync_ipv4_block($parent_bean, $ipv4, $mask) {
        for ($i = 0; $i < $this->get_ipv4_block_lenght($mask); $i += 1) {
            $ip = long2ip(ip2long($ipv4)+$i);
            $keys_values = array();
            $keys_values['name'] = $ip;
            $bean = retrieve_record_bean('btc_IP', $keys_values);
            $bean->name = $keys_values['name'];
            $bean->tipoip = $parent_bean->tipoip;
            $bean->description = $parent_bean->description;
            try {
                $ip_block_encoded = $ip."%2F".$mask;
                $ipReverse = $this->ovh->get('/ip/'.$ip_block_encoded.'/reverse/'.$ip);
                $bean->dns = $ipReverse['reverse'];
            } catch (Exception $e) {}
            $bean->save();
            $bean->load_relationship('btc_ip_btc_ip');
            $bean->btc_ip_btc_ip->add($parent_bean);
        }
    }

    private function get_ipv4_block_lenght($mask) {
        return pow(2, 32-$mask);
    }

    private function relate_ip_with_server($ip_bean, $servername) {
        $keys_values = array();
        $keys_values['ovh_server_name'] = $servername;
        $server_bean = retrieve_record_bean('btc_Servidores', $keys_values);
        if (!empty($server_bean->id)) {
            $server_bean->load_relationship('btc_servidores_btc_ip');
            $server_bean->btc_servidores_btc_ip->add($ip_bean);
        }
    }

    private function is_ipv4_block($ipv4) {
        return preg_match("/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}"
                ."(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/"
                ."([1-9]|[1-2][0-9]|3[0-2])$/", $ipv4);
    }

    

}

?>
