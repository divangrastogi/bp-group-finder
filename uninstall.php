<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    BP_Group_Finder
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if BuddyPress is available
if ( ! function_exists( 'buddypress' ) ) {
    return;
}

// Clean up plugin data
global $wpdb;

// Remove plugin options
delete_option( 'bpgf_db_version' );
delete_option( 'bpgf_max_tags_per_group' );
delete_option( 'bpgf_min_tag_length' );
delete_option( 'bpgf_max_tag_length' );

// Remove plugin transients
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_bpgf_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_bpgf_%'" );

// Remove taxonomy terms and relationships
// Note: We don't remove the taxonomy itself as it might be used by other plugins
// But we can remove terms if needed - uncomment the following lines if desired
/*
$terms = get_terms( array(
    'taxonomy' => 'group_interest',
    'hide_empty' => false,
) );

if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
    foreach ( $terms as $term ) {
        wp_delete_term( $term->term_id, 'group_interest' );
    }
}
*/

// Clear any cached data
wp_cache_flush();

// Flush rewrite rules
flush_rewrite_rules();