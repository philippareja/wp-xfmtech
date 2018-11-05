<?php

/*
  Plugin Name:  Builder Slideshow
  Version:      1.0.7
  Author:       Themify
  Description:  Builder Slideshow is an addon to use with a latest Themify theme or Themify Builder plugin. It converts the Builder layout into a slideshow. Each slide can have a different effects (select slide effect in Builder row options).
 */

defined('ABSPATH') or die;

class Builder_Slideshow {

    public $url;
    private $dir;
    private $version;

    /**
     * Creates or returns an instance of this class.
     *
     * @return	A single instance of this class.
     */
    public static function get_instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self;
        }
        return $instance;
    }

    private function __construct() {

        $this->constants();
        add_action('plugins_loaded', array($this, 'i18n'), 5);
        add_filter( 'plugin_row_meta', array( $this, 'themify_plugin_meta'), 10, 2 );
        $is_admin = is_admin();
       if($is_admin || Themify_Builder_Model::is_front_builder_activate()){
           if($is_admin){
                add_action('init', array($this, 'updater'));
                add_action('themify_do_metaboxes', array($this, 'themify_do_metaboxes'));
                add_action('themify_builder_admin_enqueue', array($this, 'admin_enqueue'), 15);
           }
            add_filter('themify_builder_row_fields_options', array($this, 'row_fields_options'));
        }
        else{
            add_filter('themify_main_script_vars', array($this, 'minify_vars'), 10, 1);
            add_filter('themify_builder_addons_assets', array($this, 'assets'), 10, 1);
            add_filter('body_class', array($this, 'themify_body_class'));
        }
    }

    public function constants() {
        $data = get_file_data(__FILE__, array('Version'));
        $this->version = $data[0];
        $this->url = trailingslashit(plugin_dir_url(__FILE__));
        $this->dir = trailingslashit(plugin_dir_path(__FILE__));
    }

	public function themify_plugin_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
                    $row_meta = array(
                      'changelogs'    => '<a href="' . esc_url( 'https://themify.me/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'builder-slideshow' ) . '">' . esc_html__( 'View Changelogs', 'builder-slideshow' ) . '</a>'
                    );
	 
                    return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
    public function i18n() {
        load_plugin_textdomain('builder-slideshow', false, '/languages');
    }

    public function assets($assets) {
        $assets['builder-slideshow'] = array(
            'selector' => '.builder-slideshow',
            'css' => themify_enque($this->url . 'assets/styles.css'),
            'js' => themify_enque($this->url . 'assets/scripts.js'),
            'ver' => $this->version,
            'external' => Themify_Builder_Model::localize_js('builderSlideshow', apply_filters('builder_slider_pro_script_vars', array(
                'autoplay' => $this->get_option('auto_play'),
                'builder_id'=>get_the_ID(),
                'url' => $this->url . 'assets/'
            )))
        );
        return $assets;
    }

    public function themify_body_class($classes) {
        if($this->get_option('enable_slides')){
            add_filter('themify_builder_row_start', array($this, 'do_slideshow'), 10, 3);
            add_action('themify_body_start', array($this, 'themify_body_start'), 1);
            $classes[] = 'builder-slideshow';
        }
        return $classes;
    }

    public function admin_enqueue() {
		if ( ! function_exists( 'themify_enque' ) ) {
			return;
		}
        wp_enqueue_script('themify-builder-slideshow-admin', themify_enque($this->url . 'assets/admin.js'));
    }

    public function updater() {
        if (class_exists('Themify_Builder_Updater')) {
            if (!function_exists('get_plugin_data'))
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            $plugin_basename = plugin_basename(__FILE__);
            $plugin_data = get_plugin_data(trailingslashit(plugin_dir_path(__FILE__)) . basename($plugin_basename));
            new Themify_Builder_Updater(array(
                'name' => trim(dirname($plugin_basename), '/'),
                'nicename' => $plugin_data['Name'],
                'update_type' => 'addon',
                    ), $this->version, trim($plugin_basename, '/'));
        }
    }

    public function themify_do_metaboxes($panels) {
        $options = array(
            array(
                'id' => 'builder-slideshow-enable-slides',
                'type' => 'checkbox',
                'title' => __('Builder Slideshow', 'builder-slideshow'),
                'label' => __('Enable slideshow', 'builder-slideshow'),
                'option_js' => true,
                'description' => __('When enabled, Builder rows will have full height and perform a slideshow transition on page scroll. Sidebar, page titles, and comments will be disabled on this mode. Set transition effect with the Builder > Row options.'),
                'name' => 'builder_slideshow_enable_slides',
                'enable_toggle' => true,
                'class' => 'builder-slideshow-enable-slides'
            ),
            array(
                'id' => 'builder-slideshow-auto-play',
                'name' => 'builder_slideshow_auto_play',
                'title' => __('Auto Slide (Auto Play Timer)', 'builder-slideshow'),
                'type' => 'dropdown',
                'meta' => array(
                    array('value' => '0', 'name' => __('Off', 'builder-slideshow'), 'selected' => true),
                    array('value' => '1', 'name' => __('1 second', 'builder-slideshow')),
                    array('value' => '2', 'name' => __('2 seconds', 'builder-slideshow')),
                    array('value' => '3', 'name' => __('3 seconds', 'builder-slideshow')),
                    array('value' => '4', 'name' => __('4 seconds', 'builder-slideshow')),
                    array('value' => '5', 'name' => __('5 seconds', 'builder-slideshow')),
                    array('value' => '6', 'name' => __('6 seconds', 'builder-slideshow')),
                    array('value' => '7', 'name' => __('7 seconds', 'builder-slideshow')),
                    array('value' => '8', 'name' => __('8 seconds', 'builder-slideshow')),
                    array('value' => '9', 'name' => __('9 seconds', 'builder-slideshow')),
                    array('value' => '10', 'name' => __('10 seconds', 'builder-slideshow')),
                ),
                'toggle' => 'builder_slideshow_enable_slides-toggle',
                'default_toggle' => 'hidden',
            ),
            array(
                'id' => 'builder-slideshow-transition-speed',
                'name' => 'builder_slideshow_transition_speed',
                'title' => __('Transition Speed', 'builder-slideshow'),
                'type' => 'dropdown',
                'meta' => array(
                    array('value' => '0.5', 'name' => __('Fast', 'builder-slideshow'), 'selected' => true),
                    array('value' => '1', 'name' => __('Normal', 'builder-slideshow')),
                    array('value' => '2', 'name' => __('Slow', 'builder-slideshow')),
                ),
                'toggle' => 'builder_slideshow_enable_slides-toggle',
                'default_toggle' => 'hidden',
            )
        );
        $panels[] = array(
            'name' => __('Builder Slideshow', 'builder-slideshow'),
            'id' => 'builder-slideshow',
            'options' => $options,
            'pages' => 'page'
        );
        return $panels;
    }

    public function get_option($name) {
        $value = null;
        if(is_page() && class_exists('Themify_Builder_Component_Module')){//temp code for compatibility  builder new version with old version of addon to avoid the fatal error, can be removed after updating(2017.07.20)
            static $result=array();
            if(!isset($result[$name])){
                $k = 'builder_slideshow_'.$name;
                if(Themify_Builder_Model::is_themify_theme()){
                    $value = themify_get($k);
                }
                else{
                    static $options = null;
                    if($options===null){
                        $options = wp_parse_args(get_option('builder_slideshow', array()),$this->get_defaults());
                    }
                    $value = isset($options[$name])?$options[$name]:null;
                }
                if($value===null){
                    $value = $this->get_defaults($k);
                }

                $result[$name] = $value;
            }
            $value = $result[$name];
        }

        return $value;
    }

    public function themify_body_start() {
            echo "
		<div class='background-slideshow-loader-wrapper' 
			style='background-color: #fff; width: 100%; height: 2048px; position: absolute; top: 0; left: 0; z-index: 1000;'>
			<div style='background: url( \"{$this->url}assets/loader.gif\" ) no-repeat center center #fff;
						width: 100%;
						height: 100%;
						position: fixed;
						top: 0;
						left: 0;
						z-index: 1000;'>
			</div>
		</div>";
    }

    private function get_defaults($key=false) {
        $options = array(
            'builder_slideshow_enable_slides' => false,
            'builder_slideshow_auto_play' => 0,
            'builder_slideshow_transition_speed' => 0.5
        );
        return $key!==false?$options[$key]:$options;
    }

    public function row_fields_options($options) {
        $new_options = array(
            // Additional CSS
            array(
                'type' => 'separator',
                'meta' => array('html' => '<hr/>')
            ),
            array(
                'id' => 'row_slide_direction',
                'label' => __('Slide Transition', 'builder-slideshow'),
                'type' => 'select',
                'meta' => array(
                    array('value' => 'slideLeft', 'name' => __('Slide Left', 'builder-slideshow'), 'selected' => true),
                    array('value' => 'slideRight', 'name' => __('Slide Right', 'builder-slideshow')),
                    array('value' => 'slideTop', 'name' => __('Slide Up', 'builder-slideshow')),
                    array('value' => 'slideBottom', 'name' => __('Slide Down', 'builder-slideshow')),
                    array('value' => 'slideLeftFade', 'name' => __('Slide Left Fade', 'builder-slideshow'), 'selected' => true),
                    array('value' => 'slideRightFade', 'name' => __('Slide Right Fade', 'builder-slideshow')),
                    array('value' => 'slideTopFade', 'name' => __('Slide Up Fade', 'builder-slideshow')),
                    array('value' => 'slideBottomFade', 'name' => __('Slide Down Fade', 'builder-slideshow')),
                    array('value' => 'zoomOut', 'name' => __('Zoom Out', 'builder-slideshow')),
                    array('value' => 'zoomTop', 'name' => __('Zoom Top', 'builder-slideshow')),
                    array('value' => 'zoomBottom', 'name' => __('Zoom Bottom', 'builder-slideshow')),
                    array('value' => 'zoomLeft', 'name' => __('Zoom Left', 'builder-slideshow')),
                    array('value' => 'zoomRight', 'name' => __('Zoom Right', 'builder-slideshow'))
                )
            ),
        );
        /* determine the position to insert the new options to */
        $position = 15;
        return array_merge(
                array_slice($options, 0, $position, true), $new_options, array_slice($options, $position, count($options) - $position, true)
        );
    }

    /**
     * Control front end display of row module.
     * @access	public
     * @return	array
     */

    public function do_slideshow($builder_id, $row,$order) {
        $slide_direction = !empty($row['styling']['row_slide_direction'])?$row['styling']['row_slide_direction']:'slideLeft';
        $slide_duration = $this->get_option('transition_speed');         
        echo '<div data-transition="'.$slide_direction.'" data-duration="'.$slide_duration.'" class="sp-slide builder-slideshow-slide">';
        add_action('themify_builder_row_end',array($this,'row_end'),10,3);
    }
    
    public function row_end($builder_id, $row,$order){
        echo '</div>';
    }

    public function minify_vars($vars) {
        $vars['minify']['js']['sliderPro.helpers'] = themify_enque($this->url . 'assets/sliderPro.helpers.js', true);
        return $vars;
    }

}
add_action('themify_builder_setup_modules',array('Builder_Slideshow','get_instance'));