<?php
/**
 * Search form template for group interests
 *
 * @package BP_Group_Finder
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current search parameters
$current_interest = isset( $_GET['interest'] ) ? sanitize_text_field( wp_unslash( $_GET['interest'] ) ) : '';
$search_terms = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
?>

<div class="bpgf-search-form">
    <form method="get" action="<?php echo esc_url( bp_get_groups_directory_url() ); ?>" class="bpgf-search-form-inner">
        <div class="bpgf-search-input-wrapper">
            <label for="bpgf-interest-search" class="screen-reader-text">
                <?php esc_html_e( 'Search by interests', 'bp-group-finder' ); ?>
            </label>
            <input
                type="text"
                id="bpgf-interest-search"
                name="interest"
                value="<?php echo esc_attr( $current_interest ); ?>"
                placeholder="<?php esc_attr_e( 'Search groups by interests...', 'bp-group-finder' ); ?>"
                autocomplete="off"
                class="bpgf-interest-input"
            />
            <button type="submit" class="bpgf-search-submit">
                <span class="screen-reader-text"><?php esc_html_e( 'Search', 'bp-group-finder' ); ?></span>
                <svg class="bpgf-search-icon" viewBox="0 0 24 24" width="20" height="20">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
            </button>
        </div>

        <?php if ( ! empty( $current_interest ) ) : ?>
            <div class="bpgf-active-filters">
                <span class="bpgf-filter-label"><?php esc_html_e( 'Filtering by:', 'bp-group-finder' ); ?></span>
                <span class="bpgf-active-tag">
                    <?php echo esc_html( $current_interest ); ?>
                    <a href="<?php echo esc_url( remove_query_arg( 'interest' ) ); ?>" class="bpgf-remove-filter" aria-label="<?php esc_attr_e( 'Remove filter', 'bp-group-finder' ); ?>">
                        &times;
                    </a>
                </span>
            </div>
        <?php endif; ?>

        <?php
        // Preserve other query parameters
        foreach ( $_GET as $key => $value ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( 'interest' !== $key && 's' !== $key ) {
                echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
            }
        }
        ?>
    </form>
</div>