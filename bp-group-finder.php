<?php
/**
 * Plugin Name: BP Group Finder by Interest Tags
 * Plugin URI: https://wbcomdesigns.com/
 * Description: Extend BuddyPress groups functionality with advanced interest-based discovery. Enable users to search, filter, and discover groups using custom interest tags.
 * Version: 1.0.0
 * Author: WBCom Designs
 * Author URI: https://wbcomdesigns.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bp-group-finder
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * BuddyPress: 10.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BPGF_VERSION', '1.0.0' );

/**
 * Plugin basename.
 */
define( 'BPGF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Plugin directory path.
 */
define( 'BPGF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'BPGF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bpgf-activator.php
 */
function activate_bp_group_finder() {
    require_once BPGF_PLUGIN_DIR . 'includes/class-bpgf-activator.php';
    BPGF_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bpgf-deactivator.php
 */
function deactivate_bp_group_finder() {
    require_once BPGF_PLUGIN_DIR . 'includes/class-bpgf-deactivator.php';
    BPGF_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bp_group_finder' );
register_deactivation_hook( __FILE__, 'deactivate_bp_group_finder' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require BPGF_PLUGIN_DIR . 'includes/class-bpgf-loader.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_bp_group_finder() {

    $plugin = new BPGF_Loader();

    /**
     * The core plugin classes that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require BPGF_PLUGIN_DIR . 'includes/class-bpgf-i18n.php';
    require BPGF_PLUGIN_DIR . 'includes/taxonomies/class-bpgf-taxonomy.php';
    require BPGF_PLUGIN_DIR . 'includes/admin/class-bpgf-metabox.php';

    /**
     * Load plugin classes
     */
    $plugin_i18n = new BPGF_i18n();
    $plugin_taxonomy = new BPGF_Taxonomy();
    $plugin_metabox = new BPGF_Metabox();

    // Load plugin text domain
    $plugin->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    $plugin->run();

}
run_bp_group_finder();