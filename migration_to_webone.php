<?php
/*
Plugin Name:       migration_to_webone
Plugin URI:        https://webone.co/
Description:       افزونه وردپرس مهاجرت به وب وان
Version:           1.0.0
Requires at least: 6.4
Requires PHP:      7.4
Author:            وب وان
Author URI:        https://profiles.wordpress.org/weboneco/
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit;
}

define('WEBONE_PLUGIN_VERSION'    , '1.0.0'                            , false);
define('WEBONE_PLUGIN_FILE'       , __FILE__                           , false);
define('WEBONE_PLUGIN_DIR_PATH'   , plugin_dir_path(WEBONE_PLUGIN_FILE), false);
define('WEBONE_PLUGIN_URL'        , plugin_dir_url(WEBONE_PLUGIN_FILE) , false);
define('WEBONE_CLASSES_NAMESPACE' , 'Webone\Webone\\'                  , false);
define('WEBONE_REST_API_NAMESPACE', 'webone/v1'                        , false);

require_once __DIR__ . '/includes/Webone.php';
function webone()
{
    $webone = new Webone();
    $webone->run();
}
webone();