'use strict';

(function ($) {
    var cuarMasterSkin = function () {

        // Variables
        // -
        var wrapperJS = '#cuar-js-content-container',
            wrapperCSS = '.cuar-css-wrapper',
            collectionContainerAnimationLength = 400,
            trayMinimumHeight = 400,

        // Stored Elements
        // -
            $wrapperJS = $(wrapperJS),
            $wrapperCSS = $(wrapperCSS),
            $collectionContainer = $('#cuar-js-collection-gallery', $wrapperJS), // mixitup container
            $collectionToList = $('#cuar-js-collection-to-list', $wrapperJS), // list view button
            $collectionToGrid = $('#cuar-js-collection-to-grid', $wrapperJS), // list view button
            $collectionFilterButtons = $('.cuar-js-collection-filters-buttons', $wrapperJS),

        // Helper Functions
        // -
            runHelpers = function () {

                // Disable element selection
                $.fn.disableSelection = function () {
                    return this
                        .attr('unselectable', 'on')
                        .css('user-select', 'none')
                        .on('selectstart', false);
                };

                // Find element scrollbar visibility
                $.fn.hasScrollBar = function () {
                    return this.get(0).scrollHeight > this.height();
                };

                // Test for IE, Add body class if version 9
                function msieversion() {
                    var ua = window.navigator.userAgent;
                    var msie = ua.indexOf("MSIE ");
                    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
                        var ieVersion = parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)));
                        if (ieVersion === 9) {
                            $('body').addClass('no-js ie' + ieVersion);
                        }
                        return ieVersion;
                    }
                    else {
                        return false;
                    }
                }

                msieversion();

                // Clean up helper that removes any leftover
                // animation classes on the primary content container
                // If left it can cause z-index and visibility problems
                /*
                 setTimeout(function () {
                 $('#content').removeClass('animated fadeIn');
                 }, 800);
                 */

            },

        // Header Functions
        // -
            runHeader = function () {

                // Nav Fluidify
                var fluidify = function () {
                    var container = $(".cuar-menu-container .nav-container > ul", $wrapperCSS);
                    var items = container.children('li');
                    var plusClass = 'menu-plus';
                    var plus = container.children('li.' + plusClass);
                    var clone = plus.find('#cuar-menu-plus-clone > li');
                    var count = items.length - 1;

                    items.not("." + plusClass).each(function (i) {

                        if(i == 0 || !items.eq(i-1).hasClass('just-hide')) {
                            $('> .dropdown-menu', items.eq(i)).addClass('dropdown-menu-right');
                            if(i > 0) {
                                $('> .dropdown-menu', items.eq(i - 1)).removeClass('dropdown-menu-right');
                            }
                        }

                        if (container.width() - plus.width() < $(this).offset().left + $(this).width() - container.offset().left) {
                            items.eq(i).addClass('just-hide');
                            clone.eq(i).removeClass('hidden');

                            if(i == items.length - 1 || !items.eq(i-1).hasClass('just-hide')) {
                                $('> .dropdown-menu', items.eq(i-1)).addClass('dropdown-menu-right');
                            }
                        } else {
                            items.eq(i).removeClass('just-hide');
                            clone.eq(i).addClass('hidden');
                        }
                        if (i == count - 1 && !$(this).hasClass('just-hide')) {
                            plus.addClass('just-hide');
                        } else {
                            plus.removeClass('just-hide');
                        }
                    });
                };

                $('.cuar-menu-container .nav-container > ul').append('<li class="menu-plus"><a href="#" data-toggle="dropdown" class="dropdown-toggle">+</a></li>').clone().appendTo('.cuar-menu-container .nav-container > ul > .menu-plus').attr('id', 'cuar-menu-plus-clone');
                $("#cuar-menu-plus-clone .dropdown").removeClass('dropdown').addClass('dropdown-submenu');
                $("#cuar-menu-plus-clone > li.menu-plus").addClass('hidden');
                $('.cuar-menu-container .nav-container > ul > .menu-plus > ul').removeClass().addClass('dropdown-menu dropdown-menu-right animated fadeIn');
                $('.cuar-menu-container .nav-container > ul > .menu-plus > a').addClass('dropdown-toggle').attr('data-toggle', 'dropdown');
                fluidify();

                $(window).on('resize', function () {
                    fluidify();
                });


                // Searchbar - Mobile modifcations
                $('.navbar-search').on('click', function (e) {
                    var This = $(this);
                    var searchForm = This.find('input');
                    var searchRemove = This.find('.search-remove');

                    // Don't do anything unless in mobile mode
                    if (!$('body.mobile-view').length) {
                        return;
                    }

                    // Open search bar and add closing icon if one isn't found
                    This.addClass('search-open');
                    if (!searchRemove.length) {
                        This.append('<div class="search-remove"></div>');
                    }

                    // Fadein remove btn and focus search input on animation complete
                    setTimeout(function () {
                        This.find('.search-remove').fadeIn();
                        searchForm.focus().one('keydown', function () {
                            $(this).val('');
                        });
                    }, 250);

                    // If remove icon clicked close search bar
                    if ($(e.target).attr('class') == 'search-remove') {
                        This.removeClass('search-open').find('.search-remove').remove();
                    }

                });
            },

        // Tray related Functions
        // -
            runTrays = function () {

                // Resize handler
                var rescale = function() {
                    if ($wrapperJS.width() < 1000) {
                        $('body').addClass('tray-rescale');
                    } else {
                        $('body').removeClass('tray-rescale tray-rescale-left tray-rescale-right');
                    }
                };

                // Debounced resize handler
                var lazyLayout = _.debounce(rescale, 300);

                // Apply needed classes
                if (!$('body').hasClass('disable-tray-rescale')) {
                    // Rescale on window resize
                    $(window).on('resize', lazyLayout);

                    // Rescale on load
                    rescale();
                }

                // Start Trays Engine directly or wait for Mixitup Collection to be initialized
                if ($collectionContainer.length) {
                    $wrapperJS.on('cuar:mixitup:initialized', traysEngine);
                } else {
                    traysEngine();
                }

                // Define the Trays Engine
                function traysEngine() {

                    // Match height of tray with the height of the tray center
                    var trayFormat = $('.tray-right, .tray-left', $wrapperJS);
                    if (trayFormat.length) {

                        // Loop each tray and set height to match tray center
                        // Never tested with 2 side trays on a same page yet
                        trayFormat.each(function (i, e) {

                            // Store Elements
                            var This = $(e);
                            var trayCenter = This.parent().find('.tray-center');
                            var heightEls = null;
                            var trayHeight = null;
                            var trayScroll = This.find('.tray-scroller');
                            var trayScrollContent = trayScroll.find('.scroller-content');

                            // Define the tray height depending on html data attributes if they exist
                            if (This.attr('data-tray-height-substract') && This.attr('data-tray-height-base')) {
                                var heightBase = 'window' ? $(window).height() : $(This.data('tray-height-base')).innerHeight();
                                var heightSubstract = This.data('tray-height-substract').split(',');
                                for (i = 0; i < heightSubstract.length; i++) {
                                    heightEls = heightEls + $(heightSubstract[i]).outerHeight(true);
                                }
                                trayHeight = heightBase - heightEls;

                            } else {

                                // If html data attributes are missing, tray height should be the same as the content height
                                trayHeight = trayCenter.height();

                                // But do not let it be too small
                                trayHeight = (trayHeight < trayMinimumHeight) ? trayMinimumHeight : trayHeight;

                            }

                            // Helper to not let the tray be too small
                            if (This.attr('data-tray-height-minimum')) {
                                trayHeight = (trayHeight < This.attr('data-tray-height-minimum')) ? This.attr('data-tray-height-minimum') : trayHeight;
                            } else {
                                trayHeight = (trayHeight < trayMinimumHeight) ? trayMinimumHeight : trayHeight;
                            }

                            // Define the new Tray height depending on data-attributes or trayCenter height
                            var trayNewHeight = trayHeight - (This.outerHeight(true) - This.innerHeight());
                            This.height(trayNewHeight);
                            trayCenter.height(trayHeight - 75); // 75 = trayCenter padding-top + padding-bottom

                            if (trayScroll.length) {

                                if (trayCenter.innerHeight() >= trayScrollContent.height()) {

                                    // Content is taller than tray inner content
                                    trayScroll.height(This.outerHeight());

                                } else {

                                    // Content is smaller than tray inner content
                                    trayScroll.height(trayHeight - (trayScroll.outerHeight(true) - trayScroll.innerHeight()));

                                }
                                trayScroll.scroller();

                                // Scroll lock all fixed content overflow
                                // Disabled annoying feature
                                // $('.cuar-page-content').scrollLock('on', 'div');

                            } else {
                                // No scroller found, Set the tray and content height
                                // Set the content height
                                    trayCenter.height(trayHeight - 75); // 75 = trayCenter padding-top + padding-bottom

                            }
                        });

                    }

                    // Perform a custom animation if tray-nav has data attribute
                    /*
                    var navAnimate = $('.tray-nav[data-nav-animate]');
                    if (navAnimate.length) {
                        var Animation = navAnimate.data('nav-animate');

                        // Set default "fadeIn" animation if one has not been previously set
                        if (Animation == null || Animation == true || Animation == "") {
                            Animation = "fadeIn";
                        }

                        // Loop through each li item and add animation after set timeout
                        setTimeout(function () {
                            navAnimate.find('li').each(function (i, e) {
                                var Timer = setTimeout(function () {
                                    $(e).addClass('animated animated-short ' + Animation);
                                }, 50 * i);
                            });
                        }, 500);
                    }*/

                    // Responsive Tray Javascript Data Helper. If browser window
                    // is <575px wide (extreme mobile) we relocate the tray left/right
                    // content into the element appointed by the user/data attr
                    var dataTray = $('.tray[data-tray-mobile]');

                    // noinspection JSValidateTypes
                    var dataAppend = dataTray.children();

                    function fcRefresh() {
                        if ($('#cuar-js-content-container').innerWidth() < 550) {
                            dataAppend.appendTo($(dataTray.data('tray-mobile')));
                        }
                        else {
                            dataAppend.appendTo(dataTray);
                        }
                    }

                    fcRefresh();

                    // Attach debounced resize handler
                    var fcResize = function () {
                        fcRefresh();
                    };
                    var fcLayout = _.debounce(fcResize, 300);
                    $(window).resize(fcLayout);

                }

            },

        // Form related Functions
        // -
            runFormElements = function () {

                // Init select2
                if ($.isFunction($.fn.select2)) {
                    $('.cuar-js-select-single', $wrapperCSS).each(function () {
                        $(this).addClass('select2-single').select2({
                            dropdownParent: $(this).parent(),
                            width: '100%',
                            minimumResultsForSearch: -1
                        });
                    });
                }

                // Init Bootstrap tooltips, if present
                if ($.isFunction($.fn.tooltip)) {
                    var Tooltips = $("[data-toggle=tooltip]", $wrapperCSS);
                    if (Tooltips.length) {
                        if (Tooltips.parents('#sidebar_left')) {
                            Tooltips.tooltip({
                                container: $('body'),
                                template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                            });
                        } else {
                            Tooltips.tooltip({container: $wrapperJS});
                        }
                    }
                }

                // Init Bootstrap Popovers, if present
                if ($.isFunction($.fn.popover)) {
                    var Popovers = $("[data-toggle=popover]", $wrapperJS);
                    if (Popovers.length) {
                        Popovers.popover({container: wrapperJS});
                    }
                }

                // Init Bootstrap persistent tooltips. This prevents a
                // popup from closing if a checkbox it contains is clicked
                $('.dropdown-menu.dropdown-persist', $wrapperCSS).on('click', function (e) {
                    e.stopPropagation();
                });

                // Prevents a dropdown menu from closing when
                // a nav-tabs menu it contains is clicked
                $('.dropdown-menu .nav-tabs li a', $wrapperCSS).on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).tab('show')
                });

                // Prevents a dropdown menu from closing when
                // a btn-group nav menu it contains is clicked
                $('.dropdown-menu .btn-group-nav a', $wrapperCSS).on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Remove active class from btn-group > btns and toggle tab content
                    $(this).siblings('a').removeClass('active').end().addClass('active').tab('show');
                });

                // if btn has ".btn-states" class we monitor it for user clicks. On Click we remove
                // the active class from its siblings and give it to the button clicked.
                // This gives the button set a menu like feel or state
                var btnStates = $('.btn-states', $wrapperCSS);
                if (btnStates.length) {
                    btnStates.on('click', function () {
                        $(this).addClass('active').siblings().removeClass('active');
                    });
                }

                // Init smoothscroll on elements with set data attr
                // data value determines smoothscroll offset
                if ($.isFunction($.fn.smoothScroll)) {
                    var SmoothScroll = $('[data-smoothscroll]', $wrapperCSS);
                    if (SmoothScroll.length) {
                        SmoothScroll.each(function (i, e) {
                            var This = $(e);
                            var Offset = This.data('smoothscroll');
                            var Links = This.find('a');

                            // Init Smoothscroll with data stored offset
                            Links.smoothScroll({
                                offset: Offset
                            });

                        });
                    }
                }

                // Responsive JS Slider
                if ($.isFunction($.fn.slick)) {
                    var slickSlider = $('.cuar-js-slick-responsive', $wrapperJS);
                    if (slickSlider.length) {
                        slickSlider.slick({
                            autoplay: false,
                            centerMode: true,
                            dots: true,
                            infinite: true,
                            respondTo: 'slider',
                            speed: 300,
                            slidesToShow: 4,
                            slidesToScroll: 4,
                            responsive: [{
                                breakpoint: 1024,
                                settings: {
                                    slidesToShow: 3,
                                    slidesToScroll: 3,
                                    infinite: true,
                                    dots: true
                                }
                            }, {
                                breakpoint: 600,
                                settings: {
                                    slidesToShow: 2,
                                    slidesToScroll: 2
                                }
                            }, {
                                breakpoint: 480,
                                settings: {
                                    slidesToShow: 1,
                                    slidesToScroll: 1
                                }
                            }]
                        });
                    }
                }

            },

        // Collections
        // -
            runCollection = function () {

                if ($collectionContainer.length > 0) {

                    // Init multiselect plugin on filter dropdowns
                    if($.fn.multiselect) {
                        $collectionFilterButtons.multiselect({
                            buttonClass: 'btn btn-default'
                        });
                    }

                    // Initiate cookie session for filters buttons
                    var cookieName = $collectionContainer.data('type') + '-collection-layout';
                    var cookieLayout = Cookies.get(cookieName);
                    if (cookieLayout != 'list' && cookieLayout != 'grid') {
                        if ($collectionContainer.data('collection-layout') != null) {
                            cookieLayout = $collectionContainer.data('collection-layout');
                        } else {
                            cookieLayout = 'grid';
                        }
                    }

                    if (cookieLayout == 'list') {
                        $collectionContainer.addClass(cookieLayout).removeClass('grid');
                        $collectionToList.addClass('btn-primary').removeClass('btn-default');
                        $collectionToGrid.addClass('btn-default').removeClass('btn-primary');
                    } else {
                        $collectionContainer.addClass(cookieLayout).removeClass('list');
                        $collectionToList.addClass('btn-default').removeClass('btn-primary');
                        $collectionToGrid.addClass('btn-primary').removeClass('btn-default');
                    }

                    // Instantiate MixItUp
                    $collectionContainer.mixItUp({
                        controls: {
                            enable: false // we won't be needing these
                        },
                        animation: {
                            duration: collectionContainerAnimationLength,
                            effects: 'fade translateZ(-360px) stagger(45ms)',
                            easing: 'ease'
                        },
                        callbacks: {
                            onMixEnd: function() {
                                setTimeout(function () {
                                    $wrapperJS.trigger('cuar:mixitup:initialized');
                                }, (collectionContainerAnimationLength+100));
                            }
                        }
                    });

                    // Bind layout mode buttons
                    $collectionToList.on('click', function () {
                        Cookies.set(cookieName, 'list');
                        $(this).addClass('btn-primary').siblings('.btn').addClass('btn-default').removeClass('btn-primary');
                        if ($collectionContainer.hasClass('list')) {
                            return
                        }
                        $collectionContainer.mixItUp('changeLayout', {
                            display: 'block',
                            containerClass: 'list'
                        }, function (state) {
                            $collectionContainer.removeClass('grid');
                        });
                    });
                    $collectionToGrid.on('click', function () {
                        Cookies.set(cookieName, 'grid');
                        $(this).addClass('btn-primary').siblings('.btn').addClass('btn-default').removeClass('btn-primary');
                        if ($collectionContainer.hasClass('grid')) {
                            return
                        }
                        $collectionContainer.mixItUp('changeLayout', {
                            display: 'inline-block',
                            containerClass: 'grid'
                        }, function (state) {
                            $collectionContainer.removeClass('list');
                        });
                    });
                }
            };

        return {
            init: function () {
                runHelpers();
                runHeader();
                runFormElements();
                runTrays();
                runCollection();
            }

        }
    }();


    $(document).ready(function () {

        "use strict";

        // Init Theme cuarMasterSkin
        cuarMasterSkin.init();

    });


})(jQuery);

