<?php

require_once('bOVH/bean_utils.php');

class DedicatedServer {

    public function __construct($ovh_api_instance) {
        $this->ovh = $ovh_api_instance;
    }

    public function sync_all_dedicated_servers() {
        try {
            $servers = $this->ovh->get('/dedicated/server');
            foreach ($servers as $server) {
                $this->sync_dedicated_server($server);
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("[bOVH] Error retrieving dedicated servers list.");
        }
    }

    public function sync_dedicated_server($servername) {
        try {
            $server_info = $this->ovh->get('/dedicated/server/'.$servername);
            $keys_values = array();
            $keys_values['identificador'] = $server_info['serverId'];
            $bean = retrieve_record_bean('btc_Servidores', $keys_values);
            if (!isset($bean->name)) $bean->name = $servername;
            $bean->identificador = $server_info['serverId'];
            $bean->datacenter = $server_info['datacenter'];
            $bean->inversa = $server_info['reverse'];
            //IP: $server_info['ip'];
            //Rack: $server_info['rack'];
        } catch (Exception $e) {
            return;
        }
        try {
            $server_info2 = $this->ovh->get('/dedicated/server/'.$servername.'/serviceInfos');
            $bean->alta = $server_info2['creation'];
            $bean->vencimiento = $server_info2['expiration'];
            //Contacto facturación: $server_info2['contactBilling'];
            //Contacto técnico: $server_info2['contactTech'];
            //Contacto administrador: $server_info2['contactAdmin'];
        } catch (Exception $e) {}
        try {
            $hardware_spec = $this->ovh->get('/dedicated/server/'.$servername
                    .'/specifications/hardware');
            //RAM: $hardware_spec['memorySize']['value'] $hardware_spec['memorySize']['unit']
            $bean->ram = $hardware_spec['memorySize']['value'] / 1024;
            //Arquitectura procesador: $hardware_spec['processorArchitecture']
            //Capacidad HD Grupo 1: $hardware_spec['diskGroups'][0]['diskSize']['value'] $hardware_spec['diskGroups'][0]['diskSize']['unit']
            $bean->modeloserver = $hardware_spec['description'];
            $bean->cpu = $hardware_spec['coresPerProcessor']
                    * $hardware_spec['numberOfProcessors'];
            $bean->threads = $hardware_spec['threadsPerProcessor']
                    * $hardware_spec['numberOfProcessors'];
            $bean->modelocpu = $hardware_spec['processorName'];
            if (isset($hardware_spec['diskGroups'][0])) {
                $diskGroup0 = $hardware_spec['diskGroups'][0];
                $bean->tipo_hd1 = $diskGroup0['diskType'];
                $bean->mumhd1 = $diskGroup0['numberOfDisks'];
                $bean->capacidadhd1 = $diskGroup0['diskSize']['value'] / 1000;
            }
            if (isset($hardware_spec['diskGroups'][1])) {
                $diskGroup0 = $hardware_spec['diskGroups'][1];
                $bean->tipo_hd2 = $diskGroup0['diskType'];
                $bean->numhd2 = $diskGroup0['numberOfDisks'];
                $bean->capacidadhd2 = $diskGroup0['diskSize']['value'] / 1000;
            }
        } catch (Exception $e) {}
        try {
            $network_spec = $this->ovh->get('/dedicated/server/'.$servername
                    .'/specifications/network');
            //Ancho de banda Mbps: $network_spec['bandwidth']['OvhToInternet']['value'] $network_spec['bandwidth']['OvhToInternet']['unit'];
            $bean->anchobanda = $network_spec['bandwidth']['OvhToInternet']['value'];
            //Ancho banda interno: $network_spec['bandwidth']['OvhToOvh']['value'] $network_spec['bandwidth']['OvhToOvh']['unit'];
            $bean->red = $network_spec['bandwidth']['OvhToOvh']['value'];
        } catch (Exception $e) {}
        /*try {
            $os_spec = $this->ovh->get('/dedicated/server/'.$servername
                    .'/statistics/os');
        } catch (Exception $e) {}*/
        $bean->save();
    }

}

?>
