<?php

use Webone\Webone\Handlers\AdminHandler;
use Webone\Webone\Handlers\RestApiHandler;

class Webone
{
    public function run()
    {
        $this->registerClassAutoloader();
        $this->registerPluginActivation();
        $this->registerPluginDeactivation();
        $this->load();
    }

    protected function registerPluginActivation()
    {
        register_activation_hook(WEBONE_PLUGIN_FILE, function () {
            //
        });
    }

    protected function registerPluginDeactivation()
    {
        register_deactivation_hook(WEBONE_PLUGIN_FILE, function () {
            //
        });
    }

    protected function load()
    {
        add_action('rest_api_init', [new RestApiHandler(), 'restApiInit'], 10, 0);
        add_action('admin_menu'   , [new AdminHandler()  , 'adminMenu' ], 10, 0);
    }

    protected function registerClassAutoloader()
    {
        spl_autoload_register(function ($class) {
            if (0 !== strpos($class, WEBONE_CLASSES_NAMESPACE)) {
                return;
            }
            $basePath = WEBONE_PLUGIN_DIR_PATH . 'includes' . DIRECTORY_SEPARATOR;
            $fileName = str_replace(WEBONE_CLASSES_NAMESPACE, $basePath, $class);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $fileName);
            $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
            $fileName .= '.php';
            if (is_readable($fileName)) {
                include_once $fileName;
            }
        });
    }
}
