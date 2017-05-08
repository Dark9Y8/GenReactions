/* global document */
/* global jQuery */

(function ($) {

    'use strict';

    var svgIconPreview = function() {
        var $preview = $('.gen-reaction-icon-preview > span');

        if (!$preview.length) {
            return;
        }

        // Icon color picker.
        $('[name=icon_color].gen-color-picker').wpColorPicker({
            'mode' : 'hsl',
            'change': function(e, ui) {
                var hex = ui.color.toString();

                $preview.css('color', hex);
            },
            'clear': function() {
                $preview.css('color', '');
            }
        });

        // Icon background color picker.
        $('[name=icon_background_color].gen-color-picker').wpColorPicker({
            'mode' : 'hsl',
            'change': function(e, ui) {
                var hex = ui.color.toString();

                $preview.css('background-color', hex);
            },
            'clear': function() {
                $preview.css('background-color', '');
            }
        });

        // Icon type switcher.
        $('[name=icon_type]').on('change', function() {
            var type = $(this).val();

            $preview.removeClass('gen-reaction-icon-with-text gen-reaction-icon-with-visual');
            $preview.addClass('gen-reaction-icon-with-' + type);
        });

        // Icon picker.
        $('.gen-reaction-icon-sets .gen-icon-item').on('click', function() {
            var $iconItem = $(this);
            var $icon = $iconItem.find('.gen-reaction-icon');
            var $newPreview = $icon.clone();

            // Set up new preview icon.
            $newPreview.attr('style', $preview.attr('style'));
            $newPreview.find('.gen-reaction-icon-text').text($('.term-name-wrap [name=name]').val());

            var iconType = $('[name=icon_type]:checked').val();
            $newPreview.removeClass('gen-reaction-icon-with-text gen-reaction-icon-with-visual');
            $newPreview.addClass('gen-reaction-icon-with-' + iconType);

            $preview.replaceWith($newPreview);
            $preview = $newPreview;
        });

        // Icon name.
        $('.term-name-wrap [name=name]').on('keyup', function() {
            var name = $(this).val();

            $preview.find('.gen-reaction-icon-text').text(name);
        });
    };

    $(document).ready(function () {
        svgIconPreview();
    });

})(jQuery);
