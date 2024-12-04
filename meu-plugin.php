<?php
/*
Plugin Name: Viz Plugin Produtos Elementor
Description: Plugin com um widget customizado para o Elementor e uma página administrativa no WordPress.
Version: 1.0
Author: Seu Nome
*/

if (!defined('ABSPATH')) {
    exit;
}

class Viz_Plugin_Produtos_Main {
    private static $instance = null;
    private $widget_instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Inicializar o plugin
        add_action('elementor/init', array($this, 'init_elementor_widget'));
        // add_action('admin_menu', array($this, 'adicionar_menu'));
        
        // Adicionar o hook para o WooCommerce
        add_action('woocommerce_after_main_content', array($this, 'render_products_hook'), 20);
        
        // Registrar estilos
        add_action('wp_enqueue_scripts', array($this, 'register_styles'));
    }

    public function init_elementor_widget() {
        // Verificar se o Elementor está ativo
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', array($this, 'elementor_nao_encontrado'));
            return;
        }

        // Incluir e registrar o widget
        require_once(plugin_dir_path(__FILE__) . 'includes/widgets/class-elementor-widget.php');
        add_action('elementor/widgets/widgets_registered', array($this, 'registrar_widget'));
    }

    public function registrar_widget() {
        $this->widget_instance = new \Viz_Produtos_Elementor_Widget();
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type($this->widget_instance);
    }

    public function elementor_nao_encontrado() {
        echo '<div class="error"><p>O plugin "Viz Plugin Produtos Elementor" requer o Elementor. Por favor, ative o Elementor antes de usar este plugin.</p></div>';
    }

    public function register_styles() {
        wp_register_style(
            'viz-plugin-style',
            plugins_url('assets/css/style.css', __FILE__),
            array(),
            '1.0.0'
        );
    }

    public function render_products_hook() {
        // Verificar se estamos em uma página de produto
        if (!is_product()) {
            return;
        }
    
        global $product;
        
        // Verificar se o produto está definido
        if (!isset($product) || empty($product->get_id())) {
            return;
        }
        
        // Carregar os estilos
        wp_enqueue_style('viz-plugin-style');
    
        // Definir argumentos para a query
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 3,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        // Obter as tags do produto e excluir o produto atual da consulta
        $tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'ids'));
        if (!empty($tags)) {
            $args['tag__in'] = $tags;
            $args['post__not_in'] = array($product->get_id());
        }
    
        // Executar a query e renderizar os produtos
        $this->render_products($args);
    }

    private function render_products($args) {
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            echo '<div class="produtos-recentes-session">';
            echo '<div class="produtos-recentes-content">';
            echo '<h3 class="produtos-recentes">Você também vai gostar</h3>';
            echo '<div class="viz-widget-produtos">';
            while ($query->have_posts()) {
                $query->the_post();
                wc_get_template_part('content', 'product'); // Exibe o template do produto
            }

            echo '</div></div></div>';
        }

        wp_reset_postdata();
    }

    public function adicionar_menu() {
        // Sua função existente de adicionar_menu aqui
    }
}

// Inicializar o plugin
function viz_plugin_produtos_init() {
    return Viz_Plugin_Produtos_Main::get_instance();
}

// Iniciar o plugin
add_action('plugins_loaded', 'viz_plugin_produtos_init');