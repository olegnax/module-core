define([
    'jquery',
    'jquery/ui',
    'owl.carousel',
], function ($) {
    'use strict';

    $.widget('mage.OXowlCarousel', {
        options: {
            items: 3,
            loop: false,
            center: false,
            rewind: false,
            lazyLoad: true,
            checkVisibility: true,

            mouseDrag: true,
            touchDrag: true,
            pullDrag: true,
            freeDrag: false,

            margin: 0,
            stagePadding: 0,

            merge: false,
            mergeFit: true,
            autoWidth: false,

            startPosition: 0,
            rtl: false,

            smartSpeed: 400,
            fluidSpeed: false,
            dragEndSpeed: false,

            responsive: {},
            responsiveRefreshRate: 200,
            responsiveBaseElement: window,

            fallbackEasing: 'swing',
            slideTransition: '',

            info: false,

            nestedItemSelector: false,
            itemElement: 'div',
            stageElement: 'div',

            refreshClass: 'owl-refresh',
            loadedClass: 'owl-loaded',
            loadingClass: 'owl-loading',
            rtlClass: 'owl-rtl',
            responsiveClass: 'owl-responsive',
            dragClass: 'owl-drag',
            itemClass: 'owl-item',
            stageClass: 'owl-stage',
            stageOuterClass: 'owl-stage-outer',
            grabClass: 'owl-grab',
            callbacks: true
        },

        _create: function () {
            this.options.onInitialized = this.owlchanged;
            this.options.onChanged = this.owlchanged;
            this.options.onRefreshed = this.owlchanged;
            this.element.owlCarousel(this.options);
            this.arrows();
            //this.owlchanged();
        },
        owlchanged: function (event) {
            var $wraper = this.$element.closest('.block.widget').eq(0);
            var current = this.current();
            $wraper.find('.ox-owl-nav').toggle(this.settings.items < this.items().length);
            var disable_nav_min = current === this.minimum();
            var disable_nav_max = current === this.maximum();
            if (this.settings.loop) {
                disable_nav_min = disable_nav_max = false;
            }
            $wraper.find('.ox-owl-next').toggleClass('disabled', disable_nav_max);
            $wraper.find('.ox-owl-prev').toggleClass('disabled', disable_nav_min);
        },
        arrows: function () {
            var $wraper = this.element.closest('.block.widget').eq(0);
            $wraper.on('click', '.ox-owl-next', $.proxy(function () {
                this.element.trigger('next.owl.carousel');
            }, this));
            $wraper.on('click', '.ox-owl-prev', $.proxy(function () {
                this.element.trigger('prev.owl.carousel');
            }, this));
        }
    });

    return $.mage.OXowlCarousel;
});
