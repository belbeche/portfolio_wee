(self["webpackChunk"] = self["webpackChunk"] || []).push([["main"],{

/***/ "./assets/dist/main.js":
/*!*****************************!*\
  !*** ./assets/dist/main.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

__webpack_require__(/*! core-js/modules/web.timers.js */ "./node_modules/core-js/modules/web.timers.js");
__webpack_require__(/*! core-js/modules/es.string.substr.js */ "./node_modules/core-js/modules/es.string.substr.js");
__webpack_require__(/*! core-js/modules/es.array.find.js */ "./node_modules/core-js/modules/es.array.find.js");
__webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
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

  // Ajax Pages loader
  function ajaxLoader() {
    // Check for hash value in URL
    var ajaxLoadedContent = $('#page-ajax-loaded');
    function showContent() {
      ajaxLoadedContent.removeClass('fadeOutLeft closed');
      ajaxLoadedContent.show();
      $('body').addClass('ajax-page-visible');
    }
    function hideContent() {
      $('#page-ajax-loaded').addClass('fadeOutLeft closed');
      $('body').removeClass('ajax-page-visible');
      setTimeout(function () {
        $('#page-ajax-loaded.closed').html('');
        ajaxLoadedContent.hide();
      }, 500);
    }
    var href = $('.ajax-page-load').each(function () {
      href = $(this).attr('href');
      if (location.hash == location.hash.split('/')[0] + '/' + href.substr(0, href.length - 5)) {
        var toLoad = $(this).attr('href');
        showContent();
        ajaxLoadedContent.load(toLoad);
        return false;
      }
    });
    $(document).on("click", "#ajax-page-close-button", function (e) {
      // Hide Ajax Loaded Page on Navigation cleck and Close button
      e.preventDefault();
      hideContent();
      location.hash = location.hash.split('/')[0];
    }).on("click", ".ajax-page-load", function () {
      // Show Ajax Loaded Page
      var hash = location.hash.split('/')[0] + '/' + $(this).attr('href').substr(0, $(this).attr('href').length - 5);
      location.hash = hash;
      showContent();
      return false;
    });
  }
  // /Ajax Pages loader

  // Contact form validator
  $(function () {
    $('#contact_form').validator();
    $('#contact_form').on('submit', function (e) {
      if (!e.isDefaultPrevented()) {
        var url = "contact_form/contact_form.php";
        $.ajax({
          type: "POST",
          url: url,
          data: $(this).serialize(),
          success: function success(data) {
            var messageAlert = 'alert-' + data.type;
            var messageText = data.message;
            var alertBox = '<div class="alert ' + messageAlert + ' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + messageText + '</div>';
            if (messageAlert && messageText) {
              $('#contact_form').find('.messages').html(alertBox);
              $('#contact_form')[0].reset();
            }
          }
        });
        return false;
      }
    });
  });
  // /Contact form validator

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

  //On Window load & Resize
  $(window).on('load', function () {
    //Load
    // Animation on Page Loading
    $(".preloader").fadeOut(800, "linear");
    animateLayout();
  }).on('hashchange', function (event) {
    if (location.hash) {
      ajaxLoader();
    }
  });

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
    $('.form-control').val('');
    $(".form-control").on("focusin", function () {
      $(this).parent('.form-group').addClass('form-group-focus');
    });
    $(".form-control").on("focusout", function () {
      if ($(this).val().length === 0) {
        $(this).parent('.form-group').removeClass('form-group-focus');
      }
    });
    $('body').append('<div id="page-ajax-loaded" class="page-portfolio-loaded animated fadeInLeft" style="display: none"><div class="preloader-portfolio"><div class="preloader-animation"><div class="preloader-spinner"></div></div></div></div>');
    ajaxLoader();

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

    //Google Maps
    $("#map").googleMap({
      zoom: 16 // Google Map ZOOM. You can change this value
    });

    $("#map").addMarker({
      address: "S601 Townsend Street, San Francisco, California, USA" // Your Address. Change it
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

/***/ "./node_modules/core-js/modules/es.array.find.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/modules/es.array.find.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var $ = __webpack_require__(/*! ../internals/export */ "./node_modules/core-js/internals/export.js");
var $find = (__webpack_require__(/*! ../internals/array-iteration */ "./node_modules/core-js/internals/array-iteration.js").find);
var addToUnscopables = __webpack_require__(/*! ../internals/add-to-unscopables */ "./node_modules/core-js/internals/add-to-unscopables.js");

var FIND = 'find';
var SKIPS_HOLES = true;

// Shouldn't skip holes
if (FIND in []) Array(1)[FIND](function () { SKIPS_HOLES = false; });

// `Array.prototype.find` method
// https://tc39.es/ecma262/#sec-array.prototype.find
$({ target: 'Array', proto: true, forced: SKIPS_HOLES }, {
  find: function find(callbackfn /* , that = undefined */) {
    return $find(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
  }
});

// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
addToUnscopables(FIND);


/***/ }),

/***/ "./node_modules/core-js/modules/es.string.substr.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/modules/es.string.substr.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var $ = __webpack_require__(/*! ../internals/export */ "./node_modules/core-js/internals/export.js");
var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var requireObjectCoercible = __webpack_require__(/*! ../internals/require-object-coercible */ "./node_modules/core-js/internals/require-object-coercible.js");
var toIntegerOrInfinity = __webpack_require__(/*! ../internals/to-integer-or-infinity */ "./node_modules/core-js/internals/to-integer-or-infinity.js");
var toString = __webpack_require__(/*! ../internals/to-string */ "./node_modules/core-js/internals/to-string.js");

var stringSlice = uncurryThis(''.slice);
var max = Math.max;
var min = Math.min;

// eslint-disable-next-line unicorn/prefer-string-slice -- required for testing
var FORCED = !''.substr || 'ab'.substr(-1) !== 'b';

// `String.prototype.substr` method
// https://tc39.es/ecma262/#sec-string.prototype.substr
$({ target: 'String', proto: true, forced: FORCED }, {
  substr: function substr(start, length) {
    var that = toString(requireObjectCoercible(this));
    var size = that.length;
    var intStart = toIntegerOrInfinity(start);
    var intLength, intEnd;
    if (intStart === Infinity) intStart = 0;
    if (intStart < 0) intStart = max(size + intStart, 0);
    intLength = length === undefined ? size : toIntegerOrInfinity(length);
    if (intLength <= 0 || intLength === Infinity) return '';
    intEnd = min(intStart + intLength, size);
    return intStart >= intEnd ? '' : stringSlice(that, intStart, intEnd);
  }
});


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
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_internals_add-to-unscopables_js-node_modules_core-js_internals_a-e38503"], () => (__webpack_exec__("./assets/dist/main.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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QTtBQUNBO0FBQ0E7QUFDQSxDQUFDOzs7Ozs7Ozs7OztBQzdCRCxRQUFRLG1CQUFPLENBQUMsdUVBQXFCO0FBQ3JDLGFBQWEsbUJBQU8sQ0FBQyx1RUFBcUI7QUFDMUMsb0JBQW9CLG1CQUFPLENBQUMsdUZBQTZCOztBQUV6RDs7QUFFQTtBQUNBO0FBQ0EsSUFBSSxzRUFBc0U7QUFDMUU7QUFDQSxDQUFDOzs7Ozs7Ozs7OztBQ1ZELFFBQVEsbUJBQU8sQ0FBQyx1RUFBcUI7QUFDckMsYUFBYSxtQkFBTyxDQUFDLHVFQUFxQjtBQUMxQyxvQkFBb0IsbUJBQU8sQ0FBQyx1RkFBNkI7O0FBRXpEOztBQUVBO0FBQ0E7QUFDQSxJQUFJLG9FQUFvRTtBQUN4RTtBQUNBLENBQUM7Ozs7Ozs7Ozs7O0FDVkQ7QUFDQSxtQkFBTyxDQUFDLHVGQUE2QjtBQUNyQyxtQkFBTyxDQUFDLHFGQUE0QiIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL2Fzc2V0cy9kaXN0L21haW4uanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvcmUtanMvaW50ZXJuYWxzL2VuZ2luZS1pcy1idW4uanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvcmUtanMvaW50ZXJuYWxzL3NjaGVkdWxlcnMtZml4LmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9jb3JlLWpzL2ludGVybmFscy92YWxpZGF0ZS1hcmd1bWVudHMtbGVuZ3RoLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9jb3JlLWpzL21vZHVsZXMvZXMuYXJyYXkuZmluZC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9tb2R1bGVzL2VzLnN0cmluZy5zdWJzdHIuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL2NvcmUtanMvbW9kdWxlcy93ZWIuc2V0LWludGVydmFsLmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9jb3JlLWpzL21vZHVsZXMvd2ViLnNldC10aW1lb3V0LmpzIiwid2VicGFjazovLy8uL25vZGVfbW9kdWxlcy9jb3JlLWpzL21vZHVsZXMvd2ViLnRpbWVycy5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKlxyXG4qIFRlbXBsYXRlIE5hbWU6IExldmVuIC0gUmVzdW1lIC8gQ1YgLyB2Q2FyZCBUZW1wbGF0ZVxyXG4qIEF1dGhvcjogbG1waXhlbHNcclxuKiBBdXRob3IgVVJMOiBodHRwOi8vdGhlbWVmb3Jlc3QubmV0L3VzZXIvbG1waXhlbHNcclxuKiBWZXJzaW9uOiAxLjUuMFxyXG4qL1xyXG5cclxuKGZ1bmN0aW9uKCQpIHtcclxuXCJ1c2Ugc3RyaWN0XCI7XHJcbiAgICB2YXIgYm9keSA9ICQoJ2JvZHknKTtcclxuXHJcbiAgICBmdW5jdGlvbiBpbWFnZUNhcm91c2VsKCkge1xyXG4gICAgICAgICQoJy5wb3J0Zm9saW8tcGFnZS1jYXJvdXNlbCcpLmVhY2goZnVuY3Rpb24oKSB7XHJcbiAgICAgICAgICAgICQodGhpcykuaW1hZ2VzTG9hZGVkKGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgICQoJy5wb3J0Zm9saW8tcGFnZS1jYXJvdXNlbCcpLm93bENhcm91c2VsKHtcclxuICAgICAgICAgICAgICAgICAgICBzbWFydFNwZWVkOjEyMDAsXHJcbiAgICAgICAgICAgICAgICAgICAgaXRlbXM6IDEsXHJcbiAgICAgICAgICAgICAgICAgICAgbG9vcDogdHJ1ZSxcclxuICAgICAgICAgICAgICAgICAgICBkb3RzOiB0cnVlLFxyXG4gICAgICAgICAgICAgICAgICAgIG5hdjogdHJ1ZSxcclxuICAgICAgICAgICAgICAgICAgICBuYXZUZXh0OiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICBhdXRvSGVpZ2h0OiB0cnVlLFxyXG4gICAgICAgICAgICAgICAgICAgIG1hcmdpbjogMTBcclxuICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9KTtcclxuICAgIH1cclxuXHJcbiAgICAvLyBBamF4IFBhZ2VzIGxvYWRlclxyXG4gICBmdW5jdGlvbiBhamF4TG9hZGVyKCkge1xyXG4gICAgICAgIC8vIENoZWNrIGZvciBoYXNoIHZhbHVlIGluIFVSTFxyXG4gICAgICAgIHZhciBhamF4TG9hZGVkQ29udGVudCA9ICQoJyNwYWdlLWFqYXgtbG9hZGVkJyk7XHJcblxyXG4gICAgICAgIGZ1bmN0aW9uIHNob3dDb250ZW50KCkge1xyXG4gICAgICAgICAgICBhamF4TG9hZGVkQ29udGVudC5yZW1vdmVDbGFzcygnZmFkZU91dExlZnQgY2xvc2VkJyk7XHJcbiAgICAgICAgICAgIGFqYXhMb2FkZWRDb250ZW50LnNob3coKTtcclxuICAgICAgICAgICAgJCgnYm9keScpLmFkZENsYXNzKCdhamF4LXBhZ2UtdmlzaWJsZScpO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgZnVuY3Rpb24gaGlkZUNvbnRlbnQoKSB7XHJcbiAgICAgICAgICAgICQoJyNwYWdlLWFqYXgtbG9hZGVkJykuYWRkQ2xhc3MoJ2ZhZGVPdXRMZWZ0IGNsb3NlZCcpO1xyXG4gICAgICAgICAgICAkKCdib2R5JykucmVtb3ZlQ2xhc3MoJ2FqYXgtcGFnZS12aXNpYmxlJyk7XHJcbiAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKXtcclxuICAgICAgICAgICAgICAgICQoJyNwYWdlLWFqYXgtbG9hZGVkLmNsb3NlZCcpLmh0bWwoJycpO1xyXG4gICAgICAgICAgICAgICAgYWpheExvYWRlZENvbnRlbnQuaGlkZSgpO1xyXG4gICAgICAgICAgICB9LCA1MDApO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgdmFyIGhyZWYgPSAkKCcuYWpheC1wYWdlLWxvYWQnKS5lYWNoKGZ1bmN0aW9uKCl7XHJcbiAgICAgICAgICAgIGhyZWYgPSAkKHRoaXMpLmF0dHIoJ2hyZWYnKTtcclxuICAgICAgICAgICAgaWYobG9jYXRpb24uaGFzaCA9PSBsb2NhdGlvbi5oYXNoLnNwbGl0KCcvJylbMF0gKyAnLycgKyBocmVmLnN1YnN0cigwLGhyZWYubGVuZ3RoLTUpKXtcclxuICAgICAgICAgICAgICAgIHZhciB0b0xvYWQgPSAgJCh0aGlzKS5hdHRyKCdocmVmJyk7XHJcbiAgICAgICAgICAgICAgICBzaG93Q29udGVudCgpO1xyXG4gICAgICAgICAgICAgICAgYWpheExvYWRlZENvbnRlbnQubG9hZCh0b0xvYWQpO1xyXG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICQoZG9jdW1lbnQpXHJcbiAgICAgICAgICAgIC5vbihcImNsaWNrXCIsXCIjYWpheC1wYWdlLWNsb3NlLWJ1dHRvblwiLCBmdW5jdGlvbiAoZSkgeyAvLyBIaWRlIEFqYXggTG9hZGVkIFBhZ2Ugb24gTmF2aWdhdGlvbiBjbGVjayBhbmQgQ2xvc2UgYnV0dG9uXHJcbiAgICAgICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XHJcbiAgICAgICAgICAgICAgICBoaWRlQ29udGVudCgpO1xyXG4gICAgICAgICAgICAgICAgbG9jYXRpb24uaGFzaCA9IGxvY2F0aW9uLmhhc2guc3BsaXQoJy8nKVswXTtcclxuICAgICAgICAgICAgfSlcclxuICAgICAgICAgICAgLm9uKFwiY2xpY2tcIixcIi5hamF4LXBhZ2UtbG9hZFwiLCBmdW5jdGlvbiAoKSB7IC8vIFNob3cgQWpheCBMb2FkZWQgUGFnZVxyXG4gICAgICAgICAgICAgICAgdmFyIGhhc2ggPSBsb2NhdGlvbi5oYXNoLnNwbGl0KCcvJylbMF0gKyAnLycgKyAkKHRoaXMpLmF0dHIoJ2hyZWYnKS5zdWJzdHIoMCwkKHRoaXMpLmF0dHIoJ2hyZWYnKS5sZW5ndGgtNSk7XHJcbiAgICAgICAgICAgICAgICBsb2NhdGlvbi5oYXNoID0gaGFzaDtcclxuICAgICAgICAgICAgICAgIHNob3dDb250ZW50KCk7XHJcblxyXG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgICAgICAgICB9KTtcclxuICAgIH1cclxuICAgIC8vIC9BamF4IFBhZ2VzIGxvYWRlclxyXG5cclxuICAgIC8vIENvbnRhY3QgZm9ybSB2YWxpZGF0b3JcclxuICAgICQoZnVuY3Rpb24gKCkge1xyXG5cclxuICAgICAgICAkKCcjY29udGFjdF9mb3JtJykudmFsaWRhdG9yKCk7XHJcblxyXG4gICAgICAgICQoJyNjb250YWN0X2Zvcm0nKS5vbignc3VibWl0JywgZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgaWYgKCFlLmlzRGVmYXVsdFByZXZlbnRlZCgpKSB7XHJcbiAgICAgICAgICAgICAgICB2YXIgdXJsID0gXCJjb250YWN0X2Zvcm0vY29udGFjdF9mb3JtLnBocFwiO1xyXG5cclxuICAgICAgICAgICAgICAgICQuYWpheCh7XHJcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJQT1NUXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgdXJsOiB1cmwsXHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YTogJCh0aGlzKS5zZXJpYWxpemUoKSxcclxuICAgICAgICAgICAgICAgICAgICBzdWNjZXNzOiBmdW5jdGlvbiAoZGF0YSlcclxuICAgICAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhciBtZXNzYWdlQWxlcnQgPSAnYWxlcnQtJyArIGRhdGEudHlwZTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIG1lc3NhZ2VUZXh0ID0gZGF0YS5tZXNzYWdlO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgdmFyIGFsZXJ0Qm94ID0gJzxkaXYgY2xhc3M9XCJhbGVydCAnICsgbWVzc2FnZUFsZXJ0ICsgJyBhbGVydC1kaXNtaXNzYWJsZVwiPjxidXR0b24gdHlwZT1cImJ1dHRvblwiIGNsYXNzPVwiY2xvc2VcIiBkYXRhLWRpc21pc3M9XCJhbGVydFwiIGFyaWEtaGlkZGVuPVwidHJ1ZVwiPiZ0aW1lczs8L2J1dHRvbj4nICsgbWVzc2FnZVRleHQgKyAnPC9kaXY+JztcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKG1lc3NhZ2VBbGVydCAmJiBtZXNzYWdlVGV4dCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJCgnI2NvbnRhY3RfZm9ybScpLmZpbmQoJy5tZXNzYWdlcycpLmh0bWwoYWxlcnRCb3gpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJCgnI2NvbnRhY3RfZm9ybScpWzBdLnJlc2V0KCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgIHJldHVybiBmYWxzZTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0pO1xyXG4gICAgfSk7XHJcbiAgICAvLyAvQ29udGFjdCBmb3JtIHZhbGlkYXRvclxyXG5cclxuICAgIC8vIFBvcnRmb2xpbyBzdWJwYWdlIGZpbHRlcnNcclxuICAgIGZ1bmN0aW9uIHBvcnRmb2xpb19pbml0KCkge1xyXG4gICAgICAgICQoICcucG9ydGZvbGlvLWNvbnRlbnQnICkuZWFjaCggZnVuY3Rpb24oKSB7XHJcbiAgICAgICAgICAgIHZhciBwb3J0Zm9saW9fZ3JpZF9jb250YWluZXIgPSAkKHRoaXMpLFxyXG4gICAgICAgICAgICAgICAgcG9ydGZvbGlvX2dyaWRfY29udGFpbmVyX2lkID0gJCh0aGlzKS5hdHRyKCdpZCcpLFxyXG4gICAgICAgICAgICAgICAgcG9ydGZvbGlvX2dyaWQgPSAkKCcjJyArIHBvcnRmb2xpb19ncmlkX2NvbnRhaW5lcl9pZCArICcgLnBvcnRmb2xpby1ncmlkJyksXHJcbiAgICAgICAgICAgICAgICBwb3J0Zm9saW9fZmlsdGVyID0gJCgnIycgKyBwb3J0Zm9saW9fZ3JpZF9jb250YWluZXJfaWQgKyAnIC5wb3J0Zm9saW8tZmlsdGVycycpLFxyXG4gICAgICAgICAgICAgICAgcG9ydGZvbGlvX2ZpbHRlcl9pdGVtID0gJCgnIycgKyBwb3J0Zm9saW9fZ3JpZF9jb250YWluZXJfaWQgKyAnIC5wb3J0Zm9saW8tZmlsdGVycyAuZmlsdGVyJyk7XHJcbiAgICAgICAgICAgICAgICBcclxuICAgICAgICAgICAgaWYgKHBvcnRmb2xpb19ncmlkKSB7XHJcblxyXG4gICAgICAgICAgICAgICAgcG9ydGZvbGlvX2dyaWQuc2h1ZmZsZSh7XHJcbiAgICAgICAgICAgICAgICAgICAgc3BlZWQ6IDQ1MCxcclxuICAgICAgICAgICAgICAgICAgICBpdGVtU2VsZWN0b3I6ICdmaWd1cmUnXHJcbiAgICAgICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgICAgICAkKCcuc2l0ZS1hdXRvLW1lbnUnKS5vbihcImNsaWNrXCIsIFwiYVwiLCBmdW5jdGlvbiAoZSkge1xyXG4gICAgICAgICAgICAgICAgICAgIHBvcnRmb2xpb19ncmlkLnNodWZmbGUoJ3VwZGF0ZScpO1xyXG4gICAgICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgcG9ydGZvbGlvX2ZpbHRlci5vbihcImNsaWNrXCIsIFwiLmZpbHRlclwiLCBmdW5jdGlvbiAoZSkge1xyXG4gICAgICAgICAgICAgICAgICAgIHBvcnRmb2xpb19ncmlkLnNodWZmbGUoJ3VwZGF0ZScpO1xyXG4gICAgICAgICAgICAgICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcclxuICAgICAgICAgICAgICAgICAgICBwb3J0Zm9saW9fZmlsdGVyX2l0ZW0ucGFyZW50KCkucmVtb3ZlQ2xhc3MoJ2FjdGl2ZScpO1xyXG4gICAgICAgICAgICAgICAgICAgICQodGhpcykucGFyZW50KCkuYWRkQ2xhc3MoJ2FjdGl2ZScpO1xyXG4gICAgICAgICAgICAgICAgICAgIHBvcnRmb2xpb19ncmlkLnNodWZmbGUoJ3NodWZmbGUnLCAkKHRoaXMpLmF0dHIoJ2RhdGEtZ3JvdXAnKSApO1xyXG4gICAgICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSlcclxuICAgIH1cclxuICAgIC8vIC9Qb3J0Zm9saW8gc3VicGFnZSBmaWx0ZXJzXHJcblxyXG4gICAgLy8gQW5pbWF0ZSBsYXlvdXRcclxuICAgIGZ1bmN0aW9uIGFuaW1hdGVMYXlvdXQoKSB7XHJcbiAgICAgICAgdmFyIHdpbmRvd1dpZHRoID0gJCh3aW5kb3cpLndpZHRoKCksXHJcbiAgICAgICAgICAgIGFuaW1hdGVkQ29udGFpbmVyID0gJycsXHJcbiAgICAgICAgICAgIGFuaW1hdGVUeXBlID0gJCgnI3BhZ2VfY29udGFpbmVyJykuYXR0cignZGF0YS1hbmltYXRpb24nKVxyXG5cclxuICAgICAgICBpZiAod2luZG93V2lkdGggPiA5OTEpIHtcclxuICAgICAgICAgICAgYW5pbWF0ZWRDb250YWluZXIgPSAkKFwiLnBhZ2UtY29udGFpbmVyXCIpO1xyXG4gICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgIGFuaW1hdGVkQ29udGFpbmVyID0gJChcIi5zaXRlLW1haW5cIik7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBhbmltYXRlZENvbnRhaW5lci5hZGRDbGFzcyhcImFuaW1hdGVkIFwiICsgYW5pbWF0ZVR5cGUpO1xyXG4gICAgICAgICQoJy5wYWdlLXNjcm9sbCcpLmFkZENsYXNzKCdhZGQtcHJlc3BlY3RpdmUnKTtcclxuICAgICAgICBhbmltYXRlZENvbnRhaW5lci5hZGRDbGFzcygndHJhbnNmb3JtM2QnKTtcclxuICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xyXG4gICAgICAgICAgICAkKCcucGFnZS1zY3JvbGwnKS5yZW1vdmVDbGFzcygnYWRkLXByZXNwZWN0aXZlJyk7XHJcbiAgICAgICAgICAgIGFuaW1hdGVkQ29udGFpbmVyLnJlbW92ZUNsYXNzKCd0cmFuc2Zvcm0zZCcpO1xyXG4gICAgICAgIH0sIDEwMDApO1xyXG4gICAgfVxyXG4gICAgLy8gL0FuaW1hdGUgbGF5b3V0XHJcblxyXG4gICAgZnVuY3Rpb24gc2Nyb2xsVG9wKCkge1xyXG4gICAgICAgIGlmICgkKGJvZHkpLnNjcm9sbFRvcCgpID4gMTUwKSB7XHJcbiAgICAgICAgICAgICQoJy5sbXBpeGVscy1zY3JvbGwtdG8tdG9wJykucmVtb3ZlQ2xhc3MoJ2hpZGRlbi1idG4nKTtcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAkKCcubG1waXhlbHMtc2Nyb2xsLXRvLXRvcCcpLmFkZENsYXNzKCdoaWRkZW4tYnRuJyk7XHJcbiAgICAgICAgfVxyXG4gICAgfVxyXG5cclxuICAgIGZ1bmN0aW9uIHNraWxsc1N0eWxlcygpIHtcclxuICAgICAgICB2YXIgY3VzdG9tX3N0eWxlcyA9IFwiXCI7XHJcbiAgICAgICAgJCggJy5za2lsbC1jb250YWluZXInICkuZWFjaCggZnVuY3Rpb24oKSB7XHJcbiAgICAgICAgICAgIHZhciB2YWx1ZSA9ICQodGhpcykuYXR0cignZGF0YS12YWx1ZScpO1xyXG5cclxuICAgICAgICAgICAgaWYoIHZhbHVlID49IDEwMSkge1xyXG4gICAgICAgICAgICAgICAgdmFsdWUgPSAnMTAwJztcclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgaWYoIHR5cGVvZiB2YWx1ZSAhPSAndW5kZWZpbmVkJyApIHtcclxuICAgICAgICAgICAgICAgIHZhciBpZCA9ICQodGhpcykuYXR0cignaWQnKSxcclxuICAgICAgICAgICAgICAgICRjdXN0b21fc3R5bGUgPSAnIycgKyBpZCArICcgLnNraWxsLXBlcmNlbnRhZ2UgeyB3aWR0aDogJyArIHZhbHVlICsgJyU7IH0gJztcclxuICAgICAgICAgICAgICAgIGN1c3RvbV9zdHlsZXMgKz0gJGN1c3RvbV9zdHlsZTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0pO1xyXG4gICAgICAgICQoJ2hlYWQnKS5hcHBlbmQoJzxzdHlsZSBkYXRhLXN0eWxlcz1cImxldmVuLXRoZW1lLXNraWxscy1jc3NcIiB0eXBlPVwidGV4dC9jc3NcIj4nICsgY3VzdG9tX3N0eWxlcyArICc8L3N0eWxlPicpO1xyXG4gICAgfVxyXG5cclxuICAgIC8vT24gV2luZG93IGxvYWQgJiBSZXNpemVcclxuICAgICQod2luZG93KVxyXG4gICAgICAgIC5vbignbG9hZCcsIGZ1bmN0aW9uKCkgeyAvL0xvYWRcclxuICAgICAgICAgICAgLy8gQW5pbWF0aW9uIG9uIFBhZ2UgTG9hZGluZ1xyXG4gICAgICAgICAgICAkKFwiLnByZWxvYWRlclwiKS5mYWRlT3V0KCA4MDAsIFwibGluZWFyXCIgKTtcclxuICAgICAgICAgICAgYW5pbWF0ZUxheW91dCgpO1xyXG4gICAgICAgIH0pXHJcbiAgICAgICAgLm9uKCdoYXNoY2hhbmdlJywgZnVuY3Rpb24oZXZlbnQpIHtcclxuICAgICAgICAgICAgaWYobG9jYXRpb24uaGFzaCkge1xyXG4gICAgICAgICAgICAgICAgYWpheExvYWRlcigpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcblxyXG5cclxuICAgIC8vIE9uIERvY3VtZW50IExvYWRcclxuICAgICQoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uICgpIHtcclxuICAgICAgICB2YXIgbW92ZW1lbnRTdHJlbmd0aCA9IDE1O1xyXG4gICAgICAgIHZhciBoZWlnaHQgPSBtb3ZlbWVudFN0cmVuZ3RoIC8gJChkb2N1bWVudCkuaGVpZ2h0KCk7XHJcbiAgICAgICAgdmFyIHdpZHRoID0gbW92ZW1lbnRTdHJlbmd0aCAvICQoZG9jdW1lbnQpLndpZHRoKCk7XHJcbiAgICAgICAgJChcImJvZHlcIikub24oJ21vdXNlbW92ZScsIGZ1bmN0aW9uKGUpe1xyXG4gICAgICAgICAgICB2YXIgcGFnZVggPSBlLnBhZ2VYIC0gKCQoZG9jdW1lbnQpLndpZHRoKCkgLyAyKSxcclxuICAgICAgICAgICAgICAgIHBhZ2VZID0gZS5wYWdlWSAtICgkKGRvY3VtZW50KS5oZWlnaHQoKSAvIDIpLFxyXG4gICAgICAgICAgICAgICAgbmV3dmFsdWVYID0gd2lkdGggKiBwYWdlWCAqIC0xLFxyXG4gICAgICAgICAgICAgICAgbmV3dmFsdWVZID0gaGVpZ2h0ICogcGFnZVkgKiAtMTtcclxuICAgICAgICAgICAgaWYgKCQoJy5wYWdlLWNvbnRhaW5lcicpLmhhc0NsYXNzKCdiZy1tb3ZlLWVmZmVjdCcpKSB7XHJcbiAgICAgICAgICAgICAgICB2YXIgZWxlbWVudHMgPSAkKCcuaG9tZS1waG90byAuaHAtaW5uZXI6bm90KC53aXRob3V0LW1vdmUpLCAubG0tYW5pbWF0ZWQtYmcnKTtcclxuICAgICAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgICAgIHZhciBlbGVtZW50cyA9ICQoJy5ob21lLXBob3RvIC5ocC1pbm5lcjpub3QoLndpdGhvdXQtbW92ZSknKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICBlbGVtZW50cy5hZGRDbGFzcygndHJhbnNpdGlvbicpO1xyXG4gICAgICAgICAgICBlbGVtZW50cy5jc3Moe1xyXG4gICAgICAgICAgICAgICAgXCJiYWNrZ3JvdW5kLXBvc2l0aW9uXCI6IFwiY2FsYyggNTAlICsgXCIgKyBuZXd2YWx1ZVggKyBcInB4ICkgY2FsYyggNTAlICsgXCIgKyBuZXd2YWx1ZVkgKyBcInB4IClcIixcclxuICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uKCkge1xyXG4gICAgICAgICAgICAgICAgZWxlbWVudHMucmVtb3ZlQ2xhc3MoJ3RyYW5zaXRpb24nKTtcclxuICAgICAgICAgICAgfSwgMzAwKTtcclxuICAgICAgICB9KVxyXG4gICAgICAgIC5zY3JvbGwoZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICBzY3JvbGxUb3AoKTtcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgLy8gSW5pdGlhbGl6ZSBQb3J0Zm9saW8gZ3JpZFxyXG4gICAgICAgIHZhciAkcG9ydGZvbGlvX2NvbnRhaW5lciA9ICQoXCIucG9ydGZvbGlvLWdyaWRcIiksXHJcbiAgICAgICAgICAgICRnYWxsZXJ5X2NvbnRhaW5lciA9ICQoXCIjcG9ydGZvbGlvLWdhbGxlcnktZ3JpZFwiKTtcclxuXHJcbiAgICAgICAgJGdhbGxlcnlfY29udGFpbmVyLmltYWdlc0xvYWRlZChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgICRnYWxsZXJ5X2NvbnRhaW5lci5tYXNvbnJ5KCk7XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICRwb3J0Zm9saW9fY29udGFpbmVyLmltYWdlc0xvYWRlZChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgIHBvcnRmb2xpb19pbml0KHRoaXMpO1xyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICBpbWFnZUNhcm91c2VsKCk7XHJcblxyXG4gICAgICAgIC8vIEJsb2cgZ3JpZCBpbml0XHJcbiAgICAgICAgdmFyICRjb250YWluZXIgPSAkKFwiLmJsb2ctbWFzb25yeVwiKTtcclxuICAgICAgICAkY29udGFpbmVyLmltYWdlc0xvYWRlZChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgICRjb250YWluZXIubWFzb25yeSh7XHJcbiAgICAgICAgICAgICAgaXRlbVNlbGVjdG9yOiAnLml0ZW0nLFxyXG4gICAgICAgICAgICAgIHJlc2l6ZTogZmFsc2VcclxuICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIC8vIE1vYmlsZSBtZW51XHJcbiAgICAgICAgJCgnLm1lbnUtdG9nZ2xlJykub24oXCJjbGlja1wiLCBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgICQoJy5zaXRlLW5hdicpLmFkZENsYXNzKCdhbmltYXRlJyk7XHJcbiAgICAgICAgICAgICQoJy5zaXRlLW5hdicpLnRvZ2dsZUNsYXNzKCdtb2JpbGUtbWVudS1oaWRlJyk7XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIC8vIFRleHQgcm90YXRpb25cclxuICAgICAgICAkKCcudGV4dC1yb3RhdGlvbicpLm93bENhcm91c2VsKHtcclxuICAgICAgICAgICAgbG9vcDogdHJ1ZSxcclxuICAgICAgICAgICAgZG90czogZmFsc2UsXHJcbiAgICAgICAgICAgIG5hdjogZmFsc2UsXHJcbiAgICAgICAgICAgIG1hcmdpbjogMTAsXHJcbiAgICAgICAgICAgIGl0ZW1zOiAxLFxyXG4gICAgICAgICAgICBhdXRvcGxheTogdHJ1ZSxcclxuICAgICAgICAgICAgYXV0b3BsYXlIb3ZlclBhdXNlOiBmYWxzZSxcclxuICAgICAgICAgICAgYXV0b3BsYXlUaW1lb3V0OiAzODAwLFxyXG4gICAgICAgICAgICBhbmltYXRlT3V0OiAnZmFkZU91dCcsXHJcbiAgICAgICAgICAgIGFuaW1hdGVJbjogJ2ZhZGVJbidcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgLy8gVGVzdGltb25pYWxzIFNsaWRlclxyXG4gICAgICAgICQoXCIudGVzdGltb25pYWxzLm93bC1jYXJvdXNlbFwiKS5vd2xDYXJvdXNlbCh7XHJcbiAgICAgICAgICAgIG5hdjogZmFsc2UsIC8vIFNob3cgbmV4dC9wcmV2IGJ1dHRvbnMuXHJcbiAgICAgICAgICAgIGl0ZW1zOiAzLCAvLyBUaGUgbnVtYmVyIG9mIGl0ZW1zIHlvdSB3YW50IHRvIHNlZSBvbiB0aGUgc2NyZWVuLlxyXG4gICAgICAgICAgICBsb29wOiBmYWxzZSwgLy8gSW5maW5pdHkgbG9vcC4gRHVwbGljYXRlIGxhc3QgYW5kIGZpcnN0IGl0ZW1zIHRvIGdldCBsb29wIGlsbHVzaW9uLlxyXG4gICAgICAgICAgICBuYXZUZXh0OiBmYWxzZSxcclxuICAgICAgICAgICAgbWFyZ2luOiAyNSxcclxuICAgICAgICAgICAgcmVzcG9uc2l2ZSA6IHtcclxuICAgICAgICAgICAgICAgIC8vIGJyZWFrcG9pbnQgZnJvbSAwIHVwXHJcbiAgICAgICAgICAgICAgICAwIDoge1xyXG4gICAgICAgICAgICAgICAgICAgIGl0ZW1zOiAxLFxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIC8vIGJyZWFrcG9pbnQgZnJvbSA0ODAgdXBcclxuICAgICAgICAgICAgICAgIDQ4MCA6IHtcclxuICAgICAgICAgICAgICAgICAgICBpdGVtczogMSxcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAvLyBicmVha3BvaW50IGZyb20gNzY4IHVwXHJcbiAgICAgICAgICAgICAgICA3NjggOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgaXRlbXM6IDIsXHJcbiAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgMTIwMCA6IHtcclxuICAgICAgICAgICAgICAgICAgICBpdGVtczogMixcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAvLyBDbGllbnRzIFNsaWRlclxyXG4gICAgICAgICQoXCIuY2xpZW50cy5vd2wtY2Fyb3VzZWxcIikuaW1hZ2VzTG9hZGVkKCkub3dsQ2Fyb3VzZWwoe1xyXG4gICAgICAgICAgICBuYXY6IGZhbHNlLCAvLyBTaG93IG5leHQvcHJldiBidXR0b25zLlxyXG4gICAgICAgICAgICBpdGVtczogMiwgLy8gVGhlIG51bWJlciBvZiBpdGVtcyB5b3Ugd2FudCB0byBzZWUgb24gdGhlIHNjcmVlbi5cclxuICAgICAgICAgICAgbG9vcDogZmFsc2UsIC8vIEluZmluaXR5IGxvb3AuIER1cGxpY2F0ZSBsYXN0IGFuZCBmaXJzdCBpdGVtcyB0byBnZXQgbG9vcCBpbGx1c2lvbi5cclxuICAgICAgICAgICAgbmF2VGV4dDogZmFsc2UsXHJcbiAgICAgICAgICAgIG1hcmdpbjogMTAsXHJcbiAgICAgICAgICAgIGF1dG9IZWlnaHQ6IGZhbHNlLFxyXG4gICAgICAgICAgICByZXNwb25zaXZlIDoge1xyXG4gICAgICAgICAgICAgICAgLy8gYnJlYWtwb2ludCBmcm9tIDAgdXBcclxuICAgICAgICAgICAgICAgIDAgOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgaXRlbXM6IDIsXHJcbiAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgLy8gYnJlYWtwb2ludCBmcm9tIDc2OCB1cFxyXG4gICAgICAgICAgICAgICAgNzY4IDoge1xyXG4gICAgICAgICAgICAgICAgICAgIGl0ZW1zOiA0LFxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIDEyMDAgOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgaXRlbXM6IDYsXHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgLy8gTGlnaHRib3ggaW5pdFxyXG4gICAgICAgIGJvZHkubWFnbmlmaWNQb3B1cCh7XHJcbiAgICAgICAgICAgIGZpeGVkQ29udGVudFBvczogZmFsc2UsXHJcbiAgICAgICAgICAgIGRlbGVnYXRlOiAnYS5saWdodGJveCcsXHJcbiAgICAgICAgICAgIHR5cGU6ICdpbWFnZScsXHJcbiAgICAgICAgICAgIHJlbW92YWxEZWxheTogMzAwLFxyXG5cclxuICAgICAgICAgICAgLy8gQ2xhc3MgdGhhdCBpcyBhZGRlZCB0byBwb3B1cCB3cmFwcGVyIGFuZCBiYWNrZ3JvdW5kXHJcbiAgICAgICAgICAgIC8vIG1ha2UgaXQgdW5pcXVlIHRvIGFwcGx5IHlvdXIgQ1NTIGFuaW1hdGlvbnMganVzdCB0byB0aGlzIGV4YWN0IHBvcHVwXHJcbiAgICAgICAgICAgIG1haW5DbGFzczogJ21mcC1mYWRlJyxcclxuICAgICAgICAgICAgaW1hZ2U6IHtcclxuICAgICAgICAgICAgICAgIC8vIG9wdGlvbnMgZm9yIGltYWdlIGNvbnRlbnQgdHlwZVxyXG4gICAgICAgICAgICAgICAgdGl0bGVTcmM6ICd0aXRsZScsXHJcbiAgICAgICAgICAgICAgICBnYWxsZXJ5OiB7XHJcbiAgICAgICAgICAgICAgICAgICAgZW5hYmxlZDogdHJ1ZVxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgfSxcclxuXHJcbiAgICAgICAgICAgIGlmcmFtZToge1xyXG4gICAgICAgICAgICAgICAgbWFya3VwOiAnPGRpdiBjbGFzcz1cIm1mcC1pZnJhbWUtc2NhbGVyXCI+JytcclxuICAgICAgICAgICAgICAgICAgICAgICAgJzxkaXYgY2xhc3M9XCJtZnAtY2xvc2VcIj48L2Rpdj4nK1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAnPGlmcmFtZSBjbGFzcz1cIm1mcC1pZnJhbWVcIiBmcmFtZWJvcmRlcj1cIjBcIiBhbGxvd2Z1bGxzY3JlZW4+PC9pZnJhbWU+JytcclxuICAgICAgICAgICAgICAgICAgICAgICAgJzxkaXYgY2xhc3M9XCJtZnAtdGl0bGUgbWZwLWJvdHRvbS1pZnJhbWUtdGl0bGVcIj48L2Rpdj4nK1xyXG4gICAgICAgICAgICAgICAgICAgICAgJzwvZGl2PicsIC8vIEhUTUwgbWFya3VwIG9mIHBvcHVwLCBgbWZwLWNsb3NlYCB3aWxsIGJlIHJlcGxhY2VkIGJ5IHRoZSBjbG9zZSBidXR0b25cclxuXHJcbiAgICAgICAgICAgICAgICBwYXR0ZXJuczoge1xyXG4gICAgICAgICAgICAgICAgICAgIHlvdXR1YmU6IHtcclxuICAgICAgICAgICAgICAgICAgICAgIGluZGV4OiAneW91dHViZS5jb20vJywgLy8gU3RyaW5nIHRoYXQgZGV0ZWN0cyB0eXBlIG9mIHZpZGVvIChpbiB0aGlzIGNhc2UgWW91VHViZSkuIFNpbXBseSB2aWEgdXJsLmluZGV4T2YoaW5kZXgpLlxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgIGlkOiBudWxsLCAvLyBTdHJpbmcgdGhhdCBzcGxpdHMgVVJMIGluIGEgdHdvIHBhcnRzLCBzZWNvbmQgcGFydCBzaG91bGQgYmUgJWlkJVxyXG4gICAgICAgICAgICAgICAgICAgICAgLy8gT3IgbnVsbCAtIGZ1bGwgVVJMIHdpbGwgYmUgcmV0dXJuZWRcclxuICAgICAgICAgICAgICAgICAgICAgIC8vIE9yIGEgZnVuY3Rpb24gdGhhdCBzaG91bGQgcmV0dXJuICVpZCUsIGZvciBleGFtcGxlOlxyXG4gICAgICAgICAgICAgICAgICAgICAgLy8gaWQ6IGZ1bmN0aW9uKHVybCkgeyByZXR1cm4gJ3BhcnNlZCBpZCc7IH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICBzcmM6ICclaWQlP2F1dG9wbGF5PTEnIC8vIFVSTCB0aGF0IHdpbGwgYmUgc2V0IGFzIGEgc291cmNlIGZvciBpZnJhbWUuXHJcbiAgICAgICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgICAgICB2aW1lbzoge1xyXG4gICAgICAgICAgICAgICAgICAgICAgaW5kZXg6ICd2aW1lby5jb20vJyxcclxuICAgICAgICAgICAgICAgICAgICAgIGlkOiAnLycsXHJcbiAgICAgICAgICAgICAgICAgICAgICBzcmM6ICcvL3BsYXllci52aW1lby5jb20vdmlkZW8vJWlkJT9hdXRvcGxheT0xJ1xyXG4gICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgZ21hcHM6IHtcclxuICAgICAgICAgICAgICAgICAgICAgIGluZGV4OiAnLy9tYXBzLmdvb2dsZS4nLFxyXG4gICAgICAgICAgICAgICAgICAgICAgc3JjOiAnJWlkJSZvdXRwdXQ9ZW1iZWQnXHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfSxcclxuXHJcbiAgICAgICAgICAgICAgICBzcmNBY3Rpb246ICdpZnJhbWVfc3JjJywgLy8gVGVtcGxhdGluZyBvYmplY3Qga2V5LiBGaXJzdCBwYXJ0IGRlZmluZXMgQ1NTIHNlbGVjdG9yLCBzZWNvbmQgYXR0cmlidXRlLiBcImlmcmFtZV9zcmNcIiBtZWFuczogZmluZCBcImlmcmFtZVwiIGFuZCBzZXQgYXR0cmlidXRlIFwic3JjXCIuXHJcbiAgICAgICAgICAgIH0sXHJcblxyXG4gICAgICAgICAgICBjYWxsYmFja3M6IHtcclxuICAgICAgICAgICAgICAgIG1hcmt1cFBhcnNlOiBmdW5jdGlvbih0ZW1wbGF0ZSwgdmFsdWVzLCBpdGVtKSB7XHJcbiAgICAgICAgICAgICAgICAgdmFsdWVzLnRpdGxlID0gaXRlbS5lbC5hdHRyKCd0aXRsZScpO1xyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAkKCcuYWpheC1wYWdlLWxvYWQtbGluaycpLm1hZ25pZmljUG9wdXAoe1xyXG4gICAgICAgICAgICB0eXBlOiAnYWpheCcsXHJcbiAgICAgICAgICAgIHJlbW92YWxEZWxheTogMzAwLFxyXG4gICAgICAgICAgICBtYWluQ2xhc3M6ICdtZnAtZmFkZScsXHJcbiAgICAgICAgICAgIGdhbGxlcnk6IHtcclxuICAgICAgICAgICAgICAgIGVuYWJsZWQ6IHRydWVcclxuICAgICAgICAgICAgfSxcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgJCgnLnBvcnRmb2xpby1wYWdlLXdyYXBwZXIgLnBvcnRmb2xpby1ncmlkJykuZWFjaChmdW5jdGlvbigpIHtcclxuICAgICAgICAgICAgJCh0aGlzKS5tYWduaWZpY1BvcHVwKHtcclxuICAgICAgICAgICAgICAgIGRlbGVnYXRlOiAnYS5nYWxsZXJ5LWxpZ2h0Ym94JyxcclxuICAgICAgICAgICAgICAgIHR5cGU6ICdpbWFnZScsXHJcbiAgICAgICAgICAgICAgICBnYWxsZXJ5OiB7XHJcbiAgICAgICAgICAgICAgICAgIGVuYWJsZWQ6dHJ1ZVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgJCgnLmZvcm0tY29udHJvbCcpLnZhbCgnJyk7XHJcblxyXG4gICAgICAgICQoXCIuZm9ybS1jb250cm9sXCIpLm9uKFwiZm9jdXNpblwiLCBmdW5jdGlvbigpe1xyXG4gICAgICAgICAgICAkKHRoaXMpLnBhcmVudCgnLmZvcm0tZ3JvdXAnKS5hZGRDbGFzcygnZm9ybS1ncm91cC1mb2N1cycpO1xyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAkKFwiLmZvcm0tY29udHJvbFwiKS5vbihcImZvY3Vzb3V0XCIsIGZ1bmN0aW9uKCl7XHJcbiAgICAgICAgICAgIGlmKCQodGhpcykudmFsKCkubGVuZ3RoID09PSAwKSB7XHJcbiAgICAgICAgICAgICAgICAkKHRoaXMpLnBhcmVudCgnLmZvcm0tZ3JvdXAnKS5yZW1vdmVDbGFzcygnZm9ybS1ncm91cC1mb2N1cycpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICQoJ2JvZHknKS5hcHBlbmQoJzxkaXYgaWQ9XCJwYWdlLWFqYXgtbG9hZGVkXCIgY2xhc3M9XCJwYWdlLXBvcnRmb2xpby1sb2FkZWQgYW5pbWF0ZWQgZmFkZUluTGVmdFwiIHN0eWxlPVwiZGlzcGxheTogbm9uZVwiPjxkaXYgY2xhc3M9XCJwcmVsb2FkZXItcG9ydGZvbGlvXCI+PGRpdiBjbGFzcz1cInByZWxvYWRlci1hbmltYXRpb25cIj48ZGl2IGNsYXNzPVwicHJlbG9hZGVyLXNwaW5uZXJcIj48L2Rpdj48L2Rpdj48L2Rpdj48L2Rpdj4nKTtcclxuXHJcbiAgICAgICAgYWpheExvYWRlcigpO1xyXG5cclxuICAgICAgICAvLyBTaWRlYmFyIHRvZ2dsZVxyXG4gICAgICAgICQoJy5zaWRlYmFyLXRvZ2dsZScpLm9uKFwiY2xpY2tcIiwgZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICAkKCcjYmxvZy1zaWRlYmFyJykudG9nZ2xlQ2xhc3MoJ29wZW4nKTtcclxuICAgICAgICAgICAgJCh0aGlzKS50b2dnbGVDbGFzcygnb3BlbicpO1xyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAkKCcubG1waXhlbHMtc2Nyb2xsLXRvLXRvcCcpLmNsaWNrKGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgJCgnYm9keSxodG1sJykuYW5pbWF0ZSh7XHJcbiAgICAgICAgICAgICAgICBzY3JvbGxUb3A6IDBcclxuICAgICAgICAgICAgfSwgNDAwKTtcclxuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgICAgIH0pO1xyXG4gICAgICAgIFxyXG4gICAgICAgIC8vR29vZ2xlIE1hcHNcclxuICAgICAgICAkKFwiI21hcFwiKS5nb29nbGVNYXAoe1xyXG4gICAgICAgICAgICB6b29tOiAxNiAvLyBHb29nbGUgTWFwIFpPT00uIFlvdSBjYW4gY2hhbmdlIHRoaXMgdmFsdWVcclxuICAgICAgICB9KTtcclxuICAgICAgICAkKFwiI21hcFwiKS5hZGRNYXJrZXIoe1xyXG4gICAgICAgICAgICBhZGRyZXNzOiBcIlM2MDEgVG93bnNlbmQgU3RyZWV0LCBTYW4gRnJhbmNpc2NvLCBDYWxpZm9ybmlhLCBVU0FcIiwgLy8gWW91ciBBZGRyZXNzLiBDaGFuZ2UgaXRcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgc2Nyb2xsVG9wKCk7XHJcblxyXG4gICAgICAgIHNraWxsc1N0eWxlcygpO1xyXG4gICAgfSk7XHJcblxyXG59KShqUXVlcnkpOyIsIi8qIGdsb2JhbCBCdW4gLS0gRGVubyBjYXNlICovXG5tb2R1bGUuZXhwb3J0cyA9IHR5cGVvZiBCdW4gPT0gJ2Z1bmN0aW9uJyAmJiBCdW4gJiYgdHlwZW9mIEJ1bi52ZXJzaW9uID09ICdzdHJpbmcnO1xuIiwiJ3VzZSBzdHJpY3QnO1xudmFyIGdsb2JhbCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9nbG9iYWwnKTtcbnZhciBhcHBseSA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9mdW5jdGlvbi1hcHBseScpO1xudmFyIGlzQ2FsbGFibGUgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvaXMtY2FsbGFibGUnKTtcbnZhciBFTkdJTkVfSVNfQlVOID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2VuZ2luZS1pcy1idW4nKTtcbnZhciBVU0VSX0FHRU5UID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2VuZ2luZS11c2VyLWFnZW50Jyk7XG52YXIgYXJyYXlTbGljZSA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9hcnJheS1zbGljZScpO1xudmFyIHZhbGlkYXRlQXJndW1lbnRzTGVuZ3RoID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL3ZhbGlkYXRlLWFyZ3VtZW50cy1sZW5ndGgnKTtcblxudmFyIEZ1bmN0aW9uID0gZ2xvYmFsLkZ1bmN0aW9uO1xuLy8gZGlydHkgSUU5LSBhbmQgQnVuIDAuMy4wLSBjaGVja3NcbnZhciBXUkFQID0gL01TSUUgLlxcLi8udGVzdChVU0VSX0FHRU5UKSB8fCBFTkdJTkVfSVNfQlVOICYmIChmdW5jdGlvbiAoKSB7XG4gIHZhciB2ZXJzaW9uID0gZ2xvYmFsLkJ1bi52ZXJzaW9uLnNwbGl0KCcuJyk7XG4gIHJldHVybiB2ZXJzaW9uLmxlbmd0aCA8IDMgfHwgdmVyc2lvblswXSA9PSAwICYmICh2ZXJzaW9uWzFdIDwgMyB8fCB2ZXJzaW9uWzFdID09IDMgJiYgdmVyc2lvblsyXSA9PSAwKTtcbn0pKCk7XG5cbi8vIElFOS0gLyBCdW4gMC4zLjAtIHNldFRpbWVvdXQgLyBzZXRJbnRlcnZhbCAvIHNldEltbWVkaWF0ZSBhZGRpdGlvbmFsIHBhcmFtZXRlcnMgZml4XG4vLyBodHRwczovL2h0bWwuc3BlYy53aGF0d2cub3JnL211bHRpcGFnZS90aW1lcnMtYW5kLXVzZXItcHJvbXB0cy5odG1sI3RpbWVyc1xuLy8gaHR0cHM6Ly9naXRodWIuY29tL292ZW4tc2gvYnVuL2lzc3Vlcy8xNjMzXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uIChzY2hlZHVsZXIsIGhhc1RpbWVBcmcpIHtcbiAgdmFyIGZpcnN0UGFyYW1JbmRleCA9IGhhc1RpbWVBcmcgPyAyIDogMTtcbiAgcmV0dXJuIFdSQVAgPyBmdW5jdGlvbiAoaGFuZGxlciwgdGltZW91dCAvKiAsIC4uLmFyZ3VtZW50cyAqLykge1xuICAgIHZhciBib3VuZEFyZ3MgPSB2YWxpZGF0ZUFyZ3VtZW50c0xlbmd0aChhcmd1bWVudHMubGVuZ3RoLCAxKSA+IGZpcnN0UGFyYW1JbmRleDtcbiAgICB2YXIgZm4gPSBpc0NhbGxhYmxlKGhhbmRsZXIpID8gaGFuZGxlciA6IEZ1bmN0aW9uKGhhbmRsZXIpO1xuICAgIHZhciBwYXJhbXMgPSBib3VuZEFyZ3MgPyBhcnJheVNsaWNlKGFyZ3VtZW50cywgZmlyc3RQYXJhbUluZGV4KSA6IFtdO1xuICAgIHZhciBjYWxsYmFjayA9IGJvdW5kQXJncyA/IGZ1bmN0aW9uICgpIHtcbiAgICAgIGFwcGx5KGZuLCB0aGlzLCBwYXJhbXMpO1xuICAgIH0gOiBmbjtcbiAgICByZXR1cm4gaGFzVGltZUFyZyA/IHNjaGVkdWxlcihjYWxsYmFjaywgdGltZW91dCkgOiBzY2hlZHVsZXIoY2FsbGJhY2spO1xuICB9IDogc2NoZWR1bGVyO1xufTtcbiIsInZhciAkVHlwZUVycm9yID0gVHlwZUVycm9yO1xuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uIChwYXNzZWQsIHJlcXVpcmVkKSB7XG4gIGlmIChwYXNzZWQgPCByZXF1aXJlZCkgdGhyb3cgJFR5cGVFcnJvcignTm90IGVub3VnaCBhcmd1bWVudHMnKTtcbiAgcmV0dXJuIHBhc3NlZDtcbn07XG4iLCIndXNlIHN0cmljdCc7XG52YXIgJCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9leHBvcnQnKTtcbnZhciAkZmluZCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9hcnJheS1pdGVyYXRpb24nKS5maW5kO1xudmFyIGFkZFRvVW5zY29wYWJsZXMgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvYWRkLXRvLXVuc2NvcGFibGVzJyk7XG5cbnZhciBGSU5EID0gJ2ZpbmQnO1xudmFyIFNLSVBTX0hPTEVTID0gdHJ1ZTtcblxuLy8gU2hvdWxkbid0IHNraXAgaG9sZXNcbmlmIChGSU5EIGluIFtdKSBBcnJheSgxKVtGSU5EXShmdW5jdGlvbiAoKSB7IFNLSVBTX0hPTEVTID0gZmFsc2U7IH0pO1xuXG4vLyBgQXJyYXkucHJvdG90eXBlLmZpbmRgIG1ldGhvZFxuLy8gaHR0cHM6Ly90YzM5LmVzL2VjbWEyNjIvI3NlYy1hcnJheS5wcm90b3R5cGUuZmluZFxuJCh7IHRhcmdldDogJ0FycmF5JywgcHJvdG86IHRydWUsIGZvcmNlZDogU0tJUFNfSE9MRVMgfSwge1xuICBmaW5kOiBmdW5jdGlvbiBmaW5kKGNhbGxiYWNrZm4gLyogLCB0aGF0ID0gdW5kZWZpbmVkICovKSB7XG4gICAgcmV0dXJuICRmaW5kKHRoaXMsIGNhbGxiYWNrZm4sIGFyZ3VtZW50cy5sZW5ndGggPiAxID8gYXJndW1lbnRzWzFdIDogdW5kZWZpbmVkKTtcbiAgfVxufSk7XG5cbi8vIGh0dHBzOi8vdGMzOS5lcy9lY21hMjYyLyNzZWMtYXJyYXkucHJvdG90eXBlLUBAdW5zY29wYWJsZXNcbmFkZFRvVW5zY29wYWJsZXMoRklORCk7XG4iLCIndXNlIHN0cmljdCc7XG52YXIgJCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9leHBvcnQnKTtcbnZhciB1bmN1cnJ5VGhpcyA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9mdW5jdGlvbi11bmN1cnJ5LXRoaXMnKTtcbnZhciByZXF1aXJlT2JqZWN0Q29lcmNpYmxlID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL3JlcXVpcmUtb2JqZWN0LWNvZXJjaWJsZScpO1xudmFyIHRvSW50ZWdlck9ySW5maW5pdHkgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvdG8taW50ZWdlci1vci1pbmZpbml0eScpO1xudmFyIHRvU3RyaW5nID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL3RvLXN0cmluZycpO1xuXG52YXIgc3RyaW5nU2xpY2UgPSB1bmN1cnJ5VGhpcygnJy5zbGljZSk7XG52YXIgbWF4ID0gTWF0aC5tYXg7XG52YXIgbWluID0gTWF0aC5taW47XG5cbi8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSB1bmljb3JuL3ByZWZlci1zdHJpbmctc2xpY2UgLS0gcmVxdWlyZWQgZm9yIHRlc3RpbmdcbnZhciBGT1JDRUQgPSAhJycuc3Vic3RyIHx8ICdhYicuc3Vic3RyKC0xKSAhPT0gJ2InO1xuXG4vLyBgU3RyaW5nLnByb3RvdHlwZS5zdWJzdHJgIG1ldGhvZFxuLy8gaHR0cHM6Ly90YzM5LmVzL2VjbWEyNjIvI3NlYy1zdHJpbmcucHJvdG90eXBlLnN1YnN0clxuJCh7IHRhcmdldDogJ1N0cmluZycsIHByb3RvOiB0cnVlLCBmb3JjZWQ6IEZPUkNFRCB9LCB7XG4gIHN1YnN0cjogZnVuY3Rpb24gc3Vic3RyKHN0YXJ0LCBsZW5ndGgpIHtcbiAgICB2YXIgdGhhdCA9IHRvU3RyaW5nKHJlcXVpcmVPYmplY3RDb2VyY2libGUodGhpcykpO1xuICAgIHZhciBzaXplID0gdGhhdC5sZW5ndGg7XG4gICAgdmFyIGludFN0YXJ0ID0gdG9JbnRlZ2VyT3JJbmZpbml0eShzdGFydCk7XG4gICAgdmFyIGludExlbmd0aCwgaW50RW5kO1xuICAgIGlmIChpbnRTdGFydCA9PT0gSW5maW5pdHkpIGludFN0YXJ0ID0gMDtcbiAgICBpZiAoaW50U3RhcnQgPCAwKSBpbnRTdGFydCA9IG1heChzaXplICsgaW50U3RhcnQsIDApO1xuICAgIGludExlbmd0aCA9IGxlbmd0aCA9PT0gdW5kZWZpbmVkID8gc2l6ZSA6IHRvSW50ZWdlck9ySW5maW5pdHkobGVuZ3RoKTtcbiAgICBpZiAoaW50TGVuZ3RoIDw9IDAgfHwgaW50TGVuZ3RoID09PSBJbmZpbml0eSkgcmV0dXJuICcnO1xuICAgIGludEVuZCA9IG1pbihpbnRTdGFydCArIGludExlbmd0aCwgc2l6ZSk7XG4gICAgcmV0dXJuIGludFN0YXJ0ID49IGludEVuZCA/ICcnIDogc3RyaW5nU2xpY2UodGhhdCwgaW50U3RhcnQsIGludEVuZCk7XG4gIH1cbn0pO1xuIiwidmFyICQgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvZXhwb3J0Jyk7XG52YXIgZ2xvYmFsID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2dsb2JhbCcpO1xudmFyIHNjaGVkdWxlcnNGaXggPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvc2NoZWR1bGVycy1maXgnKTtcblxudmFyIHNldEludGVydmFsID0gc2NoZWR1bGVyc0ZpeChnbG9iYWwuc2V0SW50ZXJ2YWwsIHRydWUpO1xuXG4vLyBCdW4gLyBJRTktIHNldEludGVydmFsIGFkZGl0aW9uYWwgcGFyYW1ldGVycyBmaXhcbi8vIGh0dHBzOi8vaHRtbC5zcGVjLndoYXR3Zy5vcmcvbXVsdGlwYWdlL3RpbWVycy1hbmQtdXNlci1wcm9tcHRzLmh0bWwjZG9tLXNldGludGVydmFsXG4kKHsgZ2xvYmFsOiB0cnVlLCBiaW5kOiB0cnVlLCBmb3JjZWQ6IGdsb2JhbC5zZXRJbnRlcnZhbCAhPT0gc2V0SW50ZXJ2YWwgfSwge1xuICBzZXRJbnRlcnZhbDogc2V0SW50ZXJ2YWxcbn0pO1xuIiwidmFyICQgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvZXhwb3J0Jyk7XG52YXIgZ2xvYmFsID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2dsb2JhbCcpO1xudmFyIHNjaGVkdWxlcnNGaXggPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvc2NoZWR1bGVycy1maXgnKTtcblxudmFyIHNldFRpbWVvdXQgPSBzY2hlZHVsZXJzRml4KGdsb2JhbC5zZXRUaW1lb3V0LCB0cnVlKTtcblxuLy8gQnVuIC8gSUU5LSBzZXRUaW1lb3V0IGFkZGl0aW9uYWwgcGFyYW1ldGVycyBmaXhcbi8vIGh0dHBzOi8vaHRtbC5zcGVjLndoYXR3Zy5vcmcvbXVsdGlwYWdlL3RpbWVycy1hbmQtdXNlci1wcm9tcHRzLmh0bWwjZG9tLXNldHRpbWVvdXRcbiQoeyBnbG9iYWw6IHRydWUsIGJpbmQ6IHRydWUsIGZvcmNlZDogZ2xvYmFsLnNldFRpbWVvdXQgIT09IHNldFRpbWVvdXQgfSwge1xuICBzZXRUaW1lb3V0OiBzZXRUaW1lb3V0XG59KTtcbiIsIi8vIFRPRE86IFJlbW92ZSB0aGlzIG1vZHVsZSBmcm9tIGBjb3JlLWpzQDRgIHNpbmNlIGl0J3Mgc3BsaXQgdG8gbW9kdWxlcyBsaXN0ZWQgYmVsb3dcbnJlcXVpcmUoJy4uL21vZHVsZXMvd2ViLnNldC1pbnRlcnZhbCcpO1xucmVxdWlyZSgnLi4vbW9kdWxlcy93ZWIuc2V0LXRpbWVvdXQnKTtcbiJdLCJuYW1lcyI6WyIkIiwiYm9keSIsImltYWdlQ2Fyb3VzZWwiLCJlYWNoIiwiaW1hZ2VzTG9hZGVkIiwib3dsQ2Fyb3VzZWwiLCJzbWFydFNwZWVkIiwiaXRlbXMiLCJsb29wIiwiZG90cyIsIm5hdiIsIm5hdlRleHQiLCJhdXRvSGVpZ2h0IiwibWFyZ2luIiwiYWpheExvYWRlciIsImFqYXhMb2FkZWRDb250ZW50Iiwic2hvd0NvbnRlbnQiLCJyZW1vdmVDbGFzcyIsInNob3ciLCJhZGRDbGFzcyIsImhpZGVDb250ZW50Iiwic2V0VGltZW91dCIsImh0bWwiLCJoaWRlIiwiaHJlZiIsImF0dHIiLCJsb2NhdGlvbiIsImhhc2giLCJzcGxpdCIsInN1YnN0ciIsImxlbmd0aCIsInRvTG9hZCIsImxvYWQiLCJkb2N1bWVudCIsIm9uIiwiZSIsInByZXZlbnREZWZhdWx0IiwidmFsaWRhdG9yIiwiaXNEZWZhdWx0UHJldmVudGVkIiwidXJsIiwiYWpheCIsInR5cGUiLCJkYXRhIiwic2VyaWFsaXplIiwic3VjY2VzcyIsIm1lc3NhZ2VBbGVydCIsIm1lc3NhZ2VUZXh0IiwibWVzc2FnZSIsImFsZXJ0Qm94IiwiZmluZCIsInJlc2V0IiwicG9ydGZvbGlvX2luaXQiLCJwb3J0Zm9saW9fZ3JpZF9jb250YWluZXIiLCJwb3J0Zm9saW9fZ3JpZF9jb250YWluZXJfaWQiLCJwb3J0Zm9saW9fZ3JpZCIsInBvcnRmb2xpb19maWx0ZXIiLCJwb3J0Zm9saW9fZmlsdGVyX2l0ZW0iLCJzaHVmZmxlIiwic3BlZWQiLCJpdGVtU2VsZWN0b3IiLCJwYXJlbnQiLCJhbmltYXRlTGF5b3V0Iiwid2luZG93V2lkdGgiLCJ3aW5kb3ciLCJ3aWR0aCIsImFuaW1hdGVkQ29udGFpbmVyIiwiYW5pbWF0ZVR5cGUiLCJzY3JvbGxUb3AiLCJza2lsbHNTdHlsZXMiLCJjdXN0b21fc3R5bGVzIiwidmFsdWUiLCJpZCIsIiRjdXN0b21fc3R5bGUiLCJhcHBlbmQiLCJmYWRlT3V0IiwiZXZlbnQiLCJyZWFkeSIsIm1vdmVtZW50U3RyZW5ndGgiLCJoZWlnaHQiLCJwYWdlWCIsInBhZ2VZIiwibmV3dmFsdWVYIiwibmV3dmFsdWVZIiwiaGFzQ2xhc3MiLCJlbGVtZW50cyIsImNzcyIsInNjcm9sbCIsIiRwb3J0Zm9saW9fY29udGFpbmVyIiwiJGdhbGxlcnlfY29udGFpbmVyIiwibWFzb25yeSIsIiRjb250YWluZXIiLCJyZXNpemUiLCJ0b2dnbGVDbGFzcyIsImF1dG9wbGF5IiwiYXV0b3BsYXlIb3ZlclBhdXNlIiwiYXV0b3BsYXlUaW1lb3V0IiwiYW5pbWF0ZU91dCIsImFuaW1hdGVJbiIsInJlc3BvbnNpdmUiLCJtYWduaWZpY1BvcHVwIiwiZml4ZWRDb250ZW50UG9zIiwiZGVsZWdhdGUiLCJyZW1vdmFsRGVsYXkiLCJtYWluQ2xhc3MiLCJpbWFnZSIsInRpdGxlU3JjIiwiZ2FsbGVyeSIsImVuYWJsZWQiLCJpZnJhbWUiLCJtYXJrdXAiLCJwYXR0ZXJucyIsInlvdXR1YmUiLCJpbmRleCIsInNyYyIsInZpbWVvIiwiZ21hcHMiLCJzcmNBY3Rpb24iLCJjYWxsYmFja3MiLCJtYXJrdXBQYXJzZSIsInRlbXBsYXRlIiwidmFsdWVzIiwiaXRlbSIsInRpdGxlIiwiZWwiLCJ2YWwiLCJjbGljayIsImFuaW1hdGUiLCJnb29nbGVNYXAiLCJ6b29tIiwiYWRkTWFya2VyIiwiYWRkcmVzcyIsImpRdWVyeSJdLCJzb3VyY2VSb290IjoiIn0=