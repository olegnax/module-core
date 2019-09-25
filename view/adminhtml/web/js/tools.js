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