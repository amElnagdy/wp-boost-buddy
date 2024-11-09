<?php

namespace WpBoostBuddy;

class Admin
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'create_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function create_admin_page()
    {
        add_menu_page(
            'WP Boost Buddy',
            'WP Boost Buddy',
            'manage_options',
            'wp-boost-buddy',
            [$this, 'render_admin_page'],
            'dashicons-admin-tools',
            60
        );
    }

    public function render_admin_page()
    {
        echo '<div id="wpbb-root"></div>';
    }

    public function enqueue_assets($hook)
    {
        if ($hook !== 'toplevel_page_wp-boost-buddy') {
            return;
        }

        wp_enqueue_script(
            'wpbb-admin-script',
            WPBB_PLUGIN_URL . 'build/index.js',
            ['wp-element'],
            WPBB_PLUGIN_VERSION,
            true
        );

        wp_enqueue_style(
            'wpbb-admin-style',
            WPBB_PLUGIN_URL . 'build/index.css',
            [],
            WPBB_PLUGIN_VERSION
        );

        wp_localize_script('wpbb-admin-script', 'wpbbSettings', [
            'ajaxURL' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wpbb_nonce'),
        ]);
    }
}
