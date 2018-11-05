(function ($) {
    $(document).ready(function () {
        var $option = $("#builder_slideshow_enable_slides"),
                $pageOptions = $('#page-options'),
                $layoutFields = $pageOptions.find('.themify_field-layout'),
                $layout = $layoutFields.find('[alt="sidebar-none"]').parent('a'),
                $layoutRow = $layout.closest('.themify_field_row'),
                $content = $layoutFields.find('[alt="full_width"]').parent('a'),
                $contentRow = $content.closest('.themify_field_row'),
                $sticky_header = $('#fixed_header-default'),
                $full_height = $('#full_height_header-default'),
                $hideTitle = $('#hide_page_title'),
                $hideTitleRow = $hideTitle.closest('.themify_field_row'),
                $comments = $('#comment_status,#ping_status'),
                $commentsRow = $('#commentstatusdiv').find('.meta-options'),
                $header = $('input[name="header_design"]').closest('.themify_field'),
                $header_horizontal = $header.find('[alt="header-horizontal"]'),
                $header = $header.find('img'),
                $rows = [$layoutRow, $contentRow, $hideTitleRow, $commentsRow],
                $icons = [$layout, $content],
                $headers = [$sticky_header, $full_height],
                $not_allowed_headers = ['header-block', 'boxed-content', 'boxed-layout', 'header-leftpane', 'header-menu-split', 'header-rightpane', 'default'];

        $option.on('change', function () {
            if ($(this).is(":checked")) {
                $.each($icons, function (index, $value) {
                    $value.trigger("click");
                });
                $.each($rows, function (index, $value) {
                    $value.animate({opacity: 0.5}, 800);
                    $('.ui-cover').show();
                });
                $.each($headers, function (index, $value) {
                    $value.trigger('change');
                    $value.closest('.themify_field_row').css({'opacity': 0.5, 'cursor': 'not-allowed', 'pointer-events': 'none'});
                });
                if ($.inArray($('[name="header_design"]').val(), $not_allowed_headers) >= 0) {
                    $header_horizontal.trigger('click');
                }
                $.each($header, function (index, $value) {
                    if ($.inArray($(this).prop('alt'), $not_allowed_headers) >= 0) {
                        $(this).closest('a').css({'opacity': 0.3, 'cursor': 'not-allowed', 'pointer-events': 'none'});
                    }
                });


                $hideTitle.val('yes').find('[value=default],[value=no]').prop('disabled', true);
                $comments.prop('checked', false).prop('disabled', true);
            } else {
                $.each($headers, function (index, $value) {
                    $value.closest('.themify_field_row').css({'opacity': 1, 'cursor': 'auto', 'pointer-events': 'auto'});
                });
                $.each($rows, function (index, $value) {
                    $value.animate({opacity: 1}, 800);
                    $('.ui-cover').hide();
                });
                $.each($header, function (index, $value) {
                    if ($.inArray($(this).prop('alt'), $not_allowed_headers) >= 0) {
                        $(this).closest('a').css({'opacity': 1, 'cursor': 'auto', 'pointer-events': 'auto'});
                    }
                });
                $hideTitle.find('[value=default],[value=no]').prop('disabled', false);
                $comments.prop('disabled', false);
            }
        });


        $option.trigger("change");
    });
}(jQuery));
