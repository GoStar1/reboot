/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/select2.js":
/*!*********************************!*\
  !*** ./resources/js/select2.js ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

$('.select2').select2();
$('.time_range').daterangepicker({
  timePicker: true,
  timePickerIncrement: 30,
  timePicker24Hour: true,
  locale: {
    format: 'MM/DD/YYYY HH:mm'
  }
});
$.fn.indexSelector = function () {
  var $selectors = $(this),
    limit = 300;
  $selectors.each(function () {
    var $select = $(this),
      ids = $select.data("ids") || '',
      itemGetUrl = '/admin/cms/defi/index/selector';
    $select.select2({
      ajax: {
        url: function url() {
          return itemGetUrl;
        },
        dataType: 'json',
        delay: 50,
        data: function data(params) {
          return {
            keyword: params.term,
            // search term
            limit: limit,
            chain: $("#chain").val()
          };
        },
        processResults: function processResults(data, params) {
          console.log("processResults");
          var results = [];

          // parse the results into the format expected by Select2
          // since we are using custom formatting functions we do not need to
          // alter the remote JSON data, except to indicate that infinite
          // scrolling can be used
          params.page = params.page || 1;
          for (var i = 0, l = data.data.length, item; i < l; i++) {
            item = data.data[i];
            results.push(item);
          }
          return {
            results: results
          };
        },
        cache: true
      },
      escapeMarkup: function escapeMarkup(markup) {
        return markup;
      },
      // let our custom formatter work
      minimumInputLength: 0,
      templateResult: function templateResult(repo) {
        if (repo.loading) return repo.text;
        var markup = "<div class='select2-result-repository clearfix'>" + "<div class='select2-result-repository__meta' style='float:left;width:90%;'>" + "<div class='select2-result-repository__title'>" + repo.title + "</div>" + "</div>" + "</div>";
        return markup;
      },
      templateSelection: function templateSelection(repo) {
        return repo.title || repo.text;
      }
    });
    console.log(ids);
    if (ids) {
      var chain = $('#chain').val();
      $.get(itemGetUrl, {
        ids: ids,
        chain: chain
      }, function (res) {
        var optionData = [],
          optionHtml = "";
        $.each(res.data || [], function (i, n) {
          n.name = n.title;
          optionData.push({
            id: n.id,
            title: n.name
          });
          optionHtml += '<option value="' + n.id + '" title="' + n.name + '" selected>' + n.name + '</option>';
        });
        $select.html(optionHtml).data("select2").trigger("selection:update", {
          data: optionData
        });
      });
    }
  });
};
$('.index-selector').indexSelector();

/***/ }),

/***/ 1:
/*!***************************************!*\
  !*** multi ./resources/js/select2.js ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/tao/sources/robot/app/resources/js/select2.js */"./resources/js/select2.js");


/***/ })

/******/ });