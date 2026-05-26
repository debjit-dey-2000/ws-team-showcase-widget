
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

        add_action('admin_init', [$this, 'check_elementor_dependency']);
        add_action('admin_notices', [$this, 'elementor_dependency_notice']);

        if (!self::is_elementor_active()) {
            return;
        }

        add_action('init', [$this, 'register_team_post_type']);
        add_action('acf/init', [$this, 'ensure_acf_definitions'], 1);
        add_action('acf/init', [$this, 'flush_rewrite_rules_once'], 20);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_action('elementor/widgets/register', [$this, 'register_widget']);

        require_once WS_TEAM_PATH . 'includes/ajax/popup-handler.php';
    }

    private static function is_elementor_active() {
        if (did_action('elementor/loaded') || class_exists('\Elementor\Plugin')) {
            return true;
        }

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active('elementor/elementor.php');
    }

    private static function is_acf_active() {
        return class_exists('ACF') || function_exists('acf') || function_exists('acf_add_local_field_group');
    }

    public static function register_team_post_type() {
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
    }

    public static function ensure_acf_definitions() {
        if (!self::is_acf_active()) {
            return;
        }

        self::create_acf_team_taxonomy();
        self::create_acf_team_fields();
    }

    public static function flush_rewrite_rules_once() {
        if (get_option('ws_team_showcase_rewrite_flushed') !== 'yes' && post_type_exists('ws_team') && taxonomy_exists('team_category')) {
            flush_rewrite_rules();
            update_option('ws_team_showcase_rewrite_flushed', 'yes');
        }
    }

    private static function create_acf_team_taxonomy() {
        if (!function_exists('acf_get_taxonomy') || !function_exists('acf_update_taxonomy')) {
            return;
        }

        if (acf_get_taxonomy('taxonomy_team_category')) {
            return;
        }

        acf_update_taxonomy([
            'key' => 'taxonomy_team_category',
            'title' => 'Team Categories',
            'taxonomy' => 'team_category',
            'object_type' => ['ws_team'],
            'labels' => [
                'name' => 'Team Categories',
                'singular_name' => 'Team Category',
                'menu_name' => 'Team Categories',
                'search_items' => 'Search Team Categories',
                'all_items' => 'All Team Categories',
                'edit_item' => 'Edit Team Category',
                'view_item' => 'View Team Category',
                'update_item' => 'Update Team Category',
                'add_new_item' => 'Add New Team Category',
                'new_item_name' => 'New Team Category Name',
                'not_found' => 'No team categories found',
            ],
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'rewrite' => [
                'permalink_rewrite' => 'custom_permalink',
                'slug' => 'team-category',
                'with_front' => true,
                'rewrite_hierarchical' => false,
            ],
            'active' => true,
        ]);
    }

    private static function create_acf_team_fields() {
        if (!function_exists('acf_get_field_group') || !function_exists('acf_update_field_group') || !function_exists('acf_update_field')) {
            return;
        }

        $field_group = acf_get_field_group('group_ws_team_member_details');

        if (!$field_group) {
            $field_group = acf_update_field_group([
                'key' => 'group_ws_team_member_details',
                'title' => 'Team Member Details',
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

        $parent_id = !empty($field_group['ID']) ? (int) $field_group['ID'] : 0;

        if (!$parent_id) {
            return;
        }

        foreach (self::get_team_acf_fields() as $index => $field) {
            if (function_exists('acf_get_field') && acf_get_field($field['key'])) {
                continue;
            }

            $field['parent'] = $parent_id;
            $field['menu_order'] = $index;
            acf_update_field($field);
        }
    }

    private static function get_team_acf_fields() {
        return [
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
        ];
    }

    public static function activate() {
        if (!self::is_elementor_active()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('WS Team Showcase Plugin requires Elementor to be installed and activated first.', 'ws-team-showcase'),
                esc_html__('Plugin Activation Error', 'ws-team-showcase'),
                ['back_link' => true]
            );
        }

        if (self::is_acf_active()) {
            self::register_team_post_type();
            self::ensure_acf_definitions();
            self::flush_rewrite_rules_once();
        }
    }

    public static function deactivate() {
        flush_rewrite_rules();
        delete_option('ws_team_showcase_rewrite_flushed');
    }

    public function check_elementor_dependency() {
        if (self::is_elementor_active()) {
            return;
        }

        deactivate_plugins(plugin_basename(__FILE__));
        set_transient('ws_team_showcase_elementor_missing_notice', 'yes', 30);
    }

    public function elementor_dependency_notice() {
        if (get_transient('ws_team_showcase_elementor_missing_notice') !== 'yes') {
            return;
        }

        delete_transient('ws_team_showcase_elementor_missing_notice');
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html__('WS Team Showcase Plugin has been deactivated because it requires Elementor to be installed and activated.', 'ws-team-showcase'); ?></p>
        </div>
        <?php
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
