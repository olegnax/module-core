define([
    'jquery',
    'jquery-ui-modules/widget',
    'jquery-ui-modules/core',
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
            wrapperSelectorClass: '.block.widget',

            callbacks: true,
            observerLoad: !(window.hasOwnProperty('OX_OWL_OBS_DISABLE') ? OX_OWL_OBS_DISABLE : false)
        },

        _create: function () {
            this.options.onInitialized = this.owlchanged;
            this.options.onChanged = this.owlchanged;
            this.options.onRefreshed = this.owlchanged;
            if (this.options.observerLoad) {
                this._loadObserver();
            } else {
                this._loadOwl();
            }
        },
        _loadObserver: function () {
            let _self = this;
            if (window.IntersectionObserver) {
                let observer = new IntersectionObserver(function (entries, observer) {
                    Array.prototype.forEach.call(entries, function (entry) {
                        if (entry.isIntersecting) {
                            _self._loadOwl.call(_self);
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    root: null,
                    rootMargin: "0px",
                    threshold: [0]
                });
                this.element.each(function (i, el) {
                    observer.observe(el);
                });
            } else {
                let debouncer = function (func) {
                    var timeoutID;
                    return function () {
                        var scope = this,
                            args = arguments;
                        clearTimeout(timeoutID);
                        timeoutID = setTimeout(function () {
                            func.apply(scope, Array.prototype.slice.call(args));
                        }, 200);
                    }
                };
                $(window).one('scroll resize', debouncer(function () {
                    let height = window.visualViewport ? window.visualViewport.height : window.innerHeight;
                    this.element.each(function (i, el) {
                        let bounding = el.getBoundingClientRect();
                        if (bounding.top != bounding.bottom && 0 < bounding.bottom && height > bounding.top) {
                            _self._loadOwl.call(_self);
                        }
                    })
                }));
            }
        },
        _loadOwl: function () {
            this.element.find('picture.owl-lazy').removeClass('owl-lazy');
            this.element.owlCarousel(this.options);
            this.arrows();
        },
        owlchanged: function (event) {
            var $wraper = this.$element.closest(this.options.wrapperSelectorClass).eq(0);
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
            var $wraper = this.element.closest(this.options.wrapperSelectorClass).eq(0);
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
