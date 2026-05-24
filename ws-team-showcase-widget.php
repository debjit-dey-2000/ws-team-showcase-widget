
<?php
/**
 * Plugin Name: WS Team Showcase Plugin
 * Plugin URI: https://example.com
 * Description: A lightweight Elementor widget for displaying team members with filters, layouts, popups, and AJAX loading.
 * Version: 1.0.0
 * Author: Debjit Dey (WS01530)
 * Author URI: https://example.com
 */

if (!defined('ABSPATH')) exit;

define('WS_TEAM_URL', plugin_dir_url(__FILE__));
define('WS_TEAM_PATH', plugin_dir_path(__FILE__));

class WS_Team_Showcase_Init {

    public function __construct() {

        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_action('elementor/widgets/register', [$this, 'register_widget']);

        require_once WS_TEAM_PATH . 'includes/ajax/popup-handler.php';
    }

    public function assets() {

        wp_register_style(
            'ws-team-style',
            WS_TEAM_URL . 'assets/css/style.css',
            [],
            filemtime(WS_TEAM_PATH . 'assets/css/style.css')
        );

        wp_register_script(
            'ws-team-script',
            WS_TEAM_URL . 'assets/js/script.js',
            ['jquery'],
            filemtime(WS_TEAM_PATH . 'assets/js/script.js'),
            true
        );

        wp_localize_script(
            'ws-team-script',
            'wsTeam',
            [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'filterNonce' => wp_create_nonce('ws_team_filter_nonce')
            ]
        );
    }

    public function register_widget($widgets_manager) {

        require_once WS_TEAM_PATH . 'includes/widgets/team-widget.php';

        $widgets_manager->register(
            new \WS_Team_Widget()
        );
    }
}

new WS_Team_Showcase_Init();
