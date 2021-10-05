/*
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) $year Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'mageUtils',
    'jquery',
    'spectrum'
], function (Element, utils, $) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            label: '',
            error: '',
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },
        initialize: function () {
            this._super();
        },
        initColorPickerCallback: function (element) {
            $(element).attr("type", "hidden").spectrum({
                preferredFormat: "rgb",
                allowEmpty: true,
                showAlpha: true,
                showInput: true,
            });
        }
    });
});