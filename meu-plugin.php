<?php
/*
Plugin Name: Meu Plugin Elementor
Description: Plugin com um widget customizado para o Elementor e uma página administrativa no WordPress.
Version: 1.0
Author: Seu Nome
*/

if (!defined('ABSPATH')) {
    exit; // Evita o acesso direto
}

// Função para verificar se o Elementor está ativo e carregado
function meu_plugin_carregar_elementor_widget() {
    // Certifique-se de que o Elementor está ativo
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', 'meu_plugin_elementor_nao_encontrado' );
        return;
    }

    // Inclua o arquivo que contém o widget
    require_once(plugin_dir_path(__FILE__) . 'includes/widgets/class-elementor-widget.php');

    // Registre o widget
    add_action('elementor/widgets/widgets_registered', 'meu_plugin_registrar_widget');
}

// Função para registrar o widget no Elementor
function meu_plugin_registrar_widget() {
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Meu_Elementor_Widget());
}

// Função para mostrar aviso se o Elementor não for encontrado
function meu_plugin_elementor_nao_encontrado() {
    echo '<div class="error"><p>O plugin "Meu Plugin Elementor" requer o Elementor. Por favor, ative o Elementor antes de usar este plugin.</p></div>';
}

// Carregar o widget somente quando o Elementor estiver completamente inicializado
add_action('elementor/init', 'meu_plugin_carregar_elementor_widget');

// Função para adicionar o menu no admin
function meu_plugin_adicionar_menu() {
    add_menu_page(
        'Meu Plugin Elementor',    // Título da página
        'Meu Plugin',              // Nome do menu
        'manage_options',          // Permissões (somente administradores podem acessar)
        'meu-plugin',              // Slug da página
        'meu_plugin_pagina_admin', // Função de callback que exibe o conteúdo da página
        'dashicons-admin-generic', // Ícone do menu
        20                         // Posição no menu
    );
}

// Função de callback que exibe o conteúdo da página administrativa
function meu_plugin_pagina_admin() {
    // Verificar se o formulário foi enviado e se há um nonce válido
    if (isset($_POST['meu_plugin_opcoes_nonce']) && wp_verify_nonce($_POST['meu_plugin_opcoes_nonce'], 'meu_plugin_opcoes')) {
        // Salvar as opções selecionadas
        $opcao_ordenacao = isset($_POST['ordenar_produtos']) ? sanitize_text_field($_POST['ordenar_produtos']) : 'recentes';
        update_option('meu_plugin_opcao_ordenacao', $opcao_ordenacao);

        $quantidade_linhas = isset($_POST['quantidade_linhas']) ? sanitize_text_field($_POST['quantidade_linhas']) : '1';
        update_option('meu_plugin_quantidade_linhas', $quantidade_linhas);
        
        echo '<div class="updated"><p>Configurações salvas.</p></div>';
    }

    // Obter a opção de ordenação salva
    $opcao_ordenacao = get_option('meu_plugin_opcao_ordenacao', 'recentes');
    $quantidade_linhas = get_option('meu_plugin_quantidade_linhas', '1');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="">
            <?php
            // Adicionar um nonce para verificar a origem da solicitação
            wp_nonce_field('meu_plugin_opcoes', 'meu_plugin_opcoes_nonce');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Ordenar produtos por', 'meu-plugin'); ?></th>
                    <td>
                        <select name="ordenar_produtos">
                            <option value="recentes" <?php selected($opcao_ordenacao, 'recentes'); ?>><?php _e('Produtos Recentes', 'meu-plugin'); ?></option>
                            <option value="mais_vendidos" <?php selected($opcao_ordenacao, 'mais_vendidos'); ?>><?php _e('Mais Vendidos', 'meu-plugin'); ?></option>
                            <option value="preco_maior_menor" <?php selected($opcao_ordenacao, 'preco_maior_menor'); ?>><?php _e('Preço: Maior para Menor', 'meu-plugin'); ?></option>
                            <option value="preco_menor_maior" <?php selected($opcao_ordenacao, 'preco_menor_maior'); ?>><?php _e('Preço: Menor para Maior', 'meu-plugin'); ?></option>
                        </select>
                        <p class="description"><?php _e('Escolha como você deseja ordenar os produtos.', 'meu-plugin'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Quantidade de linhas', 'meu-plugin'); ?></th>
                    <td>
                        <select name="quantidade_linhas">
                            <option value="1" <?php selected($quantidade_linhas, '1'); ?>>1</option>
                            <option value="2" <?php selected($quantidade_linhas, '2'); ?>>2</option>
                        </select>
                        <p class="description"><?php _e('Escolha quantas linhas com 3 produtos cada vão aparecer. Se uma, ou se duas linhas.', 'meu-plugin'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Salvar Configurações', 'meu-plugin')); ?>
        </form>
    </div>
    <?php
}

// Hook para adicionar o menu quando o WordPress estiver pronto
add_action('admin_menu', 'meu_plugin_adicionar_menu');
