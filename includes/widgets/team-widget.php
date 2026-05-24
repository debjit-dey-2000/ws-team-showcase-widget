
<?php

if (!defined('ABSPATH')) exit;

class WS_Team_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'ws_team_showcase';
    }

    public function get_title() {
        return 'WS Team Showcase';
    }

    public function get_icon() {
        return 'eicon-person';
    }

    public function get_categories() {
        return ['general'];
    }

    public function get_style_depends() {
        return ['ws-team-style'];
    }

    public function get_script_depends() {
        return ['ws-team-script'];
    }

    private function add_responsive_border_controls($prefix, $selector, $label = 'Border') {
        $this->add_control(
            $prefix . '_border_type',
            [
                'label' => $label . ' Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => 'None',
                    'solid' => 'Solid',
                    'double' => 'Double',
                    'dotted' => 'Dotted',
                    'dashed' => 'Dashed',
                    'groove' => 'Groove',
                ],
                'selectors' => [
                    $selector => 'border-style: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            $prefix . '_border_width',
            [
                'label' => $label . ' Width',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'condition' => [
                    $prefix . '_border_type!' => ''
                ],
                'selectors' => [
                    $selector => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_control(
            $prefix . '_border_color',
            [
                'label' => $label . ' Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition' => [
                    $prefix . '_border_type!' => ''
                ],
                'selectors' => [
                    $selector => 'border-color: {{VALUE}}'
                ]
            ]
        );
    }

    private function get_team_field($field, $post_id = null) {
        if (!function_exists('get_field')) {
            return '';
        }

        return get_field($field, $post_id);
    }

    private function get_filter_taxonomy($post_type) {
        $taxonomies = get_object_taxonomies($post_type, 'names');
        $preferred = ['department', 'departments', 'team_department', 'ws_department', 'team_category', 'team-category', 'category'];
        $candidates = array_values(array_unique(array_merge(
            array_values(array_intersect($preferred, $taxonomies)),
            $taxonomies
        )));
        $best_taxonomy = '';
        $best_count = 0;

        foreach ($candidates as $taxonomy) {
            $count = $this->get_taxonomy_term_count($taxonomy, true);

            if ($count > $best_count) {
                $best_taxonomy = $taxonomy;
                $best_count = $count;
            }
        }

        if ($best_taxonomy) {
            return $best_taxonomy;
        }

        foreach ($candidates as $taxonomy) {
            if ($this->get_taxonomy_term_count($taxonomy, false) > 0) {
                return $taxonomy;
            }
        }

        return $taxonomies[0] ?? '';
    }

    private function get_taxonomy_term_count($taxonomy, $hide_empty) {
        if (!taxonomy_exists($taxonomy)) {
            return 0;
        }

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => $hide_empty,
            'fields' => 'ids',
        ]);

        if (is_wp_error($terms)) {
            return 0;
        }

        return count($terms);
    }

    protected function register_controls() {

        $this->start_controls_section(
            'query_section',
            [
                'label' => 'Query',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $post_types = get_post_types(['public' => true], 'objects');
        $options = [];

        foreach ($post_types as $type) {
            $options[$type->name] = $type->label;
        }

        $this->add_control(
            'post_type',
            [
                'label' => 'Post Type',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $options,
                'default' => isset($options['ws_team']) ? 'ws_team' : 'post'
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => 'Order By',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'date' => 'Date',
                    'title' => 'Title',
                    'menu_order' => 'Menu Order',
                    'modified' => 'Last Modified',
                    'rand' => 'Random',
                ],
                'default' => 'date'
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => 'Order',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'ASC' => 'ASC',
                    'DESC' => 'DESC',
                ],
                'default' => 'DESC'
            ]
        );

        $this->add_control(
            'show_filter',
            [
                'label' => 'Filter Options',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Show',
                'label_off' => 'Hide',
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'show_dark_mode',
            [
                'label' => 'Dark Mode Toggle',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Show',
                'label_off' => 'Hide',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_layout_toggle',
            [
                'label' => 'Layout Toggle',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => 'Show',
                'label_off' => 'Hide',
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'card_hover_effect',
            [
                'label' => 'Card Hover Content Effect',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'none' => 'None',
                    'flip' => 'FlipBox',
                    'reveal-zoom' => 'Content Reveal - Zoom Blur',
                ],
                'default' => 'none',
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        /*
        ======================================
        GRID STYLE
        ======================================
        */

        $this->start_controls_section(
            'layout_style',
            [
                'label' => 'Layout',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'column_items',
            [
                'label' => 'Column Items',
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ws-team-grid' =>
                    'grid-template-columns: repeat({{VALUE}}, 1fr)'
                ]
            ]
        );

        $this->add_responsive_control(
            'row_items',
            [
                'label' => 'Row Items',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ws-team-grid' =>
                    'grid-template-rows: repeat({{VALUE}}, auto)'
                ]
            ]
        );

        $this->add_responsive_control(
            'column_gap',
            [
                'label' => 'Column Gap',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-grid' =>
                    'column-gap: {{SIZE}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'row_gap',
            [
                'label' => 'Row Gap',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-grid' =>
                    'row-gap: {{SIZE}}{{UNIT}}'
                ]
            ]
        );

        $this->end_controls_section();

        /*
        ======================================
        GRID BOX STYLE
        ======================================
        */

        $this->start_controls_section(
            'box_style',
            [
                'label' => 'Grid Box Styling',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}} .ws-team-card'
            ]
        );

        $this->add_responsive_control(
            'box_border_radius',
            [
                'label' => 'Border Radius',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-card' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('box', '{{WRAPPER}} .ws-team-card');

        $this->add_responsive_control(
            'box_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-card' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->end_controls_section();

        /*
        ======================================
        IMAGE STYLE
        ======================================
        */

        $this->start_controls_section(
            'image_style',
            [
                'label' => 'Image Options',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'image_aspect_ratio',
            [
                'label' => 'Aspect Ratio',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '1 / 1' => '1/1',
                    '3 / 4' => '3/4',
                    '9 / 16' => '9/16',
                    '2 / 3' => '2/3',
                    '3 / 2' => '3/2',
                    '16 / 9' => '16/9',
                    '4 / 3' => '4/3',
                ],
                'default' => '1 / 1',
                'selectors' => [
                    '{{WRAPPER}} .ws-team-card img' =>
                    'aspect-ratio: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'image_object_fit',
            [
                'label' => 'Object Fit',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'cover' => 'Cover',
                    'contain' => 'Contain',
                    'fill' => 'Fill',
                    'none' => 'None',
                    'scale-down' => 'Scale Down',
                ],
                'default' => 'cover',
                'selectors' => [
                    '{{WRAPPER}} .ws-team-card img' => 'object-fit: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'image_width',
            [
                'label' => 'Width',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw'],
                'selectors' => [
                    '{{WRAPPER}} .ws-team-card img' => 'width: {{SIZE}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => 'Height',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vh'],
                'selectors' => [
                    '{{WRAPPER}} .ws-team-card img' => 'height: {{SIZE}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'image_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-card img' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->end_controls_section();

        /*
        ======================================
        CONTENT BOX
        ======================================
        */

        $this->start_controls_section(
            'content_style',
            [
                'label' => 'Team Content Box',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_bg',
            [
                'label' => 'Box Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => 'Box Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('content_box', '{{WRAPPER}} .ws-team-content');

        $this->add_control(
            'heading_heading',
            [
                'label' => 'Team Member Heading',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'heading_typography',
                'selector' => '{{WRAPPER}} .ws-team-content h3'
            ]
        );

        $this->add_control(
            'heading_color',
            [
                'label' => 'Heading Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content h3' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'designation_heading',
            [
                'label' => 'Team Member Designation',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'designation_typography',
                'selector' => '{{WRAPPER}} .ws-team-content .ws-meta .ws-designation, {{WRAPPER}} .ws-team-wrapper.ws-team-table-view .ws-team-content .ws-meta .ws-designation'
            ]
        );

        $this->add_control(
            'designation_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content .ws-meta .ws-designation' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'designation_bg',
            [
                'label' => 'Background',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content .ws-meta .ws-designation' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'designation_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content .ws-meta .ws-designation' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'designation_border_radius',
            [
                'label' => 'Border Radius',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content .ws-meta .ws-designation' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('designation', '{{WRAPPER}} .ws-team-content .ws-meta .ws-designation');

        $this->add_control(
            'experience_heading',
            [
                'label' => 'Team Member Experience',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'experience_typography',
                'selector' => '{{WRAPPER}} .ws-team-content .ws-meta .ws-experience, {{WRAPPER}} .ws-team-wrapper.ws-team-table-view .ws-team-content .ws-meta .ws-experience'
            ]
        );

        $this->add_control(
            'experience_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content .ws-meta .ws-experience' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'experience_bg',
            [
                'label' => 'Background',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content .ws-meta .ws-experience' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'experience_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content .ws-meta .ws-experience' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'experience_border_radius',
            [
                'label' => 'Border Radius',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content .ws-meta .ws-experience' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('experience', '{{WRAPPER}} .ws-team-content .ws-meta .ws-experience');

        $this->add_control(
            'bio_heading',
            [
                'label' => 'Team Member Short Bio',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'bio_typography',
                'selector' => '{{WRAPPER}} .ws-team-content .ws-short-bio'
            ]
        );

        $this->add_control(
            'bio_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-content .ws-short-bio' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'social_heading',
            [
                'label' => 'Social Media Icon',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'social_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-social-icons a' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'social_bg',
            [
                'label' => 'Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-social-icons a' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'social_size',
            [
                'label' => 'Size',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .ws-social-icons a' => 'font-size: {{SIZE}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'social_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-social-icons a' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'social_radius',
            [
                'label' => 'Border Radius',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-social-icons a' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('social', '{{WRAPPER}} .ws-social-icons a');

        $this->add_responsive_control(
            'social_column_gap',
            [
                'label' => 'Column Gap',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .ws-social-icons' => 'column-gap: {{SIZE}}{{UNIT}}'
                ]
            ]
        );

        $this->add_control(
            'social_hover_color',
            [
                'label' => 'Hover Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-social-icons a:hover' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'social_hover_bg',
            [
                'label' => 'Hover Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-social-icons a:hover' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'button_heading',
            [
                'label' => 'View Details Button',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'btn_typography',
                'selector' => '{{WRAPPER}} .ws-popup-open'
            ]
        );

        $this->start_controls_tabs('button_tabs');

        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => 'Normal',
            ]
        );

        $this->add_control(
            'btn_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-open' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'btn_bg',
            [
                'label' => 'Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-open' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_hover_tab',
            [
                'label' => 'Hover',
            ]
        );

        $this->add_control(
            'btn_hover_color',
            [
                'label' => 'Hover Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-open:hover' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'btn_hover_bg',
            [
                'label' => 'Hover Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-open:hover' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'btn_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-open' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('button', '{{WRAPPER}} .ws-popup-open');

        $this->add_responsive_control(
            'btn_radius',
            [
                'label' => 'Border Radius',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-open' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_control(
            'btn_transition',
            [
                'label' => 'Transition Timing',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-open' => 'transition: color {{SIZE}}s ease, background-color {{SIZE}}s ease, border-color {{SIZE}}s ease'
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'load_more_button_style',
            [
                'label' => 'Load More Button',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'load_more_alignment',
            [
                'label' => 'Alignment',
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => 'Left',
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => 'Center',
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => 'Right',
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .ws-team-load-more-wrap' => 'text-align: {{VALUE}}'
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'load_more_typography',
                'selector' => '{{WRAPPER}} .ws-team-load-more'
            ]
        );

        $this->start_controls_tabs('load_more_button_tabs');

        $this->start_controls_tab(
            'load_more_button_normal_tab',
            [
                'label' => 'Normal',
            ]
        );

        $this->add_control(
            'load_more_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-load-more' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'load_more_bg',
            [
                'label' => 'Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-load-more' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'load_more_button_hover_tab',
            [
                'label' => 'Hover',
            ]
        );

        $this->add_control(
            'load_more_hover_color',
            [
                'label' => 'Hover Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-load-more:hover' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'load_more_hover_bg',
            [
                'label' => 'Hover Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-load-more:hover' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'load_more_hover_border_color',
            [
                'label' => 'Hover Border Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-load-more:hover' => 'border-color: {{VALUE}}'
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'load_more_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-load-more' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('load_more', '{{WRAPPER}} .ws-team-load-more');

        $this->add_responsive_control(
            'load_more_radius',
            [
                'label' => 'Border Radius',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-team-load-more' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->end_controls_section();

        /*
        ======================================
        POPUP STYLE
        ======================================
        */

        $this->start_controls_section(
            'popup_style',
            [
                'label' => 'Popup Box Options',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'popup_bg',
            [
                'label' => 'Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-container' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'popup_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-container' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_control(
            'overlay_bg',
            [
                'label' => 'Popup Overlay Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-overlay' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'popup_effect',
            [
                'label' => 'Popup Open and Close Effect',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'fade' => 'Fade',
                    'zoom' => 'Zoom',
                    'slide-up' => 'Slide Up',
                ],
                'default' => 'fade',
            ]
        );

        $this->add_responsive_control(
            'popup_radius',
            [
                'label' => 'Border Radius',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-container' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('popup', '{{WRAPPER}} .ws-popup-container');

        $this->add_control(
            'close_icon_heading',
            [
                'label' => 'Close Icon',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'close_icon_size',
            [
                'label' => 'Size',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-close' => 'font-size: {{SIZE}}{{UNIT}}'
                ]
            ]
        );

        $this->add_control(
            'close_icon_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-close' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'close_icon_bg',
            [
                'label' => 'Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-close' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'close_icon_radius',
            [
                'label' => 'Border Radius',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-close' =>
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'close_icon_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-close' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_control(
            'popup_content_heading',
            [
                'label' => 'Popup Box Content',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'popup_heading_typography',
                'selector' => '{{WRAPPER}} .ws-popup-inner h2'
            ]
        );

        $this->add_control(
            'popup_heading_color',
            [
                'label' => 'Heading Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-inner h2' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'popup_heading_margin',
            [
                'label' => 'Heading Margin',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-inner h2' =>
                    'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_control(
            'popup_designation_heading',
            [
                'label' => 'Team Member Designation',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'popup_designation_typography',
                'selector' => '{{WRAPPER}} .ws-popup-meta .ws-designation'
            ]
        );

        $this->add_control(
            'popup_designation_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-meta .ws-designation' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'popup_designation_bg',
            [
                'label' => 'Background',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-meta .ws-designation' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'popup_designation_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-meta .ws-designation' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('popup_designation', '{{WRAPPER}} .ws-popup-meta .ws-designation');

        $this->add_control(
            'popup_experience_heading',
            [
                'label' => 'Team Member Experience',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'popup_experience_typography',
                'selector' => '{{WRAPPER}} .ws-popup-meta .ws-experience'
            ]
        );

        $this->add_control(
            'popup_experience_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-meta .ws-experience' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'popup_experience_bg',
            [
                'label' => 'Background',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-meta .ws-experience' => 'background: {{VALUE}}'
                ]
            ]
        );

        $this->add_responsive_control(
            'popup_experience_padding',
            [
                'label' => 'Padding',
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-meta .ws-experience' =>
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]
        );

        $this->add_responsive_border_controls('popup_experience', '{{WRAPPER}} .ws-popup-meta .ws-experience');

        $this->add_control(
            'popup_body_heading',
            [
                'label' => 'Team Member Content',
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'popup_body_typography',
                'selector' => '{{WRAPPER}} .ws-popup-content'
            ]
        );

        $this->add_control(
            'popup_body_color',
            [
                'label' => 'Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ws-popup-content' => 'color: {{VALUE}}'
                ]
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {

        $settings = $this->get_settings_for_display();
        $filter_taxonomy = $this->get_filter_taxonomy($settings['post_type']);
        $filter_terms = $filter_taxonomy ? get_terms([
            'taxonomy' => $filter_taxonomy,
            'hide_empty' => false,
        ]) : [];
        $show_filter = ($settings['show_filter'] ?? '') === 'yes' && $filter_taxonomy && !is_wp_error($filter_terms);
        $show_dark_mode = ($settings['show_dark_mode'] ?? '') === 'yes';
        $show_layout_toggle = ($settings['show_layout_toggle'] ?? '') === 'yes';
        $toggle_id = 'ws-team-dark-toggle-' . $this->get_id();
        $wrapper_class = 'ws-team-wrapper-' . $this->get_id();
        $allowed_hover_effects = ['none', 'flip', 'reveal-zoom'];
        $card_hover_effect = in_array(($settings['card_hover_effect'] ?? 'none'), $allowed_hover_effects, true) ? $settings['card_hover_effect'] : 'none';
        $hover_effect_class = 'ws-hover-effect-' . $card_hover_effect;

        $query_args = [
            'post_type' => $settings['post_type'],
            'posts_per_page' => 8,
            'paged' => 1,
            'orderby' => $settings['orderby'],
            'order' => $settings['order']
        ];

        $query = new WP_Query($query_args);

        ?>

        <div
            class="ws-team-wrapper <?php echo esc_attr($wrapper_class); ?> <?php echo esc_attr($hover_effect_class); ?>"
            data-popup-effect="<?php echo esc_attr($settings['popup_effect'] ?? 'fade'); ?>"
            data-post-type="<?php echo esc_attr($settings['post_type']); ?>"
            data-orderby="<?php echo esc_attr($settings['orderby']); ?>"
            data-order="<?php echo esc_attr($settings['order']); ?>"
            data-taxonomy="<?php echo esc_attr($filter_taxonomy); ?>"
            data-term-id="0"
            data-posts-per-page="8"
        >

            <?php if ($show_filter || $show_dark_mode || $show_layout_toggle) : ?>
                <div class="ws-team-topbar">
                    <?php if ($show_filter) : ?>
                        <div class="ws-team-filter-wrap">
                            <select class="ws-team-category-filter" aria-label="Team Categories">
                                <option value="0">All Departments</option>
                                <?php foreach ($filter_terms as $term) : ?>
                                    <option value="<?php echo esc_attr($term->term_id); ?>">
                                        <?php echo esc_html($term->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="ws-team-filter-loader" aria-hidden="true"></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_dark_mode || $show_layout_toggle) : ?>
                        <div class="ws-team-topbar-actions">
                            <?php if ($show_layout_toggle) : ?>
                                <div class="ws-layout-toggle" role="group" aria-label="Layout toggle">
                                    <button type="button" class="ws-layout-toggle-button ws-layout-toggle-button-active" data-layout="grid" aria-label="Grid view" aria-pressed="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6.75c0-1.768 0-2.652.55-3.2C4.097 3 4.981 3 6.75 3s2.652 0 3.2.55c.55.548.55 1.432.55 3.2s0 2.652-.55 3.2c-.548.55-1.432.55-3.2.55s-2.652 0-3.2-.55C3 9.403 3 8.519 3 6.75m0 10.507c0-1.768 0-2.652.55-3.2.548-.55 1.432-.55 3.2-.55s2.652 0 3.2.55c.55.548.55 1.432.55 3.2s0 2.652-.55 3.2c-.548.55-1.432.55-3.2.55s-2.652 0-3.2-.55C3 19.91 3 19.026 3 17.258M13.5 6.75c0-1.768 0-2.652.55-3.2.548-.55 1.432-.55 3.2-.55s2.652 0 3.2.55c.55.548.55 1.432.55 3.2s0 2.652-.55 3.2c-.548.55-1.432.55-3.2.55s-2.652 0-3.2-.55c-.55-.548-.55-1.432-.55-3.2m0 10.507c0-1.768 0-2.652.55-3.2.548-.55 1.432-.55 3.2-.55s2.652 0 3.2.55c.55.548.55 1.432.55 3.2s0 2.652-.55 3.2c-.548.55-1.432.55-3.2.55s-2.652 0-3.2-.55c-.55-.548-.55-1.432-.55-3.2"/></svg>
                                    </button>
                                    <button type="button" class="ws-layout-toggle-button" data-layout="table" aria-label="Table view" aria-pressed="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path fill="currentColor" d="M5 19V5zm.616 1q-.691 0-1.153-.462T4 18.384V5.616q0-.691.463-1.153T5.616 4h12.769q.69 0 1.153.463T20 5.616v7.717q0 .215-.144.355t-.357.139-.356-.144-.143-.356V5.616q0-.231-.192-.424T18.384 5H5.616q-.231 0-.424.192T5 5.616v12.769q0 .23.192.423t.423.192h6.193q.212 0 .356.144t.144.357-.144.356-.356.143zm3.049-3.683q.22-.221.22-.549t-.222-.548-.549-.22-.548.222-.22.549.222.547.549.22.548-.221m0-3.77q.22-.22.22-.548t-.222-.548-.549-.22-.548.221-.22.55.222.547.549.22.548-.221m0-3.77q.22-.221.22-.548t-.222-.548-.549-.22-.548.221-.22.549.222.548.549.22.548-.221m7.412 3.721q.213 0 .356-.144t.144-.357-.144-.356-.356-.143h-4.385q-.212 0-.356.144t-.144.357.144.356.356.143zm0-3.77q.213 0 .356-.143.144-.144.144-.357t-.144-.356-.356-.143h-4.385q-.212 0-.356.144t-.144.357.144.356.356.143zm-4.741 7.396q.144.143.356.143h1.204q.213 0 .356-.144t.144-.356-.144-.356-.356-.144h-1.204q-.212 0-.356.144t-.144.357.144.356m6.087 3.374h-2.5q-.213 0-.356-.144t-.144-.357.144-.356.356-.143h2.5V16q0-.213.144-.356t.357-.144.356.144.143.356v2.5h2.5q.213 0 .356.144t.144.357-.144.356-.356.143h-2.5V22q0 .213-.144.356-.144.144-.357.144t-.356-.144-.143-.356z"/></svg>
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if ($show_dark_mode) : ?>
                                <div class="ws-dark-mode-toggle">
                                    <input type="checkbox" id="<?php echo esc_attr($toggle_id); ?>" class="ws-dark-toggle-input"/>
                                    <label for="<?php echo esc_attr($toggle_id); ?>"></label>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="ws-team-grid">

                <?php while($query->have_posts()) : $query->the_post(); ?>
                    <?php echo ws_team_showcase_render_card(get_the_ID()); ?>

                <?php endwhile; wp_reset_postdata(); ?>

            </div>

            <?php echo ws_team_showcase_render_load_more(1, (int) $query->max_num_pages); ?>

            <div class="ws-popup-overlay"></div>
            <div class="ws-popup-container"></div>

        </div>

        <?php
    }
}
