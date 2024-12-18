<?php

if (!defined('ABSPATH')) {
    exit;
}

class Viz_Produtos_Elementor_Widget extends \Elementor\Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
        wp_register_style('viz-widget-style', plugins_url('../../assets/css/style.css', __FILE__));
    }

    public function get_style_depends()
    {
        return ['viz-widget-style'];
    }

    public function get_name()
    {
        return 'viz-widget';
    }

    public function get_title()
    {
        return __('Viz Widget', 'viz-plugin-produtos');
    }

    public function get_icon()
    {
        return 'eicon-star';
    }

    public function get_categories()
    {
        return ['basic'];
    }

    protected function _register_controls()
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Configurações de Exibição', 'viz-plugin-produtos'),
            ]
        );

        $this->add_control(
            'ordenar_produtos',
            [
                'label' => __('Ordenar produtos por', 'viz-plugin-produtos'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'recentes',
                'options' => [
                    'recentes' => __('Produtos Recentes', 'viz-plugin-produtos'),
                    'mais_vendidos' => __('Mais Vendidos', 'viz-plugin-produtos'),
                    'preco_maior_menor' => __('Preço: Maior para Menor', 'viz-plugin-produtos'),
                    'preco_menor_maior' => __('Preço: Menor para Maior', 'viz-plugin-produtos'),
                ],
            ]
        );

        $this->add_control(
            'quantidade_linhas',
            [
                'label' => __('Quantidade de linhas (3 produtos cada)', 'viz-plugin-produtos'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => __('1', 'viz-plugin-produtos'),
                    '2' => __('2', 'viz-plugin-produtos'),
                    'all' => __('all', 'viz-plugin-produtos'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $post_per_page = 1;
        if (!empty($settings['quantidade_linhas']) && $settings['quantidade_linhas'] == 'all') {
            $post_per_page = -1;
        } else {
            $post_per_page = $settings['quantidade_linhas'] * 3;
        }

        // Definir os argumentos padrão da consulta de produtos
        $args = [
            'post_type' => 'product',
            'posts_per_page' => $post_per_page, // Número de produtos a exibir
            'orderby' => 'date', // Padrão para produtos recentes
            'order' => 'DESC',
            // 'paged' => $paged,
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
            echo '<div class="viz-widget-produtos">';
            while ($query->have_posts()) {
                $query->the_post();
                wc_get_template_part('content', 'product'); // Exibe o template do produto
            }

            echo '</div>';
        } else {
            echo __('Nenhum produto encontrado.', 'viz-plugin-produtos');
        }

        wp_reset_postdata(); // Reseta a query do WordPress
    }
}