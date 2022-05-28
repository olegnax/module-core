require(["jquery", 'prototype', 'mage/adminhtml/tools'], function ($) {
    "use strict";
    $(function () {
        window.toggleValueElements_old = window.toggleValueElements;
        window.toggleValueElements = function (checkbox, container, excludedElements, checked) {
            window.toggleValueElements_old(checkbox, container, excludedElements, checked);
            checked = $(checkbox).is(':checked');
            // spectrum-colorpicker
            var $input = $('input', container);
            if ($input.parent().find('.sp-replacer').length) {
                $input.prop('disabled', checked).spectrum(checked ? 'disable' : 'enable');
            }
            // onoff-trigger
            $('select, input, textarea, button, img', container).trigger('change');

        };
    });
});

require(["jquery", "spectrum"], function ($) {
    "use strict";
    $(function () {
        $(".ox-ss-colorpicker, .ox-ss-colorpicker .admin__field-control input[type=text]").spectrum({
            preferredFormat: "rgb",
            allowEmpty: true,
            showAlpha: true,
            showInput: true,
            move: function (tinycolor) {
                $(this).hide();
                var value = "";
                if (tinycolor && tinycolor.hasOwnProperty("_a")) {
                    if (1 > tinycolor._a) {
                        value = tinycolor.toRgbString();
                    } else {
                        value = tinycolor.toHexString();
                    }
                    $(this).val(value);
                }
            }
        }).attr("type", "hidden");
    });
});
