<?php
/**
 * Register and manage group_interest taxonomy
 *
 * @package BP_Group_Finder
 * @subpackage Taxonomies
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BPGF_Taxonomy class
 *
 * Handles the registration and management of the group_interest taxonomy
 */
class BPGF_Taxonomy {

    /**
     * Taxonomy name
     *
     * @var string
     */
    const TAXONOMY_NAME = 'group_interest';

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the taxonomy
     *
     * @since 1.0.0
     */
    public function init() {
        add_action( 'bp_init', array( $this, 'register_taxonomy' ) );
        add_action( 'bp_init', array( $this, 'associate_with_groups' ), 15 );

        // Validation hooks
        add_action( 'pre_insert_term', array( $this, 'validate_term_before_insert' ), 10, 2 );
        add_filter( 'wp_insert_term_data', array( $this, 'sanitize_term_data' ), 10, 4 );

        // Admin hooks
        if ( is_admin() ) {
            add_filter( 'manage_edit-' . self::TAXONOMY_NAME . '_columns', array( $this, 'add_group_count_column' ) );
            add_action( 'manage_' . self::TAXONOMY_NAME . '_custom_column', array( $this, 'populate_group_count_column' ), 10, 3 );
        }
    }

    /**
     * Register the group_interest taxonomy
     *
     * @since 1.0.0
     */
    public function register_taxonomy() {
        $labels = array(
            'name'                       => _x( 'Group Interests', 'taxonomy general name', 'bp-group-finder' ),
            'singular_name'              => _x( 'Group Interest', 'taxonomy singular name', 'bp-group-finder' ),
            'search_items'               => __( 'Search Interests', 'bp-group-finder' ),
            'popular_items'              => __( 'Popular Interests', 'bp-group-finder' ),
            'all_items'                  => __( 'All Interests', 'bp-group-finder' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Interest', 'bp-group-finder' ),
            'update_item'                => __( 'Update Interest', 'bp-group-finder' ),
            'add_new_item'               => __( 'Add New Interest', 'bp-group-finder' ),
            'new_item_name'              => __( 'New Interest Name', 'bp-group-finder' ),
            'separate_items_with_commas' => __( 'Separate interests with commas', 'bp-group-finder' ),
            'add_or_remove_items'        => __( 'Add or remove interests', 'bp-group-finder' ),
            'choose_from_most_used'      => __( 'Choose from the most used interests', 'bp-group-finder' ),
            'not_found'                  => __( 'No interests found.', 'bp-group-finder' ),
            'menu_name'                  => __( 'Group Interests', 'bp-group-finder' ),
        );

        $args = array(
            'hierarchical'          => false,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'show_in_nav_menus'     => false,
            'show_tagcloud'         => true,
            'show_in_rest'          => true,
            'rest_base'             => 'group-interests',
            'public'                => true,
            'publicly_queryable'    => true,
            'query_var'             => 'group-interest',
            'rewrite'               => array(
                'slug'         => 'group-interest',
                'with_front'   => false,
            ),
            'capabilities'          => array(
                'manage_terms' => 'manage_categories',
                'edit_terms'   => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'read', // Allow all logged-in users to assign terms
            ),
            'update_count_callback' => array( $this, 'update_term_count' ),
        );

        register_taxonomy( self::TAXONOMY_NAME, array( 'bp_group' ), $args );
    }

    /**
     * Associate taxonomy with BuddyPress groups
     *
     * @since 1.0.0
     */
    public function associate_with_groups() {
        register_taxonomy_for_object_type( self::TAXONOMY_NAME, 'bp_group' );
    }

    /**
     * Validate term before insertion
     *
     * @since 1.0.0
     * @param string $term     Term name.
     * @param string $taxonomy Taxonomy name.
     * @return string|WP_Error Validated term or error.
     */
    public function validate_term_before_insert( $term, $taxonomy ) {
        if ( $taxonomy !== self::TAXONOMY_NAME ) {
            return $term;
        }

        // Check minimum length
        $min_length = get_option( 'bpgf_min_tag_length', 2 );
        if ( strlen( $term ) < $min_length ) {
            return new WP_Error(
                'term_too_short',
                sprintf(
                    /* translators: %d: minimum length */
                    __( 'Interest name must be at least %d characters long.', 'bp-group-finder' ),
                    $min_length
                )
            );
        }

        // Check maximum length
        $max_length = get_option( 'bpgf_max_tag_length', 50 );
        if ( strlen( $term ) > $max_length ) {
            return new WP_Error(
                'term_too_long',
                sprintf(
                    /* translators: %d: maximum length */
                    __( 'Interest name cannot exceed %d characters.', 'bp-group-finder' ),
                    $max_length
                )
            );
        }

        // Check for valid characters (alphanumeric, spaces, hyphens)
        if ( ! preg_match( '/^[a-zA-Z0-9\s\-]+$/', $term ) ) {
            return new WP_Error(
                'invalid_characters',
                __( 'Interest names can only contain letters, numbers, spaces, and hyphens.', 'bp-group-finder' )
            );
        }

        return $term;
    }

    /**
     * Sanitize term data before insertion
     *
     * @since 1.0.0
     * @param array  $data     Term data.
     * @param string $taxonomy Taxonomy name.
     * @param array  $args     Arguments.
     * @return array Sanitized term data.
     */
    public function sanitize_term_data( $data, $taxonomy, $args ) {
        if ( $taxonomy !== self::TAXONOMY_NAME ) {
            return $data;
        }

        // Sanitize term name
        $data['name'] = sanitize_text_field( $data['name'] );

        // Sanitize slug
        if ( empty( $data['slug'] ) ) {
            $data['slug'] = sanitize_title( $data['name'] );
        } else {
            $data['slug'] = sanitize_title( $data['slug'] );
        }

        return $data;
    }

    /**
     * Add group count column to taxonomy admin
     *
     * @since 1.0.0
     * @param array $columns Columns array.
     * @return array Modified columns.
     */
    public function add_group_count_column( $columns ) {
        $columns['group_count'] = __( 'Groups', 'bp-group-finder' );
        return $columns;
    }

    /**
     * Populate group count column
     *
     * @since 1.0.0
     * @param string $content    Column content.
     * @param string $column_name Column name.
     * @param int    $term_id    Term ID.
     */
    public function populate_group_count_column( $content, $column_name, $term_id ) {
        if ( $column_name !== 'group_count' ) {
            return;
        }

        $count = $this->get_term_group_count( $term_id );
        echo esc_html( number_format_i18n( $count ) );
    }

    /**
     * Get group count for a term
     *
     * @since 1.0.0
     * @param int $term_id Term ID.
     * @return int Group count.
     */
    private function get_term_group_count( $term_id ) {
        global $wpdb;

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*)
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
            WHERE tt.term_id = %d
            AND tt.taxonomy = %s
            AND p.post_type = 'bp_group'
            AND p.post_status = 'publish'",
            $term_id,
            self::TAXONOMY_NAME
        ) );

        return (int) $count;
    }

    /**
     * Update term count callback
     *
     * @since 1.0.0
     * @param array $terms    Terms to update.
     * @param object $taxonomy Taxonomy object.
     */
    public function update_term_count( $terms, $taxonomy ) {
        // Custom count update logic if needed
        _update_generic_term_count( $terms, $taxonomy );
    }

    /**
     * Sanitize and validate taxonomy terms
     *
     * @since 1.0.0
     * @param array $terms Array of terms.
     * @return array Sanitized terms.
     */
    public function sanitize_terms( $terms ) {
        if ( ! is_array( $terms ) ) {
            $terms = array_map( 'trim', explode( ',', $terms ) );
        }

        $sanitized_terms = array();
        $max_tags = get_option( 'bpgf_max_tags_per_group', 10 );

        foreach ( $terms as $term ) {
            $term = sanitize_text_field( $term );

            if ( ! empty( $term ) ) {
                $sanitized_terms[] = $term;
            }
        }

        // Limit to maximum tags
        return array_slice( $sanitized_terms, 0, $max_tags );
    }
}