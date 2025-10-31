<?php
/**
 * Search functionality for group interests
 *
 * @package BP_Group_Finder
 * @subpackage Public
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BPGF_Search class
 *
 * Handles search functionality for group interest tags
 */
class BPGF_Search {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the search functionality
     *
     * @since 1.0.0
     */
    public function init() {
        // AJAX handlers
        add_action( 'wp_ajax_bpgf_autocomplete_tags', array( $this, 'ajax_autocomplete_tags' ) );
        add_action( 'wp_ajax_nopriv_bpgf_autocomplete_tags', array( $this, 'ajax_autocomplete_tags' ) );

        add_action( 'wp_ajax_bpgf_search_groups', array( $this, 'ajax_search_groups' ) );
        add_action( 'wp_ajax_nopriv_bpgf_search_groups', array( $this, 'ajax_search_groups' ) );
    }

    /**
     * AJAX handler for tag autocomplete
     *
     * @since 1.0.0
     */
    public function ajax_autocomplete_tags() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'bpgf_admin_nonce' ) &&
             ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'bpgf_frontend_nonce' ) ) {
            wp_send_json_error( __( 'Security check failed.', 'bp-group-finder' ) );
        }

        $term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';

        if ( strlen( $term ) < 2 ) {
            wp_send_json_error( __( 'Search term too short.', 'bp-group-finder' ) );
        }

        $tags = get_terms( array(
            'taxonomy'   => 'group_interest',
            'name__like' => $term,
            'number'     => 10,
            'orderby'    => 'count',
            'order'      => 'DESC',
            'hide_empty' => false,
        ) );

        $results = array();
        foreach ( $tags as $tag ) {
            $results[] = array(
                'id'           => $tag->term_id,
                'label'        => $tag->name,
                'value'        => $tag->name,
                'slug'         => $tag->slug,
                'group_count'  => $tag->count,
            );
        }

        wp_send_json_success( $results );
    }

    /**
     * AJAX handler for group search
     *
     * @since 1.0.0
     */
    public function ajax_search_groups() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'bpgf_directory_nonce' ) ) {
            wp_send_json_error( __( 'Security check failed.', 'bp-group-finder' ) );
        }

        $search_term = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

        if ( empty( $search_term ) ) {
            wp_send_json_error( __( 'Search term is required.', 'bp-group-finder' ) );
        }

        // Search for groups by tag
        $term = get_term_by( 'name', $search_term, 'group_interest' );
        if ( ! $term ) {
            $term = get_term_by( 'slug', $search_term, 'group_interest' );
        }

        if ( ! $term ) {
            wp_send_json_success( array(
                'groups' => array(),
                'total'  => 0,
                'page'   => $page,
            ) );
        }

        // Get groups with this tag
        $query_args = array(
            'type'      => 'active',
            'per_page'  => 20,
            'page'      => $page,
            'tax_query' => array(
                array(
                    'taxonomy' => 'group_interest',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                ),
            ),
        );

        $groups = array();
        if ( bp_has_groups( $query_args ) ) {
            while ( bp_groups() ) {
                bp_the_group();
                $groups[] = array(
                    'id'          => bp_get_group_id(),
                    'name'        => bp_get_group_name(),
                    'description' => bp_get_group_description_excerpt(),
                    'avatar'      => bp_get_group_avatar_thumb(),
                    'permalink'   => bp_get_group_permalink(),
                    'member_count' => bp_get_group_total_members(),
                    'tags'        => wp_get_object_terms( bp_get_group_id(), 'group_interest', array( 'fields' => 'names' ) ),
                );
            }
        }

        wp_send_json_success( array(
            'groups' => $groups,
            'total'  => bp_get_groups_found_count(),
            'page'   => $page,
        ) );
    }

    /**
     * Search groups by multiple tags
     *
     * @since 1.0.0
     * @param array $tags Array of tag names.
     * @param array $args Additional query arguments.
     * @return array Array of group IDs.
     */
    public function search_groups_by_tags( $tags, $args = array() ) {
        if ( empty( $tags ) || ! is_array( $tags ) ) {
            return array();
        }

        $term_ids = array();
        foreach ( $tags as $tag ) {
            $term = get_term_by( 'name', $tag, 'group_interest' );
            if ( $term ) {
                $term_ids[] = $term->term_id;
            }
        }

        if ( empty( $term_ids ) ) {
            return array();
        }

        $defaults = array(
            'type'      => 'active',
            'per_page'  => 20,
            'page'      => 1,
            'fields'    => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => 'group_interest',
                    'field'    => 'term_id',
                    'terms'    => $term_ids,
                    'operator' => 'AND', // All tags must be present
                ),
            ),
        );

        $query_args = wp_parse_args( $args, $defaults );

        $group_ids = array();
        if ( bp_has_groups( $query_args ) ) {
            while ( bp_groups() ) {
                bp_the_group();
                $group_ids[] = bp_get_group_id();
            }
        }

        return $group_ids;
    }

    /**
     * Get trending tags
     *
     * @since 1.0.0
     * @param int $limit Number of tags to return.
     * @return array Array of trending tags.
     */
    public function get_trending_tags( $limit = 10 ) {
        $settings = get_option( 'bpgf_settings', array() );
        $period_days = isset( $settings['trending_period_days'] ) ? $settings['trending_period_days'] : 30;
        $min_groups = isset( $settings['min_groups_for_trending'] ) ? $settings['min_groups_for_trending'] : 1;

        // Get tags with recent activity
        $tags = get_terms( array(
            'taxonomy'   => 'group_interest',
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => $limit * 2, // Get more to filter
            'hide_empty' => true,
        ) );

        $trending = array();
        foreach ( $tags as $tag ) {
            if ( $tag->count >= $min_groups ) {
                $trending[] = array(
                    'term'         => $tag,
                    'score'        => $this->calculate_trending_score( $tag, $period_days ),
                    'group_count'  => $tag->count,
                );
            }
        }

        // Sort by trending score
        usort( $trending, function( $a, $b ) {
            return $b['score'] <=> $a['score'];
        } );

        return array_slice( $trending, 0, $limit );
    }

    /**
     * Calculate trending score for a tag
     *
     * @since 1.0.0
     * @param object $tag        Term object.
     * @param int    $period_days Number of days to look back.
     * @return float Trending score.
     */
    private function calculate_trending_score( $tag, $period_days ) {
        // Simple algorithm: base score on group count
        // In a real implementation, this would consider:
        // - Recent group creation with this tag
        // - Recent activity in tagged groups
        // - Growth rate over time

        $base_score = $tag->count;

        // Add some randomness for demo purposes
        // In production, calculate based on actual metrics
        $random_factor = ( mt_rand( 0, 100 ) / 100 ) * 0.5; // 0-0.5

        return $base_score + $random_factor;
    }
}