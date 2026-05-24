
<?php
/**
 * Plugin Name: WS Team Showcase Plugin
 * Plugin URI: https://github.com/debjit-dey-2000/ws-team-showcase-widget.git
 * Description: A lightweight Elementor widget for displaying team members with filters, layouts, popups, and AJAX loading.
 * Version: 1.0.0
 * Author: Debjit Dey (WS01530)
 * Author URI: https://www.linkedin.com/in/debjit-dey-11d2000/
 */

if (!defined('ABSPATH')) exit;

define('WS_TEAM_URL', plugin_dir_url(__FILE__));
define('WS_TEAM_PATH', plugin_dir_path(__FILE__));

class WS_Team_Showcase_Init {

    public function __construct() {

        add_action('init', [$this, 'register_team_content_types']);
        add_action('acf/init', [$this, 'register_team_acf_fields']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_action('elementor/widgets/register', [$this, 'register_widget']);

        require_once WS_TEAM_PATH . 'includes/ajax/popup-handler.php';
    }

    private static function is_acf_active() {
        return class_exists('ACF') || function_exists('acf') || function_exists('acf_add_local_field_group');
    }

    public static function register_team_content_types() {
        if (!self::is_acf_active()) {
            return;
        }

        register_post_type(
            'ws_team',
            [
                'labels' => [
                    'name' => 'Team Members',
                    'singular_name' => 'Team Member',
                    'add_new_item' => 'Add New Team Member',
                    'edit_item' => 'Edit Team Member',
                    'new_item' => 'New Team Member',
                    'view_item' => 'View Team Member',
                    'search_items' => 'Search Team Members',
                    'not_found' => 'No team members found',
                    'not_found_in_trash' => 'No team members found in trash',
                ],
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'menu_icon' => 'dashicons-groups',
                'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
                'has_archive' => true,
                'rewrite' => [
                    'slug' => 'team',
                ],
            ]
        );

        register_taxonomy(
            'team_category',
            ['ws_team'],
            [
                'labels' => [
                    'name' => 'Team Categories',
                    'singular_name' => 'Team Category',
                    'search_items' => 'Search Team Categories',
                    'all_items' => 'All Team Categories',
                    'edit_item' => 'Edit Team Category',
                    'update_item' => 'Update Team Category',
                    'add_new_item' => 'Add New Team Category',
                    'new_item_name' => 'New Team Category Name',
                    'menu_name' => 'Team Categories',
                ],
                'public' => true,
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
                'rewrite' => [
                    'slug' => 'team-category',
                ],
            ]
        );

        if (get_option('ws_team_showcase_rewrite_flushed') !== 'yes') {
            flush_rewrite_rules();
            update_option('ws_team_showcase_rewrite_flushed', 'yes');
        }
    }

    public static function register_team_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group([
            'key' => 'group_ws_team_member_details',
            'title' => 'Team Member Details',
            'fields' => [
                [
                    'key' => 'field_ws_team_designation',
                    'label' => 'Designation',
                    'name' => 'designation',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                ],
                [
                    'key' => 'field_ws_team_experience',
                    'label' => 'Experience',
                    'name' => 'experience',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                ],
                [
                    'key' => 'field_ws_team_linkedin',
                    'label' => 'LinkedIn URL',
                    'name' => 'linkedin',
                    'type' => 'url',
                    'instructions' => '',
                    'required' => 0,
                ],
                [
                    'key' => 'field_ws_team_facebook',
                    'label' => 'Facebook URL',
                    'name' => 'facebook',
                    'type' => 'url',
                    'instructions' => '',
                    'required' => 0,
                ],
                [
                    'key' => 'field_ws_team_instagram',
                    'label' => 'Instagram URL',
                    'name' => 'instagram',
                    'type' => 'url',
                    'instructions' => '',
                    'required' => 0,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'ws_team',
                    ],
                ],
            ],
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ]);
    }

    public static function activate() {
        if (self::is_acf_active()) {
            self::register_team_content_types();
            flush_rewrite_rules();
            update_option('ws_team_showcase_rewrite_flushed', 'yes');
        }
    }

    public static function deactivate() {
        flush_rewrite_rules();
        delete_option('ws_team_showcase_rewrite_flushed');
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

register_activation_hook(__FILE__, ['WS_Team_Showcase_Init', 'activate']);
register_deactivation_hook(__FILE__, ['WS_Team_Showcase_Init', 'deactivate']);

new WS_Team_Showcase_Init();
