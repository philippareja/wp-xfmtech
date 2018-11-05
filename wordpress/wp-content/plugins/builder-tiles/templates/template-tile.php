<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Tile
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
if (TFCache::start_cache($mod_name, self::$post_id, array('ID' => $module_ID))):
    $fields_default = array(
        'size' => 'square-large',
        'flip_effect' => 'flip-left',
        'color_front' => '',
        'color_back' => '',
        'title_front' => '',
        'title_back' => '',
        'type_front' => 'text',
        'type_back' => 'text',
        'text_front' => '',
        'text_back' => '',
        'button_link_front' => '',
        'button_link_back' => '',
        'button_param_front' => array(),
        'button_param_back' => array(),
        'icon_type_front' => 'icon',
        'icon_type_back' => 'icon',
        'icon_front' => '',
        'icon_back' => '',
        'image_front' => '',
        'image_back' => '',
        'icon_color_front' => '',
        'icon_color_back' => '',
        'background_repeat_front' => '',
        'background_repeat_back' => '',
        'gallery_front' => '',
        'gallery_back' => '',
        'gallery_autoplay_front' => 'off',
        'gallery_autoplay_back' => 'off',
        'gallery_hide_timer_front' => 'no',
        'gallery_hide_timer_back' => 'no',
        'address_map_front' => '',
        'address_map_back' => '',
        'type_map_front' => 'ROADMAP',
        'type_map_back' => 'ROADMAP',
        'zoom_map_front' => 8,
        'zoom_map_back' => 8,
        'scrollwheel_map_front' => 'enable',
        'scrollwheel_map_back' => 'enable',
        'draggable_map_front' => 'enable',
        'draggable_map_back' => 'enable',
        'action_text_front' => '',
        'action_text_back' => '',
        'action_link_front' => '',
        'action_link_back' => '',
        'action_param_front' => array(),
        'action_param_back' => array(),
        'tile_autoflip' => '0',
        'background_color_front' => '',
        'background_color_back' => '',
        'text_color_front' => '',
        'text_color_back' => '',
        'link_color_front' => '',
        'link_color_back' => '',
        'background_image_front' => '',
        'background_image_back' => '',
        'animation_effect' => '',
        'css_class' => ''
    );
    $sides = array('front', 'back');
    foreach ($sides as $side) {
        if (isset($mod_settings['button_link_params_'.$side])) {
            $mod_settings['button_link_params_'.$side] = explode('|', $mod_settings['button_link_params_'.$side]);
        }
        if (isset($mod_settings['action_param_'.$side])) {
            $mod_settings['action_param_'.$side] = explode('|', $mod_settings['action_param_'.$side]);
        }
    }

    $field_args = wp_parse_args($mod_settings, $fields_default);
    unset($mod_settings);
    $animation_effect = self::parse_animation_effect($field_args['animation_effect'], $field_args);
    if(empty($field_args['size'])){
        $field_args['size'] = $fields_default['size'];
    }
    if(empty($field_args['type_front'])){
        $field_args['type_front'] = $fields_default['type_front'];
    }
    if(empty($field_args['type_back'])){
        $field_args['type_back'] = $fields_default['type_back'];
    }

	$instance = Builder_Tiles::get_instance();
    $tile_sizes = $instance->get_tile_sizes();

    $tile_size = $tile_sizes[$field_args['size']];
    if (( $field_args['type_back'] === 'blank' ) || ( $field_args['type_back'] === 'text' && $field_args['text_back'] === '' && $field_args['background_image_back'] === '' && $field_args['title_back'] == '' ) // enable flip on back text tiles with no text but have background image
            || ( $field_args['type_back'] === 'button' && $field_args['title_back'] === '' && $field_args['image_back'] === '' && $field_args['icon_back'] === '' ) || ( $field_args['type_back'] === 'gallery' && $field_args['gallery_back'] === '' ) || ( $field_args['type_back'] === 'map' && $field_args['address_map_back'] === '' )
    ) {
        $class = 'no-flip';
    } else {
        $class = 'has-flip';
    }

    $container_class = implode(' ', apply_filters('themify_builder_module_classes', array(
        'module', 'module-' . $mod_name, $module_ID, 'size-' . $field_args['size'], $field_args['flip_effect'], $class, $field_args['css_class'], 'tile-type-front-' . $field_args['type_front'], $animation_effect, in_array( $instance->get_option( 'fluid_tiles' ), array( 'yes', 1, '1' ) ) ? 'fluid-tile' : ''
                    ), $mod_name, $module_ID, $field_args)
    );

    $out_effect = array(
        'flip-horizontal' => '',
        'flip-vertical' => '',
        'fadeInUp' => 'fadeOutDown',
        'fadeIn' => 'fadeOut',
        'fadeInLeft' => 'fadeOutLeft',
        'fadeInRight' => 'fadeOutRight',
        'fadeInDown' => 'fadeOutUp',
        'zoomInUp' => 'zoomOutDown',
        'zoomInLeft' => 'zoomOutLeft',
        'zoomInRight' => 'zoomOutRight',
        'zoomInDown' => 'zoomOutUp',
    );

    $flip_button_enabled = apply_filters('builder_tiles_enable_flip_button', themify_is_touch());
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'id' => $module_ID,
        'class' => $container_class
            ), $field_args, $mod_name, $module_ID); ?>

	<!-- module tile -->
	<div <?php echo self::get_element_attributes( $container_props ); ?> data-auto-flip="<?php echo esc_attr( $field_args['tile_autoflip'] ); ?>" data-in-effect="<?php echo $field_args['flip_effect']; ?>" data-out-effect="<?php echo $out_effect[$field_args['flip_effect']]; ?>" style="visibility:hidden;">
		<!--insert-->
		<?php do_action( 'themify_builder_before_template_content_render' ); ?>
		
		<div class="tile-flip-box-wrap" style="opacity: 0"><div class="tile-flip-box">
			<?php ob_start();
			$styles = array();

			foreach( $sides as $side ) :
				$type = $field_args['type_' . $side];

				if( $type !== 'blank' ) {
					$k = '#' . $module_ID . ' .tile-' . $side;

					if( $field_args['background_color_' . $side] !== '' ) {
						$styles[$k][] = 'background-color:' . Themify_Builder_Stylesheet::get_rgba_color( $field_args['background_color_' . $side]) . ';';
					}

					if( $field_args['text_color_' . $side] !== '' ) {
						$styles[$k][] = 'color:' . Themify_Builder_Stylesheet::get_rgba_color( $field_args['text_color_' . $side] ) . ';';
					}

					if( $field_args['background_image_' . $side] !== '' ) {
						$image = themify_do_img( $field_args['background_image_' . $side], $tile_size['width'], $tile_size['height'] );
						if (!empty($image['url'])) {
							$styles[$k][] = 'background-image: url("' . esc_url($image['url']) . '");';
						}
					}

					if( $field_args['link_color_' . $side] !== '' ) {
						$k.=' a';
						$styles[$k][] = 'color:' . Themify_Builder_Stylesheet::get_rgba_color( $field_args['link_color_' . $side] ) . ';';
					}
				} ?>

				<div class="tile-<?php echo $side; ?> tile-type-<?php echo $type; ?> ui <?php echo $field_args['color_' . $side]; ?>">
					<div class="tile-inner">
						<?php if( $type === 'text' ) : ?>
							<?php if( $field_args['title_' . $side] !== '' ) : ?>
								<h4 class="tile-title"><?php echo wp_kses_post( $field_args['title_' . $side] ); ?></h4>
							<?php endif; ?>

							<div class="tile-content">
								<?php echo apply_filters( 'themify_builder_module_content', $field_args['text_' . $side] ); ?>
							</div><!-- .tile-content -->
						<?php elseif( $type === 'button' ) :
							if( $field_args['button_link_' . $side] !== '' ) :
								$params_side = isset( $field_args['button_link_params_' . $side] )
									? $field_args['button_link_params_' . $side] : array();

								printf( '<a href="%s" class="%s" %s>'
									, in_array( 'lightbox', $params_side, true )
										? esc_url( $field_args['button_link_' . $side] )
										: esc_attr( $field_args['button_link_' . $side] )
									, in_array( 'lightbox', $params_side, true )
										? ' themify_lightbox' : ''
									, in_array( 'newtab', $params_side, true )
										? ' target="_blank"' : '' );
							endif;

							if( '' !== $field_args['title_' . $side] ) : ?>
								<h4 class="tile-title"><?php echo wp_kses_post( $field_args['title_' . $side] ); ?></h4>
							<?php endif; ?>

							<?php if( $field_args['icon_type_'.$side] === 'icon' && $field_args['icon_'.$side] !== '') : ?>
								<span class="tile-icon <?php echo esc_attr(themify_get_icon($field_args["icon_{$side}"])); ?>"<?php if($field_args['icon_color_'.$side]!==''):?> style="color:<?php echo Themify_Builder_Stylesheet::get_rgba_color($field_args['icon_color_'.$side])?>"<?php endif;?>></span>
							<?php elseif( $field_args['icon_type_' . $side] === 'image' && $field_args['image_'.$side] !== '') : ?>
								<img src="<?php echo esc_url($field_args['image_'.$side]); ?>" alt="<?php  echo esc_attr($field_args['title_' . $side]); ?>" class="tile-icon" />
							<?php endif; ?>

							<?php if( $field_args['button_link_' . $side] !== '' ) echo '</a>'; ?>
						<?php elseif( $type === 'gallery' ) :
							$images = Themify_Builder_Model::get_images_from_gallery_shortcode( $field_args['gallery_' . $side] );
							$returned_items = count( $images );

							if( $returned_items > 0 ) : ?>
								<div class="gallery-shortcode-wrap twg-wrap twg-gallery-shortcode <?php echo 'yes' === $field_args['gallery_hide_timer_'.$side] ? 'no-timer' : 'with-timer'; ?>" data-bgmode="cover">
									<div class="gallery-image-holder twg-holder">
										<div class="twg-loading themify-loading"></div>

										<div class="gallery-info twg-info">
											<div class="gallery-caption twg-caption"></div><!-- /gallery-caption -->
										</div>
									</div>

									<div id="gallery-shortcode-slider-<?php echo $module_ID; ?>" class="gallery-slider-wrap twg-controls">
										<div class="gallery-slider-timer"><div class="timer-bar"></div></div><!-- /gallery-slider-timer -->

										<ul class="gallery-slider-thumbs slideshow twg-list" data-id="gallery-shortcode-slider-<?php echo $module_ID; ?>" data-autoplay="<?php echo $field_args['gallery_autoplay_'.$side]; ?>" data-effect="scroll" data-speed="1000" data-visible="<?php echo 12 >= $returned_items ? $returned_items - 1 : 12 ?>" data-width="33" data-wrap="yes" data-slidernav="yes" data-pager="no">
											<?php foreach( $images as $image ) :
												$full = wp_get_attachment_image_src( $image->ID, apply_filters( 'themify_gallery_shortcode_full_size', 'large' ) );
												$caption = $image->post_excerpt === '' ? $image->post_content : $image->post_excerpt;
												$description = $image->post_content === '' ? $image->post_excerpt : $image->post_content;
												$alt = ( $alt_text = get_post_meta( $image->ID, '_wp_attachment_image_alt', true ) ) ? $alt_text : $image->post_name;
												$full[0] = esc_url( $full[0] ); ?>

												<li class="twg-item">
													<a href="#" data-image="<?php echo $full[0]; ?>" data-caption="<?php echo esc_attr($caption); ?>" data-description="<?php echo esc_attr($description); ?>" class="twg-link">
														<?php echo themify_get_image('src=' . $full[0]. '&w=40&h=33&alt=' . $alt . '&ignore=true'); ?>
													</a>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								</div>
							<?php endif; ?>
						<?php elseif( $type === 'map' ) :
							$field_args['address_map_' . $side] = preg_replace( '/\s+/', ' ', trim( $field_args['address_map_' . $side] ) );
							$data = array();
							$data['address'] = $field_args['address_map_' . $side];
							$data['zoom'] = $field_args['zoom_map_' . $side];
							$data['type'] = $field_args['type_map_' . $side];
							$data['scroll'] = 'enable' !== $field_args['scrollwheel_map_' . $side] ? 'false' : 'true';
							$data['drag'] = 'enable' !== $field_args['draggable_map_' . $side] ? 'false' : 'true';
							$num = rand(0, 10000); ?>

							<div id="themify_map_canvas_<?php echo $num; ?>" data-map='<?php echo esc_attr( base64_encode( json_encode( $data ) ) ); ?>' class="themify_map map-container"></div>
						<?php endif; ?>

						<?php if( ( $type === 'text' || $type === 'gallery' ) && '' !== $field_args['action_link_'.$side] ) :
							$params_side = isset( $field_args['action_param_' . $side]) ? $field_args['action_param_' . $side] : false; ?>
							
							<a href="<?php echo in_array( 'lightbox', $params_side, true ) ? esc_url($field_args['action_link_'.$side]) : esc_attr($field_args['action_link_'.$side]); ?>" class="action-button
								<?php if( in_array( 'lightbox', $params_side, true ) ) echo ' themify_lightbox'; ?>"
								<?php if( in_array( 'newtab', $params_side, true ) ) echo ' target="_blank"'; ?>
								<span></span> 
								<?php echo wp_kses_post( $field_args['action_text_' . $side] ); ?>
							</a>

							<?php if( $field_args[ 'link_color_' . $side] !== '' ) {
								$k = '#' . $module_ID . ' action-button span';
								$styles[$k] = 'border-color:' . Themify_Builder_Stylesheet::get_rgba_color( $field_args['link_color_' . $side] ) . ';';
							} ?>
						<?php endif; ?>

						<?php if ($flip_button_enabled) echo '<a href="#" class="tile-flip-back-button"></a>'; ?>
					</div><!-- .tile-inner -->
				</div><!-- .tile-<?php echo $side; ?> -->

			<?php endforeach; ?>

			<?php $content = ob_get_clean();?>
			<?php if( ! empty( $styles ) ) : ?>
				<style type="text/css">
					<?php foreach( $styles as $selector => $st ) {
						if( ! empty( $st ) ) printf( '%s { %s }' . "\r\n", $selector, implode( ( array ) $st, '' ) );
					} ?>
				</style>
			<?php endif;?>
			<?php echo $content;?>
		</div><!-- .tile-flip-box --></div><!-- .tile-flip-box-wrap -->

		<?php do_action('themify_builder_after_template_content_render'); ?>
	</div><!-- /module tile -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>