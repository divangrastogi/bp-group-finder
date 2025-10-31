<?php
/**
 * Plugin settings page
 *
 * @package BP_Group_Finder
 * @subpackage Admin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BPGF_Settings class
 *
 * Handles the plugin settings page and options
 */
class BPGF_Settings {

    /**
     * Option group name
     */
    const OPTION_GROUP = 'bpgf_settings';

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the settings
     *
     * @since 1.0.0
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_assets' ) );

        // Add settings link to plugin action links
        add_filter( 'plugin_action_links_' . BPGF_PLUGIN_BASENAME, array( $this, 'add_settings_link' ) );
    }

    /**
     * Add settings page to BuddyPress menu
     *
     * @since 1.0.0
     */
    public function add_settings_page() {
        add_submenu_page(
            'bp-general-settings',
            __( 'Group Interests', 'bp-group-finder' ),
            __( 'Group Interests', 'bp-group-finder' ),
            'manage_options',
            'bpgf-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings sections and fields
     *
     * @since 1.0.0
     */
    public function register_settings() {
        // Register setting
        register_setting(
            self::OPTION_GROUP,
            'bpgf_settings',
            array( $this, 'sanitize_settings' )
        );

        // General Settings Section
        add_settings_section(
            'bpgf_general',
            __( 'General Settings', 'bp-group-finder' ),
            array( $this, 'render_general_section' ),
            'bpgf-settings'
        );

        add_settings_field(
            'bpgf_enable_plugin',
            __( 'Enable Plugin', 'bp-group-finder' ),
            array( $this, 'render_enable_plugin_field' ),
            'bpgf-settings',
            'bpgf_general'
        );

        add_settings_field(
            'bpgf_max_tags_per_group',
            __( 'Maximum Tags per Group', 'bp-group-finder' ),
            array( $this, 'render_max_tags_field' ),
            'bpgf-settings',
            'bpgf_general'
        );

        add_settings_field(
            'bpgf_min_tag_length',
            __( 'Minimum Tag Length', 'bp-group-finder' ),
            array( $this, 'render_min_tag_length_field' ),
            'bpgf-settings',
            'bpgf_general'
        );

        add_settings_field(
            'bpgf_max_tag_length',
            __( 'Maximum Tag Length', 'bp-group-finder' ),
            array( $this, 'render_max_tag_length_field' ),
            'bpgf-settings',
            'bpgf_general'
        );

        // Display Settings Section
        add_settings_section(
            'bpgf_display',
            __( 'Display Settings', 'bp-group-finder' ),
            array( $this, 'render_display_section' ),
            'bpgf-settings'
        );

        add_settings_field(
            'bpgf_show_tags_in_directory',
            __( 'Show Tags in Directory', 'bp-group-finder' ),
            array( $this, 'render_show_tags_directory_field' ),
            'bpgf-settings',
            'bpgf_display'
        );

        add_settings_field(
            'bpgf_tag_display_style',
            __( 'Tag Display Style', 'bp-group-finder' ),
            array( $this, 'render_tag_display_style_field' ),
            'bpgf-settings',
            'bpgf_display'
        );

        add_settings_field(
            'bpgf_enable_autocomplete',
            __( 'Enable Autocomplete', 'bp-group-finder' ),
            array( $this, 'render_enable_autocomplete_field' ),
            'bpgf-settings',
            'bpgf_display'
        );

        // Trending Settings Section
        add_settings_section(
            'bpgf_trending',
            __( 'Trending Settings', 'bp-group-finder' ),
            array( $this, 'render_trending_section' ),
            'bpgf-settings'
        );

        add_settings_field(
            'bpgf_trending_period_days',
            __( 'Trending Period (Days)', 'bp-group-finder' ),
            array( $this, 'render_trending_period_field' ),
            'bpgf-settings',
            'bpgf_trending'
        );

        add_settings_field(
            'bpgf_min_groups_for_trending',
            __( 'Minimum Groups for Trending', 'bp-group-finder' ),
            array( $this, 'render_min_groups_trending_field' ),
            'bpgf-settings',
            'bpgf_trending'
        );

        add_settings_field(
            'bpgf_cache_duration',
            __( 'Cache Duration (Seconds)', 'bp-group-finder' ),
            array( $this, 'render_cache_duration_field' ),
            'bpgf-settings',
            'bpgf_trending'
        );

        // Advanced Settings Section
        add_settings_section(
            'bpgf_advanced',
            __( 'Advanced Settings', 'bp-group-finder' ),
            array( $this, 'render_advanced_section' ),
            'bpgf-settings'
        );

        add_settings_field(
            'bpgf_enable_rest_api',
            __( 'Enable REST API', 'bp-group-finder' ),
            array( $this, 'render_enable_rest_api_field' ),
            'bpgf-settings',
            'bpgf_advanced'
        );

        add_settings_field(
            'bpgf_enable_ajax_search',
            __( 'Enable AJAX Search', 'bp-group-finder' ),
            array( $this, 'render_enable_ajax_search_field' ),
            'bpgf-settings',
            'bpgf_advanced'
        );

        add_settings_field(
            'bpgf_debug_mode',
            __( 'Debug Mode', 'bp-group-finder' ),
            array( $this, 'render_debug_mode_field' ),
            'bpgf-settings',
            'bpgf_advanced'
        );
    }

    /**
     * Render settings page
     *
     * @since 1.0.0
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bp-group-finder' ) );
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'BP Group Finder Settings', 'bp-group-finder' ); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( 'bpgf-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render general section description
     *
     * @since 1.0.0
     */
    public function render_general_section() {
        echo '<p>' . esc_html__( 'Configure the basic settings for the Group Interests plugin.', 'bp-group-finder' ) . '</p>';
    }

    /**
     * Render display section description
     *
     * @since 1.0.0
     */
    public function render_display_section() {
        echo '<p>' . esc_html__( 'Control how interest tags are displayed throughout the site.', 'bp-group-finder' ) . '</p>';
    }

    /**
     * Render trending section description
     *
     * @since 1.0.0
     */
    public function render_trending_section() {
        echo '<p>' . esc_html__( 'Configure how trending tags are calculated and cached.', 'bp-group-finder' ) . '</p>';
    }

    /**
     * Render advanced section description
     *
     * @since 1.0.0
     */
    public function render_advanced_section() {
        echo '<p>' . esc_html__( 'Advanced settings for developers and power users.', 'bp-group-finder' ) . '</p>';
    }

    /**
     * Render enable plugin field
     *
     * @since 1.0.0
     */
    public function render_enable_plugin_field() {
        $options = get_option( 'bpgf_settings', array() );
        $enabled = isset( $options['enable_plugin'] ) ? $options['enable_plugin'] : true;

        ?>
        <label for="bpgf_enable_plugin">
            <input type="checkbox" id="bpgf_enable_plugin" name="bpgf_settings[enable_plugin]" value="1" <?php checked( $enabled, true ); ?> />
            <?php esc_html_e( 'Enable the Group Interests functionality', 'bp-group-finder' ); ?>
        </label>
        <?php
    }

    /**
     * Render max tags field
     *
     * @since 1.0.0
     */
    public function render_max_tags_field() {
        $options = get_option( 'bpgf_settings', array() );
        $max_tags = isset( $options['max_tags_per_group'] ) ? $options['max_tags_per_group'] : 10;

        ?>
        <input type="number" id="bpgf_max_tags_per_group" name="bpgf_settings[max_tags_per_group]" value="<?php echo esc_attr( $max_tags ); ?>" min="1" max="50" />
        <p class="description"><?php esc_html_e( 'Maximum number of interest tags allowed per group.', 'bp-group-finder' ); ?></p>
        <?php
    }

    /**
     * Render min tag length field
     *
     * @since 1.0.0
     */
    public function render_min_tag_length_field() {
        $options = get_option( 'bpgf_settings', array() );
        $min_length = isset( $options['min_tag_length'] ) ? $options['min_tag_length'] : 2;

        ?>
        <input type="number" id="bpgf_min_tag_length" name="bpgf_settings[min_tag_length]" value="<?php echo esc_attr( $min_length ); ?>" min="1" max="10" />
        <p class="description"><?php esc_html_e( 'Minimum number of characters required for a tag.', 'bp-group-finder' ); ?></p>
        <?php
    }

    /**
     * Render max tag length field
     *
     * @since 1.0.0
     */
    public function render_max_tag_length_field() {
        $options = get_option( 'bpgf_settings', array() );
        $max_length = isset( $options['max_tag_length'] ) ? $options['max_tag_length'] : 50;

        ?>
        <input type="number" id="bpgf_max_tag_length" name="bpgf_settings[max_tag_length]" value="<?php echo esc_attr( $max_length ); ?>" min="10" max="100" />
        <p class="description"><?php esc_html_e( 'Maximum number of characters allowed for a tag.', 'bp-group-finder' ); ?></p>
        <?php
    }

    /**
     * Render show tags in directory field
     *
     * @since 1.0.0
     */
    public function render_show_tags_directory_field() {
        $options = get_option( 'bpgf_settings', array() );
        $show = isset( $options['show_tags_in_directory'] ) ? $options['show_tags_in_directory'] : true;

        ?>
        <label for="bpgf_show_tags_in_directory">
            <input type="checkbox" id="bpgf_show_tags_in_directory" name="bpgf_settings[show_tags_in_directory]" value="1" <?php checked( $show, true ); ?> />
            <?php esc_html_e( 'Display interest tags in the groups directory', 'bp-group-finder' ); ?>
        </label>
        <?php
    }

    /**
     * Render tag display style field
     *
     * @since 1.0.0
     */
    public function render_tag_display_style_field() {
        $options = get_option( 'bpgf_settings', array() );
        $style = isset( $options['tag_display_style'] ) ? $options['tag_display_style'] : 'chips';

        ?>
        <select id="bpgf_tag_display_style" name="bpgf_settings[tag_display_style]">
            <option value="chips" <?php selected( $style, 'chips' ); ?>><?php esc_html_e( 'Chips', 'bp-group-finder' ); ?></option>
            <option value="text" <?php selected( $style, 'text' ); ?>><?php esc_html_e( 'Text', 'bp-group-finder' ); ?></option>
        </select>
        <p class="description"><?php esc_html_e( 'How to display tags in the groups directory.', 'bp-group-finder' ); ?></p>
        <?php
    }

    /**
     * Render enable autocomplete field
     *
     * @since 1.0.0
     */
    public function render_enable_autocomplete_field() {
        $options = get_option( 'bpgf_settings', array() );
        $enabled = isset( $options['enable_autocomplete'] ) ? $options['enable_autocomplete'] : true;

        ?>
        <label for="bpgf_enable_autocomplete">
            <input type="checkbox" id="bpgf_enable_autocomplete" name="bpgf_settings[enable_autocomplete]" value="1" <?php checked( $enabled, true ); ?> />
            <?php esc_html_e( 'Enable autocomplete suggestions when typing tags', 'bp-group-finder' ); ?>
        </label>
        <?php
    }

    /**
     * Render trending period field
     *
     * @since 1.0.0
     */
    public function render_trending_period_field() {
        $options = get_option( 'bpgf_settings', array() );
        $period = isset( $options['trending_period_days'] ) ? $options['trending_period_days'] : 30;

        ?>
        <input type="number" id="bpgf_trending_period_days" name="bpgf_settings[trending_period_days]" value="<?php echo esc_attr( $period ); ?>" min="1" max="365" />
        <p class="description"><?php esc_html_e( 'Number of days to look back when calculating trending tags.', 'bp-group-finder' ); ?></p>
        <?php
    }

    /**
     * Render min groups for trending field
     *
     * @since 1.0.0
     */
    public function render_min_groups_trending_field() {
        $options = get_option( 'bpgf_settings', array() );
        $min_groups = isset( $options['min_groups_for_trending'] ) ? $options['min_groups_for_trending'] : 1;

        ?>
        <input type="number" id="bpgf_min_groups_for_trending" name="bpgf_settings[min_groups_for_trending]" value="<?php echo esc_attr( $min_groups ); ?>" min="1" max="100" />
        <p class="description"><?php esc_html_e( 'Minimum number of groups required for a tag to be considered trending.', 'bp-group-finder' ); ?></p>
        <?php
    }

    /**
     * Render cache duration field
     *
     * @since 1.0.0
     */
    public function render_cache_duration_field() {
        $options = get_option( 'bpgf_settings', array() );
        $duration = isset( $options['cache_duration'] ) ? $options['cache_duration'] : 3600;

        ?>
        <input type="number" id="bpgf_cache_duration" name="bpgf_settings[cache_duration]" value="<?php echo esc_attr( $duration ); ?>" min="300" max="86400" />
        <p class="description"><?php esc_html_e( 'How long to cache trending data (in seconds).', 'bp-group-finder' ); ?></p>
        <?php
    }

    /**
     * Render enable REST API field
     *
     * @since 1.0.0
     */
    public function render_enable_rest_api_field() {
        $options = get_option( 'bpgf_settings', array() );
        $enabled = isset( $options['enable_rest_api'] ) ? $options['enable_rest_api'] : true;

        ?>
        <label for="bpgf_enable_rest_api">
            <input type="checkbox" id="bpgf_enable_rest_api" name="bpgf_settings[enable_rest_api]" value="1" <?php checked( $enabled, true ); ?> />
            <?php esc_html_e( 'Enable REST API endpoints for external integrations', 'bp-group-finder' ); ?>
        </label>
        <?php
    }

    /**
     * Render enable AJAX search field
     *
     * @since 1.0.0
     */
    public function render_enable_ajax_search_field() {
        $options = get_option( 'bpgf_settings', array() );
        $enabled = isset( $options['enable_ajax_search'] ) ? $options['enable_ajax_search'] : true;

        ?>
        <label for="bpgf_enable_ajax_search">
            <input type="checkbox" id="bpgf_enable_ajax_search" name="bpgf_settings[enable_ajax_search]" value="1" <?php checked( $enabled, true ); ?> />
            <?php esc_html_e( 'Enable AJAX-powered search and filtering', 'bp-group-finder' ); ?>
        </label>
        <?php
    }

    /**
     * Render debug mode field
     *
     * @since 1.0.0
     */
    public function render_debug_mode_field() {
        $options = get_option( 'bpgf_settings', array() );
        $debug = isset( $options['debug_mode'] ) ? $options['debug_mode'] : false;

        ?>
        <label for="bpgf_debug_mode">
            <input type="checkbox" id="bpgf_debug_mode" name="bpgf_settings[debug_mode]" value="1" <?php checked( $debug, true ); ?> />
            <?php esc_html_e( 'Enable debug mode (logs additional information)', 'bp-group-finder' ); ?>
        </label>
        <?php
    }

    /**
     * Sanitize settings
     *
     * @since 1.0.0
     * @param array $input The input settings.
     * @return array Sanitized settings.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        // Enable plugin
        $sanitized['enable_plugin'] = isset( $input['enable_plugin'] ) ? (bool) $input['enable_plugin'] : false;

        // Max tags per group
        $sanitized['max_tags_per_group'] = isset( $input['max_tags_per_group'] )
            ? absint( $input['max_tags_per_group'] )
            : 10;
        $sanitized['max_tags_per_group'] = max( 1, min( 50, $sanitized['max_tags_per_group'] ) );

        // Min tag length
        $sanitized['min_tag_length'] = isset( $input['min_tag_length'] )
            ? absint( $input['min_tag_length'] )
            : 2;
        $sanitized['min_tag_length'] = max( 1, min( 10, $sanitized['min_tag_length'] ) );

        // Max tag length
        $sanitized['max_tag_length'] = isset( $input['max_tag_length'] )
            ? absint( $input['max_tag_length'] )
            : 50;
        $sanitized['max_tag_length'] = max( 10, min( 100, $sanitized['max_tag_length'] ) );

        // Display settings
        $sanitized['show_tags_in_directory'] = isset( $input['show_tags_in_directory'] ) ? (bool) $input['show_tags_in_directory'] : false;
        $sanitized['tag_display_style'] = isset( $input['tag_display_style'] ) && in_array( $input['tag_display_style'], array( 'chips', 'text' ), true )
            ? $input['tag_display_style']
            : 'chips';
        $sanitized['enable_autocomplete'] = isset( $input['enable_autocomplete'] ) ? (bool) $input['enable_autocomplete'] : false;

        // Trending settings
        $sanitized['trending_period_days'] = isset( $input['trending_period_days'] )
            ? absint( $input['trending_period_days'] )
            : 30;
        $sanitized['trending_period_days'] = max( 1, min( 365, $sanitized['trending_period_days'] ) );

        $sanitized['min_groups_for_trending'] = isset( $input['min_groups_for_trending'] )
            ? absint( $input['min_groups_for_trending'] )
            : 1;
        $sanitized['min_groups_for_trending'] = max( 1, min( 100, $sanitized['min_groups_for_trending'] ) );

        $sanitized['cache_duration'] = isset( $input['cache_duration'] )
            ? absint( $input['cache_duration'] )
            : 3600;
        $sanitized['cache_duration'] = max( 300, min( 86400, $sanitized['cache_duration'] ) );

        // Advanced settings
        $sanitized['enable_rest_api'] = isset( $input['enable_rest_api'] ) ? (bool) $input['enable_rest_api'] : false;
        $sanitized['enable_ajax_search'] = isset( $input['enable_ajax_search'] ) ? (bool) $input['enable_ajax_search'] : false;
        $sanitized['debug_mode'] = isset( $input['debug_mode'] ) ? (bool) $input['debug_mode'] : false;

        return $sanitized;
    }

    /**
     * Enqueue settings assets
     *
     * @since 1.0.0
     * @param string $hook The current admin page.
     */
    public function enqueue_settings_assets( $hook ) {
        if ( 'buddypress_page_bpgf-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'bpgf-settings-styles',
            BPGF_PLUGIN_URL . 'assets/css/admin/bpgf-admin.css',
            array(),
            BPGF_VERSION
        );
    }

    /**
     * Add settings link to plugin action links
     *
     * @since 1.0.0
     * @param array $links Plugin action links.
     * @return array Modified links.
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="' . admin_url( 'admin.php?page=bpgf-settings' ) . '">' . __( 'Settings', 'bp-group-finder' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }
}