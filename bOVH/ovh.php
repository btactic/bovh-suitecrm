<?php

require_once 'bOVH/php-ovh-api/vendor/autoload.php';
use \Ovh\Api;

class OVH {

    private static $instance = NULL;
    private $ovh;

    private function __construct($config_file = 'bOVH/config/ovh_api.ini') {
        if (file_exists($config_file)) {
            $this->config = parse_ini_file($config_file);
            $this->ovh = new Api($this->config['applicationKey'],
                    $this->config['applicationSecret'], 'ovh-eu',
                    $this->config['consumer_key']);
        } else {
            $GLOBALS['log']->fatal("[bOVH] Impossible to access '$config_file'.");
        }
    }

    public static function get_api_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new OVH();
        }
        return self::$instance->ovh;
    }

}
?>
