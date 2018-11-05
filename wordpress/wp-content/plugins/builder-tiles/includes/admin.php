<?php

class Builder_Tiles_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'setup_options' ), 100 );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	public function setup_options() {
		$is_themify_theme = file_exists( trailingslashit( get_template_directory() ) . 'themify/themify-utils.php' );
		$parent_page = $is_themify_theme ? 'themify' : 'themify-builder';
		add_submenu_page( $parent_page, __( 'Tiles Module', 'builder-tiles' ), __( 'Builder Tiles', 'builder-tiles' ), 'manage_options', 'builder-tiles', array( $this, 'create_admin_page' ) );
	
	}

	public function create_admin_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Builder Tiles Module', 'builder-tiles' ); ?></h2>           
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'builder_tiles' );   
				do_settings_sections( 'builder-tiles' );
				submit_button(); 
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {        
		register_setting(
			'builder_tiles', // Option group
			'builder_tiles' // Option name
		);

		add_settings_section(
			'builder-tiles-responsive', // ID
			__( 'Responsive Tiles', 'builder-tiles' ), // Title
			null, // Callback
			'builder-tiles' // Page
		);

		add_settings_field(
			'fluid_tiles', // ID
			__( 'Responsive Tiles', 'builder-contact' ), // Title 
			array( $this, 'render_option' ), // Callback
			'builder-tiles', // Page
			'builder-tiles-responsive' // Section           
		);

		add_settings_field(
			'fluid_tiles_base_size', // ID
			__( 'Base Tile Size', 'builder-contact' ), // Title 
			array( $this, 'tiles_size_control' ), // Callback
			'builder-tiles', // Page
			'builder-tiles-responsive' // Section           
		);

		add_settings_field(
			'tiles_gutter', // ID
			__( 'Tile Spacing', 'builder-contact' ), // Title 
			array( $this, 'render_spacing_field' ), // Callback
			'builder-tiles', // Page
			'builder-tiles-responsive' // Section           
		);
	}

	public function render_option() {
				$fluid_size = Builder_Tiles::get_instance()->get_option( 'fluid_tiles' );
		?>
		<select name="builder_tiles[fluid_tiles]">
			<option value="1" <?php selected( 1, $fluid_size ); ?>><?php _e( 'Enabled', 'builder-tiles' ); ?></option>
			<option value="0" <?php selected( 0, $fluid_size ); ?>><?php _e( 'Disabled', 'builder-tiles' ); ?></option>
		</select>
		<?php
	}

	public function tiles_size_control() {
				$bar_size = Builder_Tiles::get_instance()->get_option( 'fluid_tiles_base_size' );
		?>
		<select name="builder_tiles[fluid_tiles_base_size]">
			<option value="16" <?php selected( 16, $bar_size ); ?>><?php _e( '16%', 'builder-tiles' ); ?></option>
			<option value="20" <?php selected( 20, $bar_size ); ?>><?php _e( '20%', 'builder-tiles' ); ?></option>
			<option value="25" <?php selected( 25, $bar_size ); ?>><?php _e( '25%', 'builder-tiles' ); ?></option>
			<option value="30" <?php selected( 30, $bar_size ); ?>><?php _e( '30%', 'builder-tiles' ); ?></option>
		</select>
		<?php
	}

	public function render_spacing_field() {
		?><input type="text" class="small" value="<?php echo esc_attr( Builder_Tiles::get_instance()->get_option( 'gutter') ) ?>" name="builder_tiles[gutter]" />px
		<?php
	}
}
new Builder_Tiles_Admin;