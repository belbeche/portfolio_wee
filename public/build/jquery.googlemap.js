(self["webpackChunk"] = self["webpackChunk"] || []).push([["jquery.googlemap"],{

/***/ "./assets/dist/jquery.googlemap.js":
/*!*****************************************!*\
  !*** ./assets/dist/jquery.googlemap.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }
__webpack_require__(/*! core-js/modules/es.array.push.js */ "./node_modules/core-js/modules/es.array.push.js");
__webpack_require__(/*! core-js/modules/es.symbol.js */ "./node_modules/core-js/modules/es.symbol.js");
__webpack_require__(/*! core-js/modules/es.symbol.description.js */ "./node_modules/core-js/modules/es.symbol.description.js");
__webpack_require__(/*! core-js/modules/es.object.to-string.js */ "./node_modules/core-js/modules/es.object.to-string.js");
__webpack_require__(/*! core-js/modules/es.symbol.iterator.js */ "./node_modules/core-js/modules/es.symbol.iterator.js");
__webpack_require__(/*! core-js/modules/es.array.iterator.js */ "./node_modules/core-js/modules/es.array.iterator.js");
__webpack_require__(/*! core-js/modules/es.string.iterator.js */ "./node_modules/core-js/modules/es.string.iterator.js");
__webpack_require__(/*! core-js/modules/web.dom-collections.iterator.js */ "./node_modules/core-js/modules/web.dom-collections.iterator.js");
$(function () {
  $.fn.googleMap = function (params) {
    params = $.extend({
      zoom: 10,
      coords: [48.895651, 2.290569],
      type: "ROADMAP",
      debug: false,
      langage: "english",
      overviewMapControl: false,
      streetViewControl: false,
      scrollwheel: false,
      mapTypeControl: false
    }, params);
    switch (params.type) {
      case 'ROADMAP':
      case 'SATELLITE':
      case 'HYBRID':
      case 'TERRAIN':
        params.type = google.maps.MapTypeId[params.type];
        break;
      default:
        params.type = google.maps.MapTypeId.ROADMAP;
        break;
    }
    this.each(function () {
      var map = new google.maps.Map(this, {
        zoom: params.zoom,
        center: new google.maps.LatLng(params.coords[0], params.coords[1]),
        mapTypeId: params.type,
        scrollwheel: params.scrollwheel,
        streetViewControl: params.streetViewControl,
        overviewMapControl: params.overviewMapControl,
        mapTypeControl: params.mapTypeControl
      });
      $(this).data('googleMap', map);
      $(this).data('googleLang', params.langage);
      $(this).data('googleDebug', params.debug);
      $(this).data('googleMarker', new Array());
      $(this).data('googleBound', new google.maps.LatLngBounds());
    });
    return this;
  };
  $.fn.addMarker = function (params) {
    params = $.extend({
      coords: false,
      address: false,
      url: false,
      id: false,
      icon: false,
      draggable: false,
      title: "",
      text: "",
      success: function success() {}
    }, params);
    this.each(function () {
      $this = $(this);
      if (!$this.data('googleMap')) {
        if ($this.data('googleDebug')) console.error("jQuery googleMap : Unable to add a marker where there is no map !");
        return false;
      }
      if (!params.coords && !params.address) {
        if ($this.data('googleDebug')) console.error("jQuery googleMap : Unable to add a marker if you don't tell us where !");
        return false;
      }
      if (params.address && typeof params.address == "string") {
        var geocodeAsync = function ($that) {
          var geocoder = new google.maps.Geocoder();
          geocoder.geocode({
            address: params.address,
            bounds: $that.data('googleBound'),
            language: $that.data('googleLang')
          }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
              $that.data('googleBound').extend(results[0].geometry.location);
              if (params.icon) {
                var marker = new google.maps.Marker({
                  map: $this.data('googleMap'),
                  position: results[0].geometry.location,
                  title: params.title,
                  icon: params.icon,
                  draggable: params.draggable
                });
              } else {
                var marker = new google.maps.Marker({
                  map: $that.data('googleMap'),
                  position: results[0].geometry.location,
                  title: params.title,
                  draggable: params.draggable
                });
              }
              if (params.draggable) {
                google.maps.event.addListener(marker, 'dragend', function () {
                  var location = marker.getPosition();
                  var coords = {};
                  coords.lat = location.lat();
                  coords.lon = location.lng();
                  params.success(coords, $this);
                });
              }
              if (params.title != "" && params.text != "" && !params.url) {
                var infowindow = new google.maps.InfoWindow({
                  content: "<h1>" + params.title + "</h1>" + params.text
                });
                var map = $that.data('googleMap');
                google.maps.event.addListener(marker, 'click', function () {
                  infowindow.open(map, marker);
                });
              } else if (params.url) {
                google.maps.event.addListener(marker, 'click', function () {
                  document.location = params.url;
                });
              }
              if (!params.id) {
                $that.data('googleMarker').push(marker);
              } else {
                $that.data('googleMarker')[params.id] = marker;
              }
              if ($that.data('googleMarker').length == 1) {
                $that.data('googleMap').setCenter(results[0].geometry.location);
                $that.data('googleMap').setZoom($that.data('googleMap').getZoom());
              } else {
                $that.data('googleMap').fitBounds($that.data('googleBound'));
              }
              var coords = {};
              coords.lat = results[0].geometry.location.lat();
              coords.lon = results[0].geometry.location.lng();
              params.success(coords, $this);
            } else {
              if ($this.data('googleDebug')) console.error("jQuery googleMap : Unable to find the place asked for the marker (" + status + ")");
            }
          });
        }($this);
      } else {
        $this.data('googleBound').extend(new google.maps.LatLng(params.coords[0], params.coords[1]));
        if (params.icon) {
          var marker = new google.maps.Marker({
            map: $this.data('googleMap'),
            position: new google.maps.LatLng(params.coords[0], params.coords[1]),
            title: params.title,
            icon: params.icon,
            draggable: params.draggable
          });
        } else {
          var marker = new google.maps.Marker({
            map: $this.data('googleMap'),
            position: new google.maps.LatLng(params.coords[0], params.coords[1]),
            title: params.title,
            draggable: params.draggable
          });
        }
        if (params.title != "" && params.text != "" && !params.url) {
          var infowindow = new google.maps.InfoWindow({
            content: "<h1>" + params.title + "</h1>" + params.text
          });
          var map = $this.data('googleMap');
          google.maps.event.addListener(marker, 'click', function () {
            infowindow.open(map, marker);
          });
        } else if (params.url) {
          google.maps.event.addListener(marker, 'click', function () {
            document.location = params.url;
          });
        }
        if (params.draggable) {
          google.maps.event.addListener(marker, 'dragend', function () {
            var location = marker.getPosition();
            var coords = {};
            coords.lat = location.lat();
            coords.lon = location.lng();
            params.success(coords, $this);
          });
        }
        if (!params.id) {
          $this.data('googleMarker').push(marker);
        } else {
          $this.data('googleMarker')[params.id] = marker;
        }
        if ($this.data('googleMarker').length == 1) {
          $this.data('googleMap').setCenter(new google.maps.LatLng(params.coords[0], params.coords[1]));
          $this.data('googleMap').setZoom($this.data('googleMap').getZoom());
        } else {
          $this.data('googleMap').fitBounds($this.data('googleBound'));
        }
        params.success({
          lat: params.coords[0],
          lon: params.coords[1]
        }, $this);
      }
    });
    return this;
  };
  $.fn.removeMarker = function (id) {
    this.each(function () {
      var $this = $(this);
      if (!$this.data('googleMap')) {
        if ($this.data('googleDebug')) console.log("jQuery googleMap : Unable to delete a marker where there is no map !");
        return false;
      }
      var $markers = $this.data('googleMarker');
      if (typeof $markers[id] != 'undefined') {
        $markers[id].setMap(null);
        if ($this.data('googleDebug')) console.log('jQuery googleMap : marker deleted');
        return true;
      } else {
        if ($this.data('googleDebug')) console.error("jQuery googleMap : Unable to delete a marker if it not exists !");
        return false;
      }
    });
  };
  $.fn.addWay = function (params) {
    params = $.extend({
      start: false,
      end: false,
      step: [],
      route: false,
      langage: 'english'
    }, params);
    var direction = new google.maps.DirectionsService({
      region: "fr"
    });
    var way = new google.maps.DirectionsRenderer({
      draggable: true,
      map: $(this).data('googleMap'),
      panel: document.getElementById(params.route),
      provideTripAlternatives: true
    });
    document.getElementById.innerHTML = "";
    var waypoints = [];
    for (var i in params.step) {
      var step;
      if (_typeof(params.step[i]) == "object") {
        step = new google.maps.LatLng(params.step[i][0], params.step[i][1]);
      } else {
        step = params.step[i];
      }
      waypoints.push({
        location: step,
        stopover: true
      });
    }
    if (_typeof(params.end) != "object") {
      var geocodeAsync = function ($that) {
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({
          address: params.end,
          bounds: $that.data('googleBound'),
          language: params.langage
        }, function (results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            var request = {
              origin: params.start,
              destination: results[0].geometry.location,
              travelMode: google.maps.DirectionsTravelMode.DRIVING,
              region: "fr",
              waypoints: waypoints
            };
            direction.route(request, function (response, status) {
              if (status == google.maps.DirectionsStatus.OK) {
                way.setDirections(response);
              } else {
                if ($that.data('googleDebug')) console.error("jQuery googleMap : Unable to find the place asked for the route (" + response + ")");
              }
            });
          } else {
            if ($that.data('googleDebug')) console.error("jQuery googleMap : Address not found");
          }
        });
      }($(this));
    } else {
      var request = {
        origin: params.start,
        destination: new google.maps.LatLng(params.end[0], params.end[1]),
        travelMode: google.maps.DirectionsTravelMode.DRIVING,
        region: "fr",
        waypoints: waypoints
      };
      direction.route(request, function (response, status) {
        if (status == google.maps.DirectionsStatus.OK) {
          way.setDirections(response);
        } else {
          if ($(this).data('googleDebug')) console.error("jQuery googleMap : Address not found");
        }
      });
    }
    return this;
  };
});

/***/ }),

/***/ "./node_modules/core-js/internals/array-set-length.js":
/*!************************************************************!*\
  !*** ./node_modules/core-js/internals/array-set-length.js ***!
  \************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var isArray = __webpack_require__(/*! ../internals/is-array */ "./node_modules/core-js/internals/is-array.js");

var $TypeError = TypeError;
// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

// Safari < 13 does not throw an error in this case
var SILENT_ON_NON_WRITABLE_LENGTH_SET = DESCRIPTORS && !function () {
  // makes no sense without proper strict mode support
  if (this !== undefined) return true;
  try {
    // eslint-disable-next-line es/no-object-defineproperty -- safe
    Object.defineProperty([], 'length', { writable: false }).length = 1;
  } catch (error) {
    return error instanceof TypeError;
  }
}();

module.exports = SILENT_ON_NON_WRITABLE_LENGTH_SET ? function (O, length) {
  if (isArray(O) && !getOwnPropertyDescriptor(O, 'length').writable) {
    throw $TypeError('Cannot set read only .length');
  } return O.length = length;
} : function (O, length) {
  return O.length = length;
};


/***/ }),

/***/ "./node_modules/core-js/internals/does-not-exceed-safe-integer.js":
/*!************************************************************************!*\
  !*** ./node_modules/core-js/internals/does-not-exceed-safe-integer.js ***!
  \************************************************************************/
/***/ ((module) => {

var $TypeError = TypeError;
var MAX_SAFE_INTEGER = 0x1FFFFFFFFFFFFF; // 2 ** 53 - 1 == 9007199254740991

module.exports = function (it) {
  if (it > MAX_SAFE_INTEGER) throw $TypeError('Maximum allowed index exceeded');
  return it;
};


/***/ }),

/***/ "./node_modules/core-js/modules/es.array.push.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/modules/es.array.push.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var $ = __webpack_require__(/*! ../internals/export */ "./node_modules/core-js/internals/export.js");
var toObject = __webpack_require__(/*! ../internals/to-object */ "./node_modules/core-js/internals/to-object.js");
var lengthOfArrayLike = __webpack_require__(/*! ../internals/length-of-array-like */ "./node_modules/core-js/internals/length-of-array-like.js");
var setArrayLength = __webpack_require__(/*! ../internals/array-set-length */ "./node_modules/core-js/internals/array-set-length.js");
var doesNotExceedSafeInteger = __webpack_require__(/*! ../internals/does-not-exceed-safe-integer */ "./node_modules/core-js/internals/does-not-exceed-safe-integer.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");

var INCORRECT_TO_LENGTH = fails(function () {
  return [].push.call({ length: 0x100000000 }, 1) !== 4294967297;
});

// V8 and Safari <= 15.4, FF < 23 throws InternalError
// https://bugs.chromium.org/p/v8/issues/detail?id=12681
var properErrorOnNonWritableLength = function () {
  try {
    // eslint-disable-next-line es/no-object-defineproperty -- safe
    Object.defineProperty([], 'length', { writable: false }).push();
  } catch (error) {
    return error instanceof TypeError;
  }
};

var FORCED = INCORRECT_TO_LENGTH || !properErrorOnNonWritableLength();

// `Array.prototype.push` method
// https://tc39.es/ecma262/#sec-array.prototype.push
$({ target: 'Array', proto: true, arity: 1, forced: FORCED }, {
  // eslint-disable-next-line no-unused-vars -- required for `.length`
  push: function push(item) {
    var O = toObject(this);
    var len = lengthOfArrayLike(O);
    var argCount = arguments.length;
    doesNotExceedSafeInteger(len + argCount);
    for (var i = 0; i < argCount; i++) {
      O[len] = arguments[i];
      len++;
    }
    setArrayLength(O, len);
    return len;
  }
});


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_internals_add-to-unscopables_js-node_modules_core-js_internals_a-e38503","vendors-node_modules_core-js_modules_es_string_iterator_js-node_modules_core-js_modules_es_sy-0eab75"], () => (__webpack_exec__("./assets/dist/jquery.googlemap.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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QTtBQUNBO0FBQ0E7QUFDQSxvQkFBb0IsY0FBYztBQUNsQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2Rpc3QvanF1ZXJ5Lmdvb2dsZW1hcC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9pbnRlcm5hbHMvYXJyYXktc2V0LWxlbmd0aC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9pbnRlcm5hbHMvZG9lcy1ub3QtZXhjZWVkLXNhZmUtaW50ZWdlci5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9tb2R1bGVzL2VzLmFycmF5LnB1c2guanMiXSwic291cmNlc0NvbnRlbnQiOlsiJChmdW5jdGlvbigpIHtcblxuXHQkLmZuLmdvb2dsZU1hcCA9IGZ1bmN0aW9uKHBhcmFtcykge1xuXHRcdHBhcmFtcyA9ICQuZXh0ZW5kKCB7XG5cdFx0XHR6b29tIDogMTAsXG5cdFx0XHRjb29yZHMgOiBbNDguODk1NjUxLCAyLjI5MDU2OV0sXG5cdFx0XHR0eXBlIDogXCJST0FETUFQXCIsXG5cdFx0XHRkZWJ1ZyA6IGZhbHNlLFxuXHRcdFx0bGFuZ2FnZSA6IFwiZW5nbGlzaFwiLFxuXHRcdFx0b3ZlcnZpZXdNYXBDb250cm9sOiBmYWxzZSxcblx0XHRcdHN0cmVldFZpZXdDb250cm9sOiBmYWxzZSxcblx0XHRcdHNjcm9sbHdoZWVsOiBmYWxzZSxcblx0XHRcdG1hcFR5cGVDb250cm9sOiBmYWxzZVxuXHRcdH0sIHBhcmFtcyk7XG5cblx0XHRzd2l0Y2gocGFyYW1zLnR5cGUpIHtcblx0XHRcdGNhc2UgJ1JPQURNQVAnOlxuXHRcdFx0Y2FzZSAnU0FURUxMSVRFJzpcblx0XHRcdGNhc2UgJ0hZQlJJRCc6XG5cdFx0XHRjYXNlICdURVJSQUlOJzpcblx0XHRcdFx0cGFyYW1zLnR5cGUgPSBnb29nbGUubWFwcy5NYXBUeXBlSWRbcGFyYW1zLnR5cGVdO1xuXHRcdFx0XHRicmVhaztcblx0XHRcdGRlZmF1bHQ6XG5cdFx0XHRcdHBhcmFtcy50eXBlID0gZ29vZ2xlLm1hcHMuTWFwVHlwZUlkLlJPQURNQVA7XG5cdFx0XHRcdGJyZWFrO1xuXHRcdH1cblxuXHRcdHRoaXMuZWFjaChmdW5jdGlvbigpIHtcblxuXHRcdFx0dmFyIG1hcCA9IG5ldyBnb29nbGUubWFwcy5NYXAodGhpcywge1xuXHRcdFx0XHR6b29tOiBwYXJhbXMuem9vbSxcblx0XHRcdFx0Y2VudGVyOiBuZXcgZ29vZ2xlLm1hcHMuTGF0TG5nKHBhcmFtcy5jb29yZHNbMF0sIHBhcmFtcy5jb29yZHNbMV0pLFxuXHRcdFx0XHRtYXBUeXBlSWQ6IHBhcmFtcy50eXBlLFxuXHRcdFx0XHRzY3JvbGx3aGVlbDogcGFyYW1zLnNjcm9sbHdoZWVsLFxuXHRcdFx0XHRzdHJlZXRWaWV3Q29udHJvbDogcGFyYW1zLnN0cmVldFZpZXdDb250cm9sLFxuXHRcdFx0XHRvdmVydmlld01hcENvbnRyb2w6IHBhcmFtcy5vdmVydmlld01hcENvbnRyb2wsXG5cdFx0XHRcdG1hcFR5cGVDb250cm9sOiBwYXJhbXMubWFwVHlwZUNvbnRyb2xcblxuXHRcdFx0fSk7XG5cblx0XHRcdCQodGhpcykuZGF0YSgnZ29vZ2xlTWFwJywgbWFwKTtcblx0XHRcdCQodGhpcykuZGF0YSgnZ29vZ2xlTGFuZycsIHBhcmFtcy5sYW5nYWdlKTtcblx0XHRcdCQodGhpcykuZGF0YSgnZ29vZ2xlRGVidWcnLCBwYXJhbXMuZGVidWcpO1xuXHRcdFx0JCh0aGlzKS5kYXRhKCdnb29nbGVNYXJrZXInLCBuZXcgQXJyYXkoKSk7XG5cdFx0XHQkKHRoaXMpLmRhdGEoJ2dvb2dsZUJvdW5kJywgbmV3IGdvb2dsZS5tYXBzLkxhdExuZ0JvdW5kcygpKTtcblx0XHR9KTtcblxuXHRcdHJldHVybiB0aGlzO1xuXHR9XG5cblx0JC5mbi5hZGRNYXJrZXIgPSBmdW5jdGlvbihwYXJhbXMpIHtcblx0XHRwYXJhbXMgPSAkLmV4dGVuZCgge1xuXHRcdFx0Y29vcmRzIDogZmFsc2UsXG5cdFx0XHRhZGRyZXNzIDogZmFsc2UsXG5cdFx0XHR1cmwgOiBmYWxzZSxcblx0XHRcdGlkIDogZmFsc2UsXG5cdFx0XHRpY29uIDogZmFsc2UsXG5cdFx0XHRkcmFnZ2FibGUgOiBmYWxzZSxcblx0XHRcdHRpdGxlIDogXCJcIixcblx0XHRcdHRleHQgOiBcIlwiLFxuXHRcdFx0c3VjY2VzcyA6IGZ1bmN0aW9uKCkge31cblx0XHR9LCBwYXJhbXMpO1xuXG5cdFx0dGhpcy5lYWNoKGZ1bmN0aW9uKCkge1xuXHRcdFx0JHRoaXMgPSAkKHRoaXMpO1xuXG5cdFx0XHRpZighJHRoaXMuZGF0YSgnZ29vZ2xlTWFwJykpIHtcblx0XHRcdFx0aWYoJHRoaXMuZGF0YSgnZ29vZ2xlRGVidWcnKSlcblx0XHRcdFx0XHRjb25zb2xlLmVycm9yKFwialF1ZXJ5IGdvb2dsZU1hcCA6IFVuYWJsZSB0byBhZGQgYSBtYXJrZXIgd2hlcmUgdGhlcmUgaXMgbm8gbWFwICFcIik7XG5cdFx0XHRcdFx0XG5cdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdH1cblxuXHRcdFx0aWYoIXBhcmFtcy5jb29yZHMgJiYgIXBhcmFtcy5hZGRyZXNzKSB7XG5cdFx0XHRcdGlmKCR0aGlzLmRhdGEoJ2dvb2dsZURlYnVnJykpXG5cdFx0XHRcdFx0Y29uc29sZS5lcnJvcihcImpRdWVyeSBnb29nbGVNYXAgOiBVbmFibGUgdG8gYWRkIGEgbWFya2VyIGlmIHlvdSBkb24ndCB0ZWxsIHVzIHdoZXJlICFcIik7XG5cdFx0XHRcdFx0XG5cdFx0XHRcdHJldHVybiBmYWxzZTtcblx0XHRcdH1cblxuXHRcdFx0aWYocGFyYW1zLmFkZHJlc3MgJiYgdHlwZW9mIHBhcmFtcy5hZGRyZXNzID09IFwic3RyaW5nXCIpIHtcblxuXHRcdFx0XHR2YXIgZ2VvY29kZUFzeW5jID0gZnVuY3Rpb24oJHRoYXQpIHtcblxuXHRcdFx0XHRcdHZhciBnZW9jb2RlciA9IG5ldyBnb29nbGUubWFwcy5HZW9jb2RlcigpO1xuXG5cdFx0XHRcdFx0Z2VvY29kZXIuZ2VvY29kZSh7XG5cdFx0XHRcdFx0XHRhZGRyZXNzIDogcGFyYW1zLmFkZHJlc3MsXG5cdFx0XHRcdFx0XHRib3VuZHMgOiAkdGhhdC5kYXRhKCdnb29nbGVCb3VuZCcpLFxuXHRcdFx0XHRcdFx0bGFuZ3VhZ2UgOiAkdGhhdC5kYXRhKCdnb29nbGVMYW5nJylcblx0XHRcdFx0XHR9LCBmdW5jdGlvbihyZXN1bHRzLCBzdGF0dXMpIHtcblxuXHRcdFx0XHRcdFx0aWYgKHN0YXR1cyA9PSBnb29nbGUubWFwcy5HZW9jb2RlclN0YXR1cy5PSykge1xuXHRcdFx0XHRcdFx0XHQkdGhhdC5kYXRhKCdnb29nbGVCb3VuZCcpLmV4dGVuZChyZXN1bHRzWzBdLmdlb21ldHJ5LmxvY2F0aW9uKTtcblxuXHRcdFx0XHRcdFx0XHRpZihwYXJhbXMuaWNvbikge1xuXHRcdFx0XHRcdFx0XHRcdHZhciBtYXJrZXIgPSBuZXcgZ29vZ2xlLm1hcHMuTWFya2VyKHtcblx0XHRcdFx0XHRcdFx0XHRcdG1hcDogJHRoaXMuZGF0YSgnZ29vZ2xlTWFwJyksXG5cdFx0XHRcdFx0XHRcdFx0XHRwb3NpdGlvbjogcmVzdWx0c1swXS5nZW9tZXRyeS5sb2NhdGlvbixcblx0XHRcdFx0XHRcdFx0XHRcdHRpdGxlOiBwYXJhbXMudGl0bGUsXG5cdFx0XHRcdFx0XHRcdFx0XHRpY29uOiBwYXJhbXMuaWNvbixcblx0XHRcdFx0XHRcdFx0XHRcdGRyYWdnYWJsZTogcGFyYW1zLmRyYWdnYWJsZVxuXHRcdFx0XHRcdFx0XHRcdH0pO1xuXHRcdFx0XHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdFx0XHRcdHZhciBtYXJrZXIgPSBuZXcgZ29vZ2xlLm1hcHMuTWFya2VyKHtcblx0XHRcdFx0XHRcdFx0XHRcdG1hcDogJHRoYXQuZGF0YSgnZ29vZ2xlTWFwJyksXG5cdFx0XHRcdFx0XHRcdFx0XHRwb3NpdGlvbjogcmVzdWx0c1swXS5nZW9tZXRyeS5sb2NhdGlvbixcblx0XHRcdFx0XHRcdFx0XHRcdHRpdGxlOiBwYXJhbXMudGl0bGUsXG5cdFx0XHRcdFx0XHRcdFx0XHRkcmFnZ2FibGU6IHBhcmFtcy5kcmFnZ2FibGVcblx0XHRcdFx0XHRcdFx0XHR9KTtcblx0XHRcdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0XHRcdGlmKHBhcmFtcy5kcmFnZ2FibGUpIHtcblx0XHRcdFx0XHRcdFx0XHRnb29nbGUubWFwcy5ldmVudC5hZGRMaXN0ZW5lcihtYXJrZXIsICdkcmFnZW5kJywgZnVuY3Rpb24oKSB7XG5cdFx0XHRcdFx0XHRcdFx0XHR2YXIgbG9jYXRpb24gPSBtYXJrZXIuZ2V0UG9zaXRpb24oKTtcblxuXHRcdFx0XHRcdFx0XHRcdFx0dmFyIGNvb3JkcyA9IHt9O1xuXG5cdFx0XHRcdFx0XHRcdFx0XHRjb29yZHMubGF0ID0gbG9jYXRpb24ubGF0KCk7XG5cdFx0XHRcdFx0XHRcdFx0XHRjb29yZHMubG9uID0gbG9jYXRpb24ubG5nKCk7XG5cblx0XHRcdFx0XHRcdFx0XHRcdHBhcmFtcy5zdWNjZXNzKGNvb3JkcywgJHRoaXMpO1xuXHRcdFx0XHRcdFx0XHRcdH0pO1xuXHRcdFx0XHRcdFx0XHR9XG5cblx0XHRcdFx0XHRcdFx0aWYocGFyYW1zLnRpdGxlICE9IFwiXCIgJiYgcGFyYW1zLnRleHQgIT0gXCJcIiAmJiAhcGFyYW1zLnVybCkge1xuXHRcdFx0XHRcdFx0XHRcdHZhciBpbmZvd2luZG93ID0gbmV3IGdvb2dsZS5tYXBzLkluZm9XaW5kb3coe1xuXHRcdFx0XHRcdFx0XHRcdFx0Y29udGVudDogXCI8aDE+XCIrcGFyYW1zLnRpdGxlK1wiPC9oMT5cIitwYXJhbXMudGV4dFxuXHRcdFx0XHRcdFx0XHRcdH0pO1xuXG5cdFx0XHRcdFx0XHRcdFx0dmFyIG1hcCA9ICR0aGF0LmRhdGEoJ2dvb2dsZU1hcCcpO1xuXG5cdFx0XHRcdFx0XHRcdFx0Z29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIobWFya2VyLCAnY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0XHRcdFx0XHRcdGluZm93aW5kb3cub3BlbihtYXAsIG1hcmtlcik7XG5cdFx0XHRcdFx0XHRcdFx0fSk7XG5cdFx0XHRcdFx0XHRcdH0gZWxzZSBpZihwYXJhbXMudXJsKSB7XG5cdFx0XHRcdFx0XHRcdFx0Z29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIobWFya2VyLCAnY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0XHRcdFx0XHRcdGRvY3VtZW50LmxvY2F0aW9uID0gcGFyYW1zLnVybDtcblx0XHRcdFx0XHRcdFx0XHR9KTtcblx0XHRcdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0XHRcdGlmKCFwYXJhbXMuaWQpIHtcblx0XHRcdFx0XHRcdFx0XHQkdGhhdC5kYXRhKCdnb29nbGVNYXJrZXInKS5wdXNoKG1hcmtlcik7XG5cdFx0XHRcdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0XHRcdFx0JHRoYXQuZGF0YSgnZ29vZ2xlTWFya2VyJylbcGFyYW1zLmlkXSA9IG1hcmtlcjtcblx0XHRcdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0XHRcdGlmKCR0aGF0LmRhdGEoJ2dvb2dsZU1hcmtlcicpLmxlbmd0aCA9PSAxKSB7XG5cdFx0XHRcdFx0XHRcdFx0JHRoYXQuZGF0YSgnZ29vZ2xlTWFwJykuc2V0Q2VudGVyKHJlc3VsdHNbMF0uZ2VvbWV0cnkubG9jYXRpb24pO1xuXHRcdFx0XHRcdFx0XHRcdCR0aGF0LmRhdGEoJ2dvb2dsZU1hcCcpLnNldFpvb20oJHRoYXQuZGF0YSgnZ29vZ2xlTWFwJykuZ2V0Wm9vbSgpKTtcblx0XHRcdFx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRcdFx0XHQkdGhhdC5kYXRhKCdnb29nbGVNYXAnKS5maXRCb3VuZHMoJHRoYXQuZGF0YSgnZ29vZ2xlQm91bmQnKSk7XG5cdFx0XHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdFx0XHR2YXIgY29vcmRzID0ge307XG5cdFx0XHRcdFx0XHRcdGNvb3Jkcy5sYXQgPSByZXN1bHRzWzBdLmdlb21ldHJ5LmxvY2F0aW9uLmxhdCgpO1xuXHRcdFx0XHRcdFx0XHRjb29yZHMubG9uID0gcmVzdWx0c1swXS5nZW9tZXRyeS5sb2NhdGlvbi5sbmcoKTtcblxuXHRcdFx0XHRcdFx0XHRwYXJhbXMuc3VjY2Vzcyhjb29yZHMsICR0aGlzKTtcblxuXHRcdFx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRcdFx0aWYoJHRoaXMuZGF0YSgnZ29vZ2xlRGVidWcnKSlcblx0XHRcdFx0XHRcdFx0XHRjb25zb2xlLmVycm9yKFwialF1ZXJ5IGdvb2dsZU1hcCA6IFVuYWJsZSB0byBmaW5kIHRoZSBwbGFjZSBhc2tlZCBmb3IgdGhlIG1hcmtlciAoXCIrc3RhdHVzK1wiKVwiKTtcblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHR9KTtcblx0XHRcdFx0fSgkdGhpcyk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHQkdGhpcy5kYXRhKCdnb29nbGVCb3VuZCcpLmV4dGVuZChuZXcgZ29vZ2xlLm1hcHMuTGF0TG5nKHBhcmFtcy5jb29yZHNbMF0sIHBhcmFtcy5jb29yZHNbMV0pKTtcblxuICAgICAgICBcdFx0XHRpZihwYXJhbXMuaWNvbikge1xuXHRcdFx0XHRcdHZhciBtYXJrZXIgPSBuZXcgZ29vZ2xlLm1hcHMuTWFya2VyKHtcblx0XHRcdFx0XHRcdG1hcDogJHRoaXMuZGF0YSgnZ29vZ2xlTWFwJyksXG5cdFx0XHRcdFx0XHRwb3NpdGlvbjogbmV3IGdvb2dsZS5tYXBzLkxhdExuZyhwYXJhbXMuY29vcmRzWzBdLCBwYXJhbXMuY29vcmRzWzFdKSxcblx0XHRcdFx0XHRcdHRpdGxlOiBwYXJhbXMudGl0bGUsXG5cdFx0XHRcdFx0XHRpY29uOiBwYXJhbXMuaWNvbixcblx0XHRcdFx0XHRcdGRyYWdnYWJsZTogcGFyYW1zLmRyYWdnYWJsZVxuXHRcdFx0XHRcdH0pO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdHZhciBtYXJrZXIgPSBuZXcgZ29vZ2xlLm1hcHMuTWFya2VyKHtcblx0XHRcdFx0XHRcdG1hcDogJHRoaXMuZGF0YSgnZ29vZ2xlTWFwJyksXG5cdFx0XHRcdFx0XHRwb3NpdGlvbjogbmV3IGdvb2dsZS5tYXBzLkxhdExuZyhwYXJhbXMuY29vcmRzWzBdLCBwYXJhbXMuY29vcmRzWzFdKSxcblx0XHRcdFx0XHRcdHRpdGxlOiBwYXJhbXMudGl0bGUsXG5cdFx0XHRcdFx0XHRkcmFnZ2FibGU6IHBhcmFtcy5kcmFnZ2FibGVcblx0XHRcdFx0XHR9KTtcblx0XHRcdFx0fVxuXG4gICAgICAgIFx0XHRcdGlmKHBhcmFtcy50aXRsZSAhPSBcIlwiICYmIHBhcmFtcy50ZXh0ICE9IFwiXCIgJiYgIXBhcmFtcy51cmwpIHtcbiAgICAgICAgICBcdFx0XHRcdHZhciBpbmZvd2luZG93ID0gbmV3IGdvb2dsZS5tYXBzLkluZm9XaW5kb3coe1xuXHRcdFx0XHRcdFx0Y29udGVudDogXCI8aDE+XCIrcGFyYW1zLnRpdGxlK1wiPC9oMT5cIitwYXJhbXMudGV4dFxuXHRcdFx0XHRcdH0pO1xuXG5cdFx0XHRcdFx0dmFyIG1hcCA9ICR0aGlzLmRhdGEoJ2dvb2dsZU1hcCcpO1xuXG5cdCAgICAgICAgXHRcdFx0Z29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIobWFya2VyLCAnY2xpY2snLCBmdW5jdGlvbigpIHtcblx0XHQgICAgICAgIFx0XHRcdGluZm93aW5kb3cub3BlbihtYXAsIG1hcmtlcik7XG5cdCAgICAgICAgXHRcdFx0fSk7XG5cdFx0XHRcdH0gZWxzZSBpZihwYXJhbXMudXJsKSB7XG4gICAgICAgICAgXHRcdFx0XHRnb29nbGUubWFwcy5ldmVudC5hZGRMaXN0ZW5lcihtYXJrZXIsICdjbGljaycsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICBcdFx0XHRcdFx0ZG9jdW1lbnQubG9jYXRpb24gPSBwYXJhbXMudXJsO1xuICAgICAgICBcdFx0XHRcdH0pO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0aWYocGFyYW1zLmRyYWdnYWJsZSkge1xuXHRcdFx0XHRcdGdvb2dsZS5tYXBzLmV2ZW50LmFkZExpc3RlbmVyKG1hcmtlciwgJ2RyYWdlbmQnLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0XHRcdHZhciBsb2NhdGlvbiA9IG1hcmtlci5nZXRQb3NpdGlvbigpO1xuXG5cdFx0XHRcdFx0XHR2YXIgY29vcmRzID0ge307XG5cblx0XHRcdFx0XHRcdGNvb3Jkcy5sYXQgPSBsb2NhdGlvbi5sYXQoKTtcblx0XHRcdFx0XHRcdGNvb3Jkcy5sb24gPSBsb2NhdGlvbi5sbmcoKTtcblxuXHRcdFx0XHRcdFx0cGFyYW1zLnN1Y2Nlc3MoY29vcmRzLCAkdGhpcyk7XG5cdFx0XHRcdFx0fSk7XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRpZighcGFyYW1zLmlkKSB7XG4gICAgICAgXHRcdFx0XHRcdCR0aGlzLmRhdGEoJ2dvb2dsZU1hcmtlcicpLnB1c2gobWFya2VyKTtcbiAgICAgICAgXHRcdFx0fSBlbHNlIHtcbiAgICAgICAgXHRcdFx0XHQkdGhpcy5kYXRhKCdnb29nbGVNYXJrZXInKVtwYXJhbXMuaWRdID0gbWFya2VyO1xuICAgICAgICBcdFx0XHR9XG5cblx0XHRcdFx0aWYoJHRoaXMuZGF0YSgnZ29vZ2xlTWFya2VyJykubGVuZ3RoID09IDEpIHtcblx0XHRcdFx0XHQkdGhpcy5kYXRhKCdnb29nbGVNYXAnKS5zZXRDZW50ZXIobmV3IGdvb2dsZS5tYXBzLkxhdExuZyhwYXJhbXMuY29vcmRzWzBdLCBwYXJhbXMuY29vcmRzWzFdKSk7XG5cdFx0XHRcdFx0JHRoaXMuZGF0YSgnZ29vZ2xlTWFwJykuc2V0Wm9vbSgkdGhpcy5kYXRhKCdnb29nbGVNYXAnKS5nZXRab29tKCkpO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdCR0aGlzLmRhdGEoJ2dvb2dsZU1hcCcpLmZpdEJvdW5kcygkdGhpcy5kYXRhKCdnb29nbGVCb3VuZCcpKTtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdHBhcmFtcy5zdWNjZXNzKHtcblx0XHRcdFx0XHRsYXQ6IHBhcmFtcy5jb29yZHNbMF0sXG5cdFx0XHRcdFx0bG9uOiBwYXJhbXMuY29vcmRzWzFdXG5cdFx0XHRcdH0sICR0aGlzKTtcblx0XHRcdH1cblx0XHR9KTtcblxuXHRcdHJldHVybiB0aGlzO1xuXHR9XG5cblx0JC5mbi5yZW1vdmVNYXJrZXIgPSBmdW5jdGlvbihpZCkge1xuXHRcdHRoaXMuZWFjaChmdW5jdGlvbigpIHtcblx0XHRcdHZhciAkdGhpcyA9ICQodGhpcyk7XG5cbiAgICBcdFx0XHRpZighJHRoaXMuZGF0YSgnZ29vZ2xlTWFwJykpIHtcbiAgICBcdFx0XHRcdGlmKCR0aGlzLmRhdGEoJ2dvb2dsZURlYnVnJykpXG4gICAgICBcdFx0XHRcdFx0Y29uc29sZS5sb2coXCJqUXVlcnkgZ29vZ2xlTWFwIDogVW5hYmxlIHRvIGRlbGV0ZSBhIG1hcmtlciB3aGVyZSB0aGVyZSBpcyBubyBtYXAgIVwiKTtcbiAgICAgIFx0XHRcdFx0XHRcbiAgICAgIFx0XHRcdFx0cmV0dXJuIGZhbHNlO1xuICAgIFx0XHRcdH1cblxuICAgIFx0XHRcdHZhciAkbWFya2VycyA9ICR0aGlzLmRhdGEoJ2dvb2dsZU1hcmtlcicpO1xuXG4gICAgXHRcdFx0aWYodHlwZW9mICRtYXJrZXJzW2lkXSAhPSAndW5kZWZpbmVkJykge1xuICAgIFx0XHRcdFx0JG1hcmtlcnNbaWRdLnNldE1hcChudWxsKTtcbiAgICBcdFx0XHRcdFxuICAgICAgXHRcdFx0XHRpZigkdGhpcy5kYXRhKCdnb29nbGVEZWJ1ZycpKVxuICAgICAgXHRcdFx0XHRcdGNvbnNvbGUubG9nKCdqUXVlcnkgZ29vZ2xlTWFwIDogbWFya2VyIGRlbGV0ZWQnKTtcbiAgICAgIFx0XHRcdFx0XHRcbiAgICAgIFx0XHRcdFx0cmV0dXJuIHRydWU7XG4gICAgXHRcdFx0fSBlbHNlIHtcbiAgICAgIFx0XHRcdFx0aWYoJHRoaXMuZGF0YSgnZ29vZ2xlRGVidWcnKSlcbiAgICAgIFx0XHRcdFx0XHRjb25zb2xlLmVycm9yKFwialF1ZXJ5IGdvb2dsZU1hcCA6IFVuYWJsZSB0byBkZWxldGUgYSBtYXJrZXIgaWYgaXQgbm90IGV4aXN0cyAhXCIpO1xuICAgICAgXHRcdFxuICAgICAgXHRcdFx0XHRyZXR1cm4gZmFsc2U7XG4gICAgXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG5cblx0JC5mbi5hZGRXYXkgPSBmdW5jdGlvbihwYXJhbXMpIHtcblx0XHRwYXJhbXMgPSAkLmV4dGVuZCgge1xuXHRcdFx0c3RhcnQgOiBmYWxzZSxcblx0XHRcdGVuZCA6IGZhbHNlLFxuXHRcdFx0c3RlcCA6IFtdLFxuXHRcdFx0cm91dGUgOiBmYWxzZSxcblx0XHRcdGxhbmdhZ2UgOiAnZW5nbGlzaCdcblx0XHR9LCBwYXJhbXMpO1xuXG5cdFx0dmFyIGRpcmVjdGlvbiA9IG5ldyBnb29nbGUubWFwcy5EaXJlY3Rpb25zU2VydmljZSh7XG5cdFx0XHRyZWdpb246IFwiZnJcIlxuXHRcdH0pO1xuXG5cdFx0dmFyIHdheSA9IG5ldyBnb29nbGUubWFwcy5EaXJlY3Rpb25zUmVuZGVyZXIoe1xuXHRcdFx0ZHJhZ2dhYmxlOiB0cnVlLFxuXHRcdFx0bWFwOiAkKHRoaXMpLmRhdGEoJ2dvb2dsZU1hcCcpLFxuXHRcdFx0cGFuZWw6IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKHBhcmFtcy5yb3V0ZSksXG5cdFx0XHRwcm92aWRlVHJpcEFsdGVybmF0aXZlczogdHJ1ZVxuXHRcdH0pO1xuXHRcdFxuXHRcdGRvY3VtZW50LmdldEVsZW1lbnRCeUlkLmlubmVySFRNTCA9IFwiXCI7XG5cblx0XHR2YXIgd2F5cG9pbnRzID0gW107XG5cbiAgICBcdFx0Zm9yKHZhciBpIGluIHBhcmFtcy5zdGVwKSB7XG4gICAgXHRcdFx0dmFyIHN0ZXA7XG4gICAgICBcdFx0XHRpZih0eXBlb2YgcGFyYW1zLnN0ZXBbaV0gPT0gXCJvYmplY3RcIikge1xuICAgICAgICBcdFx0XHRzdGVwID0gbmV3IGdvb2dsZS5tYXBzLkxhdExuZyhwYXJhbXMuc3RlcFtpXVswXSwgcGFyYW1zLnN0ZXBbaV1bMV0pO1xuICAgICAgXHRcdFx0fSBlbHNlIHtcbiAgICAgICAgXHRcdFx0c3RlcCA9IHBhcmFtcy5zdGVwW2ldO1xuICAgICAgXHRcdFx0fVxuXG4gICAgICBcdFx0XHR3YXlwb2ludHMucHVzaCh7XG4gICAgICBcdFx0XHRcdGxvY2F0aW9uOiBzdGVwLFxuICAgICAgXHRcdFx0XHRzdG9wb3ZlcjogdHJ1ZVxuICAgICAgXHRcdFx0fSk7XG5cdFx0fVxuXG5cdFx0aWYodHlwZW9mIHBhcmFtcy5lbmQgIT0gXCJvYmplY3RcIikge1xuXHRcdFx0dmFyIGdlb2NvZGVBc3luYyA9IGZ1bmN0aW9uKCR0aGF0KSB7XG5cdFx0XHRcdHZhciBnZW9jb2RlciA9IG5ldyBnb29nbGUubWFwcy5HZW9jb2RlcigpO1xuXG5cdFx0ICAgIFx0XHRnZW9jb2Rlci5nZW9jb2RlKHtcblx0XHQgICAgXHRcdFx0YWRkcmVzcyAgOiBwYXJhbXMuZW5kLFxuXHRcdCAgICBcdFx0XHRib3VuZHMgICA6ICR0aGF0LmRhdGEoJ2dvb2dsZUJvdW5kJyksXG5cdFx0ICAgIFx0XHRcdGxhbmd1YWdlIDogcGFyYW1zLmxhbmdhZ2Vcblx0XHQgICAgXHRcdH0sIGZ1bmN0aW9uKHJlc3VsdHMsIHN0YXR1cykge1xuXHQgICAgICAgIFx0XHRcdGlmIChzdGF0dXMgPT0gZ29vZ2xlLm1hcHMuR2VvY29kZXJTdGF0dXMuT0spIHtcblx0ICAgICAgICBcdFx0XHRcdHZhciByZXF1ZXN0ID0ge1xuXHQgICAgICAgICAgICBcdFx0XHRcdFx0b3JpZ2luOiBwYXJhbXMuc3RhcnQsXG5cdCAgICAgICAgICAgIFx0XHRcdFx0XHRkZXN0aW5hdGlvbjogcmVzdWx0c1swXS5nZW9tZXRyeS5sb2NhdGlvbixcblx0ICAgICAgICAgICAgXHRcdFx0XHRcdHRyYXZlbE1vZGU6IGdvb2dsZS5tYXBzLkRpcmVjdGlvbnNUcmF2ZWxNb2RlLkRSSVZJTkcsXG5cdCAgICAgICAgICAgIFx0XHRcdFx0XHRyZWdpb246IFwiZnJcIixcblx0ICAgICAgICAgICAgXHRcdFx0XHRcdHdheXBvaW50czogd2F5cG9pbnRzXG5cdFx0ICAgICAgICBcdFx0XHR9O1xuXG5cdFx0ICAgICAgICBcdFx0XHRkaXJlY3Rpb24ucm91dGUocmVxdWVzdCwgZnVuY3Rpb24ocmVzcG9uc2UsIHN0YXR1cykge1xuXHQgICAgICAgICAgICBcdFx0XHRcdFx0aWYgKHN0YXR1cyA9PSBnb29nbGUubWFwcy5EaXJlY3Rpb25zU3RhdHVzLk9LKSB7XG5cdCAgICAgICAgICAgICAgXHRcdFx0XHRcdFx0d2F5LnNldERpcmVjdGlvbnMocmVzcG9uc2UpO1xuXHQgICAgICAgICAgICBcdFx0XHRcdFx0fSBlbHNlIHtcblx0ICAgICAgICAgICAgICBcdFx0XHRcdFx0XHRpZigkdGhhdC5kYXRhKCdnb29nbGVEZWJ1ZycpKVxuXHQgICAgICAgICAgICBcdFx0XHRcdFx0XHRcdGNvbnNvbGUuZXJyb3IoXCJqUXVlcnkgZ29vZ2xlTWFwIDogVW5hYmxlIHRvIGZpbmQgdGhlIHBsYWNlIGFza2VkIGZvciB0aGUgcm91dGUgKFwiK3Jlc3BvbnNlK1wiKVwiKTtcblx0ICAgICAgICAgICAgXHRcdFx0XHRcdH1cblx0XHQgICAgICAgIFx0XHRcdH0pO1xuXG5cdCAgICAgICAgXHRcdFx0fSBlbHNlIHtcblx0ICAgICAgICAgIFx0XHRcdFx0aWYoJHRoYXQuZGF0YSgnZ29vZ2xlRGVidWcnKSlcblx0ICAgICAgICAgIFx0XHRcdFx0XHRjb25zb2xlLmVycm9yKFwialF1ZXJ5IGdvb2dsZU1hcCA6IEFkZHJlc3Mgbm90IGZvdW5kXCIpO1xuXHQgICAgICAgIFx0XHRcdH1cblx0XHQgICAgXHRcdH0pO1xuXHQgICAgXHRcdH0oJCh0aGlzKSk7XG5cdFx0fSBlbHNlIHtcblx0XHRcdHZhciByZXF1ZXN0ID0ge1xuXHRcdFx0XHRvcmlnaW46IHBhcmFtcy5zdGFydCxcblx0XHRcdFx0ZGVzdGluYXRpb246IG5ldyBnb29nbGUubWFwcy5MYXRMbmcocGFyYW1zLmVuZFswXSwgcGFyYW1zLmVuZFsxXSksXG5cdFx0XHRcdHRyYXZlbE1vZGU6IGdvb2dsZS5tYXBzLkRpcmVjdGlvbnNUcmF2ZWxNb2RlLkRSSVZJTkcsXG5cdFx0XHRcdHJlZ2lvbjogXCJmclwiLFxuXHRcdFx0XHR3YXlwb2ludHM6IHdheXBvaW50c1xuXHRcdFx0fTtcblxuXHRcdFx0ZGlyZWN0aW9uLnJvdXRlKHJlcXVlc3QsIGZ1bmN0aW9uKHJlc3BvbnNlLCBzdGF0dXMpIHtcblx0XHRcdFx0aWYgKHN0YXR1cyA9PSBnb29nbGUubWFwcy5EaXJlY3Rpb25zU3RhdHVzLk9LKSB7XG5cdFx0XHRcdFx0d2F5LnNldERpcmVjdGlvbnMocmVzcG9uc2UpO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdGlmKCQodGhpcykuZGF0YSgnZ29vZ2xlRGVidWcnKSlcbiAgICAgICAgICBcdFx0XHRcdFx0Y29uc29sZS5lcnJvcihcImpRdWVyeSBnb29nbGVNYXAgOiBBZGRyZXNzIG5vdCBmb3VuZFwiKTtcblx0XHRcdFx0fVxuXHRcdFx0fSk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHRoaXM7XG5cdH1cbn0pO1xuIiwiJ3VzZSBzdHJpY3QnO1xudmFyIERFU0NSSVBUT1JTID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2Rlc2NyaXB0b3JzJyk7XG52YXIgaXNBcnJheSA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9pcy1hcnJheScpO1xuXG52YXIgJFR5cGVFcnJvciA9IFR5cGVFcnJvcjtcbi8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBlcy9uby1vYmplY3QtZ2V0b3ducHJvcGVydHlkZXNjcmlwdG9yIC0tIHNhZmVcbnZhciBnZXRPd25Qcm9wZXJ0eURlc2NyaXB0b3IgPSBPYmplY3QuZ2V0T3duUHJvcGVydHlEZXNjcmlwdG9yO1xuXG4vLyBTYWZhcmkgPCAxMyBkb2VzIG5vdCB0aHJvdyBhbiBlcnJvciBpbiB0aGlzIGNhc2VcbnZhciBTSUxFTlRfT05fTk9OX1dSSVRBQkxFX0xFTkdUSF9TRVQgPSBERVNDUklQVE9SUyAmJiAhZnVuY3Rpb24gKCkge1xuICAvLyBtYWtlcyBubyBzZW5zZSB3aXRob3V0IHByb3BlciBzdHJpY3QgbW9kZSBzdXBwb3J0XG4gIGlmICh0aGlzICE9PSB1bmRlZmluZWQpIHJldHVybiB0cnVlO1xuICB0cnkge1xuICAgIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBlcy9uby1vYmplY3QtZGVmaW5lcHJvcGVydHkgLS0gc2FmZVxuICAgIE9iamVjdC5kZWZpbmVQcm9wZXJ0eShbXSwgJ2xlbmd0aCcsIHsgd3JpdGFibGU6IGZhbHNlIH0pLmxlbmd0aCA9IDE7XG4gIH0gY2F0Y2ggKGVycm9yKSB7XG4gICAgcmV0dXJuIGVycm9yIGluc3RhbmNlb2YgVHlwZUVycm9yO1xuICB9XG59KCk7XG5cbm1vZHVsZS5leHBvcnRzID0gU0lMRU5UX09OX05PTl9XUklUQUJMRV9MRU5HVEhfU0VUID8gZnVuY3Rpb24gKE8sIGxlbmd0aCkge1xuICBpZiAoaXNBcnJheShPKSAmJiAhZ2V0T3duUHJvcGVydHlEZXNjcmlwdG9yKE8sICdsZW5ndGgnKS53cml0YWJsZSkge1xuICAgIHRocm93ICRUeXBlRXJyb3IoJ0Nhbm5vdCBzZXQgcmVhZCBvbmx5IC5sZW5ndGgnKTtcbiAgfSByZXR1cm4gTy5sZW5ndGggPSBsZW5ndGg7XG59IDogZnVuY3Rpb24gKE8sIGxlbmd0aCkge1xuICByZXR1cm4gTy5sZW5ndGggPSBsZW5ndGg7XG59O1xuIiwidmFyICRUeXBlRXJyb3IgPSBUeXBlRXJyb3I7XG52YXIgTUFYX1NBRkVfSU5URUdFUiA9IDB4MUZGRkZGRkZGRkZGRkY7IC8vIDIgKiogNTMgLSAxID09IDkwMDcxOTkyNTQ3NDA5OTFcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbiAoaXQpIHtcbiAgaWYgKGl0ID4gTUFYX1NBRkVfSU5URUdFUikgdGhyb3cgJFR5cGVFcnJvcignTWF4aW11bSBhbGxvd2VkIGluZGV4IGV4Y2VlZGVkJyk7XG4gIHJldHVybiBpdDtcbn07XG4iLCIndXNlIHN0cmljdCc7XG52YXIgJCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9leHBvcnQnKTtcbnZhciB0b09iamVjdCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy90by1vYmplY3QnKTtcbnZhciBsZW5ndGhPZkFycmF5TGlrZSA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9sZW5ndGgtb2YtYXJyYXktbGlrZScpO1xudmFyIHNldEFycmF5TGVuZ3RoID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2FycmF5LXNldC1sZW5ndGgnKTtcbnZhciBkb2VzTm90RXhjZWVkU2FmZUludGVnZXIgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvZG9lcy1ub3QtZXhjZWVkLXNhZmUtaW50ZWdlcicpO1xudmFyIGZhaWxzID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2ZhaWxzJyk7XG5cbnZhciBJTkNPUlJFQ1RfVE9fTEVOR1RIID0gZmFpbHMoZnVuY3Rpb24gKCkge1xuICByZXR1cm4gW10ucHVzaC5jYWxsKHsgbGVuZ3RoOiAweDEwMDAwMDAwMCB9LCAxKSAhPT0gNDI5NDk2NzI5Nztcbn0pO1xuXG4vLyBWOCBhbmQgU2FmYXJpIDw9IDE1LjQsIEZGIDwgMjMgdGhyb3dzIEludGVybmFsRXJyb3Jcbi8vIGh0dHBzOi8vYnVncy5jaHJvbWl1bS5vcmcvcC92OC9pc3N1ZXMvZGV0YWlsP2lkPTEyNjgxXG52YXIgcHJvcGVyRXJyb3JPbk5vbldyaXRhYmxlTGVuZ3RoID0gZnVuY3Rpb24gKCkge1xuICB0cnkge1xuICAgIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBlcy9uby1vYmplY3QtZGVmaW5lcHJvcGVydHkgLS0gc2FmZVxuICAgIE9iamVjdC5kZWZpbmVQcm9wZXJ0eShbXSwgJ2xlbmd0aCcsIHsgd3JpdGFibGU6IGZhbHNlIH0pLnB1c2goKTtcbiAgfSBjYXRjaCAoZXJyb3IpIHtcbiAgICByZXR1cm4gZXJyb3IgaW5zdGFuY2VvZiBUeXBlRXJyb3I7XG4gIH1cbn07XG5cbnZhciBGT1JDRUQgPSBJTkNPUlJFQ1RfVE9fTEVOR1RIIHx8ICFwcm9wZXJFcnJvck9uTm9uV3JpdGFibGVMZW5ndGgoKTtcblxuLy8gYEFycmF5LnByb3RvdHlwZS5wdXNoYCBtZXRob2Rcbi8vIGh0dHBzOi8vdGMzOS5lcy9lY21hMjYyLyNzZWMtYXJyYXkucHJvdG90eXBlLnB1c2hcbiQoeyB0YXJnZXQ6ICdBcnJheScsIHByb3RvOiB0cnVlLCBhcml0eTogMSwgZm9yY2VkOiBGT1JDRUQgfSwge1xuICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tdW51c2VkLXZhcnMgLS0gcmVxdWlyZWQgZm9yIGAubGVuZ3RoYFxuICBwdXNoOiBmdW5jdGlvbiBwdXNoKGl0ZW0pIHtcbiAgICB2YXIgTyA9IHRvT2JqZWN0KHRoaXMpO1xuICAgIHZhciBsZW4gPSBsZW5ndGhPZkFycmF5TGlrZShPKTtcbiAgICB2YXIgYXJnQ291bnQgPSBhcmd1bWVudHMubGVuZ3RoO1xuICAgIGRvZXNOb3RFeGNlZWRTYWZlSW50ZWdlcihsZW4gKyBhcmdDb3VudCk7XG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCBhcmdDb3VudDsgaSsrKSB7XG4gICAgICBPW2xlbl0gPSBhcmd1bWVudHNbaV07XG4gICAgICBsZW4rKztcbiAgICB9XG4gICAgc2V0QXJyYXlMZW5ndGgoTywgbGVuKTtcbiAgICByZXR1cm4gbGVuO1xuICB9XG59KTtcbiJdLCJuYW1lcyI6WyIkIiwiZm4iLCJnb29nbGVNYXAiLCJwYXJhbXMiLCJleHRlbmQiLCJ6b29tIiwiY29vcmRzIiwidHlwZSIsImRlYnVnIiwibGFuZ2FnZSIsIm92ZXJ2aWV3TWFwQ29udHJvbCIsInN0cmVldFZpZXdDb250cm9sIiwic2Nyb2xsd2hlZWwiLCJtYXBUeXBlQ29udHJvbCIsImdvb2dsZSIsIm1hcHMiLCJNYXBUeXBlSWQiLCJST0FETUFQIiwiZWFjaCIsIm1hcCIsIk1hcCIsImNlbnRlciIsIkxhdExuZyIsIm1hcFR5cGVJZCIsImRhdGEiLCJBcnJheSIsIkxhdExuZ0JvdW5kcyIsImFkZE1hcmtlciIsImFkZHJlc3MiLCJ1cmwiLCJpZCIsImljb24iLCJkcmFnZ2FibGUiLCJ0aXRsZSIsInRleHQiLCJzdWNjZXNzIiwiJHRoaXMiLCJjb25zb2xlIiwiZXJyb3IiLCJnZW9jb2RlQXN5bmMiLCIkdGhhdCIsImdlb2NvZGVyIiwiR2VvY29kZXIiLCJnZW9jb2RlIiwiYm91bmRzIiwibGFuZ3VhZ2UiLCJyZXN1bHRzIiwic3RhdHVzIiwiR2VvY29kZXJTdGF0dXMiLCJPSyIsImdlb21ldHJ5IiwibG9jYXRpb24iLCJtYXJrZXIiLCJNYXJrZXIiLCJwb3NpdGlvbiIsImV2ZW50IiwiYWRkTGlzdGVuZXIiLCJnZXRQb3NpdGlvbiIsImxhdCIsImxvbiIsImxuZyIsImluZm93aW5kb3ciLCJJbmZvV2luZG93IiwiY29udGVudCIsIm9wZW4iLCJkb2N1bWVudCIsInB1c2giLCJsZW5ndGgiLCJzZXRDZW50ZXIiLCJzZXRab29tIiwiZ2V0Wm9vbSIsImZpdEJvdW5kcyIsInJlbW92ZU1hcmtlciIsImxvZyIsIiRtYXJrZXJzIiwic2V0TWFwIiwiYWRkV2F5Iiwic3RhcnQiLCJlbmQiLCJzdGVwIiwicm91dGUiLCJkaXJlY3Rpb24iLCJEaXJlY3Rpb25zU2VydmljZSIsInJlZ2lvbiIsIndheSIsIkRpcmVjdGlvbnNSZW5kZXJlciIsInBhbmVsIiwiZ2V0RWxlbWVudEJ5SWQiLCJwcm92aWRlVHJpcEFsdGVybmF0aXZlcyIsImlubmVySFRNTCIsIndheXBvaW50cyIsImkiLCJzdG9wb3ZlciIsInJlcXVlc3QiLCJvcmlnaW4iLCJkZXN0aW5hdGlvbiIsInRyYXZlbE1vZGUiLCJEaXJlY3Rpb25zVHJhdmVsTW9kZSIsIkRSSVZJTkciLCJyZXNwb25zZSIsIkRpcmVjdGlvbnNTdGF0dXMiLCJzZXREaXJlY3Rpb25zIl0sInNvdXJjZVJvb3QiOiIifQ==