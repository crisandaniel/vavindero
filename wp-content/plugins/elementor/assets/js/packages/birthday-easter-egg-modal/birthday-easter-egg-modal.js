/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/react-dom/client.js":
/*!******************************************!*\
  !*** ./node_modules/react-dom/client.js ***!
  \******************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {



var m = __webpack_require__(/*! react-dom */ "react-dom");
if (false) // removed by dead control flow
{} else {
  var i = m.__SECRET_INTERNALS_DO_NOT_USE_OR_YOU_WILL_BE_FIRED;
  exports.createRoot = function(c, o) {
    i.usingClientEntryPoint = true;
    try {
      return m.createRoot(c, o);
    } finally {
      i.usingClientEntryPoint = false;
    }
  };
  exports.hydrateRoot = function(c, h, o) {
    i.usingClientEntryPoint = true;
    try {
      return m.hydrateRoot(c, h, o);
    } finally {
      i.usingClientEntryPoint = false;
    }
  };
}


/***/ }),

/***/ "./packages/apps/birthday-easter-egg-modal/src/app.tsx":
/*!*************************************************************!*\
  !*** ./packages/apps/birthday-easter-egg-modal/src/app.tsx ***!
  \*************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   App: function() { return /* binding */ App; }
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _elementor_ui__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @elementor/ui */ "@elementor/ui");
/* harmony import */ var _elementor_ui__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_elementor_ui__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_app_content__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/app-content */ "./packages/apps/birthday-easter-egg-modal/src/components/app-content.tsx");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./constants */ "./packages/apps/birthday-easter-egg-modal/src/constants.ts");
/* harmony import */ var _types__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./types */ "./packages/apps/birthday-easter-egg-modal/src/types.ts");






function App({
  container
}) {
  const [isMounted, setIsMounted] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const handleOpen = () => setIsMounted(true);
    document.addEventListener(_constants__WEBPACK_IMPORTED_MODULE_3__.TRIGGER_EVENT, handleOpen);
    return () => {
      document.removeEventListener(_constants__WEBPACK_IMPORTED_MODULE_3__.TRIGGER_EVENT, handleOpen);
    };
  }, []);
  const handleDisposed = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => setIsMounted(false), []);
  const config = (0,_types__WEBPACK_IMPORTED_MODULE_4__.getModalConfig)();
  if (!config || !isMounted) {
    return null;
  }
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_ui__WEBPACK_IMPORTED_MODULE_1__.DirectionProvider, {
    rtl: document.dir === 'rtl'
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_ui__WEBPACK_IMPORTED_MODULE_1__.LocalizationProvider, null, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_ui__WEBPACK_IMPORTED_MODULE_1__.ThemeProvider, {
    colorScheme: "light",
    palette: "unstable"
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_components_app_content__WEBPACK_IMPORTED_MODULE_2__.AppContent, {
    onClose: handleDisposed,
    container: container,
    config: config
  }))));
}

/***/ }),

/***/ "./packages/apps/birthday-easter-egg-modal/src/components/app-content.tsx":
/*!********************************************************************************!*\
  !*** ./packages/apps/birthday-easter-egg-modal/src/components/app-content.tsx ***!
  \********************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AppContent: function() { return /* binding */ AppContent; }
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @elementor/editor-modal-shell */ "@elementor/editor-modal-shell");
/* harmony import */ var _elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _elementor_ui__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @elementor/ui */ "@elementor/ui");
/* harmony import */ var _elementor_ui__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_elementor_ui__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../constants */ "./packages/apps/birthday-easter-egg-modal/src/constants.ts");
/* harmony import */ var _birthday_background_lottie__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./birthday-background-lottie */ "./packages/apps/birthday-easter-egg-modal/src/components/birthday-background-lottie.tsx");






function AppContent({
  onClose,
  container,
  config
}) {
  const [isLottieCompleted, setIsLottieCompleted] = (0,react__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  return isLottieCompleted ? /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1__.ModalShell, {
    onClose: onClose,
    container: container,
    revealDuration: 0,
    sx: {
      display: 'flex',
      flexDirection: 'row',
      width: '900px',
      height: '432px',
      position: 'relative',
      '&::after': {
        content: '""',
        position: 'fixed',
        inset: 0,
        backgroundColor: 'common.white',
        zIndex: 10,
        pointerEvents: 'none',
        animation: 'e-modal-white-wash 700ms ease 250ms forwards'
      },
      '@keyframes e-modal-white-wash': {
        from: {
          opacity: 1
        },
        to: {
          opacity: 0
        }
      }
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_ui__WEBPACK_IMPORTED_MODULE_2__.Box, {
    sx: {
      height: '100%',
      aspectRatio: '1',
      backgroundImage: `url(${config.hero})`,
      backgroundRepeat: 'no-repeat',
      backgroundPosition: 'center',
      backgroundSize: 'cover'
    }
  }), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(ContentPanel, {
    config: config
  })) : /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_birthday_background_lottie__WEBPACK_IMPORTED_MODULE_4__.BirthdayBackgroundLottie, {
    lottieData: config.lottie,
    onLottieComplete: () => setIsLottieCompleted(true)
  });
}
function ContentPanel({
  config
}) {
  const {
    close
  } = (0,_elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1__.useModalShell)();
  const hidePromotion = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => {
    const promotionElement = document.querySelector('#elementor-panel-category-v4-elements .elementor-element-wrapper:has([data-library-element-type="e-birthday-easter-egg"])');
    promotionElement?.remove();
  }, []);
  const setCtaVisited = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => {
    const ajax = window.elementorCommon?.ajax;
    try {
      ajax?.addRequest(_constants__WEBPACK_IMPORTED_MODULE_3__.SET_CTA_VISITED_ACTION, {
        data: {
          visited: true
        }
      }, true);
      return true;
    } catch {
      return false;
    }
  }, []);
  const onCtaVisit = (0,react__WEBPACK_IMPORTED_MODULE_0__.useCallback)(() => {
    if (setCtaVisited()) {
      hidePromotion();
    }
    close();
  }, [hidePromotion, close, setCtaVisited]);
  return /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_ui__WEBPACK_IMPORTED_MODULE_2__.Stack, {
    sx: {
      height: '100%',
      py: 5,
      paddingInlineStart: 4,
      paddingInlineEnd: 5,
      bgcolor: 'background.paper'
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_ui__WEBPACK_IMPORTED_MODULE_2__.Stack, {
    gap: 2.5,
    flexGrow: 2,
    sx: {
      justifyContent: 'center'
    }
  }, /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_ui__WEBPACK_IMPORTED_MODULE_2__.Typography, {
    variant: "h4",
    color: "text.secondary"
  }, config.header), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_ui__WEBPACK_IMPORTED_MODULE_2__.Typography, {
    variant: "body1",
    color: "text.primary"
  }, config.content)), /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_ui__WEBPACK_IMPORTED_MODULE_2__.Button, {
    variant: "contained",
    color: "primary",
    size: "large",
    href: config.cta.url,
    target: "_blank",
    rel: "noopener noreferrer",
    onClick: onCtaVisit,
    sx: {
      alignSelf: 'flex-end'
    }
  }, config.cta.label));
}

/***/ }),

/***/ "./packages/apps/birthday-easter-egg-modal/src/components/birthday-background-lottie.tsx":
/*!***********************************************************************************************!*\
  !*** ./packages/apps/birthday-easter-egg-modal/src/components/birthday-background-lottie.tsx ***!
  \***********************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   BirthdayBackgroundLottie: function() { return /* binding */ BirthdayBackgroundLottie; }
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @elementor/editor-modal-shell */ "@elementor/editor-modal-shell");
/* harmony import */ var _elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1__);



function BirthdayBackgroundLottie({
  lottieData,
  onLottieComplete
}) {
  (0,react__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!lottieData) {
      onLottieComplete();
    }
  }, [lottieData, onLottieComplete]);
  return lottieData && /*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1__.BackgroundLottie, {
    onComplete: onLottieComplete,
    animationData: lottieData,
    loop: false,
    zIndex: _elementor_editor_modal_shell__WEBPACK_IMPORTED_MODULE_1__.MODAL_Z_INDEX + 1
  });
}

/***/ }),

/***/ "./packages/apps/birthday-easter-egg-modal/src/constants.ts":
/*!******************************************************************!*\
  !*** ./packages/apps/birthday-easter-egg-modal/src/constants.ts ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SET_CTA_VISITED_ACTION: function() { return /* binding */ SET_CTA_VISITED_ACTION; },
/* harmony export */   TRIGGER_EVENT: function() { return /* binding */ TRIGGER_EVENT; }
/* harmony export */ });
const TRIGGER_EVENT = 'birthday-easter-egg:open';
const SET_CTA_VISITED_ACTION = 'birthday_easter_egg_set_cta_visited';

/***/ }),

/***/ "./packages/apps/birthday-easter-egg-modal/src/init.tsx":
/*!**************************************************************!*\
  !*** ./packages/apps/birthday-easter-egg-modal/src/init.tsx ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   init: function() { return /* binding */ init; }
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_dom_client__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-dom/client */ "./node_modules/react-dom/client.js");
/* harmony import */ var _app__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./app */ "./packages/apps/birthday-easter-egg-modal/src/app.tsx");



const ROOT_ELEMENT_ID = 'e-birthday-easter-egg-root';
function init() {
  const rootElement = getOrCreateRootElement();
  (0,react_dom_client__WEBPACK_IMPORTED_MODULE_1__.createRoot)(rootElement).render(/*#__PURE__*/react__WEBPACK_IMPORTED_MODULE_0__.createElement(_app__WEBPACK_IMPORTED_MODULE_2__.App, {
    container: rootElement.ownerDocument.body
  }));
}
function getOrCreateRootElement() {
  const existing = document.getElementById(ROOT_ELEMENT_ID);
  if (existing) {
    return existing;
  }
  const el = document.createElement('div');
  el.id = ROOT_ELEMENT_ID;
  document.body.appendChild(el);
  return el;
}

/***/ }),

/***/ "./packages/apps/birthday-easter-egg-modal/src/types.ts":
/*!**************************************************************!*\
  !*** ./packages/apps/birthday-easter-egg-modal/src/types.ts ***!
  \**************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getModalConfig: function() { return /* binding */ getModalConfig; }
/* harmony export */ });
function getModalConfig() {
  return window.elementor?.config?.birthdayEasterEggModal ?? null;
}

/***/ }),

/***/ "@elementor/editor-modal-shell":
/*!***************************************************!*\
  !*** external ["elementorV2","editorModalShell"] ***!
  \***************************************************/
/***/ (function(module) {

module.exports = window["elementorV2"]["editorModalShell"];

/***/ }),

/***/ "@elementor/ui":
/*!*************************************!*\
  !*** external ["elementorV2","ui"] ***!
  \*************************************/
/***/ (function(module) {

module.exports = window["elementorV2"]["ui"];

/***/ }),

/***/ "react":
/*!**************************!*\
  !*** external ["React"] ***!
  \**************************/
/***/ (function(module) {

module.exports = window["React"];

/***/ }),

/***/ "react-dom":
/*!*****************************!*\
  !*** external ["ReactDOM"] ***!
  \*****************************/
/***/ (function(module) {

module.exports = window["ReactDOM"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
!function() {
/*!**************************************************************!*\
  !*** ./packages/apps/birthday-easter-egg-modal/src/index.ts ***!
  \**************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   App: function() { return /* reexport safe */ _app__WEBPACK_IMPORTED_MODULE_0__.App; },
/* harmony export */   TRIGGER_EVENT: function() { return /* reexport safe */ _constants__WEBPACK_IMPORTED_MODULE_2__.TRIGGER_EVENT; },
/* harmony export */   init: function() { return /* reexport safe */ _init__WEBPACK_IMPORTED_MODULE_1__.init; }
/* harmony export */ });
/* harmony import */ var _app__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./app */ "./packages/apps/birthday-easter-egg-modal/src/app.tsx");
/* harmony import */ var _init__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./init */ "./packages/apps/birthday-easter-egg-modal/src/init.tsx");
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./constants */ "./packages/apps/birthday-easter-egg-modal/src/constants.ts");



}();
(window.elementorV2 = window.elementorV2 || {}).birthdayEasterEggModal = __webpack_exports__;
/******/ })()
;
window.elementorV2.birthdayEasterEggModal?.init?.();
//# sourceMappingURL=birthday-easter-egg-modal.js.map