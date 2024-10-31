<?php
/*
 * Plugin Name: Score'n'co Widget
 * Plugin URI: https://scorenco.com/clubs/widgets-wordpress
 * Description: Installez directement sur votre site Wordpress un widget créé sur Score'n'co.
 * Version: 1.7.2
 * Requires at least: 3.1
 * Author: Score'n'co
 * Author: https://scorenco.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: score-nco-widget
 * Domain Path: /languages
 * Author URI: https://scorenco.com
 */
// Block direct requests

if ( !defined('ABSPATH') )
    die('-1');
    
    
add_action( 'widgets_init', function(){
     register_widget( 'Scorenco_Widget' );
}); 

add_action( 'init', 'wpdocs_load_textdomain' );
 
function wpdocs_load_textdomain() {
    load_plugin_textdomain( 'wpdocs_textdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

function my_plugin_load_my_own_textdomain( $mofile, $domain ) {
    if ( 'score-nco-widget' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
        $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
        $mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
    }
    return $mofile;
}
add_filter( 'load_textdomain_mofile', 'my_plugin_load_my_own_textdomain', 10, 2 );

/**
 * Adds Scorenco_Widget widget.
 */
class Scorenco_Widget extends WP_Widget {

    protected $scripts = array();
    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'Scorenco_Widget', // Base ID
            __('Widget Score\'n\'co', 'score-nco-widget'), // Name
            array( 'description' => __( 'Un widget automatique Score\'n\'co', 'score-nco-widget' ), ) // Args
        );

        wp_enqueue_script( 'iframeResizer', plugins_url( 'scripts/iframeResizer.min.js', __FILE__ ), array(), null, true );
        wp_enqueue_script( 'resize', plugins_url( 'scripts/resize.js', __FILE__ ), array(), null, true );

        $this->scripts['iframeResizer'] = false;
        $this->scripts['resize'] = false;

        add_action( 'wp_print_footer_scripts', array( &$this, 'remove_scripts' ) );

        //add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_resizer' ) );
    }
    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {

        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( '', 'score-nco-widget' );
        }
        if ( isset( $instance[ 'height' ] ) && !empty( $instance[ 'height' ] ) ) {
            $height = 'height="'.$instance[ 'height' ].'" ';
            $option = __( '', 'score-nco-widget' );
            $scrolling = __( '', 'score-nco-widget' );
        }
        else {
            $height = __( '', 'score-nco-widget' );
            $option = __( '?auto_height=true', 'score-nco-widget' );
            $scrolling = __( 'scrolling="no"', 'score-nco-widget' );
            $this->scripts['iframeResizer'] = true;
            $this->scripts['resize'] = true;
        }
        
        ?>
        <iframe
            id="<?php echo $title; ?>"
            <?php echo $height; ?>
            src="https://scorenco.com/widget/<?php echo $title; ?>/<?php echo $option; ?>"
            style="display: block; width: 100%; overflow: auto; margin: auto; border-width: 0px;">
            <?php echo $scrolling; ?>
        </iframe>

        <?php
    }
    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( '', 'score-nco-widget' );
        }
        if ( isset( $instance[ 'height' ] ) ) {
            $height = $instance[ 'height' ];
        }
        else {
            $height = __( '400', 'score-nco-widget' );
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Identifiant du widget :' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Hauteur du widget :' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="number" value="<?php echo esc_attr( $height ); ?>">
        </p>
        <?php 
    }

    public function remove_scripts() {
        foreach ( $this->scripts as $script => $keep ) {
            if ( ! $keep ) {
                // It seems dequeue is not "powerful" enough, you really need to deregister it
                wp_deregister_script( $script );
            }
        }
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['height'] = ( ! empty( $new_instance['height'] ) ) ? strip_tags( $new_instance['height'] ) : '';
        return $instance;
    }
} // class Scorenco_Widget