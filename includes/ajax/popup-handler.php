
<?php

add_action('wp_ajax_ws_team_popup', 'ws_team_popup_callback');
add_action('wp_ajax_nopriv_ws_team_popup', 'ws_team_popup_callback');
add_action('wp_ajax_ws_team_filter', 'ws_team_filter_callback');
add_action('wp_ajax_nopriv_ws_team_filter', 'ws_team_filter_callback');
add_action('wp_ajax_ws_team_load_more', 'ws_team_load_more_callback');
add_action('wp_ajax_nopriv_ws_team_load_more', 'ws_team_load_more_callback');

if (!function_exists('ws_team_showcase_get_field')) {
    function ws_team_showcase_get_field($field, $post_id = null) {
        if (!function_exists('get_field')) {
            return '';
        }

        return get_field($field, $post_id);
    }
}

if (!function_exists('ws_team_showcase_get_short_bio')) {
    function ws_team_showcase_get_short_bio($post_id) {
        $content = get_post_field('post_content', $post_id);
        $content = wp_strip_all_tags(strip_shortcodes($content));
        $content = trim(preg_replace('/\s+/', ' ', $content));

        if (!$content) {
            return '';
        }

        return wp_trim_words($content, 38, '...');
    }
}

if (!function_exists('ws_team_showcase_get_social_icon')) {
    function ws_team_showcase_get_social_icon($network) {
        $icons = [
            'linkedin' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.5 8h4V23h-4V8zm7 0h3.84v2.05h.05c.53-1 1.84-2.05 3.79-2.05 4.05 0 4.8 2.67 4.8 6.14V23h-4v-7.86c0-1.88-.03-4.29-2.61-4.29-2.62 0-3.02 2.04-3.02 4.15V23h-4V8z"/></svg>',
            'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M22.68 0H1.32C.59 0 0 .59 0 1.32v21.36C0 23.41.59 24 1.32 24h11.5v-9.29H9.69v-3.62h3.13V8.41c0-3.1 1.89-4.79 4.66-4.79 1.32 0 2.46.1 2.79.14V7h-1.91c-1.5 0-1.79.71-1.79 1.76v2.31h3.58l-.47 3.62h-3.11V24h6.11c.73 0 1.32-.59 1.32-1.32V1.32C24 .59 23.41 0 22.68 0z"/></svg>',
            'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.22.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.05.41 2.22.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.22-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.05.36-2.22.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.22-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.05-.41-2.22-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.22.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.05-.36 2.22-.41 1.27-.06 1.65-.07 4.85-.07zM12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.31-1.46.72-2.13 1.39C1.34 2.69.93 3.36.62 4.15.32 4.91.12 5.79.06 7.06.01 8.34 0 8.75 0 12s.01 3.66.07 4.94c.06 1.27.26 2.15.56 2.91.31.79.72 1.46 1.39 2.13.67.67 1.34 1.08 2.13 1.39.76.3 1.64.5 2.91.56 1.28.06 1.69.07 4.94.07s3.66-.01 4.94-.07c1.27-.06 2.15-.26 2.91-.56.79-.31 1.46-.72 2.13-1.39.67-.67 1.08-1.34 1.39-2.13.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.94s-.01-3.66-.07-4.94c-.06-1.27-.26-2.15-.56-2.91-.31-.79-.72-1.46-1.39-2.13C21.31 1.35 20.64.94 19.85.63c-.76-.3-1.64-.5-2.91-.56C15.66.01 15.25 0 12 0zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm7.85-10.41a1.44 1.44 0 1 1-2.88 0 1.44 1.44 0 0 1 2.88 0z"/></svg>',
        ];

        return $icons[$network] ?? '';
    }
}

if (!function_exists('ws_team_showcase_render_card')) {
    function ws_team_showcase_render_card($post_id) {
        $designation = ws_team_showcase_get_field('designation', $post_id);
        $experience = ws_team_showcase_get_field('experience', $post_id);
        $short_bio = ws_team_showcase_get_short_bio($post_id);
        $social_links = [
            'linkedin' => [
                'url' => ws_team_showcase_get_field('linkedin', $post_id),
            ],
            'facebook' => [
                'url' => ws_team_showcase_get_field('facebook', $post_id),
            ],
            'instagram' => [
                'url' => ws_team_showcase_get_field('instagram', $post_id),
            ],
        ];

        ob_start();
        ?>
        <div class="ws-team-card">

            <?php echo get_the_post_thumbnail($post_id, 'large', ['loading' => 'lazy']); ?>

            <div class="ws-team-content">

                <h3><?php echo esc_html(get_the_title($post_id)); ?></h3>

                <?php if (array_filter(array_column($social_links, 'url'))) : ?>
                    <div class="ws-social-icons">
                        <?php foreach ($social_links as $network => $social) : ?>
                            <?php if ($social['url']) : ?>
                                <a href="<?php echo esc_url($social['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr(ucfirst($network)); ?>">
                                    <?php echo ws_team_showcase_get_social_icon($network); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="ws-meta">
                    <?php if ($designation) : ?>
                        <span class="ws-designation"><?php echo esc_html($designation); ?></span>
                    <?php endif; ?>
                    <?php if ($experience) : ?>
                        <span class="ws-experience"><?php echo esc_html($experience); ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($short_bio) : ?>
                    <p class="ws-short-bio">
                        <?php echo esc_html($short_bio); ?>
                    </p>
                <?php endif; ?>

                <button
                    class="ws-popup-open"
                    data-id="<?php echo esc_attr($post_id); ?>"
                >
                    View Details
                </button>

            </div>

        </div>
        <?php

        return ob_get_clean();
    }
}

if (!function_exists('ws_team_showcase_get_query_args')) {
    function ws_team_showcase_get_query_args($params = []) {
        $allowed_orderby = ['date', 'title', 'menu_order', 'modified', 'rand'];
        $post_type = isset($params['post_type']) ? sanitize_key($params['post_type']) : 'post';
        $taxonomy = isset($params['taxonomy']) ? sanitize_key($params['taxonomy']) : '';
        $term_id = isset($params['term_id']) ? absint($params['term_id']) : 0;
        $orderby = isset($params['orderby']) && in_array($params['orderby'], $allowed_orderby, true) ? $params['orderby'] : 'date';
        $order = isset($params['order']) && strtoupper($params['order']) === 'ASC' ? 'ASC' : 'DESC';
        $paged = isset($params['paged']) ? max(1, absint($params['paged'])) : 1;
        $posts_per_page = isset($params['posts_per_page']) ? max(1, absint($params['posts_per_page'])) : 8;

        $args = [
            'post_type' => $post_type,
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'orderby' => $orderby,
            'order' => $order,
        ];

        if ($taxonomy && $term_id && taxonomy_exists($taxonomy)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term_id,
                ],
            ];
        }

        return $args;
    }
}

if (!function_exists('ws_team_showcase_render_load_more')) {
    function ws_team_showcase_render_load_more($current_page, $max_pages) {
        if ($max_pages <= $current_page) {
            return '';
        }

        ob_start();
        ?>
        <div class="ws-team-load-more-wrap">
            <button
                type="button"
                class="ws-team-load-more"
                data-page="<?php echo esc_attr($current_page); ?>"
                data-max-pages="<?php echo esc_attr($max_pages); ?>"
            >
                Load More
            </button>
        </div>
        <?php

        return ob_get_clean();
    }
}

if (!function_exists('ws_team_showcase_get_cards_html')) {
    function ws_team_showcase_get_cards_html($query) {
        ob_start();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                echo ws_team_showcase_render_card(get_the_ID());
            }
            wp_reset_postdata();
        }

        return ob_get_clean();
    }
}

function ws_team_popup_callback() {

    $post_id = intval($_POST['post_id']);
    $designation = function_exists('get_field') ? get_field('designation', $post_id) : '';
    $experience = function_exists('get_field') ? get_field('experience', $post_id) : '';

    ?>

    <div class="ws-popup-inner">

        <button class="ws-popup-close">×</button>

        <h2><?php echo get_the_title($post_id); ?></h2>

        <?php if ($designation || $experience) : ?>
            <div class="ws-popup-meta">
                <?php if ($designation) : ?>
                    <span class="ws-designation"><?php echo esc_html($designation); ?></span>
                <?php endif; ?>
                <?php if ($experience) : ?>
                    <span class="ws-experience"><?php echo esc_html($experience); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="ws-popup-content">
            <?php echo apply_filters('the_content', get_post_field('post_content', $post_id)); ?>
        </div>

    </div>

    <?php

    wp_die();
}

function ws_team_filter_callback() {
    check_ajax_referer('ws_team_filter_nonce', 'nonce');

    $params = [
        'post_type' => $_POST['post_type'] ?? 'post',
        'taxonomy' => $_POST['taxonomy'] ?? '',
        'term_id' => $_POST['term_id'] ?? 0,
        'orderby' => $_POST['orderby'] ?? 'date',
        'order' => $_POST['order'] ?? 'DESC',
        'posts_per_page' => $_POST['posts_per_page'] ?? 8,
        'paged' => 1,
    ];

    $query = new WP_Query(ws_team_showcase_get_query_args($params));
    $cards_html = ws_team_showcase_get_cards_html($query);

    wp_send_json_success([
        'html' => $cards_html,
        'load_more' => ws_team_showcase_render_load_more(1, (int) $query->max_num_pages),
        'has_posts' => $cards_html !== '',
    ]);
}

function ws_team_load_more_callback() {
    check_ajax_referer('ws_team_filter_nonce', 'nonce');

    $current_page = isset($_POST['page']) ? max(1, absint($_POST['page'])) : 1;
    $next_page = $current_page + 1;
    $params = [
        'post_type' => $_POST['post_type'] ?? 'post',
        'taxonomy' => $_POST['taxonomy'] ?? '',
        'term_id' => $_POST['term_id'] ?? 0,
        'orderby' => $_POST['orderby'] ?? 'date',
        'order' => $_POST['order'] ?? 'DESC',
        'posts_per_page' => $_POST['posts_per_page'] ?? 8,
        'paged' => $next_page,
    ];

    $query = new WP_Query(ws_team_showcase_get_query_args($params));

    wp_send_json_success([
        'html' => ws_team_showcase_get_cards_html($query),
        'page' => $next_page,
        'max_pages' => (int) $query->max_num_pages,
        'has_more' => $next_page < (int) $query->max_num_pages,
    ]);
}
