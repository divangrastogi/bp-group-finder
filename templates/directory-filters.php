<?php
/**
 * Directory filters template
 *
 * @package BP_Group_Finder
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current filter values
$current_interest = isset( $_GET['interest'] ) ? sanitize_text_field( wp_unslash( $_GET['interest'] ) ) : '';
$search_terms = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

// Get popular tags for suggestions
$popular_tags = get_terms( array(
    'taxonomy'   => 'group_interest',
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 5,
    'hide_empty' => true,
) );
?>

<div class="bpgf-directory-filters">
    <div class="bpgf-search-container">
        <div class="bpgf-search-input-wrapper">
            <input
                type="text"
                id="bpgf-group-search"
                class="bpgf-search-input"
                placeholder="<?php esc_attr_e( 'Search groups by interests...', 'bp-group-finder' ); ?>"
                value="<?php echo esc_attr( $current_interest ); ?>"
                autocomplete="off"
            />
            <button type="button" id="bpgf-search-btn" class="bpgf-search-btn">
                <?php esc_html_e( 'Search', 'bp-group-finder' ); ?>
            </button>
        </div>

        <?php if ( ! empty( $current_interest ) ) : ?>
            <div class="bpgf-active-filters">
                <span class="bpgf-filter-label"><?php esc_html_e( 'Filtering by:', 'bp-group-finder' ); ?></span>
                <span class="bpgf-tag-chip active" data-tag="<?php echo esc_attr( $current_interest ); ?>">
                    <?php echo esc_html( $current_interest ); ?>
                    <button type="button" class="bpgf-remove-filter" aria-label="<?php esc_attr_e( 'Remove filter', 'bp-group-finder' ); ?>">
                        &times;
                    </button>
                </span>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $popular_tags ) ) : ?>
            <div class="bpgf-popular-interests">
                <span class="bpgf-popular-label"><?php esc_html_e( 'Popular:', 'bp-group-finder' ); ?></span>
                <?php foreach ( $popular_tags as $tag ) : ?>
                    <?php $link = add_query_arg( 'interest', $tag->slug, bp_get_groups_directory_url() ); ?>
                    <a href="<?php echo esc_url( $link ); ?>" class="bpgf-tag-chip" data-tag="<?php echo esc_attr( $tag->name ); ?>">
                        <?php echo esc_html( $tag->name ); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>