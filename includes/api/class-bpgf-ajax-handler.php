<?php
/**
 * Handle AJAX requests for search and filtering
 *
 * @package BP_Group_Finder
 * @subpackage API
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BPGF_AJAX_Handler class
 *
 * Handles AJAX requests for the plugin
 */
class BPGF_AJAX_Handler {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize AJAX handlers
     *
     * @since 1.0.0
     */
    public function init() {
        // Admin AJAX
        add_action( 'wp_ajax_bpgf_autocomplete', array( $this, 'ajax_autocomplete_tags' ) );

        // Public AJAX
        add_action( 'wp_ajax_bpgf_search_groups', array( $this, 'ajax_search_groups' ) );
        add_action( 'wp_ajax_nopriv_bpgf_search_groups', array( $this, 'ajax_search_groups' ) );

        add_action( 'wp_ajax_bpgf_tag_stats', array( $this, 'ajax_tag_stats' ) );
        add_action( 'wp_ajax_nopriv_bpgf_tag_stats', array( $this, 'ajax_tag_stats' ) );
    }

    /**
     * AJAX handler for tag autocomplete (admin)
     *
     * @since 1.0.0
     */
    public function ajax_autocomplete_tags() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'bpgf_admin_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'bp-group-finder' ) ) );
        }

        $query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';

        if ( strlen( $query ) < 2 ) {
            wp_send_json_error( array( 'message' => __( 'Query too short.', 'bp-group-finder' ) ) );
        }

        $terms = get_terms( array(
            'taxonomy'   => 'group_interest',
            'name__like' => $query,
            'number'     => 10,
            'hide_empty' => false,
        ) );

        $results = array();
        foreach ( $terms as $term ) {
            $results[] = array(
                'id'          => $term->term_id,
                'name'        => $term->name,
                'slug'        => $term->slug,
                'count'       => $term->count,
            );
        }

        wp_send_json_success( array( 'results' => $results ) );
    }

    /**
     * AJAX handler for group search
     *
     * @since 1.0.0
     */
    public function ajax_search_groups() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'bpgf_directory_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'bp-group-finder' ) ) );
        }

        $interest = isset( $_POST['interest'] ) ? sanitize_text_field( wp_unslash( $_POST['interest'] ) ) : '';
        $page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;

        if ( empty( $interest ) ) {
            wp_send_json_error( array( 'message' => __( 'Interest is required.', 'bp-group-finder' ) ) );
        }

        // Find the term
        $term = get_term_by( 'name', $interest, 'group_interest' );
        if ( ! $term ) {
            $term = get_term_by( 'slug', $interest, 'group_interest' );
        }

        if ( ! $term ) {
            wp_send_json_success( array(
                'groups' => array(),
                'total'  => 0,
                'page'   => $page,
            ) );
        }

        // Query groups
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

        $groups_data = array();
        if ( bp_has_groups( $query_args ) ) {
            while ( bp_groups() ) {
                bp_the_group();
                $groups_data[] = array(
                    'id'            => bp_get_group_id(),
                    'name'          => bp_get_group_name(),
                    'description'   => bp_get_group_description_excerpt(),
                    'avatar'        => bp_get_group_avatar_thumb(),
                    'permalink'     => bp_get_group_permalink(),
                    'member_count'  => bp_get_group_total_members(),
                    'last_activity' => bp_get_group_last_active(),
                );
            }
        }

        wp_send_json_success( array(
            'groups' => $groups_data,
            'total'  => bp_get_groups_found_count(),
            'page'   => $page,
        ) );
    }

    /**
     * AJAX handler for tag statistics
     *
     * @since 1.0.0
     */
    public function ajax_tag_stats() {
        // Verify nonce
        if ( ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'bpgf_directory_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'bp-group-finder' ) ) );
        }

        $tag_id = isset( $_POST['tag_id'] ) ? absint( $_POST['tag_id'] ) : 0;

        if ( ! $tag_id ) {
            wp_send_json_error( array( 'message' => __( 'Tag ID is required.', 'bp-group-finder' ) ) );
        }

        $term = get_term( $tag_id, 'group_interest' );
        if ( ! $term || is_wp_error( $term ) ) {
            wp_send_json_error( array( 'message' => __( 'Tag not found.', 'bp-group-finder' ) ) );
        }

        // Get group count
        $group_count = $term->count;

        // Calculate trending score (simplified)
        $trending_score = $group_count; // In real implementation, use more complex algorithm

        wp_send_json_success( array(
            'tag_id'        => $tag_id,
            'name'          => $term->name,
            'group_count'   => $group_count,
            'trending_score' => $trending_score,
        ) );
    }
}