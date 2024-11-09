<?php

namespace WpBoostBuddy;

use WpBoostBuddy\Recommendations;

class TTFBCheck
{
    private static $proxy_api_url = WPBB_PROXY_URL . 'proxy-speedvitals-ttfb';

    private $recommendations;

    public function __construct(Recommendations $recommendations)
    {
        $this->recommendations = $recommendations;
        add_action('wp_ajax_wpbb_ttfb_check', array($this, 'handle_ttfb_check'));
    }

    public function handle_ttfb_check()
    {
        check_ajax_referer('wpbb_nonce', 'security');

        $url = get_site_url();
        $region = isset($_POST['region']) ? sanitize_text_field($_POST['region']) : 'europe';

        $ttfb_data = $this->get_ttfb_data($url, $region);

        $this->recommendations->add_result('ttfb', $ttfb_data);

        wp_send_json_success(['message' => 'TTFB check completed']);
    }

    private function get_ttfb_data($url, $region)
    {
        $request_body = array(
            'url' => $url,
            'region' => $region,
        );

        $response = wp_remote_post(self::$proxy_api_url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($request_body),
            'timeout' => 30,
        ]);

        if ($response['response']['code'] != 200) {
            return [
                'success' => false,
                'data'    => [],
                'message' => 'Error fetching TTFB data: ' . $response['response']['message'],
            ];
        }

        $decoded_body = json_decode(wp_remote_retrieve_body($response), true);

        if ($decoded_body === null) {
            return [
                'success' => false,
                'data'    => [],
                'message' => 'Invalid response format from TTFB API.',
            ];
        }

        $formatted_data = $this->process_ttfb_response($decoded_body);

        return [
            'success' => true,
            'data'    => $formatted_data,
            'message' => 'TTFB data fetched successfully.',
        ];
    }

    private function process_ttfb_response($data)
    {
        $formatted_data = [];

        foreach ($data as $result) {
            $formatted_data[] = [
                'location' => $result['location'],
                'ttfb'     => $result['ttfb'],
            ];
        }

        return $formatted_data;
    }
}
