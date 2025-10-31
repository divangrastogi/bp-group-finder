<?php
/**
 * WordPress widget for displaying trending tags
 *
 * @package BP_Group_Finder
 * @subpackage Admin
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BPGF_Trending_Widget class
 *
 * Displays trending group interest tags
 */
class BPGF_Trending_Widget extends WP_Widget {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct(
            'bpgf_trending_tags',
            __( 'Trending Group Interests', 'bp-group-finder' ),
            array(
                'description' => __( 'Display trending group interest tags', 'bp-group-finder' ),
                'classname'   => 'bpgf-trending-widget',
            )
        );
    }

    /**
     * Widget output
     *
     * @since 1.0.0
     * @param array $args     Widget arguments.
     * @param array $instance Widget instance.
     */
    public function widget( $args, $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Trending Interests', 'bp-group-finder' );
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        $number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
        $time_period = ! empty( $instance['time_period'] ) ? absint( $instance['time_period'] ) : 30;
        $show_counts = isset( $instance['show_counts'] ) ? $instance['show_counts'] : true;
        $display_style = ! empty( $instance['display_style'] ) ? $instance['display_style'] : 'cloud';

        echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if ( $title ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        $this->render_trending_tags( $number, $time_period, $show_counts, $display_style );

        echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Widget settings form
     *
     * @since 1.0.0
     * @param array $instance Current settings.
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Trending Interests', 'bp-group-finder' );
        $number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;
        $time_period = ! empty( $instance['time_period'] ) ? absint( $instance['time_period'] ) : 30;
        $show_counts = isset( $instance['show_counts'] ) ? (bool) $instance['show_counts'] : true;
        $display_style = ! empty( $instance['display_style'] ) ? $instance['display_style'] : 'cloud';

        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Title:', 'bp-group-finder' ); ?>
            </label>
            <input
                class="widefat"
                id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                type="text"
                value="<?php echo esc_attr( $title ); ?>"
            />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>">
                <?php esc_html_e( 'Number of tags to show:', 'bp-group-finder' ); ?>
            </label>
            <input
                class="tiny-text"
                id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>"
                type="number"
                step="1"
                min="1"
                max="20"
                value="<?php echo esc_attr( $number ); ?>"
                size="3"
            />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'time_period' ) ); ?>">
                <?php esc_html_e( 'Time period (days):', 'bp-group-finder' ); ?>
            </label>
            <select
                id="<?php echo esc_attr( $this->get_field_id( 'time_period' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'time_period' ) ); ?>"
            >
                <option value="7" <?php selected( $time_period, 7 ); ?>><?php esc_html_e( 'Last 7 days', 'bp-group-finder' ); ?></option>
                <option value="30" <?php selected( $time_period, 30 ); ?>><?php esc_html_e( 'Last 30 days', 'bp-group-finder' ); ?></option>
                <option value="90" <?php selected( $time_period, 90 ); ?>><?php esc_html_e( 'Last 90 days', 'bp-group-finder' ); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>">
                <?php esc_html_e( 'Display style:', 'bp-group-finder' ); ?>
            </label>
            <select
                id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>"
            >
                <option value="cloud" <?php selected( $display_style, 'cloud' ); ?>><?php esc_html_e( 'Tag cloud', 'bp-group-finder' ); ?></option>
                <option value="list" <?php selected( $display_style, 'list' ); ?>><?php esc_html_e( 'List', 'bp-group-finder' ); ?></option>
            </select>
        </p>

        <p>
            <input
                class="checkbox"
                type="checkbox"
                id="<?php echo esc_attr( $this->get_field_id( 'show_counts' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'show_counts' ) ); ?>"
                <?php checked( $show_counts ); ?>
            />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_counts' ) ); ?>">
                <?php esc_html_e( 'Show group counts', 'bp-group-finder' ); ?>
            </label>
        </p>
        <?php
    }

    /**
     * Update widget settings
     *
     * @since 1.0.0
     * @param array $new_instance New settings.
     * @param array $old_instance Old settings.
     * @return array Updated settings.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();

        $instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['number'] = ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5;
        $instance['time_period'] = ! empty( $new_instance['time_period'] ) ? absint( $new_instance['time_period'] ) : 30;
        $instance['display_style'] = ! empty( $new_instance['display_style'] ) && in_array( $new_instance['display_style'], array( 'cloud', 'list' ), true )
            ? $new_instance['display_style']
            : 'cloud';
        $instance['show_counts'] = isset( $new_instance['show_counts'] ) ? (bool) $new_instance['show_counts'] : false;

        // Clear cache
        $this->clear_widget_cache();

        return $instance;
    }

    /**
     * Render trending tags
     *
     * @since 1.0.0
     * @param int    $number       Number of tags to show.
     * @param int    $time_period  Time period in days.
     * @param bool   $show_counts  Whether to show counts.
     * @param string $display_style Display style (cloud|list).
     */
    private function render_trending_tags( $number, $time_period, $show_counts, $display_style ) {
        $cache_key = 'bpgf_trending_widget_' . md5( serialize( func_get_args() ) );
        $trending = wp_cache_get( $cache_key, 'bp_group_finder' );

        if ( false === $trending ) {
            $search = new BPGF_Search();
            $trending = $search->get_trending_tags( $number );

            // Cache for 1 hour
            wp_cache_set( $cache_key, $trending, 'bp_group_finder', HOUR_IN_SECONDS );
        }

        if ( empty( $trending ) ) {
            echo '<p>' . esc_html__( 'No trending interests found.', 'bp-group-finder' ) . '</p>';
            return;
        }

        if ( 'cloud' === $display_style ) {
            $this->render_tag_cloud( $trending, $show_counts );
        } else {
            $this->render_tag_list( $trending, $show_counts );
        }
    }

    /**
     * Render tag cloud
     *
     * @since 1.0.0
     * @param array $trending    Trending tags.
     * @param bool  $show_counts Whether to show counts.
     */
    private function render_tag_cloud( $trending, $show_counts ) {
        $max_score = max( array_column( $trending, 'score' ) );
        $min_score = min( array_column( $trending, 'score' ) );

        echo '<div class="bpgf-tag-cloud">';

        foreach ( $trending as $item ) {
            $term = $item['term'];
            $score = $item['score'];
            $group_count = $item['group_count'];

            // Calculate font size (between 0.8em and 2.0em)
            if ( $max_score === $min_score ) {
                $font_size = 1.4; // Medium size if all scores are equal
            } else {
                $font_size = 0.8 + ( ( $score - $min_score ) / ( $max_score - $min_score ) ) * 1.2;
            }

            $link = add_query_arg( 'interest', $term->slug, bp_get_groups_directory_url() );

            printf(
                '<a href="%s" class="bpgf-trending-tag" style="font-size: %sem;" title="%s">%s</a>',
                esc_url( $link ),
                esc_attr( number_format( $font_size, 1 ) ),
                esc_attr( sprintf( _n( '%d group', '%d groups', $group_count, 'bp-group-finder' ), $group_count ) ),
                esc_html( $term->name )
            );

            if ( $show_counts ) {
                echo '<span class="bpgf-tag-count">(' . esc_html( $group_count ) . ')</span>';
            }
        }

        echo '</div>';
    }

    /**
     * Render tag list
     *
     * @since 1.0.0
     * @param array $trending    Trending tags.
     * @param bool  $show_counts Whether to show counts.
     */
    private function render_tag_list( $trending, $show_counts ) {
        echo '<ul class="bpgf-tag-list">';

        foreach ( $trending as $item ) {
            $term = $item['term'];
            $group_count = $item['group_count'];

            $link = add_query_arg( 'interest', $term->slug, bp_get_groups_directory_url() );

            echo '<li class="bpgf-tag-item">';

            printf(
                '<a href="%s" class="bpgf-trending-tag">%s</a>',
                esc_url( $link ),
                esc_html( $term->name )
            );

            if ( $show_counts ) {
                printf(
                    '<span class="bpgf-tag-count">%s</span>',
                    esc_html( sprintf( _n( '(%d group)', '(%d groups)', $group_count, 'bp-group-finder' ), $group_count ) )
                );
            }

            echo '</li>';
        }

        echo '</ul>';
    }

    /**
     * Clear widget cache
     *
     * @since 1.0.0
     */
    private function clear_widget_cache() {
        // Clear all widget caches
        wp_cache_flush();
    }
}

// Register the widget
function bpgf_register_trending_widget() {
    register_widget( 'BPGF_Trending_Widget' );
}
add_action( 'widgets_init', 'bpgf_register_trending_widget' );