<?php
/**
 * Display group tags template
 *
 * @package BP_Group_Finder
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$group_id = bp_get_group_id();
if ( ! $group_id ) {
    return;
}

$terms = wp_get_object_terms( $group_id, 'group_interest', array( 'fields' => 'names' ) );
if ( is_wp_error( $terms ) || empty( $terms ) ) {
    return;
}

$settings = get_option( 'bpgf_settings', array() );
$display_style = isset( $settings['tag_display_style'] ) ? $settings['tag_display_style'] : 'chips';
?>

<div class="bpgf-group-tags">
    <?php if ( 'chips' === $display_style ) : ?>
        <?php foreach ( $terms as $term ) : ?>
            <?php
            $term_obj = get_term_by( 'name', $term, 'group_interest' );
            if ( $term_obj ) :
                $link = add_query_arg( 'interest', $term_obj->slug, bp_get_groups_directory_url() );
            ?>
                <a href="<?php echo esc_url( $link ); ?>" class="bpgf-tag-chip" data-tag="<?php echo esc_attr( $term ); ?>">
                    <?php echo esc_html( $term ); ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else : ?>
        <span class="bpgf-tags-text">
            <?php
            $tag_links = array();
            foreach ( $terms as $term ) {
                $term_obj = get_term_by( 'name', $term, 'group_interest' );
                if ( $term_obj ) {
                    $link = add_query_arg( 'interest', $term_obj->slug, bp_get_groups_directory_url() );
                    $tag_links[] = '<a href="' . esc_url( $link ) . '" class="bpgf-tag-link">' . esc_html( $term ) . '</a>';
                }
            }
            echo implode( ', ', $tag_links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </span>
    <?php endif; ?>
</div>