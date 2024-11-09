<?php

namespace WpBoostBuddy;

class MediaOptimization
{

    private $size_threshold = 500; // 500kb
    private $batch_size = 50;
    private $max_files = 10;
    private $recommendations;

    public function __construct(Recommendations $recommendations)
    {
        $this->recommendations = $recommendations;
        add_action('wp_ajax_wpbb_media_optimization', [$this, 'handle_media_optimization']);
    }

    public function handle_media_optimization()
    {
        check_ajax_referer('wpbb_nonce', 'security');

        $needs_optimization = $this->scan_media_files();

        $this->recommendations->add_result('media_optimization', [
            'success' => true,
            'data'    => ['needs_optimization' => $needs_optimization],
            'message' => $needs_optimization ? 'Media optimization needed.' : 'Media files are optimized.',
        ]);

        wp_send_json_success(['message' => 'Media optimization check completed']);
    }


    private function scan_media_files()
    {
        $offset = 0;
        $found_large_files = 0;
        $has_more_files = true;

        while ($has_more_files && $found_large_files < $this->max_files) {
            $args = [
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'posts_per_page' => $this->batch_size,
                'offset' => $offset,
                'meta_query' => [
                    [
                        'key' => '_wp_attachment_metadata',
                        'compare' => 'EXISTS',
                    ],
                ],
            ];

            $query = new \WP_Query($args);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $metadata = wp_get_attachment_metadata(get_the_ID());

                    if (isset($metadata['filesize']) && $metadata['filesize'] / 1024 > $this->size_threshold) {
                        $found_large_files++;
                        if ($found_large_files >= $this->max_files) {
                            wp_reset_postdata();
                            return true;
                        }
                    }
                }
                wp_reset_postdata();
            } else {
                $has_more_files = false;
                break;
            }

            if ($query->found_posts <= $offset + $this->batch_size) {
                $has_more_files = false;
                break;
            }

            $offset += $this->batch_size;
        }

        return $found_large_files > 0;
    }
}
