<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BP_Group_Finder
 * @subpackage BP_Group_Finder/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    BP_Group_Finder
 * @subpackage BP_Group_Finder/includes
 * @author     WBCom Designs <admin@wbcomdesigns.com>
 */
class BPGF_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {

        // Flush rewrite rules
        flush_rewrite_rules();

        // Clear any scheduled events if any
        // wp_clear_scheduled_hook( 'bpgf_daily_cleanup' );

    }

}