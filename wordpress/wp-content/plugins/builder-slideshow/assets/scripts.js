(function ($) {
    var BuilderSlideshow = {
        /**
         * Initialize the addon.
         */
        init: function () {
            var self = this;
            builderSlideshow.isSlideshowActive = true;
            builderSlideshow.liveStyling = [];

            builderSlideshow.firstWowInit = true;

            Themify.LoadAsync(builderSlideshow.url + 'jquery.sliderPro.min.js', function () {
                Themify.LoadAsync(builderSlideshow.url + 'sliderPro.helpers.js', function () {
                    Themify.LoadAsync(builderSlideshow.url + 'jquery.mousewheel.min.js', callback, '3.1.13', null, function () {
                        return ('undefined' !== typeof $.fn.mousewheel);
                    });
                }, '1.2.1', null, function () {
                    return ($.inArray('TransitionEffects', $.SliderPro.modules) !== -1);
                });
            }, '1.2.1', null, function () {
                return ('undefined' !== typeof $.fn.sliderPro);
            });

            function callback() {
                self.disableWow();
                self.editMarkup();
                self.cacheElements();
                self.bindEvents();
                //initialize slider pro
                if ($('.module_row').length > 0) {
                    self.initSliderPro();
                } else {
                    self.destroyLoader();
                }
            }
        },
        /**
         * Cache elements we're referencing
         */
        cacheElements: function () {
            this.dom = {
                window: $(window),
                document: $(document),
                body: Themify.body
            };
        },
        /**
         * Bind any events that need to happen
         */
		bindEvents: function () {
			this.dom.body
				.on( 'themify_onepage_afterload', function ( e, previousSection, newSection ) {
					var $allSections = $( '.sp-slide .module_row' ).not( previousSection );

					! $( previousSection ).data( 'slider-wow-animated' ) 
						&& $( previousSection ).data( 'slider-wow-animated', true );

					if ( ! $(newSection).data('slider-wow-animated') 
						&& tbLocalScript && tbLocalScript.animationInviewSelectors ) {
						$allSections.find( tbLocalScript.animationInviewSelectors.join() )
							.css( 'visibility', 'collapse' )
							.find( '*' )
							.css( 'opacity', '0' );
					}
				})
				.on( 'themify_onepage_afterload_complete', function ( e, previousSection, newSection ) {
					var $allSections = $('.sp-slide .module_row').not( previousSection ),
						isAnimated = $( newSection ).data( 'slider-wow-animated' );

					if ( tbLocalScript && tbLocalScript.animationInviewSelectors ) {
						$allSections.find( tbLocalScript.animationInviewSelectors.join() )
							.css( 'visibility', isAnimated ? 'visible' : '' )
							.find( '*' )
							.css( 'opacity', '1' );
					}

					if ( ! isAnimated ) {
						'undefined' !== typeof ThemifyBuilderModuleJs
							&& builderSlideshow.wowInit2
							&& builderSlideshow.wowInit2();

						$(newSection).data( 'slider-wow-animated', true );
					}
				});

            this.dom.window.on('tfsmartresize', $.proxy(function () {
                if (!this.isDesktop()) {
                    if (builderSlideshow.isSlideshowActive) {
                        builderSlideshow.isSlideshowActive = false;
                        this.unbindScroll();
                    }
                } else if (!builderSlideshow.isSlideshowActive) {
                    this.bindScroll();
                    builderSlideshow.isSlideshowActive = true;
                    this.initSliderPro();
                }
            }, this));

        },
        // Get builder rows anchor class to ID //////////////////////////////
        getClassToId: function ($section) {
            var classes = $section.prop('class').split(' '),
                    expr = new RegExp('^tb_section-', 'i'),
                    spanClass = null;
            for (var i = 0, len = classes.length; i < len; ++i) {
                if (expr.test(classes[i])) {
                    spanClass = classes[i];
                }
            }
            return spanClass === null ? '' : spanClass.replace('tb_section-', '');
        },
        bindScroll: function () {
            if (this.isDesktop()) {
                Themify.body.addClass('builder-slideshow-scroll');

                $('.slider-pro').on('mousewheel', $.proxy(function (e) {
                    e.preventDefault();
                    if (!this.dom.window.data('isSliding')) {
                        this.dom.window.data('isSliding', true);
                        if (e.deltaY > 0) {
                            $('.slider-pro').sliderPro('previousSlide');
                        }
                        else {
                            $('.slider-pro').sliderPro('nextSlide');
                        }
                    }
                }, this));

            }
        },
        unbindScroll: function () {
            Themify.body.removeClass('builder-slideshow-scroll');
            $('.slider-pro').off("mousewheel");
        },
        showLoader: function () {
            if ($('.sp-slide').length > 0) {
                Themify.body.append('<div class="background-slideshow-loader-wrapper"><div></div></div>');
            }
        },
        destroyLoader: function () {
            $('.background-slideshow-loader-wrapper').remove();
        },
        disableWow: function () {
            var callbackTimer = setInterval(function () {
                if ('undefined' !== typeof ThemifyBuilderModuleJs) {
                    clearInterval(callbackTimer);
                    builderSlideshow.wowInit2 = ThemifyBuilderModuleJs.wowInit;
                    ThemifyBuilderModuleJs.wowInit = function () {
                    };
                }
            }, 100);
        },
        editMarkup: function () {
            var sliderPro = $('<div class="slider-pro" id="builder-slideshow">').append('<div class="sp-slides"></div>'),
				builder = $('#themify_builder_content-'+builderSlideshow.builder_id),
				slides = builder.children('.sp-slide');

			slides = slides.filter( function() {
				return $( this ).children( '.module_row' ).is( ':visible' );
			} );

			sliderPro.append( slides );
			builder.html(sliderPro);
			this.bindScroll();
        },
        initSliderPro: function () {
            var config = {
				width: this.dom.window.width(),
				autoHeight: false,
				responsive: true,
				arrows: true,
				height: '100vh',
				fadeArrows: false,
				fadeOutPreviousSlide: false,
				startSlide: Number(this.getStartSlide()),
				touchSwipe: tbLocalScript.isTouch, // on touch devices, enable the touchSwipe
				init: $.proxy(function () {
					this.dom.body.addClass('show-builder-slideshow').removeClass('hide-builder-slideshow');
					var margin = 0 - $('.sp-buttons').height() / 2;
					$('.sp-buttons').css('margin-bottom', margin + 'px');
					this.verticalAlignRows();

					this.destroyLoader();
				}, this),
				gotoSlide: $.proxy(function (event) {
					var previousSection = $('.sp-slide').eq(event.previousIndex).find('.module_row');
					var newSection = $('.sp-slide').eq(event.index).find('.module_row');

					this.dom.body.trigger('themify_onepage_afterload', [previousSection, newSection]);
				}, this),
				gotoSlideComplete: $.proxy(function (event) {
					var previousSection = $('.sp-slide').eq(event.previousIndex).find('.module_row'),
						newSection = $('.sp-slide').eq(event.index).find('.module_row'),
						self = this;

					setTimeout(function () {
						self.dom.window.data('isSliding', false);
					}, 250);
					
					this.dom.body.trigger('themify_onepage_afterload_complete', [previousSection, newSection]);
				}, this)
			};

            if (this.isAutoplay()) {
                config.autoplay = true;
                config.autoplayDelay = Number(builderSlideshow.autoplay) * 1000;

                config._autoplay = true;
                config._autoplayDelay = Number(builderSlideshow.autoplay) * 1000;
            } else {
                config._autoplay = false;
                config.autoplay = false;
            }

            $('.slider-pro').sliderPro(config).css('visibility', 'visible');
        },
        destroySliderPro: function () {
            $('.slider-pro').sliderPro('destroy');

            this.dom.body.addClass('hide-builder-slideshow').removeClass('show-builder-slideshow');
            this.verticalAlignRows();
            this.dom.body.trigger('themify_onepage_afterload').trigger('themify_onepage_afterload_complete');
        },
        updateSliderHeight: function () {
            $('.module_row').css('min-height', '100vh');
        },
        getStartSlide: function () {
            var hash = location.hash;
            if (hash) {
                hash = hash.substring(1, hash.length);
                var ele = $('[data-anchor="' + hash + '"]').get(0);
                return Number($('.module_row').index(ele));
            }

            return 0;
        },
        verticalAlignRows: function () {
            $('.module_row').each(function () {
                var rowHeight = $(this).height(),
                        padding = (rowHeight - $(this).find('.row_inner').height()) / 2;
                $(this).css({
                    'padding-top': padding,
                    'padding-bottom': padding,
                });
            });
        },
        isAutoplay: function () {
            return builderSlideshow.autoplay > 0;
        },
        isDesktop: function () {
            return $(window).width() > 780;
        }
    };
    BuilderSlideshow.init();
}(jQuery));
