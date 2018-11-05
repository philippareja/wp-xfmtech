;
(function ($, window, document, undefined) {

    'use strict';

    var _updater_el;

    function showAlert() {
        $(".themify_updater_alert").addClass("busy").fadeIn(800);
    }
    function hideAlert(status) {
        if (status == 'error') {
            status = 'error';
        } else {
            status = 'done';
        }
        $(".themify_updater_alert").removeClass("busy").addClass(status).delay(800).fadeOut(800, function () {
            $(this).removeClass(status);
        });
    }
    function showErrors(verbose) {
        $(".themify_updater_promt_overlay, .themify-updater-promt-box").delay(900).fadeIn(500);
        $('.themify-updater-promt-box .show-error').show();
        $('.themify-updater-promt-box .show-error p').remove();
        $('.themify-updater-promt-box .error-msg').after('<p class="prompt-error">' + verbose + '</p>');
        $('.themify-updater-promt-box .show-login').hide();
    }

    $(function () {
        //
        // Upgrade Theme and Plugins
        //

        $('#wpbody').on('click', 'a.themify-updater', function(e){
            e.preventDefault();
            console.log(this);

            if ( $(this).hasClass('themify-updater-stop') ) {
                $(this).closest('.notifications').after('<div id="themifyUpdateRrror" class="notice notice-error is-dismissible">'+ themify_upgrader.error_message +'</div>');
                return;
            }

            if (!confirm(themify_upgrader.check_backup)) return;

            var $this = $(this),
                $parent =$this.closest('.notifications'),
                action = $this.data('update_type');
            var data = {
                slug: $this.data('plugin'),
                action: action.substring(0, action.length - 1),
                _ajax_nonce: $this.data('nonce'),
                _fs_nonce: '',
                username: '',
                password: '',
                connection_type: '',
                public_key: '',
                private_key: '',
            };
            if ( action === 'update-plugins' ) {
                data.plugin = $this.data('base');
            }
            $.ajax({
                url:ajaxurl,
                type:'POST',
                data:data,
                beforeSend: function(){
                    showAlert();
                },
                success: function(response) {
                    response = typeof response === 'string' ? JSON.parse(response) : response;
                    if (response.success) {
                        hideAlert();
                        setTimeout( function(){ location.reload(); },1500);
                    } else {
                        hideAlert('error');
                        if ( $parent.siblings("#themifyUpdateRrror").length > 0) {
                            $parent.siblings("#themifyUpdateRrror").remove();
                        }
                        $parent.after('<div id="themifyUpdateRrror" class="notice notice-error is-dismissible">'+ response.data.errorMessage +'</div>');
                    }
                },
                error: function() {
                    hideAlert('error');
                }
            });
        });


        $('.themify_updater_changelogs').on('click', function (e) {
            e.preventDefault();
            var $self = $(this),
                    url = $self.data('changelog');
            $('.themify-updater-promt-box .show-error').hide();
            $('.themify_updater_alert').addClass('busy').fadeIn(300);
            $('.themify_updater_promt_overlay,.themify-updater-promt-box').fadeIn(300);
            var $iframe = $('<iframe src="' + url + '" />');
            $iframe.on('load', function () {
                $('.themify_updater_alert').removeClass('busy').fadeOut(300);
            }).prependTo('.themify-updater-promt-box');
            $('.themify-updater-promt-box').addClass('show-changelog');
			
			$('.themify_updater_promt_overlay').one('click', function (e) {
				$(this).fadeOut(300);
				$('.themify-updater-promt-box').fadeOut(300).find('iframe').remove();
			});

        });
    });
}(jQuery, window, document));