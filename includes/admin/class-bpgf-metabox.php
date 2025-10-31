<?php
/**
 * Add metabox for group interest tags
 *
 * @package BP_Group_Finder
 * @subpackage Admin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BPGF_Metabox class
 *
 * Handles the metabox for adding interest tags to BuddyPress groups
 */
class BPGF_Metabox {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the metabox
     *
     * @since 1.0.0
     */
    public function init() {
        add_action( 'add_meta_boxes_bp_group', array( $this, 'add_metabox' ) );
        add_action( 'save_post_bp_group', array( $this, 'save_metabox' ), 10, 2 );

        // Frontend group creation
        add_action( 'groups_custom_group_fields_editable', array( $this, 'add_to_bp_form' ) );
        add_action( 'groups_group_details_edited', array( $this, 'save_bp_form' ) );
        add_action( 'groups_created_group', array( $this, 'save_bp_form' ) );

        // Enqueue scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
    }

    /**
     * Register metabox for group edit screen
     *
     * @since 1.0.0
     */
    public function add_metabox() {
        add_meta_box(
            'bpgf-interest-tags',
            __( 'Group Interests', 'bp-group-finder' ),
            array( $this, 'render_metabox' ),
            'bp_group',
            'side',
            'default'
        );
    }

    /**
     * Render metabox content
     *
     * @since 1.0.0
     * @param WP_Post $post The post object.
     */
    public function render_metabox( $post ) {
        wp_nonce_field( 'bpgf_metabox_nonce', 'bpgf_metabox_nonce' );

        $terms = wp_get_object_terms( $post->ID, 'group_interest', array( 'fields' => 'names' ) );
        $current_tags = is_wp_error( $terms ) ? array() : $terms;

        // Get popular tags for suggestions
        $popular_tags = $this->get_popular_tags( 10 );

        ?>
        <div class="bpgf-metabox">
            <p>
                <label for="bpgf-interest-tags-input">
                    <?php esc_html_e( 'Interest Tags:', 'bp-group-finder' ); ?>
                </label>
            </p>

            <div class="bpgf-tags-input-wrapper">
                <input
                    type="text"
                    id="bpgf-interest-tags-input"
                    class="bpgf-tags-input"
                    placeholder="<?php esc_attr_e( 'Add interests (comma separated)...', 'bp-group-finder' ); ?>"
                    autocomplete="off"
                />
                <input
                    type="hidden"
                    name="bpgf_interest_tags"
                    id="bpgf-interest-tags-hidden"
                    value="<?php echo esc_attr( implode( ',', $current_tags ) ); ?>"
                />
            </div>

            <div class="bpgf-current-tags" id="bpgf-current-tags">
                <?php foreach ( $current_tags as $tag ) : ?>
                    <span class="bpgf-tag-chip" data-tag="<?php echo esc_attr( $tag ); ?>">
                        <?php echo esc_html( $tag ); ?>
                        <button type="button" class="bpgf-remove-tag" aria-label="<?php esc_attr_e( 'Remove tag', 'bp-group-finder' ); ?>">
                            &times;
                        </button>
                    </span>
                <?php endforeach; ?>
            </div>

            <?php if ( ! empty( $popular_tags ) ) : ?>
                <div class="bpgf-popular-tags">
                    <p><strong><?php esc_html_e( 'Popular tags:', 'bp-group-finder' ); ?></strong></p>
                    <div class="bpgf-popular-tags-list">
                        <?php foreach ( $popular_tags as $tag ) : ?>
                            <button type="button" class="bpgf-add-popular-tag" data-tag="<?php echo esc_attr( $tag->name ); ?>">
                                <?php echo esc_html( $tag->name ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <p class="description">
                <?php
                printf(
                    /* translators: %d: maximum number of tags */
                    esc_html__( 'Maximum %d tags allowed per group.', 'bp-group-finder' ),
                    get_option( 'bpgf_max_tags_per_group', 10 )
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Save metabox data
     *
     * @since 1.0.0
     * @param int     $post_id The post ID.
     * @param WP_Post $post    The post object.
     */
    public function save_metabox( $post_id, $post ) {
        // Verify nonce
        if ( ! isset( $_POST['bpgf_metabox_nonce'] ) ||
             ! wp_verify_nonce( sanitize_key( $_POST['bpgf_metabox_nonce'] ), 'bpgf_metabox_nonce' ) ) {
            return;
        }

        // Check if user has permission
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check if this is an autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Sanitize and save tags
        if ( isset( $_POST['bpgf_interest_tags'] ) ) {
            $tags_string = sanitize_text_field( wp_unslash( $_POST['bpgf_interest_tags'] ) );
            $this->save_group_tags( $post_id, $tags_string );
        }
    }

    /**
     * Add interest field to BuddyPress group creation/edit form
     *
     * @since 1.0.0
     */
    public function add_to_bp_form() {
        $group_id = bp_get_current_group_id();
        $terms = array();

        if ( $group_id ) {
            $terms = wp_get_object_terms( $group_id, 'group_interest', array( 'fields' => 'names' ) );
            $terms = is_wp_error( $terms ) ? array() : $terms;
        }

        ?>
        <div class="editfield field_group_interests">
            <fieldset>
                <legend><?php esc_html_e( 'Group Interests', 'bp-group-finder' ); ?></legend>

                <div class="bpgf-bp-form-wrapper">
                    <input
                        type="text"
                        name="group_interests"
                        id="group-interests"
                        value="<?php echo esc_attr( implode( ', ', $terms ) ); ?>"
                        placeholder="<?php esc_attr_e( 'e.g., photography, coding, music', 'bp-group-finder' ); ?>"
                        autocomplete="off"
                    />

                    <p class="description">
                        <?php
                        printf(
                            /* translators: %d: maximum number of tags */
                            esc_html__( 'Add interest tags to help others find your group. Maximum %d tags allowed.', 'bp-group-finder' ),
                            get_option( 'bpgf_max_tags_per_group', 10 )
                        );
                        ?>
                    </p>
                </div>

                <?php wp_nonce_field( 'bpgf_bp_form_nonce', 'bpgf_bp_form_nonce' ); ?>
            </fieldset>
        </div>
        <?php
    }

    /**
     * Save BuddyPress form data
     *
     * @since 1.0.0
     * @param int $group_id The group ID.
     */
    public function save_bp_form( $group_id ) {
        // Verify nonce
        if ( ! isset( $_POST['bpgf_bp_form_nonce'] ) ||
             ! wp_verify_nonce( sanitize_key( $_POST['bpgf_bp_form_nonce'] ), 'bpgf_bp_form_nonce' ) ) {
            return;
        }

        // Check permissions
        if ( ! is_user_logged_in() ) {
            return;
        }

        // Sanitize and save tags
        if ( isset( $_POST['group_interests'] ) ) {
            $tags_string = sanitize_text_field( wp_unslash( $_POST['group_interests'] ) );
            $this->save_group_tags( $group_id, $tags_string );
        }
    }

    /**
     * Save group tags
     *
     * @since 1.0.0
     * @param int    $group_id    The group ID.
     * @param string $tags_string Comma-separated tags string.
     */
    private function save_group_tags( $group_id, $tags_string ) {
        // Parse tags
        $tags = array_map( 'trim', explode( ',', $tags_string ) );
        $tags = array_filter( $tags, 'strlen' );

        // Sanitize and validate
        $taxonomy = new BPGF_Taxonomy();
        $tags = $taxonomy->sanitize_terms( $tags );

        // Set object terms
        wp_set_object_terms( $group_id, $tags, 'group_interest' );

        // Clear related caches
        $this->clear_group_cache( $group_id );
    }

    /**
     * Get popular tags
     *
     * @since 1.0.0
     * @param int $limit Number of tags to return.
     * @return array Array of term objects.
     */
    private function get_popular_tags( $limit = 10 ) {
        return get_terms( array(
            'taxonomy'   => 'group_interest',
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => $limit,
            'hide_empty' => true,
        ) );
    }

    /**
     * Clear group-related caches
     *
     * @since 1.0.0
     * @param int $group_id The group ID.
     */
    private function clear_group_cache( $group_id ) {
        wp_cache_delete( 'bpgf_group_tags_' . $group_id, 'bp_group_finder' );
        wp_cache_delete( 'bpgf_popular_tags', 'bp_group_finder' );
    }

    /**
     * Enqueue admin assets
     *
     * @since 1.0.0
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }

        global $post;
        if ( 'bp_group' !== $post->post_type ) {
            return;
        }

        wp_enqueue_script(
            'bpgf-admin-metabox',
            BPGF_PLUGIN_URL . 'assets/js/admin/bpgf-admin.js',
            array( 'jquery', 'jquery-ui-autocomplete' ),
            BPGF_VERSION,
            true
        );

        wp_enqueue_style(
            'bpgf-admin-styles',
            BPGF_PLUGIN_URL . 'assets/css/admin/bpgf-admin.css',
            array(),
            BPGF_VERSION
        );

        wp_localize_script( 'bpgf-admin-metabox', 'bpgfAdmin', array(
            'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( 'bpgf_admin_nonce' ),
            'maxTags'      => get_option( 'bpgf_max_tags_per_group', 10 ),
            'strings'      => array(
                'maxTagsReached' => __( 'Maximum number of tags reached.', 'bp-group-finder' ),
                'removeTag'      => __( 'Remove tag', 'bp-group-finder' ),
                'addTag'         => __( 'Add tag', 'bp-group-finder' ),
            ),
        ) );
    }

    /**
     * Enqueue frontend assets
     *
     * @since 1.0.0
     */
    public function enqueue_frontend_assets() {
        if ( ! bp_is_group() && ! bp_is_group_create() ) {
            return;
        }

        wp_enqueue_script(
            'bpgf-frontend-tags',
            BPGF_PLUGIN_URL . 'assets/js/public/bpgf-directory.js',
            array( 'jquery' ),
            BPGF_VERSION,
            true
        );

        wp_enqueue_style(
            'bpgf-frontend-styles',
            BPGF_PLUGIN_URL . 'assets/css/public/bpgf-public.css',
            array(),
            BPGF_VERSION
        );

        wp_localize_script( 'bpgf-frontend-tags', 'bpgfFrontend', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'bpgf_frontend_nonce' ),
            'maxTags' => get_option( 'bpgf_max_tags_per_group', 10 ),
        ) );
    }
}