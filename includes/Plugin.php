<?php

namespace WpBoostBuddy;

class Plugin
{

    private $recommendations;
    public function __construct()
    {
        add_action('init', [$this, 'init']);
        new Admin();
    }

    public function init()
    {
        $this->recommendations = new Recommendations();
        new TTFBCheck($this->recommendations);
        new MediaOptimization($this->recommendations);

        add_action('wp_ajax_wpbb_get_recommendations', [$this, 'handle_get_recommendations']);
    }

    public function handle_get_recommendations()
    {
        check_ajax_referer('wpbb_nonce', 'security');

        $response = $this->recommendations->get_recommendations();

        if ($response['success']) {
            wp_send_json_success($response['recommendations']);
        } else {
            wp_send_json_error($response['message']);
        }
    }
}
