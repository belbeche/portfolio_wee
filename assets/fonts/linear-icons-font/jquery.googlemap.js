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
/******/ __webpack_require__.O(0, ["vendors-node_modules_core-js_internals_array-slice_js-node_modules_core-js_internals_export_j-81953d","vendors-node_modules_core-js_modules_es_object_to-string_js-node_modules_core-js_modules_es_s-6657b7"], () => (__webpack_exec__("./assets/dist/jquery.googlemap.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ }
]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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QTtBQUNBO0FBQ0E7QUFDQSxvQkFBb0IsY0FBYztBQUNsQztBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxDQUFDIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vYXNzZXRzL2Rpc3QvanF1ZXJ5Lmdvb2dsZW1hcC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9pbnRlcm5hbHMvYXJyYXktc2V0LWxlbmd0aC5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9pbnRlcm5hbHMvZG9lcy1ub3QtZXhjZWVkLXNhZmUtaW50ZWdlci5qcyIsIndlYnBhY2s6Ly8vLi9ub2RlX21vZHVsZXMvY29yZS1qcy9tb2R1bGVzL2VzLmFycmF5LnB1c2guanMiXSwic291cmNlc0NvbnRlbnQiOlsiJChmdW5jdGlvbigpIHtcclxuXHJcblx0JC5mbi5nb29nbGVNYXAgPSBmdW5jdGlvbihwYXJhbXMpIHtcclxuXHRcdHBhcmFtcyA9ICQuZXh0ZW5kKCB7XHJcblx0XHRcdHpvb20gOiAxMCxcclxuXHRcdFx0Y29vcmRzIDogWzQ4Ljg5NTY1MSwgMi4yOTA1NjldLFxyXG5cdFx0XHR0eXBlIDogXCJST0FETUFQXCIsXHJcblx0XHRcdGRlYnVnIDogZmFsc2UsXHJcblx0XHRcdGxhbmdhZ2UgOiBcImVuZ2xpc2hcIixcclxuXHRcdFx0b3ZlcnZpZXdNYXBDb250cm9sOiBmYWxzZSxcclxuXHRcdFx0c3RyZWV0Vmlld0NvbnRyb2w6IGZhbHNlLFxyXG5cdFx0XHRzY3JvbGx3aGVlbDogZmFsc2UsXHJcblx0XHRcdG1hcFR5cGVDb250cm9sOiBmYWxzZVxyXG5cdFx0fSwgcGFyYW1zKTtcclxuXHJcblx0XHRzd2l0Y2gocGFyYW1zLnR5cGUpIHtcclxuXHRcdFx0Y2FzZSAnUk9BRE1BUCc6XHJcblx0XHRcdGNhc2UgJ1NBVEVMTElURSc6XHJcblx0XHRcdGNhc2UgJ0hZQlJJRCc6XHJcblx0XHRcdGNhc2UgJ1RFUlJBSU4nOlxyXG5cdFx0XHRcdHBhcmFtcy50eXBlID0gZ29vZ2xlLm1hcHMuTWFwVHlwZUlkW3BhcmFtcy50eXBlXTtcclxuXHRcdFx0XHRicmVhaztcclxuXHRcdFx0ZGVmYXVsdDpcclxuXHRcdFx0XHRwYXJhbXMudHlwZSA9IGdvb2dsZS5tYXBzLk1hcFR5cGVJZC5ST0FETUFQO1xyXG5cdFx0XHRcdGJyZWFrO1xyXG5cdFx0fVxyXG5cclxuXHRcdHRoaXMuZWFjaChmdW5jdGlvbigpIHtcclxuXHJcblx0XHRcdHZhciBtYXAgPSBuZXcgZ29vZ2xlLm1hcHMuTWFwKHRoaXMsIHtcclxuXHRcdFx0XHR6b29tOiBwYXJhbXMuem9vbSxcclxuXHRcdFx0XHRjZW50ZXI6IG5ldyBnb29nbGUubWFwcy5MYXRMbmcocGFyYW1zLmNvb3Jkc1swXSwgcGFyYW1zLmNvb3Jkc1sxXSksXHJcblx0XHRcdFx0bWFwVHlwZUlkOiBwYXJhbXMudHlwZSxcclxuXHRcdFx0XHRzY3JvbGx3aGVlbDogcGFyYW1zLnNjcm9sbHdoZWVsLFxyXG5cdFx0XHRcdHN0cmVldFZpZXdDb250cm9sOiBwYXJhbXMuc3RyZWV0Vmlld0NvbnRyb2wsXHJcblx0XHRcdFx0b3ZlcnZpZXdNYXBDb250cm9sOiBwYXJhbXMub3ZlcnZpZXdNYXBDb250cm9sLFxyXG5cdFx0XHRcdG1hcFR5cGVDb250cm9sOiBwYXJhbXMubWFwVHlwZUNvbnRyb2xcclxuXHJcblx0XHRcdH0pO1xyXG5cclxuXHRcdFx0JCh0aGlzKS5kYXRhKCdnb29nbGVNYXAnLCBtYXApO1xyXG5cdFx0XHQkKHRoaXMpLmRhdGEoJ2dvb2dsZUxhbmcnLCBwYXJhbXMubGFuZ2FnZSk7XHJcblx0XHRcdCQodGhpcykuZGF0YSgnZ29vZ2xlRGVidWcnLCBwYXJhbXMuZGVidWcpO1xyXG5cdFx0XHQkKHRoaXMpLmRhdGEoJ2dvb2dsZU1hcmtlcicsIG5ldyBBcnJheSgpKTtcclxuXHRcdFx0JCh0aGlzKS5kYXRhKCdnb29nbGVCb3VuZCcsIG5ldyBnb29nbGUubWFwcy5MYXRMbmdCb3VuZHMoKSk7XHJcblx0XHR9KTtcclxuXHJcblx0XHRyZXR1cm4gdGhpcztcclxuXHR9XHJcblxyXG5cdCQuZm4uYWRkTWFya2VyID0gZnVuY3Rpb24ocGFyYW1zKSB7XHJcblx0XHRwYXJhbXMgPSAkLmV4dGVuZCgge1xyXG5cdFx0XHRjb29yZHMgOiBmYWxzZSxcclxuXHRcdFx0YWRkcmVzcyA6IGZhbHNlLFxyXG5cdFx0XHR1cmwgOiBmYWxzZSxcclxuXHRcdFx0aWQgOiBmYWxzZSxcclxuXHRcdFx0aWNvbiA6IGZhbHNlLFxyXG5cdFx0XHRkcmFnZ2FibGUgOiBmYWxzZSxcclxuXHRcdFx0dGl0bGUgOiBcIlwiLFxyXG5cdFx0XHR0ZXh0IDogXCJcIixcclxuXHRcdFx0c3VjY2VzcyA6IGZ1bmN0aW9uKCkge31cclxuXHRcdH0sIHBhcmFtcyk7XHJcblxyXG5cdFx0dGhpcy5lYWNoKGZ1bmN0aW9uKCkge1xyXG5cdFx0XHQkdGhpcyA9ICQodGhpcyk7XHJcblxyXG5cdFx0XHRpZighJHRoaXMuZGF0YSgnZ29vZ2xlTWFwJykpIHtcclxuXHRcdFx0XHRpZigkdGhpcy5kYXRhKCdnb29nbGVEZWJ1ZycpKVxyXG5cdFx0XHRcdFx0Y29uc29sZS5lcnJvcihcImpRdWVyeSBnb29nbGVNYXAgOiBVbmFibGUgdG8gYWRkIGEgbWFya2VyIHdoZXJlIHRoZXJlIGlzIG5vIG1hcCAhXCIpO1xyXG5cdFx0XHRcdFx0XHJcblx0XHRcdFx0cmV0dXJuIGZhbHNlO1xyXG5cdFx0XHR9XHJcblxyXG5cdFx0XHRpZighcGFyYW1zLmNvb3JkcyAmJiAhcGFyYW1zLmFkZHJlc3MpIHtcclxuXHRcdFx0XHRpZigkdGhpcy5kYXRhKCdnb29nbGVEZWJ1ZycpKVxyXG5cdFx0XHRcdFx0Y29uc29sZS5lcnJvcihcImpRdWVyeSBnb29nbGVNYXAgOiBVbmFibGUgdG8gYWRkIGEgbWFya2VyIGlmIHlvdSBkb24ndCB0ZWxsIHVzIHdoZXJlICFcIik7XHJcblx0XHRcdFx0XHRcclxuXHRcdFx0XHRyZXR1cm4gZmFsc2U7XHJcblx0XHRcdH1cclxuXHJcblx0XHRcdGlmKHBhcmFtcy5hZGRyZXNzICYmIHR5cGVvZiBwYXJhbXMuYWRkcmVzcyA9PSBcInN0cmluZ1wiKSB7XHJcblxyXG5cdFx0XHRcdHZhciBnZW9jb2RlQXN5bmMgPSBmdW5jdGlvbigkdGhhdCkge1xyXG5cclxuXHRcdFx0XHRcdHZhciBnZW9jb2RlciA9IG5ldyBnb29nbGUubWFwcy5HZW9jb2RlcigpO1xyXG5cclxuXHRcdFx0XHRcdGdlb2NvZGVyLmdlb2NvZGUoe1xyXG5cdFx0XHRcdFx0XHRhZGRyZXNzIDogcGFyYW1zLmFkZHJlc3MsXHJcblx0XHRcdFx0XHRcdGJvdW5kcyA6ICR0aGF0LmRhdGEoJ2dvb2dsZUJvdW5kJyksXHJcblx0XHRcdFx0XHRcdGxhbmd1YWdlIDogJHRoYXQuZGF0YSgnZ29vZ2xlTGFuZycpXHJcblx0XHRcdFx0XHR9LCBmdW5jdGlvbihyZXN1bHRzLCBzdGF0dXMpIHtcclxuXHJcblx0XHRcdFx0XHRcdGlmIChzdGF0dXMgPT0gZ29vZ2xlLm1hcHMuR2VvY29kZXJTdGF0dXMuT0spIHtcclxuXHRcdFx0XHRcdFx0XHQkdGhhdC5kYXRhKCdnb29nbGVCb3VuZCcpLmV4dGVuZChyZXN1bHRzWzBdLmdlb21ldHJ5LmxvY2F0aW9uKTtcclxuXHJcblx0XHRcdFx0XHRcdFx0aWYocGFyYW1zLmljb24pIHtcclxuXHRcdFx0XHRcdFx0XHRcdHZhciBtYXJrZXIgPSBuZXcgZ29vZ2xlLm1hcHMuTWFya2VyKHtcclxuXHRcdFx0XHRcdFx0XHRcdFx0bWFwOiAkdGhpcy5kYXRhKCdnb29nbGVNYXAnKSxcclxuXHRcdFx0XHRcdFx0XHRcdFx0cG9zaXRpb246IHJlc3VsdHNbMF0uZ2VvbWV0cnkubG9jYXRpb24sXHJcblx0XHRcdFx0XHRcdFx0XHRcdHRpdGxlOiBwYXJhbXMudGl0bGUsXHJcblx0XHRcdFx0XHRcdFx0XHRcdGljb246IHBhcmFtcy5pY29uLFxyXG5cdFx0XHRcdFx0XHRcdFx0XHRkcmFnZ2FibGU6IHBhcmFtcy5kcmFnZ2FibGVcclxuXHRcdFx0XHRcdFx0XHRcdH0pO1xyXG5cdFx0XHRcdFx0XHRcdH0gZWxzZSB7XHJcblx0XHRcdFx0XHRcdFx0XHR2YXIgbWFya2VyID0gbmV3IGdvb2dsZS5tYXBzLk1hcmtlcih7XHJcblx0XHRcdFx0XHRcdFx0XHRcdG1hcDogJHRoYXQuZGF0YSgnZ29vZ2xlTWFwJyksXHJcblx0XHRcdFx0XHRcdFx0XHRcdHBvc2l0aW9uOiByZXN1bHRzWzBdLmdlb21ldHJ5LmxvY2F0aW9uLFxyXG5cdFx0XHRcdFx0XHRcdFx0XHR0aXRsZTogcGFyYW1zLnRpdGxlLFxyXG5cdFx0XHRcdFx0XHRcdFx0XHRkcmFnZ2FibGU6IHBhcmFtcy5kcmFnZ2FibGVcclxuXHRcdFx0XHRcdFx0XHRcdH0pO1xyXG5cdFx0XHRcdFx0XHRcdH1cclxuXHJcblx0XHRcdFx0XHRcdFx0aWYocGFyYW1zLmRyYWdnYWJsZSkge1xyXG5cdFx0XHRcdFx0XHRcdFx0Z29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIobWFya2VyLCAnZHJhZ2VuZCcsIGZ1bmN0aW9uKCkge1xyXG5cdFx0XHRcdFx0XHRcdFx0XHR2YXIgbG9jYXRpb24gPSBtYXJrZXIuZ2V0UG9zaXRpb24oKTtcclxuXHJcblx0XHRcdFx0XHRcdFx0XHRcdHZhciBjb29yZHMgPSB7fTtcclxuXHJcblx0XHRcdFx0XHRcdFx0XHRcdGNvb3Jkcy5sYXQgPSBsb2NhdGlvbi5sYXQoKTtcclxuXHRcdFx0XHRcdFx0XHRcdFx0Y29vcmRzLmxvbiA9IGxvY2F0aW9uLmxuZygpO1xyXG5cclxuXHRcdFx0XHRcdFx0XHRcdFx0cGFyYW1zLnN1Y2Nlc3MoY29vcmRzLCAkdGhpcyk7XHJcblx0XHRcdFx0XHRcdFx0XHR9KTtcclxuXHRcdFx0XHRcdFx0XHR9XHJcblxyXG5cdFx0XHRcdFx0XHRcdGlmKHBhcmFtcy50aXRsZSAhPSBcIlwiICYmIHBhcmFtcy50ZXh0ICE9IFwiXCIgJiYgIXBhcmFtcy51cmwpIHtcclxuXHRcdFx0XHRcdFx0XHRcdHZhciBpbmZvd2luZG93ID0gbmV3IGdvb2dsZS5tYXBzLkluZm9XaW5kb3coe1xyXG5cdFx0XHRcdFx0XHRcdFx0XHRjb250ZW50OiBcIjxoMT5cIitwYXJhbXMudGl0bGUrXCI8L2gxPlwiK3BhcmFtcy50ZXh0XHJcblx0XHRcdFx0XHRcdFx0XHR9KTtcclxuXHJcblx0XHRcdFx0XHRcdFx0XHR2YXIgbWFwID0gJHRoYXQuZGF0YSgnZ29vZ2xlTWFwJyk7XHJcblxyXG5cdFx0XHRcdFx0XHRcdFx0Z29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIobWFya2VyLCAnY2xpY2snLCBmdW5jdGlvbigpIHtcclxuXHRcdFx0XHRcdFx0XHRcdFx0aW5mb3dpbmRvdy5vcGVuKG1hcCwgbWFya2VyKTtcclxuXHRcdFx0XHRcdFx0XHRcdH0pO1xyXG5cdFx0XHRcdFx0XHRcdH0gZWxzZSBpZihwYXJhbXMudXJsKSB7XHJcblx0XHRcdFx0XHRcdFx0XHRnb29nbGUubWFwcy5ldmVudC5hZGRMaXN0ZW5lcihtYXJrZXIsICdjbGljaycsIGZ1bmN0aW9uKCkge1xyXG5cdFx0XHRcdFx0XHRcdFx0XHRkb2N1bWVudC5sb2NhdGlvbiA9IHBhcmFtcy51cmw7XHJcblx0XHRcdFx0XHRcdFx0XHR9KTtcclxuXHRcdFx0XHRcdFx0XHR9XHJcblxyXG5cdFx0XHRcdFx0XHRcdGlmKCFwYXJhbXMuaWQpIHtcclxuXHRcdFx0XHRcdFx0XHRcdCR0aGF0LmRhdGEoJ2dvb2dsZU1hcmtlcicpLnB1c2gobWFya2VyKTtcclxuXHRcdFx0XHRcdFx0XHR9IGVsc2Uge1xyXG5cdFx0XHRcdFx0XHRcdFx0JHRoYXQuZGF0YSgnZ29vZ2xlTWFya2VyJylbcGFyYW1zLmlkXSA9IG1hcmtlcjtcclxuXHRcdFx0XHRcdFx0XHR9XHJcblxyXG5cdFx0XHRcdFx0XHRcdGlmKCR0aGF0LmRhdGEoJ2dvb2dsZU1hcmtlcicpLmxlbmd0aCA9PSAxKSB7XHJcblx0XHRcdFx0XHRcdFx0XHQkdGhhdC5kYXRhKCdnb29nbGVNYXAnKS5zZXRDZW50ZXIocmVzdWx0c1swXS5nZW9tZXRyeS5sb2NhdGlvbik7XHJcblx0XHRcdFx0XHRcdFx0XHQkdGhhdC5kYXRhKCdnb29nbGVNYXAnKS5zZXRab29tKCR0aGF0LmRhdGEoJ2dvb2dsZU1hcCcpLmdldFpvb20oKSk7XHJcblx0XHRcdFx0XHRcdFx0fSBlbHNlIHtcclxuXHRcdFx0XHRcdFx0XHRcdCR0aGF0LmRhdGEoJ2dvb2dsZU1hcCcpLmZpdEJvdW5kcygkdGhhdC5kYXRhKCdnb29nbGVCb3VuZCcpKTtcclxuXHRcdFx0XHRcdFx0XHR9XHJcblxyXG5cdFx0XHRcdFx0XHRcdHZhciBjb29yZHMgPSB7fTtcclxuXHRcdFx0XHRcdFx0XHRjb29yZHMubGF0ID0gcmVzdWx0c1swXS5nZW9tZXRyeS5sb2NhdGlvbi5sYXQoKTtcclxuXHRcdFx0XHRcdFx0XHRjb29yZHMubG9uID0gcmVzdWx0c1swXS5nZW9tZXRyeS5sb2NhdGlvbi5sbmcoKTtcclxuXHJcblx0XHRcdFx0XHRcdFx0cGFyYW1zLnN1Y2Nlc3MoY29vcmRzLCAkdGhpcyk7XHJcblxyXG5cdFx0XHRcdFx0XHR9IGVsc2Uge1xyXG5cdFx0XHRcdFx0XHRcdGlmKCR0aGlzLmRhdGEoJ2dvb2dsZURlYnVnJykpXHJcblx0XHRcdFx0XHRcdFx0XHRjb25zb2xlLmVycm9yKFwialF1ZXJ5IGdvb2dsZU1hcCA6IFVuYWJsZSB0byBmaW5kIHRoZSBwbGFjZSBhc2tlZCBmb3IgdGhlIG1hcmtlciAoXCIrc3RhdHVzK1wiKVwiKTtcclxuXHRcdFx0XHRcdFx0fVxyXG5cdFx0XHRcdFx0fSk7XHJcblx0XHRcdFx0fSgkdGhpcyk7XHJcblx0XHRcdH0gZWxzZSB7XHJcblx0XHRcdFx0JHRoaXMuZGF0YSgnZ29vZ2xlQm91bmQnKS5leHRlbmQobmV3IGdvb2dsZS5tYXBzLkxhdExuZyhwYXJhbXMuY29vcmRzWzBdLCBwYXJhbXMuY29vcmRzWzFdKSk7XHJcblxyXG4gICAgICAgIFx0XHRcdGlmKHBhcmFtcy5pY29uKSB7XHJcblx0XHRcdFx0XHR2YXIgbWFya2VyID0gbmV3IGdvb2dsZS5tYXBzLk1hcmtlcih7XHJcblx0XHRcdFx0XHRcdG1hcDogJHRoaXMuZGF0YSgnZ29vZ2xlTWFwJyksXHJcblx0XHRcdFx0XHRcdHBvc2l0aW9uOiBuZXcgZ29vZ2xlLm1hcHMuTGF0TG5nKHBhcmFtcy5jb29yZHNbMF0sIHBhcmFtcy5jb29yZHNbMV0pLFxyXG5cdFx0XHRcdFx0XHR0aXRsZTogcGFyYW1zLnRpdGxlLFxyXG5cdFx0XHRcdFx0XHRpY29uOiBwYXJhbXMuaWNvbixcclxuXHRcdFx0XHRcdFx0ZHJhZ2dhYmxlOiBwYXJhbXMuZHJhZ2dhYmxlXHJcblx0XHRcdFx0XHR9KTtcclxuXHRcdFx0XHR9IGVsc2Uge1xyXG5cdFx0XHRcdFx0dmFyIG1hcmtlciA9IG5ldyBnb29nbGUubWFwcy5NYXJrZXIoe1xyXG5cdFx0XHRcdFx0XHRtYXA6ICR0aGlzLmRhdGEoJ2dvb2dsZU1hcCcpLFxyXG5cdFx0XHRcdFx0XHRwb3NpdGlvbjogbmV3IGdvb2dsZS5tYXBzLkxhdExuZyhwYXJhbXMuY29vcmRzWzBdLCBwYXJhbXMuY29vcmRzWzFdKSxcclxuXHRcdFx0XHRcdFx0dGl0bGU6IHBhcmFtcy50aXRsZSxcclxuXHRcdFx0XHRcdFx0ZHJhZ2dhYmxlOiBwYXJhbXMuZHJhZ2dhYmxlXHJcblx0XHRcdFx0XHR9KTtcclxuXHRcdFx0XHR9XHJcblxyXG4gICAgICAgIFx0XHRcdGlmKHBhcmFtcy50aXRsZSAhPSBcIlwiICYmIHBhcmFtcy50ZXh0ICE9IFwiXCIgJiYgIXBhcmFtcy51cmwpIHtcclxuICAgICAgICAgIFx0XHRcdFx0dmFyIGluZm93aW5kb3cgPSBuZXcgZ29vZ2xlLm1hcHMuSW5mb1dpbmRvdyh7XHJcblx0XHRcdFx0XHRcdGNvbnRlbnQ6IFwiPGgxPlwiK3BhcmFtcy50aXRsZStcIjwvaDE+XCIrcGFyYW1zLnRleHRcclxuXHRcdFx0XHRcdH0pO1xyXG5cclxuXHRcdFx0XHRcdHZhciBtYXAgPSAkdGhpcy5kYXRhKCdnb29nbGVNYXAnKTtcclxuXHJcblx0ICAgICAgICBcdFx0XHRnb29nbGUubWFwcy5ldmVudC5hZGRMaXN0ZW5lcihtYXJrZXIsICdjbGljaycsIGZ1bmN0aW9uKCkge1xyXG5cdFx0ICAgICAgICBcdFx0XHRpbmZvd2luZG93Lm9wZW4obWFwLCBtYXJrZXIpO1xyXG5cdCAgICAgICAgXHRcdFx0fSk7XHJcblx0XHRcdFx0fSBlbHNlIGlmKHBhcmFtcy51cmwpIHtcclxuICAgICAgICAgIFx0XHRcdFx0Z29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIobWFya2VyLCAnY2xpY2snLCBmdW5jdGlvbigpIHtcclxuICAgICAgICAgICAgICBcdFx0XHRcdFx0ZG9jdW1lbnQubG9jYXRpb24gPSBwYXJhbXMudXJsO1xyXG4gICAgICAgIFx0XHRcdFx0fSk7XHJcblx0XHRcdFx0fVxyXG5cclxuXHRcdFx0XHRpZihwYXJhbXMuZHJhZ2dhYmxlKSB7XHJcblx0XHRcdFx0XHRnb29nbGUubWFwcy5ldmVudC5hZGRMaXN0ZW5lcihtYXJrZXIsICdkcmFnZW5kJywgZnVuY3Rpb24oKSB7XHJcblx0XHRcdFx0XHRcdHZhciBsb2NhdGlvbiA9IG1hcmtlci5nZXRQb3NpdGlvbigpO1xyXG5cclxuXHRcdFx0XHRcdFx0dmFyIGNvb3JkcyA9IHt9O1xyXG5cclxuXHRcdFx0XHRcdFx0Y29vcmRzLmxhdCA9IGxvY2F0aW9uLmxhdCgpO1xyXG5cdFx0XHRcdFx0XHRjb29yZHMubG9uID0gbG9jYXRpb24ubG5nKCk7XHJcblxyXG5cdFx0XHRcdFx0XHRwYXJhbXMuc3VjY2Vzcyhjb29yZHMsICR0aGlzKTtcclxuXHRcdFx0XHRcdH0pO1xyXG5cdFx0XHRcdH1cclxuXHJcblx0XHRcdFx0aWYoIXBhcmFtcy5pZCkge1xyXG4gICAgICAgXHRcdFx0XHRcdCR0aGlzLmRhdGEoJ2dvb2dsZU1hcmtlcicpLnB1c2gobWFya2VyKTtcclxuICAgICAgICBcdFx0XHR9IGVsc2Uge1xyXG4gICAgICAgIFx0XHRcdFx0JHRoaXMuZGF0YSgnZ29vZ2xlTWFya2VyJylbcGFyYW1zLmlkXSA9IG1hcmtlcjtcclxuICAgICAgICBcdFx0XHR9XHJcblxyXG5cdFx0XHRcdGlmKCR0aGlzLmRhdGEoJ2dvb2dsZU1hcmtlcicpLmxlbmd0aCA9PSAxKSB7XHJcblx0XHRcdFx0XHQkdGhpcy5kYXRhKCdnb29nbGVNYXAnKS5zZXRDZW50ZXIobmV3IGdvb2dsZS5tYXBzLkxhdExuZyhwYXJhbXMuY29vcmRzWzBdLCBwYXJhbXMuY29vcmRzWzFdKSk7XHJcblx0XHRcdFx0XHQkdGhpcy5kYXRhKCdnb29nbGVNYXAnKS5zZXRab29tKCR0aGlzLmRhdGEoJ2dvb2dsZU1hcCcpLmdldFpvb20oKSk7XHJcblx0XHRcdFx0fSBlbHNlIHtcclxuXHRcdFx0XHRcdCR0aGlzLmRhdGEoJ2dvb2dsZU1hcCcpLmZpdEJvdW5kcygkdGhpcy5kYXRhKCdnb29nbGVCb3VuZCcpKTtcclxuXHRcdFx0XHR9XHJcblxyXG5cdFx0XHRcdHBhcmFtcy5zdWNjZXNzKHtcclxuXHRcdFx0XHRcdGxhdDogcGFyYW1zLmNvb3Jkc1swXSxcclxuXHRcdFx0XHRcdGxvbjogcGFyYW1zLmNvb3Jkc1sxXVxyXG5cdFx0XHRcdH0sICR0aGlzKTtcclxuXHRcdFx0fVxyXG5cdFx0fSk7XHJcblxyXG5cdFx0cmV0dXJuIHRoaXM7XHJcblx0fVxyXG5cclxuXHQkLmZuLnJlbW92ZU1hcmtlciA9IGZ1bmN0aW9uKGlkKSB7XHJcblx0XHR0aGlzLmVhY2goZnVuY3Rpb24oKSB7XHJcblx0XHRcdHZhciAkdGhpcyA9ICQodGhpcyk7XHJcblxyXG4gICAgXHRcdFx0aWYoISR0aGlzLmRhdGEoJ2dvb2dsZU1hcCcpKSB7XHJcbiAgICBcdFx0XHRcdGlmKCR0aGlzLmRhdGEoJ2dvb2dsZURlYnVnJykpXHJcbiAgICAgIFx0XHRcdFx0XHRjb25zb2xlLmxvZyhcImpRdWVyeSBnb29nbGVNYXAgOiBVbmFibGUgdG8gZGVsZXRlIGEgbWFya2VyIHdoZXJlIHRoZXJlIGlzIG5vIG1hcCAhXCIpO1xyXG4gICAgICBcdFx0XHRcdFx0XHJcbiAgICAgIFx0XHRcdFx0cmV0dXJuIGZhbHNlO1xyXG4gICAgXHRcdFx0fVxyXG5cclxuICAgIFx0XHRcdHZhciAkbWFya2VycyA9ICR0aGlzLmRhdGEoJ2dvb2dsZU1hcmtlcicpO1xyXG5cclxuICAgIFx0XHRcdGlmKHR5cGVvZiAkbWFya2Vyc1tpZF0gIT0gJ3VuZGVmaW5lZCcpIHtcclxuICAgIFx0XHRcdFx0JG1hcmtlcnNbaWRdLnNldE1hcChudWxsKTtcclxuICAgIFx0XHRcdFx0XHJcbiAgICAgIFx0XHRcdFx0aWYoJHRoaXMuZGF0YSgnZ29vZ2xlRGVidWcnKSlcclxuICAgICAgXHRcdFx0XHRcdGNvbnNvbGUubG9nKCdqUXVlcnkgZ29vZ2xlTWFwIDogbWFya2VyIGRlbGV0ZWQnKTtcclxuICAgICAgXHRcdFx0XHRcdFxyXG4gICAgICBcdFx0XHRcdHJldHVybiB0cnVlO1xyXG4gICAgXHRcdFx0fSBlbHNlIHtcclxuICAgICAgXHRcdFx0XHRpZigkdGhpcy5kYXRhKCdnb29nbGVEZWJ1ZycpKVxyXG4gICAgICBcdFx0XHRcdFx0Y29uc29sZS5lcnJvcihcImpRdWVyeSBnb29nbGVNYXAgOiBVbmFibGUgdG8gZGVsZXRlIGEgbWFya2VyIGlmIGl0IG5vdCBleGlzdHMgIVwiKTtcclxuICAgICAgXHRcdFxyXG4gICAgICBcdFx0XHRcdHJldHVybiBmYWxzZTtcclxuICAgIFx0XHRcdH1cclxuXHRcdH0pO1xyXG5cdH1cclxuXHJcblx0JC5mbi5hZGRXYXkgPSBmdW5jdGlvbihwYXJhbXMpIHtcclxuXHRcdHBhcmFtcyA9ICQuZXh0ZW5kKCB7XHJcblx0XHRcdHN0YXJ0IDogZmFsc2UsXHJcblx0XHRcdGVuZCA6IGZhbHNlLFxyXG5cdFx0XHRzdGVwIDogW10sXHJcblx0XHRcdHJvdXRlIDogZmFsc2UsXHJcblx0XHRcdGxhbmdhZ2UgOiAnZW5nbGlzaCdcclxuXHRcdH0sIHBhcmFtcyk7XHJcblxyXG5cdFx0dmFyIGRpcmVjdGlvbiA9IG5ldyBnb29nbGUubWFwcy5EaXJlY3Rpb25zU2VydmljZSh7XHJcblx0XHRcdHJlZ2lvbjogXCJmclwiXHJcblx0XHR9KTtcclxuXHJcblx0XHR2YXIgd2F5ID0gbmV3IGdvb2dsZS5tYXBzLkRpcmVjdGlvbnNSZW5kZXJlcih7XHJcblx0XHRcdGRyYWdnYWJsZTogdHJ1ZSxcclxuXHRcdFx0bWFwOiAkKHRoaXMpLmRhdGEoJ2dvb2dsZU1hcCcpLFxyXG5cdFx0XHRwYW5lbDogZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQocGFyYW1zLnJvdXRlKSxcclxuXHRcdFx0cHJvdmlkZVRyaXBBbHRlcm5hdGl2ZXM6IHRydWVcclxuXHRcdH0pO1xyXG5cdFx0XHJcblx0XHRkb2N1bWVudC5nZXRFbGVtZW50QnlJZC5pbm5lckhUTUwgPSBcIlwiO1xyXG5cclxuXHRcdHZhciB3YXlwb2ludHMgPSBbXTtcclxuXHJcbiAgICBcdFx0Zm9yKHZhciBpIGluIHBhcmFtcy5zdGVwKSB7XHJcbiAgICBcdFx0XHR2YXIgc3RlcDtcclxuICAgICAgXHRcdFx0aWYodHlwZW9mIHBhcmFtcy5zdGVwW2ldID09IFwib2JqZWN0XCIpIHtcclxuICAgICAgICBcdFx0XHRzdGVwID0gbmV3IGdvb2dsZS5tYXBzLkxhdExuZyhwYXJhbXMuc3RlcFtpXVswXSwgcGFyYW1zLnN0ZXBbaV1bMV0pO1xyXG4gICAgICBcdFx0XHR9IGVsc2Uge1xyXG4gICAgICAgIFx0XHRcdHN0ZXAgPSBwYXJhbXMuc3RlcFtpXTtcclxuICAgICAgXHRcdFx0fVxyXG5cclxuICAgICAgXHRcdFx0d2F5cG9pbnRzLnB1c2goe1xyXG4gICAgICBcdFx0XHRcdGxvY2F0aW9uOiBzdGVwLFxyXG4gICAgICBcdFx0XHRcdHN0b3BvdmVyOiB0cnVlXHJcbiAgICAgIFx0XHRcdH0pO1xyXG5cdFx0fVxyXG5cclxuXHRcdGlmKHR5cGVvZiBwYXJhbXMuZW5kICE9IFwib2JqZWN0XCIpIHtcclxuXHRcdFx0dmFyIGdlb2NvZGVBc3luYyA9IGZ1bmN0aW9uKCR0aGF0KSB7XHJcblx0XHRcdFx0dmFyIGdlb2NvZGVyID0gbmV3IGdvb2dsZS5tYXBzLkdlb2NvZGVyKCk7XHJcblxyXG5cdFx0ICAgIFx0XHRnZW9jb2Rlci5nZW9jb2RlKHtcclxuXHRcdCAgICBcdFx0XHRhZGRyZXNzICA6IHBhcmFtcy5lbmQsXHJcblx0XHQgICAgXHRcdFx0Ym91bmRzICAgOiAkdGhhdC5kYXRhKCdnb29nbGVCb3VuZCcpLFxyXG5cdFx0ICAgIFx0XHRcdGxhbmd1YWdlIDogcGFyYW1zLmxhbmdhZ2VcclxuXHRcdCAgICBcdFx0fSwgZnVuY3Rpb24ocmVzdWx0cywgc3RhdHVzKSB7XHJcblx0ICAgICAgICBcdFx0XHRpZiAoc3RhdHVzID09IGdvb2dsZS5tYXBzLkdlb2NvZGVyU3RhdHVzLk9LKSB7XHJcblx0ICAgICAgICBcdFx0XHRcdHZhciByZXF1ZXN0ID0ge1xyXG5cdCAgICAgICAgICAgIFx0XHRcdFx0XHRvcmlnaW46IHBhcmFtcy5zdGFydCxcclxuXHQgICAgICAgICAgICBcdFx0XHRcdFx0ZGVzdGluYXRpb246IHJlc3VsdHNbMF0uZ2VvbWV0cnkubG9jYXRpb24sXHJcblx0ICAgICAgICAgICAgXHRcdFx0XHRcdHRyYXZlbE1vZGU6IGdvb2dsZS5tYXBzLkRpcmVjdGlvbnNUcmF2ZWxNb2RlLkRSSVZJTkcsXHJcblx0ICAgICAgICAgICAgXHRcdFx0XHRcdHJlZ2lvbjogXCJmclwiLFxyXG5cdCAgICAgICAgICAgIFx0XHRcdFx0XHR3YXlwb2ludHM6IHdheXBvaW50c1xyXG5cdFx0ICAgICAgICBcdFx0XHR9O1xyXG5cclxuXHRcdCAgICAgICAgXHRcdFx0ZGlyZWN0aW9uLnJvdXRlKHJlcXVlc3QsIGZ1bmN0aW9uKHJlc3BvbnNlLCBzdGF0dXMpIHtcclxuXHQgICAgICAgICAgICBcdFx0XHRcdFx0aWYgKHN0YXR1cyA9PSBnb29nbGUubWFwcy5EaXJlY3Rpb25zU3RhdHVzLk9LKSB7XHJcblx0ICAgICAgICAgICAgICBcdFx0XHRcdFx0XHR3YXkuc2V0RGlyZWN0aW9ucyhyZXNwb25zZSk7XHJcblx0ICAgICAgICAgICAgXHRcdFx0XHRcdH0gZWxzZSB7XHJcblx0ICAgICAgICAgICAgICBcdFx0XHRcdFx0XHRpZigkdGhhdC5kYXRhKCdnb29nbGVEZWJ1ZycpKVxyXG5cdCAgICAgICAgICAgIFx0XHRcdFx0XHRcdFx0Y29uc29sZS5lcnJvcihcImpRdWVyeSBnb29nbGVNYXAgOiBVbmFibGUgdG8gZmluZCB0aGUgcGxhY2UgYXNrZWQgZm9yIHRoZSByb3V0ZSAoXCIrcmVzcG9uc2UrXCIpXCIpO1xyXG5cdCAgICAgICAgICAgIFx0XHRcdFx0XHR9XHJcblx0XHQgICAgICAgIFx0XHRcdH0pO1xyXG5cclxuXHQgICAgICAgIFx0XHRcdH0gZWxzZSB7XHJcblx0ICAgICAgICAgIFx0XHRcdFx0aWYoJHRoYXQuZGF0YSgnZ29vZ2xlRGVidWcnKSlcclxuXHQgICAgICAgICAgXHRcdFx0XHRcdGNvbnNvbGUuZXJyb3IoXCJqUXVlcnkgZ29vZ2xlTWFwIDogQWRkcmVzcyBub3QgZm91bmRcIik7XHJcblx0ICAgICAgICBcdFx0XHR9XHJcblx0XHQgICAgXHRcdH0pO1xyXG5cdCAgICBcdFx0fSgkKHRoaXMpKTtcclxuXHRcdH0gZWxzZSB7XHJcblx0XHRcdHZhciByZXF1ZXN0ID0ge1xyXG5cdFx0XHRcdG9yaWdpbjogcGFyYW1zLnN0YXJ0LFxyXG5cdFx0XHRcdGRlc3RpbmF0aW9uOiBuZXcgZ29vZ2xlLm1hcHMuTGF0TG5nKHBhcmFtcy5lbmRbMF0sIHBhcmFtcy5lbmRbMV0pLFxyXG5cdFx0XHRcdHRyYXZlbE1vZGU6IGdvb2dsZS5tYXBzLkRpcmVjdGlvbnNUcmF2ZWxNb2RlLkRSSVZJTkcsXHJcblx0XHRcdFx0cmVnaW9uOiBcImZyXCIsXHJcblx0XHRcdFx0d2F5cG9pbnRzOiB3YXlwb2ludHNcclxuXHRcdFx0fTtcclxuXHJcblx0XHRcdGRpcmVjdGlvbi5yb3V0ZShyZXF1ZXN0LCBmdW5jdGlvbihyZXNwb25zZSwgc3RhdHVzKSB7XHJcblx0XHRcdFx0aWYgKHN0YXR1cyA9PSBnb29nbGUubWFwcy5EaXJlY3Rpb25zU3RhdHVzLk9LKSB7XHJcblx0XHRcdFx0XHR3YXkuc2V0RGlyZWN0aW9ucyhyZXNwb25zZSk7XHJcblx0XHRcdFx0fSBlbHNlIHtcclxuXHRcdFx0XHRcdGlmKCQodGhpcykuZGF0YSgnZ29vZ2xlRGVidWcnKSlcclxuICAgICAgICAgIFx0XHRcdFx0XHRjb25zb2xlLmVycm9yKFwialF1ZXJ5IGdvb2dsZU1hcCA6IEFkZHJlc3Mgbm90IGZvdW5kXCIpO1xyXG5cdFx0XHRcdH1cclxuXHRcdFx0fSk7XHJcblx0XHR9XHJcblxyXG5cdFx0cmV0dXJuIHRoaXM7XHJcblx0fVxyXG59KTtcclxuIiwiJ3VzZSBzdHJpY3QnO1xudmFyIERFU0NSSVBUT1JTID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2Rlc2NyaXB0b3JzJyk7XG52YXIgaXNBcnJheSA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9pcy1hcnJheScpO1xuXG52YXIgJFR5cGVFcnJvciA9IFR5cGVFcnJvcjtcbi8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBlcy9uby1vYmplY3QtZ2V0b3ducHJvcGVydHlkZXNjcmlwdG9yIC0tIHNhZmVcbnZhciBnZXRPd25Qcm9wZXJ0eURlc2NyaXB0b3IgPSBPYmplY3QuZ2V0T3duUHJvcGVydHlEZXNjcmlwdG9yO1xuXG4vLyBTYWZhcmkgPCAxMyBkb2VzIG5vdCB0aHJvdyBhbiBlcnJvciBpbiB0aGlzIGNhc2VcbnZhciBTSUxFTlRfT05fTk9OX1dSSVRBQkxFX0xFTkdUSF9TRVQgPSBERVNDUklQVE9SUyAmJiAhZnVuY3Rpb24gKCkge1xuICAvLyBtYWtlcyBubyBzZW5zZSB3aXRob3V0IHByb3BlciBzdHJpY3QgbW9kZSBzdXBwb3J0XG4gIGlmICh0aGlzICE9PSB1bmRlZmluZWQpIHJldHVybiB0cnVlO1xuICB0cnkge1xuICAgIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBlcy9uby1vYmplY3QtZGVmaW5lcHJvcGVydHkgLS0gc2FmZVxuICAgIE9iamVjdC5kZWZpbmVQcm9wZXJ0eShbXSwgJ2xlbmd0aCcsIHsgd3JpdGFibGU6IGZhbHNlIH0pLmxlbmd0aCA9IDE7XG4gIH0gY2F0Y2ggKGVycm9yKSB7XG4gICAgcmV0dXJuIGVycm9yIGluc3RhbmNlb2YgVHlwZUVycm9yO1xuICB9XG59KCk7XG5cbm1vZHVsZS5leHBvcnRzID0gU0lMRU5UX09OX05PTl9XUklUQUJMRV9MRU5HVEhfU0VUID8gZnVuY3Rpb24gKE8sIGxlbmd0aCkge1xuICBpZiAoaXNBcnJheShPKSAmJiAhZ2V0T3duUHJvcGVydHlEZXNjcmlwdG9yKE8sICdsZW5ndGgnKS53cml0YWJsZSkge1xuICAgIHRocm93ICRUeXBlRXJyb3IoJ0Nhbm5vdCBzZXQgcmVhZCBvbmx5IC5sZW5ndGgnKTtcbiAgfSByZXR1cm4gTy5sZW5ndGggPSBsZW5ndGg7XG59IDogZnVuY3Rpb24gKE8sIGxlbmd0aCkge1xuICByZXR1cm4gTy5sZW5ndGggPSBsZW5ndGg7XG59O1xuIiwidmFyICRUeXBlRXJyb3IgPSBUeXBlRXJyb3I7XG52YXIgTUFYX1NBRkVfSU5URUdFUiA9IDB4MUZGRkZGRkZGRkZGRkY7IC8vIDIgKiogNTMgLSAxID09IDkwMDcxOTkyNTQ3NDA5OTFcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbiAoaXQpIHtcbiAgaWYgKGl0ID4gTUFYX1NBRkVfSU5URUdFUikgdGhyb3cgJFR5cGVFcnJvcignTWF4aW11bSBhbGxvd2VkIGluZGV4IGV4Y2VlZGVkJyk7XG4gIHJldHVybiBpdDtcbn07XG4iLCIndXNlIHN0cmljdCc7XG52YXIgJCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9leHBvcnQnKTtcbnZhciB0b09iamVjdCA9IHJlcXVpcmUoJy4uL2ludGVybmFscy90by1vYmplY3QnKTtcbnZhciBsZW5ndGhPZkFycmF5TGlrZSA9IHJlcXVpcmUoJy4uL2ludGVybmFscy9sZW5ndGgtb2YtYXJyYXktbGlrZScpO1xudmFyIHNldEFycmF5TGVuZ3RoID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2FycmF5LXNldC1sZW5ndGgnKTtcbnZhciBkb2VzTm90RXhjZWVkU2FmZUludGVnZXIgPSByZXF1aXJlKCcuLi9pbnRlcm5hbHMvZG9lcy1ub3QtZXhjZWVkLXNhZmUtaW50ZWdlcicpO1xudmFyIGZhaWxzID0gcmVxdWlyZSgnLi4vaW50ZXJuYWxzL2ZhaWxzJyk7XG5cbnZhciBJTkNPUlJFQ1RfVE9fTEVOR1RIID0gZmFpbHMoZnVuY3Rpb24gKCkge1xuICByZXR1cm4gW10ucHVzaC5jYWxsKHsgbGVuZ3RoOiAweDEwMDAwMDAwMCB9LCAxKSAhPT0gNDI5NDk2NzI5Nztcbn0pO1xuXG4vLyBWOCBhbmQgU2FmYXJpIDw9IDE1LjQsIEZGIDwgMjMgdGhyb3dzIEludGVybmFsRXJyb3Jcbi8vIGh0dHBzOi8vYnVncy5jaHJvbWl1bS5vcmcvcC92OC9pc3N1ZXMvZGV0YWlsP2lkPTEyNjgxXG52YXIgcHJvcGVyRXJyb3JPbk5vbldyaXRhYmxlTGVuZ3RoID0gZnVuY3Rpb24gKCkge1xuICB0cnkge1xuICAgIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBlcy9uby1vYmplY3QtZGVmaW5lcHJvcGVydHkgLS0gc2FmZVxuICAgIE9iamVjdC5kZWZpbmVQcm9wZXJ0eShbXSwgJ2xlbmd0aCcsIHsgd3JpdGFibGU6IGZhbHNlIH0pLnB1c2goKTtcbiAgfSBjYXRjaCAoZXJyb3IpIHtcbiAgICByZXR1cm4gZXJyb3IgaW5zdGFuY2VvZiBUeXBlRXJyb3I7XG4gIH1cbn07XG5cbnZhciBGT1JDRUQgPSBJTkNPUlJFQ1RfVE9fTEVOR1RIIHx8ICFwcm9wZXJFcnJvck9uTm9uV3JpdGFibGVMZW5ndGgoKTtcblxuLy8gYEFycmF5LnByb3RvdHlwZS5wdXNoYCBtZXRob2Rcbi8vIGh0dHBzOi8vdGMzOS5lcy9lY21hMjYyLyNzZWMtYXJyYXkucHJvdG90eXBlLnB1c2hcbiQoeyB0YXJnZXQ6ICdBcnJheScsIHByb3RvOiB0cnVlLCBhcml0eTogMSwgZm9yY2VkOiBGT1JDRUQgfSwge1xuICAvLyBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tdW51c2VkLXZhcnMgLS0gcmVxdWlyZWQgZm9yIGAubGVuZ3RoYFxuICBwdXNoOiBmdW5jdGlvbiBwdXNoKGl0ZW0pIHtcbiAgICB2YXIgTyA9IHRvT2JqZWN0KHRoaXMpO1xuICAgIHZhciBsZW4gPSBsZW5ndGhPZkFycmF5TGlrZShPKTtcbiAgICB2YXIgYXJnQ291bnQgPSBhcmd1bWVudHMubGVuZ3RoO1xuICAgIGRvZXNOb3RFeGNlZWRTYWZlSW50ZWdlcihsZW4gKyBhcmdDb3VudCk7XG4gICAgZm9yICh2YXIgaSA9IDA7IGkgPCBhcmdDb3VudDsgaSsrKSB7XG4gICAgICBPW2xlbl0gPSBhcmd1bWVudHNbaV07XG4gICAgICBsZW4rKztcbiAgICB9XG4gICAgc2V0QXJyYXlMZW5ndGgoTywgbGVuKTtcbiAgICByZXR1cm4gbGVuO1xuICB9XG59KTtcbiJdLCJuYW1lcyI6WyIkIiwiZm4iLCJnb29nbGVNYXAiLCJwYXJhbXMiLCJleHRlbmQiLCJ6b29tIiwiY29vcmRzIiwidHlwZSIsImRlYnVnIiwibGFuZ2FnZSIsIm92ZXJ2aWV3TWFwQ29udHJvbCIsInN0cmVldFZpZXdDb250cm9sIiwic2Nyb2xsd2hlZWwiLCJtYXBUeXBlQ29udHJvbCIsImdvb2dsZSIsIm1hcHMiLCJNYXBUeXBlSWQiLCJST0FETUFQIiwiZWFjaCIsIm1hcCIsIk1hcCIsImNlbnRlciIsIkxhdExuZyIsIm1hcFR5cGVJZCIsImRhdGEiLCJBcnJheSIsIkxhdExuZ0JvdW5kcyIsImFkZE1hcmtlciIsImFkZHJlc3MiLCJ1cmwiLCJpZCIsImljb24iLCJkcmFnZ2FibGUiLCJ0aXRsZSIsInRleHQiLCJzdWNjZXNzIiwiJHRoaXMiLCJjb25zb2xlIiwiZXJyb3IiLCJnZW9jb2RlQXN5bmMiLCIkdGhhdCIsImdlb2NvZGVyIiwiR2VvY29kZXIiLCJnZW9jb2RlIiwiYm91bmRzIiwibGFuZ3VhZ2UiLCJyZXN1bHRzIiwic3RhdHVzIiwiR2VvY29kZXJTdGF0dXMiLCJPSyIsImdlb21ldHJ5IiwibG9jYXRpb24iLCJtYXJrZXIiLCJNYXJrZXIiLCJwb3NpdGlvbiIsImV2ZW50IiwiYWRkTGlzdGVuZXIiLCJnZXRQb3NpdGlvbiIsImxhdCIsImxvbiIsImxuZyIsImluZm93aW5kb3ciLCJJbmZvV2luZG93IiwiY29udGVudCIsIm9wZW4iLCJkb2N1bWVudCIsInB1c2giLCJsZW5ndGgiLCJzZXRDZW50ZXIiLCJzZXRab29tIiwiZ2V0Wm9vbSIsImZpdEJvdW5kcyIsInJlbW92ZU1hcmtlciIsImxvZyIsIiRtYXJrZXJzIiwic2V0TWFwIiwiYWRkV2F5Iiwic3RhcnQiLCJlbmQiLCJzdGVwIiwicm91dGUiLCJkaXJlY3Rpb24iLCJEaXJlY3Rpb25zU2VydmljZSIsInJlZ2lvbiIsIndheSIsIkRpcmVjdGlvbnNSZW5kZXJlciIsInBhbmVsIiwiZ2V0RWxlbWVudEJ5SWQiLCJwcm92aWRlVHJpcEFsdGVybmF0aXZlcyIsImlubmVySFRNTCIsIndheXBvaW50cyIsImkiLCJzdG9wb3ZlciIsInJlcXVlc3QiLCJvcmlnaW4iLCJkZXN0aW5hdGlvbiIsInRyYXZlbE1vZGUiLCJEaXJlY3Rpb25zVHJhdmVsTW9kZSIsIkRSSVZJTkciLCJyZXNwb25zZSIsIkRpcmVjdGlvbnNTdGF0dXMiLCJzZXREaXJlY3Rpb25zIl0sInNvdXJjZVJvb3QiOiIifQ==