<?php
/**
 * Trending tags widget template
 *
 * @package BP_Group_Finder
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Widget instance variables
$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Trending Interests', 'bp-group-finder' );
$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
$time_period = isset( $instance['time_period'] ) ? absint( $instance['time_period'] ) : 30;
$show_counts = isset( $instance['show_counts'] ) ? $instance['show_counts'] : true;
$display_style = isset( $instance['display_style'] ) ? $instance['display_style'] : 'cloud';

// Get trending tags
$search = new BPGF_Search();
$trending = $search->get_trending_tags( $number );

if ( empty( $trending ) ) {
    return;
}
?>

<div class="bpgf-trending-widget">
    <?php if ( ! empty( $title ) ) : ?>
        <h3 class="bpgf-widget-title"><?php echo esc_html( $title ); ?></h3>
    <?php endif; ?>

    <?php if ( 'cloud' === $display_style ) : ?>
        <div class="bpgf-tag-cloud">
            <?php foreach ( $trending as $item ) : ?>
                <?php
                $term = $item['term'];
                $group_count = $item['group_count'];
                $link = add_query_arg( 'interest', $term->slug, bp_get_groups_directory_url() );
                ?>
                <a href="<?php echo esc_url( $link ); ?>" class="bpgf-trending-tag" title="<?php echo esc_attr( sprintf( _n( '%d group', '%d groups', $group_count, 'bp-group-finder' ), $group_count ) ); ?>">
                    <?php echo esc_html( $term->name ); ?>
                </a>
                <?php if ( $show_counts ) : ?>
                    <span class="bpgf-tag-count">(<?php echo esc_html( $group_count ); ?>)</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <ul class="bpgf-tag-list">
            <?php foreach ( $trending as $item ) : ?>
                <?php
                $term = $item['term'];
                $group_count = $item['group_count'];
                $link = add_query_arg( 'interest', $term->slug, bp_get_groups_directory_url() );
                ?>
                <li class="bpgf-tag-item">
                    <a href="<?php echo esc_url( $link ); ?>" class="bpgf-trending-tag">
                        <?php echo esc_html( $term->name ); ?>
                    </a>
                    <?php if ( $show_counts ) : ?>
                        <span class="bpgf-tag-count">
                            <?php echo esc_html( sprintf( _n( '(%d group)', '(%d groups)', $group_count, 'bp-group-finder' ), $group_count ) ); ?>
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>