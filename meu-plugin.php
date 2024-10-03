<?php
/*
Plugin Name: Meu Plugin Elementor
Description: Plugin com um widget customizado para o Elementor e uma página administrativa no WordPress.
Version: 1.0
Author: Seu Nome
*/

if (!defined('ABSPATH')) {
    exit;
}

class Meu_Plugin_Main {
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
        add_action('admin_menu', array($this, 'adicionar_menu'));
        
        // Adicionar o hook para o WooCommerce
        // add_action('woocommerce_after_main_content', array($this, 'render_products_hook'), 20);
        // Add after the product summary
        add_action('woocommerce_single_product_summary', array($this, 'render_products_hook'), 20);
        
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
        $this->widget_instance = new \Meu_Elementor_Widget();
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type($this->widget_instance);
    }

    public function elementor_nao_encontrado() {
        echo '<div class="error"><p>O plugin "Meu Plugin Elementor" requer o Elementor. Por favor, ative o Elementor antes de usar este plugin.</p></div>';
    }

    public function register_styles() {
        wp_register_style(
            'meu-plugin-style',
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

        // Carregar os estilos
        wp_enqueue_style('meu-plugin-style');

        // Buscar configurações salvas ou usar padrões
        $settings = array(
            'ordenar_produtos' => 'relacionados',
            'quantidade_linhas' => 1
        );

        // Definir argumentos para a query
        $args = $this->get_products_query_args($settings);

        // Executar a query e renderizar os produtos
        $this->render_products($args);
    }

    private function get_products_query_args($settings) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => ($settings['quantidade_linhas'] * 3),
            'orderby' => 'date',
            'order' => 'DESC',
        );

        switch ($settings['ordenar_produtos']) {
            case 'mais_vendidos':
                $args['meta_key'] = 'total_sales';
                $args['orderby'] = 'meta_value_num';
                break;

            case 'preco_maior_menor':
                $args['meta_key'] = '_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;

            case 'preco_menor_maior':
                $args['meta_key'] = '_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;

            case 'relacionados':
                global $product;
                if ($product) {
                    $tags = wc_get_product_tag_terms($product->get_id(), array('fields' => 'ids'));
                    $args['tag__in'] = $tags;
                    $args['post__not_in'] = array($product->get_id());
                }
                break;
        }

        return $args;
    }

    private function render_products($args) {
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            echo '<div class="produtos-recentes-session">';
            echo '<div class="produtos-recentes-content">';
            echo '<h3 class="produtos-recentes">Você também vai gostar</h3>';
            echo '<div class="meu-widget-produtos">';
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
function meu_plugin_init() {
    return Meu_Plugin_Main::get_instance();
}

// Iniciar o plugin
add_action('plugins_loaded', 'meu_plugin_init');