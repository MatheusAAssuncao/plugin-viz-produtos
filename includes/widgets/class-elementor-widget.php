<?php

if (!defined('ABSPATH')) {
    exit;
}

class Meu_Elementor_Widget extends \Elementor\Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
        wp_register_style('meu-widget-style', plugins_url('../../assets/css/style.css', __FILE__));
        add_action('woocommerce_after_main_content', array($this, 'render_elementor_widget'), 20);
    }

    public function get_style_depends()
    {
        return ['meu-widget-style'];
    }

    public function get_name()
    {
        return 'meu-widget';
    }

    public function get_title()
    {
        return __('Meu Widget', 'meu-plugin');
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
                'label' => __('Configurações de Exibição', 'meu-plugin'),
            ]
        );

        $this->add_control(
            'ordenar_produtos',
            [
                'label' => __('Ordenar produtos por', 'meu-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'recentes',
                'options' => [
                    'recentes' => __('Produtos Recentes', 'meu-plugin'),
                    'mais_vendidos' => __('Mais Vendidos', 'meu-plugin'),
                    'preco_maior_menor' => __('Preço: Maior para Menor', 'meu-plugin'),
                    'preco_menor_maior' => __('Preço: Menor para Maior', 'meu-plugin'),
                    'relacionados' => __('Produtos Relacionados', 'meu-plugin'),
                ],
            ]
        );

        $this->add_control(
            'quantidade_linhas',
            [
                'label' => __('Quantidade de linhas (3 produtos cada)', 'meu-plugin'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => __('1', 'meu-plugin'),
                    '2' => __('2', 'meu-plugin'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    public function render_elementor_widget() {
        // Verifica se estamos em uma página de produto único
        if (!is_product()) {
            return;
        }

        // Verifica se o Elementor está ativo
        if (!did_action('elementor/loaded')) {
            return;
        }

        echo '<div class="elementor-widget-container produtos-customizados">';
        
        // Configurações padrão do widget
        $settings = [
            'ordenar_produtos' => 'relacionados',
            'quantidade_linhas' => '1',
        ];

        // Renderizar o conteúdo do widget
        echo '<div class="meu-widget-wrapper">';
        $this->render_content($settings);
        echo '</div>';
        
        echo '</div>';
    }

    protected function render_content($settings = null) {
        // Se não foram passadas configurações, pegar as configurações do Elementor
        if (!$settings) {
            $settings = $this->get_settings_for_display();
        }

        // Definir os argumentos padrão da consulta de produtos
        $args = [
            'post_type' => 'product',
            'posts_per_page' => ($settings['quantidade_linhas'] * 3),
            'orderby' => 'date',
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

            case 'relacionados':
                global $product;
                if ($product) {
                    $tags = wc_get_product_tag_terms($product->get_id(), ['fields' => 'ids']);
                    $args['tag__in'] = $tags;
                    $args['post__not_in'] = [$product->get_id()];
                }
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
                wc_get_template_part('content', 'product');
            }
            echo '</div>';
        } else {
            echo __('Nenhum produto encontrado.', 'meu-plugin');
        }

        wp_reset_postdata();
    }

    // Sobrescrever o método render original para usar render_content
    protected function render() {
        $this->render_content();
    }

    // Opcional: Adicionar estilos específicos para esta posição
    /*
    public function add_custom_styles() {
        if (!is_product()) return;
        ?>
        <style>
            .meu-widget-wrapper {
                margin: 40px 0;
                padding: 20px;
                background: #fff;
                border-radius: 5px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .meu-widget-produtos {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }

            @media (max-width: 768px) {
                .meu-widget-produtos {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (max-width: 480px) {
                .meu-widget-produtos {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php
    }
    */
}

// Registrar os estilos customizados
// add_action('wp_head', array('Meu_Elementor_Widget', 'add_custom_styles'));