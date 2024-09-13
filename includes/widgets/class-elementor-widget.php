<?php

if (!defined('ABSPATH')) {
    exit;
}

class Meu_Elementor_Widget extends \Elementor\Widget_Base {
    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        wp_register_style('meu-widget-style', plugins_url('../../assets/css/style.css', __FILE__));
    }
    
    public function get_style_depends() {
        return ['meu-widget-style'];
    }

    public function get_name() {
        return 'meu-widget';
    }

    public function get_title() {
        return __('Meu Widget', 'meu-plugin');
    }

    public function get_icon() {
        return 'eicon-star';
    }

    public function get_categories() {
        return ['basic'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Configurações de Exibição', 'meu-plugin'),
            ]
        );
    
        $this->add_control(
            'ordenar_produtos',
            [
                'label' => __('Ordenar Produtos Por', 'meu-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'recentes',
                'options' => [
                    'recentes' => __('Produtos Recentes', 'meu-plugin'),
                    'mais_vendidos' => __('Mais Vendidos', 'meu-plugin'),
                    'preco_maior_menor' => __('Preço: Maior para Menor', 'meu-plugin'),
                    'preco_menor_maior' => __('Preço: Menor para Maior', 'meu-plugin'),
                ],
            ]
        );
    
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Definir os argumentos padrão da consulta de produtos
        $args = [
            'post_type' => 'product',
            'posts_per_page' => 6, // Número de produtos a exibir
            'orderby' => 'date', // Padrão para produtos recentes
            'order' => 'DESC',
        ];

        // Modificar a query com base na escolha do usuário
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

            case 'recentes':
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }

        // Executar a query de produtos
        $query = new \WP_Query($args);

        // Loop de exibição dos produtos
        if ($query->have_posts()) {
            echo '<div class="meu-widget-produtos">';
            while ($query->have_posts()) {
                $query->the_post();
                wc_get_template_part('content', 'product'); // Exibe o template do produto
            }
            echo '</div>';
        } else {
            echo __('Nenhum produto encontrado.', 'meu-plugin');
        }

        wp_reset_postdata(); // Reseta a query do WordPress
    }
}
