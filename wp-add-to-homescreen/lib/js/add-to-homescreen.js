
(function(globals, setup, isMobile, localforage){
  'use strict';

  var PRIVATE_NAME = '__wp-add-to-homescreen';

  var window = globals;
  var navigator = window.navigator;
  var document = window.document;

  var wpAddToHomescreen = globals.wpAddToHomescreen = {
    storage: localforage.createInstance({ name: PRIVATE_NAME }),

    init: function (overlayContainer, buttonContainer) {
      if (this.isPlatformSupported()) {
        this.overlay.init(overlayContainer, document.body);
        this.installAddToHomescreenButton(buttonContainer);
        window.addEventListener('beforeinstallprompt', this._onBeforeInstall.bind(this));
      }
    },

    installAddToHomescreenButton: function (container) {
      var button = document.createElement('BUTTON');
      button.id = 'wp-add-to-homescreen-button';
      button.onclick = wpAddToHomescreen.overlay.show;
      button.style.backgroundImage = 'url(' + setup.add2homeIconUrl + ')';
      container.appendChild(button);
      window.addEventListener('scroll', function () {
        if (window.scrollY > 0) {
          button.classList.add('hidden');
        }
        else {
          button.classList.remove('hidden');
        }
      });
      return button;
    },


    _onBeforeInstall: function (event) {
      wpAddToHomescreen.stats.logOnce('prompted');
      event.userChoice.then(function (choice) {
        if (choice.outcome === 'accepted') {
          wpAddToHomescreen.stats.logOnce('installed');
        }
      }.bind(this));
    },

    isPlatformSupported: function () {
      return isMobile.any && this.detectBrowser();
    },

    detectBrowser: function () {
      if (/Gecko\/[\d\.]+ Firefox\/[\d\.]+/.test(navigator.userAgent)) {
        return 'fennec';
      }
      else if (/OPR\/[\d\.]+/.test(navigator.userAgent)) {
        return 'opera';
      }
      else if (/Chrome\/[\d\.]+/.test(navigator.userAgent)) {
        return 'chrome';
      }
      else if (/AppleWebKit\/[\d\.]+/.test(navigator.userAgent)) {
        return 'safari';
      }
      else {
        return null;
      }
    },

    detectPlatform: function () {
      return 'android';
    }
  };

  wpAddToHomescreen.stats = {

    logOnce: function (event, data) {
      var lock = 'done-log-once-' + event;
      return wpAddToHomescreen.storage.getItem(lock)
      .then(function (isDone) {
        if (!isDone) {
          this.sendEvent(event, data);
        }
        return wpAddToHomescreen.storage.setItem(lock, true);
      }.bind(this));
    },

    sendEvent: function (event, data) {
      data = data || {};
      data.event = event;
      var encodedData = (function () {
        var form = new FormData();
        Object.keys(data).forEach(function (key) { form.append(key, data[key]); });
        return form;
      })();
      var xhr = new XMLHttpRequest();
      xhr.open('POST', setup.statsEndPoint, true);
      xhr.send(encodedData);
    }
  };

  wpAddToHomescreen.overlay = {
    container: null,

    element: null,

    body: null,

    init: function (overlayContainer, bodyElement) {
      this.show = this.show.bind(this);
      this.hide = this.hide.bind(this);
      this.body = bodyElement;
      this.container = overlayContainer;
      this.element = this.installOverlay(overlayContainer);
    },

    installOverlay: function (container) {
      var browser = wpAddToHomescreen.detectBrowser();
      var platform = wpAddToHomescreen.detectPlatform();
      var overlay = this.buildOverlay(browser, platform);
      container.appendChild(overlay);
      return overlay;
    },

    buildOverlay: function (browser, platform) {
      var themeColor = document.querySelector('meta[name=theme-color]').getAttribute('content');
      var contrastedColor = this.getContrastedColor(themeColor);

      var overlay = document.createElement('SECTION');
      overlay.id = 'wp-add-to-homescreen-overlay';
      overlay.style.backgroundColor = themeColor;
      overlay.style.color = contrastedColor;

      var wrapper = document.createElement('DIV');

      var title = document.createElement('H2');
      title.textContent = setup.title;

      var instructions = this.getInstructions(browser);
      var explanation = instructions[0];
      var arrowHint = { vertical: instructions[1], horizontal: instructions[2] };

      var arrow = document.createElement('DIV');
      arrow.classList.add('arrow');
      arrow.classList.add(arrowHint.vertical);
      arrow.classList.add(arrowHint.horizontal);
      arrow.textContent = "▲";
      arrow.style.color = contrastedColor;

      var instructionsSection = document.createElement('SECTION');
      instructionsSection.classList.add('instructions');
      instructionsSection.appendChild(explanation);

      var dismissButton = document.createElement('BUTTON');
      dismissButton.classList.add('dismiss');
      dismissButton.textContent = setup.dismissText;
      dismissButton.style.color = themeColor;
      dismissButton.style.backgroundColor = contrastedColor;
      dismissButton.onclick = this.hide;

      wrapper.appendChild(arrow);
      wrapper.appendChild(title);
      wrapper.appendChild(instructionsSection);
      wrapper.appendChild(dismissButton);
      overlay.appendChild(wrapper);

      return overlay;
    },

    // http://stackoverflow.com/questions/3942878/how-to-decide-font-color-in-white-or-black-depending-on-background-color#answer-3943023
    getContrastedColor: function (color) {
      color = this.normalizeColor(color);
      var red = parseInt(color.substr(1,2), 16) / 255.0;
      var green = parseInt(color.substr(3,2), 16) / 255.0;
      var blue = parseInt(color.substr(5,2), 16) / 255.0;
      var colors = [red, green, blue].map(function (color) {
        return (color <= 0.03928) ? (color / 12.92) : Math.pow((color + 0.055)/1.055, 2.4);
      });
      var luminance = colors[0] * 0.2126 + colors[1] * 0.7152 + colors[2] * 0.0722;
      return (luminance > 0.179) ? '#000000' : '#FFFFFF';
    },

    normalizeColor: function (color) {
      if (color.length === 4) {
        color = ['#', color[1], color[1], color[2], color[2], color[3], color[3]].join('');
      }
      return color;
    },

    show: function () {
      this.element.classList.add('shown');
      this.body.classList.add('noscroll');
      this.preventScroll();
      wpAddToHomescreen.stats.logOnce('instructions-shown');
    },

    hide: function () {
      this.element.classList.remove('shown');
      this.body.classList.remove('noscroll');
      this.restoreScroll();
    },

    getInstructions: function (browser) {
      return this.instructionsByBrowser[browser].call(this, setup);
    },

    instructionsByBrowser: {
      fennec: function (setup) {
        var buffer = document.createDocumentFragment();
        var p = document.createElement('P');
        p.innerHTML = '<strong>Long press</strong> the navigation bar and tap ' +
          'on <q>Add to Home Screen</q>.';
        buffer.appendChild(p);
        return [buffer, 'top', 'right'];
      },
      chrome: function (setup) {
        var buffer = document.createDocumentFragment();
        var p = document.createElement('P');
        p.innerHTML = '<strong>Tap on &#8942;</strong> and then tap on <q>Add to ' +
          'Home screen</q>.';
        buffer.appendChild(p);
        return [buffer, 'top', 'right'];
      },
      opera: function (setup) {
        var buffer = document.createDocumentFragment();
        var p = document.createElement('P');
        p.innerHTML = '<strong>Tap on the + icon</strong>, then tap on <q>Add to ' +
          'home screen</q>.';
        buffer.appendChild(p);
        return [buffer, 'top', 'left'];
      },
      safari: function (setup) {
        var buffer = document.createDocumentFragment();
        var p = document.createElement('P');
        p.innerHTML = '<strong>Tap on the share icon</strong>, then tap on <q>Add to ' +
          'Home Screen</q>.';
        buffer.appendChild(p);
        return [buffer, 'bottom', 'center'];
      }
    },

    preventScroll: function () {
      ['scroll', 'touchmove', 'mousewheel'].forEach(function (event) {
        this.container.addEventListener(event, this.noScroll, true);
      }.bind(this));
    },

    restoreScroll: function () {
      ['scroll', 'touchmove', 'mousewheel'].forEach(function (event) {
        this.container.removeEventListener(event, this.noScroll, true);
      }.bind(this));
    },

    noScroll: function (evt) {
      evt.preventDefault();
    }
  };
})(window, wpAddToHomescreenSetup, isMobile, localforage);
