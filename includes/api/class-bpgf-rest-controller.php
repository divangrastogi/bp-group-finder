<?php
/**
 * REST API controller for group interests
 *
 * @package BP_Group_Finder
 * @subpackage API
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BPGF_REST_Controller class
 *
 * Handles REST API endpoints for group interests
 */
class BPGF_REST_Controller extends WP_REST_Controller {

    /**
     * Namespace
     *
     * @var string
     */
    protected $namespace = 'bp-groups/v1';

    /**
     * Rest base
     *
     * @var string
     */
    protected $rest_base = 'interests';

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the REST API
     *
     * @since 1.0.0
     */
    public function init() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes
     *
     * @since 1.0.0
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_items' ),
            'permission_callback' => array( $this, 'get_items_permissions_check' ),
        ) );
    }

    /**
     * Get collection parameters
     *
     * @since 1.0.0
     * @return array Collection parameters.
     */
    public function get_collection_params() {
        return array(
            'page'     => array(
                'description' => __( 'Current page of the collection.', 'bp-group-finder' ),
                'type'        => 'integer',
                'default'     => 1,
                'minimum'     => 1,
            ),
            'per_page' => array(
                'description' => __( 'Maximum number of items to be returned in result set.', 'bp-group-finder' ),
                'type'        => 'integer',
                'default'     => 10,
                'minimum'     => 1,
                'maximum'     => 100,
            ),
            'search'   => array(
                'description' => __( 'Limit results to those matching a string.', 'bp-group-finder' ),
                'type'        => 'string',
            ),
            'orderby'  => array(
                'description' => __( 'Sort collection by object attribute.', 'bp-group-finder' ),
                'type'        => 'string',
                'default'     => 'name',
                'enum'        => array( 'name', 'slug', 'count', 'id' ),
            ),
            'order'    => array(
                'description' => __( 'Order sort attribute ascending or descending.', 'bp-group-finder' ),
                'type'        => 'string',
                'default'     => 'asc',
                'enum'        => array( 'asc', 'desc' ),
            ),
        );
    }

    /**
     * Get interests collection
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_items( $request ) {
        $args = array(
            'taxonomy'   => 'group_interest',
            'hide_empty' => false,
            'number'     => $request->get_param( 'per_page' ),
            'offset'     => ( $request->get_param( 'page' ) - 1 ) * $request->get_param( 'per_page' ),
            'search'     => $request->get_param( 'search' ),
            'orderby'    => $request->get_param( 'orderby' ),
            'order'      => $request->get_param( 'order' ),
        );

        $terms = get_terms( $args );

        if ( is_wp_error( $terms ) ) {
            return $terms;
        }

        $data = array();
        foreach ( $terms as $term ) {
            $term_data = $this->prepare_item_for_response( $term, $request );
            $data[] = $this->prepare_response_for_collection( $term_data );
        }

        $response = rest_ensure_response( $data );

        // Set pagination headers
        $total = wp_count_terms( 'group_interest', array( 'hide_empty' => false ) );
        $response->header( 'X-WP-Total', $total );
        $response->header( 'X-WP-TotalPages', ceil( $total / $request->get_param( 'per_page' ) ) );

        return $response;
    }

    /**
     * Get single interest
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_item( $request ) {
        $term = get_term( $request->get_param( 'id' ), 'group_interest' );

        if ( is_wp_error( $term ) || ! $term ) {
            return new WP_Error( 'rest_term_invalid', __( 'Term does not exist.', 'bp-group-finder' ), array( 'status' => 404 ) );
        }

        $response = $this->prepare_item_for_response( $term, $request );
        return rest_ensure_response( $response );
    }

    /**
     * Get trending interests
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_trending( $request ) {
        $limit = $request->get_param( 'limit' );

        $search = new BPGF_Search();
        $trending = $search->get_trending_tags( $limit );

        $data = array();
        foreach ( $trending as $item ) {
            $data[] = array(
                'id'            => $item['term']->term_id,
                'name'          => $item['term']->name,
                'slug'          => $item['term']->slug,
                'description'   => $item['term']->description,
                'group_count'   => $item['group_count'],
                'trending_score' => $item['score'],
                'link'          => add_query_arg( 'interest', $item['term']->slug, bp_get_groups_directory_url() ),
            );
        }

        return rest_ensure_response( $data );
    }

    /**
     * Search interests
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function search_interests( $request ) {
        $query = $request->get_param( 'query' );
        $limit = $request->get_param( 'limit' );

        $terms = get_terms( array(
            'taxonomy'   => 'group_interest',
            'name__like' => $query,
            'number'     => $limit,
            'hide_empty' => false,
        ) );

        if ( is_wp_error( $terms ) ) {
            return $terms;
        }

        $data = array();
        foreach ( $terms as $term ) {
            $data[] = array(
                'id'          => $term->term_id,
                'name'        => $term->name,
                'slug'        => $term->slug,
                'description' => $term->description,
                'group_count' => $term->count,
                'link'        => add_query_arg( 'interest', $term->slug, bp_get_groups_directory_url() ),
            );
        }

        return rest_ensure_response( $data );
    }

    /**
     * Prepare item for response
     *
     * @since 1.0.0
     * @param WP_Term         $term    Term object.
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function prepare_item_for_response( $term, $request ) {
        $data = array(
            'id'          => $term->term_id,
            'name'        => $term->name,
            'slug'        => $term->slug,
            'description' => $term->description,
            'group_count' => $term->count,
            'link'        => add_query_arg( 'interest', $term->slug, bp_get_groups_directory_url() ),
        );

        return rest_ensure_response( $data );
    }

    /**
     * Check if a given request has access to get items
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
        // Public endpoint
        return true;
    }

    /**
     * Check if a given request has access to get a specific item
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full details about the request.
     * @return bool|WP_Error True if the request has read access for the item, WP_Error object otherwise.
     */
    public function get_item_permissions_check( $request ) {
        // Public endpoint
        return true;
    }
}