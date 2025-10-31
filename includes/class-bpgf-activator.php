<?php
/**
 * Fired during plugin activation
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BP_Group_Finder
 * @subpackage BP_Group_Finder/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    BP_Group_Finder
 * @subpackage BP_Group_Finder/includes
 * @author     WBCom Designs <admin@wbcomdesigns.com>
 */
class BPGF_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {

        // Check if BuddyPress is active
        if ( ! function_exists( 'buddypress' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( 'BuddyPress Group Finder requires BuddyPress to be installed and active.', 'bp-group-finder' ) );
        }

        // Check BuddyPress version
        if ( version_compare( BP_VERSION, '10.0.0', '<' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( 'BuddyPress Group Finder requires BuddyPress version 10.0.0 or higher.', 'bp-group-finder' ) );
        }

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set default options
        add_option( 'bpgf_db_version', '1.0.0' );
        add_option( 'bpgf_max_tags_per_group', 10 );
        add_option( 'bpgf_min_tag_length', 2 );
        add_option( 'bpgf_max_tag_length', 50 );

    }

}