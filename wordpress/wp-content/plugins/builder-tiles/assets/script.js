var Builder_Tiles;
/**
 * The array holds the interval objects set for auto-flip functionality
 */
var flip_inervals = [];
var current_interval = null;
function matchQuery(a,b){return parseQuery(a).some(function(a){var c=a.inverse,d="all"===a.type||b.type===a.type;if(d&&c||!d&&!c)return!1;var e=a.expressions.every(function(a){var c=a.feature,d=a.modifier,e=a.value,f=b[c];if(!f)return!1;switch(c){case"orientation":case"scan":return f.toLowerCase()===e.toLowerCase();case"width":case"height":case"device-width":case"device-height":e=toPx(e),f=toPx(f);break;case"resolution":e=toDpi(e),f=toDpi(f);break;case"aspect-ratio":case"device-aspect-ratio":case"device-pixel-ratio":e=toDecimal(e),f=toDecimal(f);break;case"grid":case"color":case"color-index":case"monochrome":e=parseInt(e,10)||1,f=parseInt(f,10)||0}switch(d){case"min":return f>=e;case"max":return e>=f;default:return f===e}});return e&&!c||!e&&c})}function parseQuery(a){return a.split(",").map(function(a){a=a.trim();var b=a.match(RE_MEDIA_QUERY);if(!b)throw new SyntaxError('Invalid CSS media query: "'+a+'"');var c=b[1],d=b[2],e=((b[3]||"")+(b[4]||"")).trim(),f={};if(f.inverse=!!c&&"not"===c.toLowerCase(),f.type=d?d.toLowerCase():"all",!e)return f.expressions=[],f;if(e=e.match(/\([^\)]+\)/g),!e)throw new SyntaxError('Invalid CSS media query: "'+a+'"');return f.expressions=e.map(function(b){var c=b.match(RE_MQ_EXPRESSION);if(!c)throw new SyntaxError('Invalid CSS media query: "'+a+'"');var d=c[1].toLowerCase().match(RE_MQ_FEATURE);return{modifier:d[1],feature:d[2],value:c[2]}}),f})}function toDecimal(a){var c,b=Number(a);return b||(c=a.match(/^(\d+)\s*\/\s*(\d+)$/),b=c[1]/c[2]),b}function toDpi(a){var b=parseFloat(a),c=String(a).match(RE_RESOLUTION_UNIT)[1];switch(c){case"dpcm":return b/2.54;case"dppx":return 96*b;default:return b}}function toPx(a){var b=parseFloat(a),c=String(a).match(RE_LENGTH_UNIT)[1];switch(c){case"em":return 16*b;case"rem":return 16*b;case"cm":return 96*b/2.54;case"mm":return 96*b/2.54/10;case"in":return 96*b;case"pt":return 72*b;case"pc":return 72*b/12;default:return b}}var RE_MEDIA_QUERY=/^(?:(only|not)?\s*([_a-z][_a-z0-9-]*)|(\([^\)]+\)))(?:\s*and\s*(.*))?$/i,RE_MQ_EXPRESSION=/^\(\s*([_a-z-][_a-z0-9-]*)\s*(?:\:\s*([^\)]+))?\s*\)$/,RE_MQ_FEATURE=/^(?:(min|max)-)?(.+)/,RE_LENGTH_UNIT=/(em|rem|px|cm|mm|in|pt|pc)?\s*$/,RE_RESOLUTION_UNIT=/(dpi|dpcm|dppx)?\s*$/;

(function ($) {

	var Themify_Carousel_Tools = {
		intervals: [],
		highlight: function( item ) {
			item.addClass('current');
		},
		unhighlight: function($context) {
			$('li', $context).removeClass('current');
		},

		timer: function($timer, intervalID, timeout, step) {
			var progress = 0,
				increment = 0;

			this.resetTimer($timer, intervalID);

			this.intervals[intervalID] = setInterval(function() {
				progress += step;
				increment = ( progress * 100 ) / timeout;
				$timer.css('width', increment + '%');
			}, step);
		},

		resetTimer: function($timer, intervalID) {
			if ( null !== this.intervals[intervalID] ) {
				clearInterval( this.intervals[intervalID] );
			}
			$timer.width('width', '0%');
		},

		getCenter: function ($context) {
			var visible = $context.triggerHandler('currentVisible'),
					value = typeof visible !== 'undefined' ? visible.length : 1;

			return Math.floor(value / 2);
		},
		getDirection: function ($context, $element) {
			var visible = $context.triggerHandler('currentVisible'),
					center = Math.floor(visible.length / 2),
					index = $element.index();
			return index >= center?'next':'prev';
		},
		adjustCarousel: function ($context) {
			if ($context.closest('.twg-wrap').length > 0) {
				var visible = $context.triggerHandler('currentVisible'),
						visibleLength = typeof visible !== 'undefined' ? visible.length : 1,
						liWidth = $('li:first-child', $context).width();

				$context.triggerHandler('configuration', {width: '' + liWidth * visibleLength, responsive: false});
				$context.parent().css('width', (liWidth * visible) + 'px');
			}
		}
	};

	function createCarousel(obj) {
		obj.each(function() {
			var $this = $(this),
				autoSpeed = 'off' !== $this.data('autoplay') ? parseInt($this.data('autoplay'), 10) : 0,
				sliderArgs = {
					responsive : true,
					circular :  !!('yes' === $this.data('wrap')),
					infinite : true,
					height: 'auto',
					swipe: true,
					scroll : {
						items : $this.data('scroll') ? parseInt( $this.data('scroll'), 10 ) : 1,
						fx : $this.data('effect'),
						duration : parseInt($this.data('speed')),
						onBefore : function(items) {
							var $twgWrap = $this.closest('.twg-wrap'),
								$timer = $('.timer-bar', $twgWrap);
							if ( $timer.length > 0 ) {
								Themify_Carousel_Tools.timer($timer, $this.data('id'), autoSpeed, 20);
								Themify_Carousel_Tools.unhighlight( $this );
							}
						},
						onAfter : function(items) {
							var newItems = items.items.visible,
							$twgWrap = $this.closest('.twg-wrap' );
							if ( $twgWrap.length > 0 ) {
								var $center = newItems.filter(':eq(' + Themify_Carousel_Tools.getCenter($this) + ')');
								$('.twg-link', $center).trigger('click');
								Themify_Carousel_Tools.highlight( $center );
							}
						}
					},
					auto : {
						play : !!('off' != $this.data('autoplay')),
						timeoutDuration : autoSpeed
					},
					items : {
						visible : {
							min : 1,
							max : $this.data('visible') ? parseInt( $this.data('visible'), 10 ) : 1
						},
						width : $this.data('width') ? parseInt( $this.data('width'), 10 ) : 222
					},
					prev : {
						button: 'yes' === $this.data('slidernav') ? '#' + $this.data('id') + ' .carousel-prev' : null
					},
					next : {
						button: 'yes' === $this.data('slidernav') ? '#' + $this.data('id') + ' .carousel-next' : null
					},
					pagination : {
						container : 'yes' === $this.data('pager') ? '#' + $this.data('id') + ' .carousel-pager' : null,
						anchorBuilder: function() {
							if ( $this.closest('.testimonial.slider').length > 0 ) {
								var thumb = $('.testimonial-post', this).data('thumb'),
									thumbw = $('.testimonial-post', this).data('thumbw'),
									thumbh = $('.testimonial-post', this).data('thumbh');
								return '<span><a href="#"><img src="' + thumb + '" width="' + thumbw + '" height="' + thumbh + '" /></a></span>';
							}
							if ( ( $this.closest('.portfolio-multiple.slider').length > 0 ) || ( $this.closest('.team-multiple.slider').length > 0 ) ) {
								return '<a href="#"></a>';
							}
							return false;
						}
					},
					onCreate : function() {
						var $slideshowWrap = $this.closest('.slideshow-wrap' ),
							$teamSliderWrap = $this.closest('.team-multiple.slider' ),
							$portfolioSliderWrap = $this.closest('.portfolio-multiple.slider' ),
							$testimonialSlider = $this.closest('.testimonial.slider' ),
							$twgWrap = $this.closest('.twg-wrap');

						$this.closest('.slider').prevAll('.slideshow-slider-loader').first().remove(); // remove slider loader

						$slideshowWrap.css({
							'visibility' : 'visible',
							'height' : 'auto'
						}).addClass('carousel-ready');

						if( $testimonialSlider.length > 0 ) {
							$testimonialSlider.css({
								'visibility' : 'visible',
								'height' : 'auto'
							});
							$('.carousel-pager', $slideshowWrap).addClass('testimonial-pager');
						}

						if ( $teamSliderWrap.length > 0 ) {
							$teamSliderWrap.css({
								'visibility' : 'visible',
								'height' : 'auto'
							});
							$('.carousel-prev, .carousel-next', $teamSliderWrap ).text('');
						}
						if ( $portfolioSliderWrap.length > 0 ) {
							$portfolioSliderWrap.css({
								'visibility' : 'visible',
								'height' : 'auto'
							});
							$('.carousel-prev, .carousel-next', $portfolioSliderWrap ).text('');
						}

						if ( 'no' === $this.data('slidernav') ) {
							$('.carousel-prev,.carousel-next', $slideshowWrap).remove();
						}

						if ( $twgWrap.length > 0 ) {

							var center = Themify_Carousel_Tools.getCenter($this),
								$center = $('li', $this).filter(':eq(' + center + ')');

							Themify_Carousel_Tools.highlight( $center );

							$this.trigger( 'slideTo', [ -center, { duration: 0 } ] );
							$('.carousel-pager', $twgWrap).remove();
							$('.carousel-prev', $twgWrap).addClass('gallery-slider-prev').text('');
							$('.carousel-next', $twgWrap).addClass('gallery-slider-next').text('');
						}
						Themify_Carousel_Tools.adjustCarousel($this);
					}
				};

			// Fix unresponsive js script when there are only one slider item
			if ( $this.children().length < 2 ) {
				sliderArgs.onCreate();
				return true; // skip initialize carousel on this element
			}

			$this.carouFredSel( sliderArgs ).find('li').on(themifyScript.galleryEvent, function(){
				if ( $this.closest('.twg-wrap').length > 0 ) {
					var $thisli = $(this);
					$('li', $this).removeClass('current');
					$thisli.addClass('current');
					$thisli.trigger('slideTo', [
						$thisli,
						- Themify_Carousel_Tools.getCenter($this),
						false,
						{
							items: 1,
							duration: 300,
							onBefore : function(items) {
								var $twgWrap = $this.closest('.twg-wrap' ),
									$timer = $('.timer-bar', $twgWrap);
								if ( $timer.length > 0 ) {
									Themify_Carousel_Tools.timer($timer, $this.data('id'), autoSpeed, 20);
									Themify_Carousel_Tools.unhighlight( $this );
								}
							},
							onAfter	: function(items) { }
						},
						null,
						Themify_Carousel_Tools.getDirection($this, $thisli)]
					);
				}
			});

			/////////////////////////////////////////////
			// Resize thumbnail strip on window resize
			/////////////////////////////////////////////
			$(window).on('tfsmartresize.builder_tiles_adjustcarousel', Themify_Carousel_Tools.adjustCarousel($this) );

		});
	}
	
	Builder_Tiles = {
		init: function() {
			Builder_Tiles.do_lightbox();

			function wload() {
				window.loaded
					? Builder_Tiles.do_tiles() 
					: $( window ).on( 'load', Builder_Tiles.do_tiles );
			}

			if ( Themify.is_builder_active ) {
				$( 'body' ).on( 'builder_load_module_partial', Builder_Tiles.do_tiles );
				$( 'body' ).on( 'tb_module_sort', Builder_Tiles.update_masonry );
				Themify.is_builder_loaded && wload();
			} else {
				wload();
			}

			$( 'body' )
			// Flip Effect
			.on( 'mouseenter', '.module-tile.has-flip', function(e){
				Builder_Tiles.flip_tile( $( this ), 'back' );
				current_interval = $( this ).attr( 'id' );
			} )
			.on( 'mouseleave', '.module-tile.has-flip', function(){
				current_interval = null;
				Builder_Tiles.flip_tile( $( this ), 'front' );
			} )
			.on( 'click', '.module-tile .tile-flip-back-button', function(){
				Builder_Tiles.flip_tile( $( this ).closest( '.module-tile' ) );
				return false;
			} );
		},

		do_lightbox: function() {
			if ( $('.module-tile .themify_lightbox').length ) {
				Themify.InitGallery();
			}
		},

		add_tiles_loader: function() {
			$( '.tb-column:has(".module-tile")' ).append( '<div class="tile-loader"></div>' );
		},

		update_masonry: function( e, item ) {
			setTimeout( Builder_Tiles.do_tiles, 100 );
		},

		do_masonry: function ( container ) {
			var masonryData = container.data( 'masonry' ),
				sizer = '';

			if( BuilderTiles.fluid_tiles == 'yes' ) {
				// find Google Map objects inside tiles, force repaint
				container.find( '.map-container' ).each(function(){
					if( typeof $( this ).data( 'gmap_object' ) === 'object' ) {
						google.maps.event.trigger( $( this ).data( 'gmap_object' ) ,'resize' );
					}
				});
			}

			if( container.find( '.size-square-small' ).length ) {
				sizer = '.size-square-small';
			} else if( container.find( '.size-portrait' ).length ) {
				sizer = '.size-portrait';
			} else {
				sizer = '.module-tile';
			}

			masonryData && masonryData.destroy();

			container.masonry( {
				itemSelector: '.module-tile',
				columnWidth: sizer,
				isResizeBound: true,
				gutter: 0,
				fitWidth: true,
				percentPosition: BuilderTiles.fluid_tiles == 'yes',
				isOriginLeft : BuilderTiles.isOriginLeft == '1'
			} );
		},
		do_tiles: function( e, el, type ) {
			var elements;

			if( el && type ){
				if( type === 'module' ) {
					if( ! el.hasClass( 'module-tile' ) ) return;
					elements = el;
				} else if( type === 'column' ) {
					elements = el.closest( '.module_column:has(".module-tile")' );
				} else if( type === 'row' || type === 'subrow' ) {
					elements = $( BuilderTiles.tiles_selector, el );
				}
			} else {
				elements = $( BuilderTiles.tiles_selector );
			}
			
			function callback() {
				elements.each( function() {
					var tiles = $( this ).hasClass( 'module-tile' ) ? $( this ) : $( '.module-tile', this ),
						tileBase = BuilderTiles.tiles_base || 30, tileWrap;

					if( BuilderTiles.fluid_tiles == 'no' ) {
						tileBase = 'fixed';
					}

					+ BuilderTiles.gutter && tiles.children( '.tile-flip-box-wrap' ).css( 'padding', + BuilderTiles.gutter / 2 );
					! tiles.parent( '.tiles-wrap' ).length && tiles.wrapAll( '<div class="tiles-wrap tiles-base-' + tileBase + '" />' );

					// apply masonry effect to each tile group
					tileWrap = tiles.parent( '.tiles-wrap' );

					setTimeout( function() {
						tileWrap.addClass( 'tiles-ready' );
					}, 500 );

					if( tileWrap.length && typeof tileWrap[0].style.grid !== 'string' && ! Themify.is_builder_active ) {
						tileWrap.each( function( index, el ) { $( el ).addClass( 'module-tiles-masonry' ); setTimeout( function() { Builder_Tiles.do_masonry( $( el ) ) }, 300 ); });
					}
					$( this ).find( '.tile-loader' ).remove();
				} );
				
				// Auto Flip
				var flip = type === 'module' ? elements : $( '.module-tile.has-flip', elements );
				
				flip.each( function() {
					if( $( this ).data( 'auto-flip' ) > 0 ) {
						var el = $( this ),
							sec = el.data( 'auto-flip' ) * 1000,
							id = el.prop( 'id' );

						flip_inervals[id] && clearInterval( flip_inervals[id] );

						flip_inervals[id] = window.setInterval( function() {
							if( $( '#' + id ).length === 0 ) {
								clearInterval( flip_inervals[id] );
								return;
							}

							current_interval !== id && Builder_Tiles.flip_tile( el );
						}, sec );
					}
				});

				var $galleries = type === 'module'
					? elements.find( '.twg-wrap' )
					: $( '.module-tile .twg-wrap', elements );
				
				function gallery_callback(){
					$galleries.ThemifyWideGallery( {
						speed: parseInt( BuilderTiles.galleryFadeSpeed, 10 ),
						event: BuilderTiles.galleryEvent,
						ajax_url: BuilderTiles.ajax_url,
						networkError: BuilderTiles.networkError,
						termSeparator: BuilderTiles.termSeparator
					} );
				}

				if( $galleries.length ) {
					if ( 'undefined' !== typeof $.fn.ThemifyWideGallery ) {
						gallery_callback();
					} else{
						Themify.LoadAsync( BuilderTiles.url+'themify.widegallery.js'
							, gallery_callback, BuilderTiles.ver, null, function() {
							return ( 'undefined' !== typeof $.fn.ThemifyWideGallery );
						});
					}
				}

				var slideshow = type === 'module'
					? elements.find( '.slideshow' )
					: $( '.module-tile .slideshow', elements );
				
				if( slideshow.length ) {
					Themify.LoadAsync( themify_vars.url + '/js/carousel.min.js', function() {
						createCarousel( slideshow );
					}, null, null, function() {
						return ( 'undefined' !== typeof $.fn.carouFredSel );
					});
				}
			}

			if( elements.length ) {
				if ( 'undefined' !== typeof $.fn.masonry ) {
					callback();
				} else{
					Themify.LoadAsync( BuilderTiles.include_url + 'masonry.min.js'
						, callback, BuilderTiles.ver, null, function() {
						return ( 'undefined' !== typeof $.fn.masonry );
					} );
				}
			}
		},

		flip_tile : function( el, side ) {
			var back = el.find( '.tile-back' ).prop( 'style', '' );
			
			side = side || ( el.hasClass( 'builder-tile-flip' ) ? 'front' : 'back' );

			el.toggleClass( 'builder-tile-flip', side === 'back' )
			back.addClass( 'wow animated' );
			back.toggleClass( el.data( 'in-effect' ), side === 'back' );
			back.toggleClass( el.data( 'out-effect' ), side === 'front' );

			window.setTimeout( function() {
				el.toggleClass( 'builder-tile-flipped', side === 'back' );
			}, BuilderTiles.transition_duration );
		}

	};

	Builder_Tiles.init();
}(jQuery));