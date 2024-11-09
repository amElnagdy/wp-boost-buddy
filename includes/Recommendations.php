<?php

namespace WpBoostBuddy;

class Recommendations
{
    private $test_results = [];
    private $recommendations = [];
    private static $proxy_api_url = WPBB_PROXY_URL . 'get-recommendations';

    public function add_result($test_name, $result)
    {
        $this->test_results[$test_name] = $result;
    }

    public function get_recommendations()
    {
        $response = wp_remote_post(self::$proxy_api_url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'    => json_encode(['test_results' => $this->test_results]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => 'Error fetching recommendations: ' . $response->get_error_message()];
        }

        $decoded_body = json_decode(wp_remote_retrieve_body($response), true);

        if ($decoded_body === null) {
            return ['success' => false, 'message' => 'Invalid response format from recommendations API.'];
        }

        $this->recommendations = $decoded_body;

        return ['success' => true, 'recommendations' => $this->recommendations];
    }
}
