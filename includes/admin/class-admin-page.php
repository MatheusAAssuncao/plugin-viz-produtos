<?php

if (!defined('ABSPATH')) {
    exit;
}

class Meu_Plugin_Admin_Page {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
    }

    public function add_plugin_page() {
        add_menu_page(
            'Meu Plugin', // Título da página
            'Meu Plugin', // Título do menu
            'manage_options', // Capability
            'meu-plugin', // Slug
            array($this, 'create_admin_page'), // Função do conteúdo da página
            'dashicons-admin-generic', // Ícone
            6 // Posição no menu
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Configurações do Meu Plugin', 'meu-plugin'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('meu_plugin_options_group');
                do_settings_sections('meu-plugin-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

if (is_admin()) {
    new Meu_Plugin_Admin_Page();
}
