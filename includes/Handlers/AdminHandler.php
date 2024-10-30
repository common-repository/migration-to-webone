<?php

namespace Webone\Webone\Handlers;

class AdminHandler
{
    public function adminMenu()
    {
        wp_enqueue_style('webone_style', WEBONE_PLUGIN_URL . 'assets/style.css', [], WEBONE_PLUGIN_VERSION);
        add_menu_page('مهاجرت به وب وان', 'مهاجرت به وب وان', 'manage_options', 'webone', [$this, 'homePage'], '', 200);
    }

    public function homePage()
    {
        include_once __DIR__ . '/../Partials/home.phtml';
    }
}