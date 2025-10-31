<?php
/**
 * Extend BuddyPress group directory with tag filtering
 *
 * @package BP_Group_Finder
 * @subpackage Public
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BPGF_Directory class
 *
 * Handles the public display of group interest tags and filtering
 */
class BPGF_Directory {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the directory integration
     *
     * @since 1.0.0
     */
    public function init() {
        // Add search form to directory
        add_action( 'bp_before_directory_groups_content', array( $this, 'add_directory_filters' ) );

        // Modify groups query
        add_filter( 'bp_after_has_groups_parse_args', array( $this, 'filter_groups_query' ) );

        // Display tags on group items
        add_action( 'bp_directory_groups_item', array( $this, 'display_group_tags' ) );

        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Handle AJAX filtering
        add_action( 'wp_ajax_bpgf_filter_groups', array( $this, 'ajax_filter_groups' ) );
        add_action( 'wp_ajax_nopriv_bpgf_filter_groups', array( $this, 'ajax_filter_groups' ) );
    }

    /**
     * Add filter UI to group directory
     *
     * @since 1.0.0
     */
    public function add_directory_filters() {
        // Check if we're on the groups directory
        if ( ! bp_is_groups_directory() ) {
            return;
        }

        // Get current filter values
        $current_interest = isset( $_GET['interest'] ) ? sanitize_text_field( wp_unslash( $_GET['interest'] ) ) : '';
        $search_terms = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

        ?>
        <div class="bpgf-directory-filters">
            <div class="bpgf-search-container">
                <div class="bpgf-search-input-wrapper">
                    <input
                        type="text"
                        id="bpgf-group-search"
                        class="bpgf-search-input"
                        placeholder="<?php esc_attr_e( 'Search groups by interests...', 'bp-group-finder' ); ?>"
                        value="<?php echo esc_attr( $current_interest ); ?>"
                        autocomplete="off"
                    />
                    <button type="button" id="bpgf-search-btn" class="bpgf-search-btn">
                        <?php esc_html_e( 'Search', 'bp-group-finder' ); ?>
                    </button>
                </div>

                <?php if ( ! empty( $current_interest ) ) : ?>
                    <div class="bpgf-active-filters">
                        <span class="bpgf-filter-label"><?php esc_html_e( 'Filtering by:', 'bp-group-finder' ); ?></span>
                        <span class="bpgf-tag-chip active" data-tag="<?php echo esc_attr( $current_interest ); ?>">
                            <?php echo esc_html( $current_interest ); ?>
                            <button type="button" class="bpgf-remove-filter" aria-label="<?php esc_attr_e( 'Remove filter', 'bp-group-finder' ); ?>">
                                &times;
                            </button>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="bpgf-popular-interests">
                    <span class="bpgf-popular-label"><?php esc_html_e( 'Popular:', 'bp-group-finder' ); ?></span>
                    <?php echo $this->get_popular_tags_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Modify groups query to include tag filtering
     *
     * @since 1.0.0
     * @param array $query_args Query arguments.
     * @return array Modified query arguments.
     */
    public function filter_groups_query( $query_args ) {
        // Check for interest filter
        if ( isset( $_GET['interest'] ) && ! empty( $_GET['interest'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $interest = sanitize_text_field( wp_unslash( $_GET['interest'] ) );

            // Get term by slug
            $term = get_term_by( 'slug', $interest, 'group_interest' );
            if ( $term ) {
                $query_args['tax_query'][] = array(
                    'taxonomy' => 'group_interest',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                );
            }
        }

        return $query_args;
    }

    /**
     * Display tags on group directory items
     *
     * @since 1.0.0
     */
    public function display_group_tags() {
        $group_id = bp_get_group_id();
        if ( ! $group_id ) {
            return;
        }

        $terms = wp_get_object_terms( $group_id, 'group_interest', array( 'fields' => 'names' ) );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return;
        }

        $settings = get_option( 'bpgf_settings', array() );
        $display_style = isset( $settings['tag_display_style'] ) ? $settings['tag_display_style'] : 'chips';

        echo '<div class="bpgf-group-tags">';

        if ( 'chips' === $display_style ) {
            foreach ( $terms as $term ) {
                $term_obj = get_term_by( 'name', $term, 'group_interest' );
                if ( $term_obj ) {
                    $link = add_query_arg( 'interest', $term_obj->slug, bp_get_groups_directory_url() );
                    printf(
                        '<a href="%s" class="bpgf-tag-chip" data-tag="%s">%s</a>',
                        esc_url( $link ),
                        esc_attr( $term ),
                        esc_html( $term )
                    );
                }
            }
        } else {
            // Text style
            $tag_links = array();
            foreach ( $terms as $term ) {
                $term_obj = get_term_by( 'name', $term, 'group_interest' );
                if ( $term_obj ) {
                    $link = add_query_arg( 'interest', $term_obj->slug, bp_get_groups_directory_url() );
                    $tag_links[] = sprintf(
                        '<a href="%s" class="bpgf-tag-link">%s</a>',
                        esc_url( $link ),
                        esc_html( $term )
                    );
                }
            }

            if ( ! empty( $tag_links ) ) {
                echo '<span class="bpgf-tags-text">' . implode( ', ', $tag_links ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }

        echo '</div>';
    }

    /**
     * Handle AJAX directory filtering
     *
     * @since 1.0.0
     */
    public function ajax_filter_groups() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'bpgf_directory_nonce' ) ) {
            wp_send_json_error( __( 'Security check failed.', 'bp-group-finder' ) );
        }

        $interest = isset( $_POST['interest'] ) ? sanitize_text_field( wp_unslash( $_POST['interest'] ) ) : '';

        // Build query args
        $query_args = array(
            'type'            => 'active',
            'per_page'        => 20,
            'page'            => 1,
        );

        if ( ! empty( $interest ) ) {
            $term = get_term_by( 'slug', $interest, 'group_interest' );
            if ( $term ) {
                $query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'group_interest',
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ),
                );
            }
        }

        // Get groups
        if ( bp_has_groups( $query_args ) ) {
            ob_start();

            while ( bp_groups() ) {
                bp_the_group();
                bp_get_template_part( 'groups/single/group' );
            }

            $html = ob_get_clean();

            wp_send_json_success( array(
                'html' => $html,
                'found' => bp_get_groups_found_count(),
            ) );
        } else {
            wp_send_json_error( __( 'No groups found.', 'bp-group-finder' ) );
        }
    }

    /**
     * Get popular tags HTML
     *
     * @since 1.0.0
     * @return string HTML for popular tags.
     */
    private function get_popular_tags_html() {
        $popular_tags = get_terms( array(
            'taxonomy'   => 'group_interest',
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => 5,
            'hide_empty' => true,
        ) );

        if ( empty( $popular_tags ) ) {
            return '';
        }

        $html = '';
        foreach ( $popular_tags as $tag ) {
            $link = add_query_arg( 'interest', $tag->slug, bp_get_groups_directory_url() );
            $html .= sprintf(
                '<a href="%s" class="bpgf-tag-chip" data-tag="%s">%s</a>',
                esc_url( $link ),
                esc_attr( $tag->name ),
                esc_html( $tag->name )
            );
        }

        return $html;
    }

    /**
     * Enqueue public assets
     *
     * @since 1.0.0
     */
    public function enqueue_assets() {
        if ( ! bp_is_groups_directory() ) {
            return;
        }

        wp_enqueue_script(
            'bpgf-directory-js',
            BPGF_PLUGIN_URL . 'assets/js/public/bpgf-directory.js',
            array( 'jquery' ),
            BPGF_VERSION,
            true
        );

        wp_enqueue_style(
            'bpgf-directory-css',
            BPGF_PLUGIN_URL . 'assets/css/public/bpgf-public.css',
            array(),
            BPGF_VERSION
        );

        wp_localize_script( 'bpgf-directory-js', 'bpgfDirectory', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'bpgf_directory_nonce' ),
            'strings' => array(
                'searching' => __( 'Searching...', 'bp-group-finder' ),
                'noResults' => __( 'No groups found.', 'bp-group-finder' ),
            ),
        ) );
    }
}