<?php
/**
 * Plugin Name:  Franklin Mini Codeblock
 * Description:  Minimal, fast syntax highlighting with no dependencies
 * Version:      1.3.11
 * Author:       Franklin
 * Author URI:   https://frankl.in
 * Text Domain:  franklin-mini-codeblock
 * License:      Unlicense
 */

if (!defined('ABSPATH')) exit;

class Franklin_Mini_Codeblock {
    private $version = '1.3.11';

    public function __construct() {
        add_action('init', [$this, 'register_block']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
    }

    public function register_block() {
        register_block_type(__DIR__ . '/assets', [
            'render_callback' => [$this, 'render_block']
        ]);
    }

    public function render_block($attributes, $content) {
        $code = isset($attributes['code']) ? (string) $attributes['code'] : '';
        if ($code === '') {
            return '';
        }

        $id = isset($attributes['id']) ? (string) $attributes['id'] : '';
        $language = isset($attributes['language']) ? (string) $attributes['language'] : 'javascript';
        $clean_id = $id ? str_replace(' ', '-', strip_tags($id)) : '';
        $id_attr = $clean_id ? ' data-id="' . esc_attr($clean_id) . '"' : '';

        return '<div class="fmc-wrapper">'
            . '<div class="fmc-header">'
            . '<span class="fmc-lang">&nbsp;</span>'
            . '<button class="fmc-copy" aria-label="Copy code"' . $id_attr . '>Copy</button>'
            . '</div>'
            . '<pre class="fmc-pre"><code class="fmc-code" data-lang="' . esc_attr($language) . '">'
            . esc_html($code)
            . '</code></pre>'
            . '</div>';
    }

    public function frontend_assets() {
        if (!has_block('franklin/mini-codeblock')) {
            return;
        }

        wp_enqueue_style(
            'franklin-mini-codeblock-style',
            plugins_url('assets/style.css', __FILE__),
            [],
            $this->version
        );

        wp_enqueue_script(
            'franklin-mini-codeblock-frontend',
            plugins_url('assets/frontend.js', __FILE__),
            [],
            $this->version,
            true
        );
    }
}

new Franklin_Mini_Codeblock();
