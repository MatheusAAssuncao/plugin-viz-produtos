<?php

if (!defined('ABSPATH')) {
    exit;
}

class Viz_Plugin_Produtos_Admin_Page {
    public function __construct() {
        // add_action('admin_menu', array($this, 'add_plugin_page'));
    }

    public function add_plugin_page() {
        add_menu_page(
            'Viz Plugin Produtos', // Título da página
            'Viz Plugin Produtos', // Título do menu
            'manage_options', // Capability
            'viz-plugin-produtos', // Slug
            array($this, 'create_admin_page'), // Função do conteúdo da página
            'dashicons-admin-generic', // Ícone
            6 // Posição no menu
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Configurações do Viz Plugin Produtos', 'viz-plugin-produtos'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('viz_plugin_produtos_options_group');
                do_settings_sections('viz-plugin-produtos-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

if (is_admin()) {
    new Viz_Plugin_Produtos_Admin_Page();
}
