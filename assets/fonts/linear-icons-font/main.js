(self["webpackChunk"] = self["webpackChunk"] || []).push([["main"],{

/***/ "./assets/dist/main.js":
/*!*****************************!*\
  !*** ./assets/dist/main.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

__webpack_require__(/*! core-js/modules/web.timers.js */ "./node_modules/core-js/modules/web.timers.js");
/*
* Template Name: Leven - Resume / CV / vCard Template
* Author: lmpixels
* Author URL: http://themeforest.net/user/lmpixels
* Version: 1.5.0
*/

(function ($) {
  "use strict";

  var body = $('body');
  function imageCarousel() {
    $('.portfolio-page-carousel').each(function () {
      $(this).imagesLoaded(function () {
        $('.portfolio-page-carousel').owlCarousel({
          smartSpeed: 1200,
          items: 1,
          loop: true,
          dots: true,
          nav: true,
          navText: false,
          autoHeight: true,
          margin: 10
        });
      });
    });
  }

  // Portfolio subpage filters
  function portfolio_init() {
    $('.portfolio-content').each(function () {
      var portfolio_grid_container = $(this),
        portfolio_grid_container_id = $(this).attr('id'),
        portfolio_grid = $('#' + portfolio_grid_container_id + ' .portfolio-grid'),
        portfolio_filter = $('#' + portfolio_grid_container_id + ' .portfolio-filters'),
        portfolio_filter_item = $('#' + portfolio_grid_container_id + ' .portfolio-filters .filter');
      if (portfolio_grid) {
        portfolio_grid.shuffle({
          speed: 450,
          itemSelector: 'figure'
        });
        $('.site-auto-menu').on("click", "a", function (e) {
          portfolio_grid.shuffle('update');
        });
        portfolio_filter.on("click", ".filter", function (e) {
          portfolio_grid.shuffle('update');
          e.preventDefault();
          portfolio_filter_item.parent().removeClass('active');
          $(this).parent().addClass('active');
          portfolio_grid.shuffle('shuffle', $(this).attr('data-group'));
        });
      }
    });
  }
  // /Portfolio subpage filters

  // Animate layout
  function animateLayout() {
    var windowWidth = $(window).width(),
      animatedContainer = '',
      animateType = $('#page_container').attr('data-animation');
    if (windowWidth > 991) {
      animatedContainer = $(".page-container");
    } else {
      animatedContainer = $(".site-main");
    }
    animatedContainer.addClass("animated " + animateType);
    $('.page-scroll').addClass('add-prespective');
    animatedContainer.addClass('transform3d');
    setTimeout(function () {
      $('.page-scroll').removeClass('add-prespective');
      animatedContainer.removeClass('transform3d');
    }, 1000);
  }
  // /Animate layout

  function scrollTop() {
    if ($(body).scrollTop() > 150) {
      $('.lmpixels-scroll-to-top').removeClass('hidden-btn');
    } else {
      $('.lmpixels-scroll-to-top').addClass('hidden-btn');
    }
  }
  function skillsStyles() {
    var custom_styles = "";
    $('.skill-container').each(function () {
      var value = $(this).attr('data-value');
      if (value >= 101) {
        value = '100';
      }
      if (typeof value != 'undefined') {
        var id = $(this).attr('id'),
          $custom_style = '#' + id + ' .skill-percentage { width: ' + value + '%; } ';
        custom_styles += $custom_style;
      }
    });
    $('head').append('<style data-styles="leven-theme-skills-css" type="text/css">' + custom_styles + '</style>');
  }

  // On Document Load
  $(document).ready(function () {
    var movementStrength = 15;
    var height = movementStrength / $(document).height();
    var width = movementStrength / $(document).width();
    $("body").on('mousemove', function (e) {
      var pageX = e.pageX - $(document).width() / 2,
        pageY = e.pageY - $(document).height() / 2,
        newvalueX = width * pageX * -1,
        newvalueY = height * pageY * -1;
      if ($('.page-container').hasClass('bg-move-effect')) {
        var elements = $('.home-photo .hp-inner:not(.without-move), .lm-animated-bg');
      } else {
        var elements = $('.home-photo .hp-inner:not(.without-move)');
      }
      elements.addClass('transition');
      elements.css({
        "background-position": "calc( 50% + " + newvalueX + "px ) calc( 50% + " + newvalueY + "px )"
      });
      setTimeout(function () {
        elements.removeClass('transition');
      }, 300);
    }).scroll(function () {
      scrollTop();
    });

    // Initialize Portfolio grid
    var $portfolio_container = $(".portfolio-grid"),
      $gallery_container = $("#portfolio-gallery-grid");
    $gallery_container.imagesLoaded(function () {
      $gallery_container.masonry();
    });
    $portfolio_container.imagesLoaded(function () {
      portfolio_init(this);
    });
    imageCarousel();

    // Blog grid init
    var $container = $(".blog-masonry");
    $container.imagesLoaded(function () {
      $container.masonry({
        itemSelector: '.item',
        resize: false
      });
    });

    // Mobile menu
    $('.menu-toggle').on("click", function () {
      $('.site-nav').addClass('animate');
      $('.site-nav').toggleClass('mobile-menu-hide');
    });

    // Text rotation
    $('.text-rotation').owlCarousel({
      loop: true,
      dots: false,
      nav: false,
      margin: 10,
      items: 1,
      autoplay: true,
      autoplayHoverPause: false,
      autoplayTimeout: 3800,
      animateOut: 'fadeOut',
      animateIn: 'fadeIn'
    });

    // Testimonials Slider
    $(".testimonials.owl-carousel").owlCarousel({
      nav: false,
      // Show next/prev buttons.
      items: 3,
      // The number of items you want to see on the screen.
      loop: false,
      // Infinity loop. Duplicate last and first items to get loop illusion.
      navText: false,
      margin: 25,
      responsive: {
        // breakpoint from 0 up
        0: {
          items: 1
        },
        // breakpoint from 480 up
        480: {
          items: 1
        },
        // breakpoint from 768 up
        768: {
          items: 2
        },
        1200: {
          items: 2
        }
      }
    });

    // Clients Slider
    $(".clients.owl-carousel").imagesLoaded().owlCarousel({
      nav: false,
      // Show next/prev buttons.
      items: 2,
      // The number of items you want to see on the screen.
      loop: false,
      // Infinity loop. Duplicate last and first items to get loop illusion.
      navText: false,
      margin: 10,
      autoHeight: false,
      responsive: {
        // breakpoint from 0 up
        0: {
          items: 2
        },
        // breakpoint from 768 up
        768: {
          items: 4
        },
        1200: {
          items: 6
        }
      }
    });

    // Lightbox init
    body.magnificPopup({
      fixedContentPos: false,
      delegate: 'a.lightbox',
      type: 'image',
      removalDelay: 300,
      // Class that is added to popup wrapper and background
      // make it unique to apply your CSS animations just to this exact popup
      mainClass: 'mfp-fade',
      image: {
        // options for image content type
        titleSrc: 'title',
        gallery: {
          enabled: true
        }
      },
      iframe: {
        markup: '<div class="mfp-iframe-scaler">' + '<div class="mfp-close"></div>' + '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' + '<div class="mfp-title mfp-bottom-iframe-title"></div>' + '</div>',
        // HTML markup of popup, `mfp-close` will be replaced by the close button

        patterns: {
          youtube: {
            index: 'youtube.com/',
            // String that detects type of video (in this case YouTube). Simply via url.indexOf(index).

            id: null,
            // String that splits URL in a two parts, second part should be %id%
            // Or null - full URL will be returned
            // Or a function that should return %id%, for example:
            // id: function(url) { return 'parsed id'; }

            src: '%id%?autoplay=1' // URL that will be set as a source for iframe.
          },

          vimeo: {
            index: 'vimeo.com/',
            id: '/',
            src: '//player.vimeo.com/video/%id%?autoplay=1'
          },
          gmaps: {
            index: '//maps.google.',
            src: '%id%&output=embed'
          }
        },
        srcAction: 'iframe_src' // Templating object key. First part defines CSS selector, second attribute. "iframe_src" means: find "iframe" and set attribute "src".
      },

      callbacks: {
        markupParse: function markupParse(template, values, item) {
          values.title = item.el.attr('title');
        }
      }
    });
    $('.ajax-page-load-link').magnificPopup({
      type: 'ajax',
      removalDelay: 300,
      mainClass: 'mfp-fade',
      gallery: {
        enabled: true
      }
    });
    $('.portfolio-page-wrapper .portfolio-grid').each(function () {
      $(this).magnificPopup({
        delegate: 'a.gallery-lightbox',
        type: 'image',
        gallery: {
          enabled: true
        }
      });
    });

    /*$('.form-control').val('');*/

    // Sidebar toggle
    $('.sidebar-toggle').on("click", function () {
      $('#blog-sidebar').toggleClass('open');
      $(this).toggleClass('open');
    });
    $('.lmpixels-scroll-to-top').click(function () {
      $('body,html').animate({
        scrollTop: 0
      }, 400);
      return false;
    });
    scrollTop();
    skillsStyles();
  });
})(jQuery);

/***/ }),

/***/ "./node_modules/core-js/internals/engine-is-bun.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/internals/engine-is-bun.js ***!
  \*********************************************************/
/***/ ((module) => {

/* global Bun -- Deno case */
module.exports = typeof Bun == 'function' && Bun && typeof Bun.version == 'string';


/***/ }),

/***/ "./node_modules/core-js/internals/schedulers-fix.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/schedulers-fix.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var global = __webpack_require__(/*! ../internals/global */ "./node_modules/core-js/internals/global.js");
var apply = __webpack_require__(/*! ../internals/function-apply */ "./node_modules/core-js/internals/function-apply.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var ENGINE_IS_BUN = __webpack_require__(/*! ../internals/engine-is-bun */ "./node_modules/core-js/internals/engine-is-bun.js");
var USER_AGENT = __webpack_require__(/*! ../internals/engine-user-agent */ "./node_modules/core-js/internals/engine-user-agent.js");
var arraySlice = __webpack_require__(/*! ../internals/array-slice */ "./node_modules/core-js/internals/array-slice.js");
var validateArgumentsLength = __webpack_require__(/*! ../internals/validate-arguments-length */ "./node_modules/core-js/internals/validate-arguments-length.js");

var Function = global.Function;
// dirty IE9- and Bun 0.3.0- checks
var WRAP = /MSIE .\./.test(USER_AGENT) || ENGINE_IS_BUN && (function () {
  var version = global.Bun.version.split('.');
  return version.length < 3 || version[0] == 0 && (version[1] < 3 || version[1] == 3 && version[2] == 0);
})();

// IE9- / Bun 0.3.0- setTimeout / setInterval / setImmediate additional parameters fix
// https://html.spec.whatwg.org/multipage/timers-and-user-prompts.html#timers
// https://github.com/oven-sh/bun/issues/1633
module.exports = function (scheduler, hasTimeArg) {
  var firstParamIndex = hasTimeArg ? 2 : 1;
  return WRAP ? function (handler, timeout /* , ...arguments */) {
    var boundArgs = validateArgumentsLength(arguments.length, 1) > firstParamIndex;
    var fn = isCallable(handler) ? handler : Function(handler);
    var params = boundArgs ? arraySlice(arguments, firstParamIndex) : [];
    var callback = boundArgs ? function () {
      apply(fn, this, params);
    } : fn;
    return hasTimeArg ? scheduler(callback, timeout) : scheduler(callback);
  } : scheduler;
};


/***/ }),

/***/ "./node_modules/core-js/internals/validate-arguments-length.js":
/*!*********************************************************************!*\
  !*** ./node_modules/core-js/internals/validate-arguments-length.js ***!
  \*********************************************************************/
/***/ ((module) => {

var $TypeError = TypeError;

module.exports = function (passed, required) {
  if (passed < required) throw $TypeError('Not enough arguments');
  return passed;
};


/***/ }),

/***/ "./node_modules/core-js/modules/web.set-interval.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/modules/web.set-interval.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

var $ = __webpack_require__(/*! ../internals/export */ "./node_modules/core-js/internals/export.js");
var global = __webpack_require__(/*! ../internals/global */ "./node_modules/core-js/internals/global.js");
var schedulersFix = __webpack_require__(/*! ../internals/schedulers-fix */ "./node_modules/core-js/internals/schedulers-fix.js");

var setInterval = schedulersFix(global.setInterval, true);

// Bun / IE9- setInterval additional parameters fix
// https://html.spec.whatwg.org/multipage/timers-and-user-prompts.html#dom-setinterval
$({ global: true, bind: true, forced: global.setInterval !== setInterval }, {
  setInterval: setInterval
});


/***/ }),

/***/ "./node_modules/core-js/modules/web.set-timeout.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/modules/web.set-timeout.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

var $ = __webpack_require__(/*! ../internals/export */ "./node_modules/core-js/internals/export.js");
var global = __webpack_require__(/*! ../internals/global */ "./node_modules/core-js/internals/global.js");
var schedulersFix = __webpack_require__(/*! ../internals/schedulers-fix */ "./node_modules/core-js/internals/schedulers-fix.js");

var setTimeout = schedulersFix(global.setTimeout, true);

// Bun / IE9- setTimeout additional parameters fix
// https://html.spec.whatwg.org/multipage/timers-and-user-prompts.html#dom-settimeout
$({ global: true, bind: true, forced: global.setTimeout !== setTimeout }, {
  setTimeout: setTimeout
});


/***/ }),

/***/ "./node_modules/core-js/modules/web.timers.js":
/*!****************************************************!*\
  !*** ./node_modules/core-js/modules/web.timers.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// TODO: Remove this module from `core-js@4` since it's split to modules listed below
__webpack_require__(/*! ../modules/web.set-interval */ "./node_modules/core-js/modules/web.set-interval.js");
__webpack_require__(/*! ../modules/web.set-timeout */ "./node_modules/core-js/modules/web.set-timeout.js");


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_internals_array-slice_js-node_modules_core-js_internals_export_j-81953d"], () => (__webpack_exec__("./assets/dist/main.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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QUNWRDtBQUNBLG1CQUFPLENBQUMsdUZBQTZCO0FBQ3JDLG1CQUFPLENBQUMscUZBQTRCIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2Rpc3QvbWFpbi5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9pbnRlcm5hbHMvZW5naW5lLWlzLWJ1bi5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9pbnRlcm5hbHMvc2NoZWR1bGVycy1maXguanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvcmUtanMvaW50ZXJuYWxzL3ZhbGlkYXRlLWFyZ3VtZW50cy1sZW5ndGguanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvcmUtanMvbW9kdWxlcy93ZWIuc2V0LWludGVydmFsLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9jb3JlLWpzL21vZHVsZXMvd2ViLnNldC10aW1lb3V0LmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9jb3JlLWpzL21vZHVsZXMvd2ViLnRpbWVycy5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKlxyXG4qIFRlbXBsYXRlIE5hbWU6IExldmVuIC0gUmVzdW1lIC8gQ1YgLyB2Q2FyZCBUZW1wbGF0ZVxyXG4qIEF1dGhvcjogbG1waXhlbHNcclxuKiBBdXRob3IgVVJMOiBodHRwOi8vdGhlbWVmb3Jlc3QubmV0L3VzZXIvbG1waXhlbHNcclxuKiBWZXJzaW9uOiAxLjUuMFxyXG4qL1xyXG5cclxuKGZ1bmN0aW9uKCQpIHtcclxuXCJ1c2Ugc3RyaWN0XCI7XHJcbiAgICB2YXIgYm9keSA9ICQoJ2JvZHknKTtcclxuXHJcbiAgICBmdW5jdGlvbiBpbWFnZUNhcm91c2VsKCkge1xyXG4gICAgICAgICQoJy5wb3J0Zm9saW8tcGFnZS1jYXJvdXNlbCcpLmVhY2goZnVuY3Rpb24oKSB7XHJcbiAgICAgICAgICAgICQodGhpcykuaW1hZ2VzTG9hZGVkKGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgICQoJy5wb3J0Zm9saW8tcGFnZS1jYXJvdXNlbCcpLm93bENhcm91c2VsKHtcclxuICAgICAgICAgICAgICAgICAgICBzbWFydFNwZWVkOjEyMDAsXHJcbiAgICAgICAgICAgICAgICAgICAgaXRlbXM6IDEsXHJcbiAgICAgICAgICAgICAgICAgICAgbG9vcDogdHJ1ZSxcclxuICAgICAgICAgICAgICAgICAgICBkb3RzOiB0cnVlLFxyXG4gICAgICAgICAgICAgICAgICAgIG5hdjogdHJ1ZSxcclxuICAgICAgICAgICAgICAgICAgICBuYXZUZXh0OiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICBhdXRvSGVpZ2h0OiB0cnVlLFxyXG4gICAgICAgICAgICAgICAgICAgIG1hcmdpbjogMTBcclxuICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9KTtcclxuICAgIH1cclxuXHJcbiAgICAvLyBQb3J0Zm9saW8gc3VicGFnZSBmaWx0ZXJzXHJcbiAgICBmdW5jdGlvbiBwb3J0Zm9saW9faW5pdCgpIHtcclxuICAgICAgICAkKCAnLnBvcnRmb2xpby1jb250ZW50JyApLmVhY2goIGZ1bmN0aW9uKCkge1xyXG4gICAgICAgICAgICB2YXIgcG9ydGZvbGlvX2dyaWRfY29udGFpbmVyID0gJCh0aGlzKSxcclxuICAgICAgICAgICAgICAgIHBvcnRmb2xpb19ncmlkX2NvbnRhaW5lcl9pZCA9ICQodGhpcykuYXR0cignaWQnKSxcclxuICAgICAgICAgICAgICAgIHBvcnRmb2xpb19ncmlkID0gJCgnIycgKyBwb3J0Zm9saW9fZ3JpZF9jb250YWluZXJfaWQgKyAnIC5wb3J0Zm9saW8tZ3JpZCcpLFxyXG4gICAgICAgICAgICAgICAgcG9ydGZvbGlvX2ZpbHRlciA9ICQoJyMnICsgcG9ydGZvbGlvX2dyaWRfY29udGFpbmVyX2lkICsgJyAucG9ydGZvbGlvLWZpbHRlcnMnKSxcclxuICAgICAgICAgICAgICAgIHBvcnRmb2xpb19maWx0ZXJfaXRlbSA9ICQoJyMnICsgcG9ydGZvbGlvX2dyaWRfY29udGFpbmVyX2lkICsgJyAucG9ydGZvbGlvLWZpbHRlcnMgLmZpbHRlcicpO1xyXG4gICAgICAgICAgICAgICAgXHJcbiAgICAgICAgICAgIGlmIChwb3J0Zm9saW9fZ3JpZCkge1xyXG5cclxuICAgICAgICAgICAgICAgIHBvcnRmb2xpb19ncmlkLnNodWZmbGUoe1xyXG4gICAgICAgICAgICAgICAgICAgIHNwZWVkOiA0NTAsXHJcbiAgICAgICAgICAgICAgICAgICAgaXRlbVNlbGVjdG9yOiAnZmlndXJlJ1xyXG4gICAgICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgJCgnLnNpdGUtYXV0by1tZW51Jykub24oXCJjbGlja1wiLCBcImFcIiwgZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICBwb3J0Zm9saW9fZ3JpZC5zaHVmZmxlKCd1cGRhdGUnKTtcclxuICAgICAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgICAgIHBvcnRmb2xpb19maWx0ZXIub24oXCJjbGlja1wiLCBcIi5maWx0ZXJcIiwgZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICBwb3J0Zm9saW9fZ3JpZC5zaHVmZmxlKCd1cGRhdGUnKTtcclxuICAgICAgICAgICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XHJcbiAgICAgICAgICAgICAgICAgICAgcG9ydGZvbGlvX2ZpbHRlcl9pdGVtLnBhcmVudCgpLnJlbW92ZUNsYXNzKCdhY3RpdmUnKTtcclxuICAgICAgICAgICAgICAgICAgICAkKHRoaXMpLnBhcmVudCgpLmFkZENsYXNzKCdhY3RpdmUnKTtcclxuICAgICAgICAgICAgICAgICAgICBwb3J0Zm9saW9fZ3JpZC5zaHVmZmxlKCdzaHVmZmxlJywgJCh0aGlzKS5hdHRyKCdkYXRhLWdyb3VwJykgKTtcclxuICAgICAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0pXHJcbiAgICB9XHJcbiAgICAvLyAvUG9ydGZvbGlvIHN1YnBhZ2UgZmlsdGVyc1xyXG5cclxuICAgIC8vIEFuaW1hdGUgbGF5b3V0XHJcbiAgICBmdW5jdGlvbiBhbmltYXRlTGF5b3V0KCkge1xyXG4gICAgICAgIHZhciB3aW5kb3dXaWR0aCA9ICQod2luZG93KS53aWR0aCgpLFxyXG4gICAgICAgICAgICBhbmltYXRlZENvbnRhaW5lciA9ICcnLFxyXG4gICAgICAgICAgICBhbmltYXRlVHlwZSA9ICQoJyNwYWdlX2NvbnRhaW5lcicpLmF0dHIoJ2RhdGEtYW5pbWF0aW9uJylcclxuXHJcbiAgICAgICAgaWYgKHdpbmRvd1dpZHRoID4gOTkxKSB7XHJcbiAgICAgICAgICAgIGFuaW1hdGVkQ29udGFpbmVyID0gJChcIi5wYWdlLWNvbnRhaW5lclwiKTtcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICBhbmltYXRlZENvbnRhaW5lciA9ICQoXCIuc2l0ZS1tYWluXCIpO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgYW5pbWF0ZWRDb250YWluZXIuYWRkQ2xhc3MoXCJhbmltYXRlZCBcIiArIGFuaW1hdGVUeXBlKTtcclxuICAgICAgICAkKCcucGFnZS1zY3JvbGwnKS5hZGRDbGFzcygnYWRkLXByZXNwZWN0aXZlJyk7XHJcbiAgICAgICAgYW5pbWF0ZWRDb250YWluZXIuYWRkQ2xhc3MoJ3RyYW5zZm9ybTNkJyk7XHJcbiAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcclxuICAgICAgICAgICAgJCgnLnBhZ2Utc2Nyb2xsJykucmVtb3ZlQ2xhc3MoJ2FkZC1wcmVzcGVjdGl2ZScpO1xyXG4gICAgICAgICAgICBhbmltYXRlZENvbnRhaW5lci5yZW1vdmVDbGFzcygndHJhbnNmb3JtM2QnKTtcclxuICAgICAgICB9LCAxMDAwKTtcclxuICAgIH1cclxuICAgIC8vIC9BbmltYXRlIGxheW91dFxyXG5cclxuICAgIGZ1bmN0aW9uIHNjcm9sbFRvcCgpIHtcclxuICAgICAgICBpZiAoJChib2R5KS5zY3JvbGxUb3AoKSA+IDE1MCkge1xyXG4gICAgICAgICAgICAkKCcubG1waXhlbHMtc2Nyb2xsLXRvLXRvcCcpLnJlbW92ZUNsYXNzKCdoaWRkZW4tYnRuJyk7XHJcbiAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgJCgnLmxtcGl4ZWxzLXNjcm9sbC10by10b3AnKS5hZGRDbGFzcygnaGlkZGVuLWJ0bicpO1xyXG4gICAgICAgIH1cclxuICAgIH1cclxuXHJcbiAgICBmdW5jdGlvbiBza2lsbHNTdHlsZXMoKSB7XHJcbiAgICAgICAgdmFyIGN1c3RvbV9zdHlsZXMgPSBcIlwiO1xyXG4gICAgICAgICQoICcuc2tpbGwtY29udGFpbmVyJyApLmVhY2goIGZ1bmN0aW9uKCkge1xyXG4gICAgICAgICAgICB2YXIgdmFsdWUgPSAkKHRoaXMpLmF0dHIoJ2RhdGEtdmFsdWUnKTtcclxuXHJcbiAgICAgICAgICAgIGlmKCB2YWx1ZSA+PSAxMDEpIHtcclxuICAgICAgICAgICAgICAgIHZhbHVlID0gJzEwMCc7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIGlmKCB0eXBlb2YgdmFsdWUgIT0gJ3VuZGVmaW5lZCcgKSB7XHJcbiAgICAgICAgICAgICAgICB2YXIgaWQgPSAkKHRoaXMpLmF0dHIoJ2lkJyksXHJcbiAgICAgICAgICAgICAgICAkY3VzdG9tX3N0eWxlID0gJyMnICsgaWQgKyAnIC5za2lsbC1wZXJjZW50YWdlIHsgd2lkdGg6ICcgKyB2YWx1ZSArICclOyB9ICc7XHJcbiAgICAgICAgICAgICAgICBjdXN0b21fc3R5bGVzICs9ICRjdXN0b21fc3R5bGU7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KTtcclxuICAgICAgICAkKCdoZWFkJykuYXBwZW5kKCc8c3R5bGUgZGF0YS1zdHlsZXM9XCJsZXZlbi10aGVtZS1za2lsbHMtY3NzXCIgdHlwZT1cInRleHQvY3NzXCI+JyArIGN1c3RvbV9zdHlsZXMgKyAnPC9zdHlsZT4nKTtcclxuICAgIH1cclxuXHJcbiAgICAvLyBPbiBEb2N1bWVudCBMb2FkXHJcbiAgICAkKGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgdmFyIG1vdmVtZW50U3RyZW5ndGggPSAxNTtcclxuICAgICAgICB2YXIgaGVpZ2h0ID0gbW92ZW1lbnRTdHJlbmd0aCAvICQoZG9jdW1lbnQpLmhlaWdodCgpO1xyXG4gICAgICAgIHZhciB3aWR0aCA9IG1vdmVtZW50U3RyZW5ndGggLyAkKGRvY3VtZW50KS53aWR0aCgpO1xyXG4gICAgICAgICQoXCJib2R5XCIpLm9uKCdtb3VzZW1vdmUnLCBmdW5jdGlvbihlKXtcclxuICAgICAgICAgICAgdmFyIHBhZ2VYID0gZS5wYWdlWCAtICgkKGRvY3VtZW50KS53aWR0aCgpIC8gMiksXHJcbiAgICAgICAgICAgICAgICBwYWdlWSA9IGUucGFnZVkgLSAoJChkb2N1bWVudCkuaGVpZ2h0KCkgLyAyKSxcclxuICAgICAgICAgICAgICAgIG5ld3ZhbHVlWCA9IHdpZHRoICogcGFnZVggKiAtMSxcclxuICAgICAgICAgICAgICAgIG5ld3ZhbHVlWSA9IGhlaWdodCAqIHBhZ2VZICogLTE7XHJcbiAgICAgICAgICAgIGlmICgkKCcucGFnZS1jb250YWluZXInKS5oYXNDbGFzcygnYmctbW92ZS1lZmZlY3QnKSkge1xyXG4gICAgICAgICAgICAgICAgdmFyIGVsZW1lbnRzID0gJCgnLmhvbWUtcGhvdG8gLmhwLWlubmVyOm5vdCgud2l0aG91dC1tb3ZlKSwgLmxtLWFuaW1hdGVkLWJnJyk7XHJcbiAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICB2YXIgZWxlbWVudHMgPSAkKCcuaG9tZS1waG90byAuaHAtaW5uZXI6bm90KC53aXRob3V0LW1vdmUpJyk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgZWxlbWVudHMuYWRkQ2xhc3MoJ3RyYW5zaXRpb24nKTtcclxuICAgICAgICAgICAgZWxlbWVudHMuY3NzKHtcclxuICAgICAgICAgICAgICAgIFwiYmFja2dyb3VuZC1wb3NpdGlvblwiOiBcImNhbGMoIDUwJSArIFwiICsgbmV3dmFsdWVYICsgXCJweCApIGNhbGMoIDUwJSArIFwiICsgbmV3dmFsdWVZICsgXCJweCApXCIsXHJcbiAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgc2V0VGltZW91dChmdW5jdGlvbigpIHtcclxuICAgICAgICAgICAgICAgIGVsZW1lbnRzLnJlbW92ZUNsYXNzKCd0cmFuc2l0aW9uJyk7XHJcbiAgICAgICAgICAgIH0sIDMwMCk7XHJcbiAgICAgICAgfSlcclxuICAgICAgICAuc2Nyb2xsKGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgc2Nyb2xsVG9wKCk7XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIC8vIEluaXRpYWxpemUgUG9ydGZvbGlvIGdyaWRcclxuICAgICAgICB2YXIgJHBvcnRmb2xpb19jb250YWluZXIgPSAkKFwiLnBvcnRmb2xpby1ncmlkXCIpLFxyXG4gICAgICAgICAgICAkZ2FsbGVyeV9jb250YWluZXIgPSAkKFwiI3BvcnRmb2xpby1nYWxsZXJ5LWdyaWRcIik7XHJcblxyXG4gICAgICAgICRnYWxsZXJ5X2NvbnRhaW5lci5pbWFnZXNMb2FkZWQoZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICAkZ2FsbGVyeV9jb250YWluZXIubWFzb25yeSgpO1xyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAkcG9ydGZvbGlvX2NvbnRhaW5lci5pbWFnZXNMb2FkZWQoZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICBwb3J0Zm9saW9faW5pdCh0aGlzKTtcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgaW1hZ2VDYXJvdXNlbCgpO1xyXG5cclxuICAgICAgICAvLyBCbG9nIGdyaWQgaW5pdFxyXG4gICAgICAgIHZhciAkY29udGFpbmVyID0gJChcIi5ibG9nLW1hc29ucnlcIik7XHJcbiAgICAgICAgJGNvbnRhaW5lci5pbWFnZXNMb2FkZWQoZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICAkY29udGFpbmVyLm1hc29ucnkoe1xyXG4gICAgICAgICAgICAgIGl0ZW1TZWxlY3RvcjogJy5pdGVtJyxcclxuICAgICAgICAgICAgICByZXNpemU6IGZhbHNlXHJcbiAgICAgICAgICAgIH0pO1xyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAvLyBNb2JpbGUgbWVudVxyXG4gICAgICAgICQoJy5tZW51LXRvZ2dsZScpLm9uKFwiY2xpY2tcIiwgZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICAkKCcuc2l0ZS1uYXYnKS5hZGRDbGFzcygnYW5pbWF0ZScpO1xyXG4gICAgICAgICAgICAkKCcuc2l0ZS1uYXYnKS50b2dnbGVDbGFzcygnbW9iaWxlLW1lbnUtaGlkZScpO1xyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAvLyBUZXh0IHJvdGF0aW9uXHJcbiAgICAgICAgJCgnLnRleHQtcm90YXRpb24nKS5vd2xDYXJvdXNlbCh7XHJcbiAgICAgICAgICAgIGxvb3A6IHRydWUsXHJcbiAgICAgICAgICAgIGRvdHM6IGZhbHNlLFxyXG4gICAgICAgICAgICBuYXY6IGZhbHNlLFxyXG4gICAgICAgICAgICBtYXJnaW46IDEwLFxyXG4gICAgICAgICAgICBpdGVtczogMSxcclxuICAgICAgICAgICAgYXV0b3BsYXk6IHRydWUsXHJcbiAgICAgICAgICAgIGF1dG9wbGF5SG92ZXJQYXVzZTogZmFsc2UsXHJcbiAgICAgICAgICAgIGF1dG9wbGF5VGltZW91dDogMzgwMCxcclxuICAgICAgICAgICAgYW5pbWF0ZU91dDogJ2ZhZGVPdXQnLFxyXG4gICAgICAgICAgICBhbmltYXRlSW46ICdmYWRlSW4nXHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIC8vIFRlc3RpbW9uaWFscyBTbGlkZXJcclxuICAgICAgICAkKFwiLnRlc3RpbW9uaWFscy5vd2wtY2Fyb3VzZWxcIikub3dsQ2Fyb3VzZWwoe1xyXG4gICAgICAgICAgICBuYXY6IGZhbHNlLCAvLyBTaG93IG5leHQvcHJldiBidXR0b25zLlxyXG4gICAgICAgICAgICBpdGVtczogMywgLy8gVGhlIG51bWJlciBvZiBpdGVtcyB5b3Ugd2FudCB0byBzZWUgb24gdGhlIHNjcmVlbi5cclxuICAgICAgICAgICAgbG9vcDogZmFsc2UsIC8vIEluZmluaXR5IGxvb3AuIER1cGxpY2F0ZSBsYXN0IGFuZCBmaXJzdCBpdGVtcyB0byBnZXQgbG9vcCBpbGx1c2lvbi5cclxuICAgICAgICAgICAgbmF2VGV4dDogZmFsc2UsXHJcbiAgICAgICAgICAgIG1hcmdpbjogMjUsXHJcbiAgICAgICAgICAgIHJlc3BvbnNpdmUgOiB7XHJcbiAgICAgICAgICAgICAgICAvLyBicmVha3BvaW50IGZyb20gMCB1cFxyXG4gICAgICAgICAgICAgICAgMCA6IHtcclxuICAgICAgICAgICAgICAgICAgICBpdGVtczogMSxcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAvLyBicmVha3BvaW50IGZyb20gNDgwIHVwXHJcbiAgICAgICAgICAgICAgICA0ODAgOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgaXRlbXM6IDEsXHJcbiAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgLy8gYnJlYWtwb2ludCBmcm9tIDc2OCB1cFxyXG4gICAgICAgICAgICAgICAgNzY4IDoge1xyXG4gICAgICAgICAgICAgICAgICAgIGl0ZW1zOiAyLFxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIDEyMDAgOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgaXRlbXM6IDIsXHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgLy8gQ2xpZW50cyBTbGlkZXJcclxuICAgICAgICAkKFwiLmNsaWVudHMub3dsLWNhcm91c2VsXCIpLmltYWdlc0xvYWRlZCgpLm93bENhcm91c2VsKHtcclxuICAgICAgICAgICAgbmF2OiBmYWxzZSwgLy8gU2hvdyBuZXh0L3ByZXYgYnV0dG9ucy5cclxuICAgICAgICAgICAgaXRlbXM6IDIsIC8vIFRoZSBudW1iZXIgb2YgaXRlbXMgeW91IHdhbnQgdG8gc2VlIG9uIHRoZSBzY3JlZW4uXHJcbiAgICAgICAgICAgIGxvb3A6IGZhbHNlLCAvLyBJbmZpbml0eSBsb29wLiBEdXBsaWNhdGUgbGFzdCBhbmQgZmlyc3QgaXRlbXMgdG8gZ2V0IGxvb3AgaWxsdXNpb24uXHJcbiAgICAgICAgICAgIG5hdlRleHQ6IGZhbHNlLFxyXG4gICAgICAgICAgICBtYXJnaW46IDEwLFxyXG4gICAgICAgICAgICBhdXRvSGVpZ2h0OiBmYWxzZSxcclxuICAgICAgICAgICAgcmVzcG9uc2l2ZSA6IHtcclxuICAgICAgICAgICAgICAgIC8vIGJyZWFrcG9pbnQgZnJvbSAwIHVwXHJcbiAgICAgICAgICAgICAgICAwIDoge1xyXG4gICAgICAgICAgICAgICAgICAgIGl0ZW1zOiAyLFxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIC8vIGJyZWFrcG9pbnQgZnJvbSA3NjggdXBcclxuICAgICAgICAgICAgICAgIDc2OCA6IHtcclxuICAgICAgICAgICAgICAgICAgICBpdGVtczogNCxcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAxMjAwIDoge1xyXG4gICAgICAgICAgICAgICAgICAgIGl0ZW1zOiA2LFxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIC8vIExpZ2h0Ym94IGluaXRcclxuICAgICAgICBib2R5Lm1hZ25pZmljUG9wdXAoe1xyXG4gICAgICAgICAgICBmaXhlZENvbnRlbnRQb3M6IGZhbHNlLFxyXG4gICAgICAgICAgICBkZWxlZ2F0ZTogJ2EubGlnaHRib3gnLFxyXG4gICAgICAgICAgICB0eXBlOiAnaW1hZ2UnLFxyXG4gICAgICAgICAgICByZW1vdmFsRGVsYXk6IDMwMCxcclxuXHJcbiAgICAgICAgICAgIC8vIENsYXNzIHRoYXQgaXMgYWRkZWQgdG8gcG9wdXAgd3JhcHBlciBhbmQgYmFja2dyb3VuZFxyXG4gICAgICAgICAgICAvLyBtYWtlIGl0IHVuaXF1ZSB0byBhcHBseSB5b3VyIENTUyBhbmltYXRpb25zIGp1c3QgdG8gdGhpcyBleGFjdCBwb3B1cFxyXG4gICAgICAgICAgICBtYWluQ2xhc3M6ICdtZnAtZmFkZScsXHJcbiAgICAgICAgICAgIGltYWdlOiB7XHJcbiAgICAgICAgICAgICAgICAvLyBvcHRpb25zIGZvciBpbWFnZSBjb250ZW50IHR5cGVcclxuICAgICAgICAgICAgICAgIHRpdGxlU3JjOiAndGl0bGUnLFxyXG4gICAgICAgICAgICAgICAgZ2FsbGVyeToge1xyXG4gICAgICAgICAgICAgICAgICAgIGVuYWJsZWQ6IHRydWVcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIH0sXHJcblxyXG4gICAgICAgICAgICBpZnJhbWU6IHtcclxuICAgICAgICAgICAgICAgIG1hcmt1cDogJzxkaXYgY2xhc3M9XCJtZnAtaWZyYW1lLXNjYWxlclwiPicrXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICc8ZGl2IGNsYXNzPVwibWZwLWNsb3NlXCI+PC9kaXY+JytcclxuICAgICAgICAgICAgICAgICAgICAgICAgJzxpZnJhbWUgY2xhc3M9XCJtZnAtaWZyYW1lXCIgZnJhbWVib3JkZXI9XCIwXCIgYWxsb3dmdWxsc2NyZWVuPjwvaWZyYW1lPicrXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICc8ZGl2IGNsYXNzPVwibWZwLXRpdGxlIG1mcC1ib3R0b20taWZyYW1lLXRpdGxlXCI+PC9kaXY+JytcclxuICAgICAgICAgICAgICAgICAgICAgICc8L2Rpdj4nLCAvLyBIVE1MIG1hcmt1cCBvZiBwb3B1cCwgYG1mcC1jbG9zZWAgd2lsbCBiZSByZXBsYWNlZCBieSB0aGUgY2xvc2UgYnV0dG9uXHJcblxyXG4gICAgICAgICAgICAgICAgcGF0dGVybnM6IHtcclxuICAgICAgICAgICAgICAgICAgICB5b3V0dWJlOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICBpbmRleDogJ3lvdXR1YmUuY29tLycsIC8vIFN0cmluZyB0aGF0IGRldGVjdHMgdHlwZSBvZiB2aWRlbyAoaW4gdGhpcyBjYXNlIFlvdVR1YmUpLiBTaW1wbHkgdmlhIHVybC5pbmRleE9mKGluZGV4KS5cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICBpZDogbnVsbCwgLy8gU3RyaW5nIHRoYXQgc3BsaXRzIFVSTCBpbiBhIHR3byBwYXJ0cywgc2Vjb25kIHBhcnQgc2hvdWxkIGJlICVpZCVcclxuICAgICAgICAgICAgICAgICAgICAgIC8vIE9yIG51bGwgLSBmdWxsIFVSTCB3aWxsIGJlIHJldHVybmVkXHJcbiAgICAgICAgICAgICAgICAgICAgICAvLyBPciBhIGZ1bmN0aW9uIHRoYXQgc2hvdWxkIHJldHVybiAlaWQlLCBmb3IgZXhhbXBsZTpcclxuICAgICAgICAgICAgICAgICAgICAgIC8vIGlkOiBmdW5jdGlvbih1cmwpIHsgcmV0dXJuICdwYXJzZWQgaWQnOyB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgc3JjOiAnJWlkJT9hdXRvcGxheT0xJyAvLyBVUkwgdGhhdCB3aWxsIGJlIHNldCBhcyBhIHNvdXJjZSBmb3IgaWZyYW1lLlxyXG4gICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgdmltZW86IHtcclxuICAgICAgICAgICAgICAgICAgICAgIGluZGV4OiAndmltZW8uY29tLycsXHJcbiAgICAgICAgICAgICAgICAgICAgICBpZDogJy8nLFxyXG4gICAgICAgICAgICAgICAgICAgICAgc3JjOiAnLy9wbGF5ZXIudmltZW8uY29tL3ZpZGVvLyVpZCU/YXV0b3BsYXk9MSdcclxuICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgIGdtYXBzOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICBpbmRleDogJy8vbWFwcy5nb29nbGUuJyxcclxuICAgICAgICAgICAgICAgICAgICAgIHNyYzogJyVpZCUmb3V0cHV0PWVtYmVkJ1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH0sXHJcblxyXG4gICAgICAgICAgICAgICAgc3JjQWN0aW9uOiAnaWZyYW1lX3NyYycsIC8vIFRlbXBsYXRpbmcgb2JqZWN0IGtleS4gRmlyc3QgcGFydCBkZWZpbmVzIENTUyBzZWxlY3Rvciwgc2Vjb25kIGF0dHJpYnV0ZS4gXCJpZnJhbWVfc3JjXCIgbWVhbnM6IGZpbmQgXCJpZnJhbWVcIiBhbmQgc2V0IGF0dHJpYnV0ZSBcInNyY1wiLlxyXG4gICAgICAgICAgICB9LFxyXG5cclxuICAgICAgICAgICAgY2FsbGJhY2tzOiB7XHJcbiAgICAgICAgICAgICAgICBtYXJrdXBQYXJzZTogZnVuY3Rpb24odGVtcGxhdGUsIHZhbHVlcywgaXRlbSkge1xyXG4gICAgICAgICAgICAgICAgIHZhbHVlcy50aXRsZSA9IGl0ZW0uZWwuYXR0cigndGl0bGUnKTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfSxcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgJCgnLmFqYXgtcGFnZS1sb2FkLWxpbmsnKS5tYWduaWZpY1BvcHVwKHtcclxuICAgICAgICAgICAgdHlwZTogJ2FqYXgnLFxyXG4gICAgICAgICAgICByZW1vdmFsRGVsYXk6IDMwMCxcclxuICAgICAgICAgICAgbWFpbkNsYXNzOiAnbWZwLWZhZGUnLFxyXG4gICAgICAgICAgICBnYWxsZXJ5OiB7XHJcbiAgICAgICAgICAgICAgICBlbmFibGVkOiB0cnVlXHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICQoJy5wb3J0Zm9saW8tcGFnZS13cmFwcGVyIC5wb3J0Zm9saW8tZ3JpZCcpLmVhY2goZnVuY3Rpb24oKSB7XHJcbiAgICAgICAgICAgICQodGhpcykubWFnbmlmaWNQb3B1cCh7XHJcbiAgICAgICAgICAgICAgICBkZWxlZ2F0ZTogJ2EuZ2FsbGVyeS1saWdodGJveCcsXHJcbiAgICAgICAgICAgICAgICB0eXBlOiAnaW1hZ2UnLFxyXG4gICAgICAgICAgICAgICAgZ2FsbGVyeToge1xyXG4gICAgICAgICAgICAgICAgICBlbmFibGVkOnRydWVcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIC8qJCgnLmZvcm0tY29udHJvbCcpLnZhbCgnJyk7Ki9cclxuXHJcbiAgICAgICAgLy8gU2lkZWJhciB0b2dnbGVcclxuICAgICAgICAkKCcuc2lkZWJhci10b2dnbGUnKS5vbihcImNsaWNrXCIsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgJCgnI2Jsb2ctc2lkZWJhcicpLnRvZ2dsZUNsYXNzKCdvcGVuJyk7XHJcbiAgICAgICAgICAgICQodGhpcykudG9nZ2xlQ2xhc3MoJ29wZW4nKTtcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgJCgnLmxtcGl4ZWxzLXNjcm9sbC10by10b3AnKS5jbGljayhmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgICQoJ2JvZHksaHRtbCcpLmFuaW1hdGUoe1xyXG4gICAgICAgICAgICAgICAgc2Nyb2xsVG9wOiAwXHJcbiAgICAgICAgICAgIH0sIDQwMCk7XHJcbiAgICAgICAgICAgIHJldHVybiBmYWxzZTtcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgc2Nyb2xsVG9wKCk7XHJcblxyXG4gICAgICAgIHNraWxsc1N0eWxlcygpO1xyXG4gICAgfSk7XHJcblxyXG59KShqUXVlcnkpOyIsIi8qIGdsb2JhbCBCdW4gLS0gRGVubyBjYXNlICovXG5tb2R1bGUuZXhwb3J0cyA9IHR5cGVvZiBCdW4gPT0gJ2Z1bmN0aW9uJyAmJiBCdW4gJiYgdHlwZW9mIEJ1bi52ZXJzaW9uID09ICdzdHJpbmcnO1xuIiwiJ3VzZSBzdHJpY3QnO1xudmFyIGdsb2JhbCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9nbG9iYWwnKTtcbnZhciBhcHBseSA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9mdW5jdGlvbi1hcHBseScpO1xudmFyIGlzQ2FsbGFibGUgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvaXMtY2FsbGFibGUnKTtcbnZhciBFTkdJTkVfSVNfQlVOID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2VuZ2luZS1pcy1idW4nKTtcbnZhciBVU0VSX0FHRU5UID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2VuZ2luZS11c2VyLWFnZW50Jyk7XG52YXIgYXJyYXlTbGljZSA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9hcnJheS1zbGljZScpO1xudmFyIHZhbGlkYXRlQXJndW1lbnRzTGVuZ3RoID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL3ZhbGlkYXRlLWFyZ3VtZW50cy1sZW5ndGgnKTtcblxudmFyIEZ1bmN0aW9uID0gZ2xvYmFsLkZ1bmN0aW9uO1xuLy8gZGlydHkgSUU5LSBhbmQgQnVuIDAuMy4wLSBjaGVja3NcbnZhciBXUkFQID0gL01TSUUgLlxcLi8udGVzdChVU0VSX0FHRU5UKSB8fCBFTkdJTkVfSVNfQlVOICYmIChmdW5jdGlvbiAoKSB7XG4gIHZhciB2ZXJzaW9uID0gZ2xvYmFsLkJ1bi52ZXJzaW9uLnNwbGl0KCcuJyk7XG4gIHJldHVybiB2ZXJzaW9uLmxlbmd0aCA8IDMgfHwgdmVyc2lvblswXSA9PSAwICYmICh2ZXJzaW9uWzFdIDwgMyB8fCB2ZXJzaW9uWzFdID09IDMgJiYgdmVyc2lvblsyXSA9PSAwKTtcbn0pKCk7XG5cbi8vIElFOS0gLyBCdW4gMC4zLjAtIHNldFRpbWVvdXQgLyBzZXRJbnRlcnZhbCAvIHNldEltbWVkaWF0ZSBhZGRpdGlvbmFsIHBhcmFtZXRlcnMgZml4XG4vLyBodHRwczovL2h0bWwuc3BlYy53aGF0d2cub3JnL211bHRpcGFnZS90aW1lcnMtYW5kLXVzZXItcHJvbXB0cy5odG1sI3RpbWVyc1xuLy8gaHR0cHM6Ly9naXRodWIuY29tL292ZW4tc2gvYnVuL2lzc3Vlcy8xNjMzXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uIChzY2hlZHVsZXIsIGhhc1RpbWVBcmcpIHtcbiAgdmFyIGZpcnN0UGFyYW1JbmRleCA9IGhhc1RpbWVBcmcgPyAyIDogMTtcbiAgcmV0dXJuIFdSQVAgPyBmdW5jdGlvbiAoaGFuZGxlciwgdGltZW91dCAvKiAsIC4uLmFyZ3VtZW50cyAqLykge1xuICAgIHZhciBib3VuZEFyZ3MgPSB2YWxpZGF0ZUFyZ3VtZW50c0xlbmd0aChhcmd1bWVudHMubGVuZ3RoLCAxKSA+IGZpcnN0UGFyYW1JbmRleDtcbiAgICB2YXIgZm4gPSBpc0NhbGxhYmxlKGhhbmRsZXIpID8gaGFuZGxlciA6IEZ1bmN0aW9uKGhhbmRsZXIpO1xuICAgIHZhciBwYXJhbXMgPSBib3VuZEFyZ3MgPyBhcnJheVNsaWNlKGFyZ3VtZW50cywgZmlyc3RQYXJhbUluZGV4KSA6IFtdO1xuICAgIHZhciBjYWxsYmFjayA9IGJvdW5kQXJncyA/IGZ1bmN0aW9uICgpIHtcbiAgICAgIGFwcGx5KGZuLCB0aGlzLCBwYXJhbXMpO1xuICAgIH0gOiBmbjtcbiAgICByZXR1cm4gaGFzVGltZUFyZyA/IHNjaGVkdWxlcihjYWxsYmFjaywgdGltZW91dCkgOiBzY2hlZHVsZXIoY2FsbGJhY2spO1xuICB9IDogc2NoZWR1bGVyO1xufTtcbiIsInZhciAkVHlwZUVycm9yID0gVHlwZUVycm9yO1xuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uIChwYXNzZWQsIHJlcXVpcmVkKSB7XG4gIGlmIChwYXNzZWQgPCByZXF1aXJlZCkgdGhyb3cgJFR5cGVFcnJvcignTm90IGVub3VnaCBhcmd1bWVudHMnKTtcbiAgcmV0dXJuIHBhc3NlZDtcbn07XG4iLCJ2YXIgJCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9leHBvcnQnKTtcbnZhciBnbG9iYWwgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvZ2xvYmFsJyk7XG52YXIgc2NoZWR1bGVyc0ZpeCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9zY2hlZHVsZXJzLWZpeCcpO1xuXG52YXIgc2V0SW50ZXJ2YWwgPSBzY2hlZHVsZXJzRml4KGdsb2JhbC5zZXRJbnRlcnZhbCwgdHJ1ZSk7XG5cbi8vIEJ1biAvIElFOS0gc2V0SW50ZXJ2YWwgYWRkaXRpb25hbCBwYXJhbWV0ZXJzIGZpeFxuLy8gaHR0cHM6Ly9odG1sLnNwZWMud2hhdHdnLm9yZy9tdWx0aXBhZ2UvdGltZXJzLWFuZC11c2VyLXByb21wdHMuaHRtbCNkb20tc2V0aW50ZXJ2YWxcbiQoeyBnbG9iYWw6IHRydWUsIGJpbmQ6IHRydWUsIGZvcmNlZDogZ2xvYmFsLnNldEludGVydmFsICE9PSBzZXRJbnRlcnZhbCB9LCB7XG4gIHNldEludGVydmFsOiBzZXRJbnRlcnZhbFxufSk7XG4iLCJ2YXIgJCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9leHBvcnQnKTtcbnZhciBnbG9iYWwgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvZ2xvYmFsJyk7XG52YXIgc2NoZWR1bGVyc0ZpeCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9zY2hlZHVsZXJzLWZpeCcpO1xuXG52YXIgc2V0VGltZW91dCA9IHNjaGVkdWxlcnNGaXgoZ2xvYmFsLnNldFRpbWVvdXQsIHRydWUpO1xuXG4vLyBCdW4gLyBJRTktIHNldFRpbWVvdXQgYWRkaXRpb25hbCBwYXJhbWV0ZXJzIGZpeFxuLy8gaHR0cHM6Ly9odG1sLnNwZWMud2hhdHdnLm9yZy9tdWx0aXBhZ2UvdGltZXJzLWFuZC11c2VyLXByb21wdHMuaHRtbCNkb20tc2V0dGltZW91dFxuJCh7IGdsb2JhbDogdHJ1ZSwgYmluZDogdHJ1ZSwgZm9yY2VkOiBnbG9iYWwuc2V0VGltZW91dCAhPT0gc2V0VGltZW91dCB9LCB7XG4gIHNldFRpbWVvdXQ6IHNldFRpbWVvdXRcbn0pO1xuIiwiLy8gVE9ETzogUmVtb3ZlIHRoaXMgbW9kdWxlIGZyb20gYGNvcmUtanNANGAgc2luY2UgaXQncyBzcGxpdCB0byBtb2R1bGVzIGxpc3RlZCBiZWxvd1xucmVxdWlyZSgnLi4vbW9kdWxlcy93ZWIuc2V0LWludGVydmFsJyk7XG5yZXF1aXJlKCcuLi9tb2R1bGVzL3dlYi5zZXQtdGltZW91dCcpO1xuIl0sIm5hbWVzIjpbIiQiLCJib2R5IiwiaW1hZ2VDYXJvdXNlbCIsImVhY2giLCJpbWFnZXNMb2FkZWQiLCJvd2xDYXJvdXNlbCIsInNtYXJ0U3BlZWQiLCJpdGVtcyIsImxvb3AiLCJkb3RzIiwibmF2IiwibmF2VGV4dCIsImF1dG9IZWlnaHQiLCJtYXJnaW4iLCJwb3J0Zm9saW9faW5pdCIsInBvcnRmb2xpb19ncmlkX2NvbnRhaW5lciIsInBvcnRmb2xpb19ncmlkX2NvbnRhaW5lcl9pZCIsImF0dHIiLCJwb3J0Zm9saW9fZ3JpZCIsInBvcnRmb2xpb19maWx0ZXIiLCJwb3J0Zm9saW9fZmlsdGVyX2l0ZW0iLCJzaHVmZmxlIiwic3BlZWQiLCJpdGVtU2VsZWN0b3IiLCJvbiIsImUiLCJwcmV2ZW50RGVmYXVsdCIsInBhcmVudCIsInJlbW92ZUNsYXNzIiwiYWRkQ2xhc3MiLCJhbmltYXRlTGF5b3V0Iiwid2luZG93V2lkdGgiLCJ3aW5kb3ciLCJ3aWR0aCIsImFuaW1hdGVkQ29udGFpbmVyIiwiYW5pbWF0ZVR5cGUiLCJzZXRUaW1lb3V0Iiwic2Nyb2xsVG9wIiwic2tpbGxzU3R5bGVzIiwiY3VzdG9tX3N0eWxlcyIsInZhbHVlIiwiaWQiLCIkY3VzdG9tX3N0eWxlIiwiYXBwZW5kIiwiZG9jdW1lbnQiLCJyZWFkeSIsIm1vdmVtZW50U3RyZW5ndGgiLCJoZWlnaHQiLCJwYWdlWCIsInBhZ2VZIiwibmV3dmFsdWVYIiwibmV3dmFsdWVZIiwiaGFzQ2xhc3MiLCJlbGVtZW50cyIsImNzcyIsInNjcm9sbCIsIiRwb3J0Zm9saW9fY29udGFpbmVyIiwiJGdhbGxlcnlfY29udGFpbmVyIiwibWFzb25yeSIsIiRjb250YWluZXIiLCJyZXNpemUiLCJ0b2dnbGVDbGFzcyIsImF1dG9wbGF5IiwiYXV0b3BsYXlIb3ZlclBhdXNlIiwiYXV0b3BsYXlUaW1lb3V0IiwiYW5pbWF0ZU91dCIsImFuaW1hdGVJbiIsInJlc3BvbnNpdmUiLCJtYWduaWZpY1BvcHVwIiwiZml4ZWRDb250ZW50UG9zIiwiZGVsZWdhdGUiLCJ0eXBlIiwicmVtb3ZhbERlbGF5IiwibWFpbkNsYXNzIiwiaW1hZ2UiLCJ0aXRsZVNyYyIsImdhbGxlcnkiLCJlbmFibGVkIiwiaWZyYW1lIiwibWFya3VwIiwicGF0dGVybnMiLCJ5b3V0dWJlIiwiaW5kZXgiLCJzcmMiLCJ2aW1lbyIsImdtYXBzIiwic3JjQWN0aW9uIiwiY2FsbGJhY2tzIiwibWFya3VwUGFyc2UiLCJ0ZW1wbGF0ZSIsInZhbHVlcyIsIml0ZW0iLCJ0aXRsZSIsImVsIiwiY2xpY2siLCJhbmltYXRlIiwialF1ZXJ5Il0sInNvdXJjZVJvb3QiOiIifQ==