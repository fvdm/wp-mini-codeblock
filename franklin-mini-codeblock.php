<?php
/**
 * Plugin Name: Franklin Mini Codeblock
 * Description: Minimal, fast syntax highlighting with no dependencies
 * Version: 1.3.6
 * Author: Franklin
 * Author URI: https://frankl.in
 * Text Domain: franklin-mini-codeblock
 */

if (!defined('ABSPATH')) exit;

class Franklin_Mini_Codeblock {

    public function __construct() {
        $this->version = '1.3.6';

        add_action('init', array($this, 'register_block'));
//        add_action('enqueue_block_editor_assets', array($this, 'editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_assets'));
    }

    public function register_block() {
        register_block_type(__DIR__ . '/build', array(
            'render_callback' => array($this, 'render_block')
        ));
    }

    public function render_block( $attributes, $content ) {
        // 1. Get raw code attribute as string
        $id       = isset( $attributes['id'] ) ? (string) $attributes['id'] : '';
        $code     = isset( $attributes['code'] ) ? (string) $attributes['code'] : '';
        $language = isset( $attributes['language'] ) ? (string) $attributes['language'] : 'javascript';
    
        if ( $code === '' ) {
            return '';
        }
    
        // 2. Escape for HTML output *right here*
        $escaped_code = esc_html( $code );
        $clean_id = str_replace( ' ', '-', strip_tags( $id ) );
    
        // 3. Build markup – note: NO sprintf placeholders inside code area
        if ( $clean_id ) {
          $clean_id_attr = ' data-id="fmc-' . $clean_id . '"';
        }

        $html  = '<div class="fmc-wrapper">';
        $html .=   '<div class="fmc-header">';
        //$html .=     '<span class="fmc-lang">' . esc_html( strtoupper( $language ) ) . '</span>';
        $html .=     '<span class="fmc-lang">&nbsp;</span>';
        $html .=     '<button class="fmc-copy" aria-label="Copy code"' . $clean_id_attr . '>Copy</button>';
        $html .=   '</div>';
        $html .=   '<pre class="fmc-pre"><code class="fmc-code" data-lang="' . esc_attr( $language ) . '">';
        $html .=     $escaped_code;
        $html .=   '</code></pre>';
        $html .= '</div>';
    
        return $html;
    }

    public function editor_assets() {
        wp_enqueue_script(
            'franklin-mini-codeblock-editor',
            plugins_url('build/editor.js', __FILE__),
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'),
            $this->version
        );

        wp_enqueue_style(
            'franklin-mini-codeblock-editor-style',
            plugins_url('build/editor.css', __FILE__),
            array(),
            $this->version
        );
    }

    public function frontend_assets() {
        if (!has_block('franklin/mini-codeblock')) {
            return;
        }

        wp_enqueue_style(
            'franklin-mini-codeblock-style',
            plugins_url('build/style.css', __FILE__),
            array(),
            $this->version
        );

        wp_enqueue_script(
            'franklin-mini-codeblock-frontend',
            plugins_url('build/frontend.js', __FILE__),
            array(),
            $this->version,
            true
        );
    }
}

new Franklin_Mini_Codeblock();
