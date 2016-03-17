
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
      wpAddToHomescreen.stats.registerPrompted()
      .then(this.storage.setItem('prompted', true));

      event.userChoice.then(function (choice) {
        if (choice.outcome === 'accepted') {
          wpAddToHomescreen.stats.registerInstallation()
          .then(this.storage.setItem('installed', true));
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
    registerPrompted: function () {
      return wpAddToHomescreen.storage.getItem('prompted')
      .then(function (isPrompted) {
        if (!isPrompted) {
          this.sendEvent('prompted');
        }
        return Promise.resolve();
      }.bind(this));
    },

    registerInstallation: function () {
      return wpAddToHomescreen.storage.getItem('installed')
      .then(function (isInstalled) {
        if (!isInstalled) {
          this.sendEvent('installed');
        }
        return Promise.resolve();
      }.bind(this));
    },

    registerHowToShown: function () {
      wpAddToHomescreen.storage.getItem('how-to-was-displayed')
      .then(function (instructionsDisplayed) {
        if (!instructionsDisplayed) {
          this.sendEvent('how-to-was-displayed');
          wpAddToHomescreen.storage.setItem('how-to-was-displayed', true);
        }
      }.bind(this));
    },

    sendEvent: function (metric, data) {
      data = data || {};
      data.metric = metric;
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
    element: null,

    body: null,

    init: function (overlayContainer, bodyElement) {
      this.show = this.show.bind(this);
      this.hide = this.hide.bind(this);
      this.body = bodyElement;
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
      var div = document.createElement('DIV');
      div.id = 'wp-add-to-homescreen-overlay';

      var invitationParagraph = document.createElement('P');
      invitationParagraph.classList.add('invitation');
      invitationParagraph.textContent = setup.invitationText;

      var explanationImage = document.createElement('IMG');
      explanationImage.class = 'explanation';
      explanationImage.src = this.getExplanationImage(platform);

      var hr = document.createElement('hr');
      hr.class = 'separator';

      var instructionsSection = document.createElement('SECTION');
      instructionsSection.classList.add('instructions');
      instructionsSection.appendChild(this.getInstructions(browser));

      var dismissButton = document.createElement('BUTTON');
      dismissButton.classList.add('dismiss');
      dismissButton.textContent = setup.dismissText;
      dismissButton.onclick = this.hide;

      div.appendChild(instructionsSection);
      div.appendChild(explanationImage);
      div.appendChild(invitationParagraph);
      div.appendChild(dismissButton);

      return div;
    },

    show: function () {
      this.element.classList.add('shown');
      this.body.classList.add('noscroll');
      wpAddToHomescreen.stats.registerHowToShown();
    },

    hide: function () {
      this.element.classList.remove('shown');
      this.body.classList.remove('noscroll');
    },

    getExplanationImage: function (platform) {
      return setup.libUrl + 'imgs/' + platform + '.png';
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
        return buffer;
      },
      chrome: function (setup) {
        var buffer = document.createDocumentFragment();
        var p = document.createElement('P');
        p.innerHTML = '<strong>Tap on menu</strong>, then tap on <q>Add to ' +
          'Home Screen</q>.';
        buffer.appendChild(p);
        return buffer;
      },
      opera: function (setup) {
        var buffer = document.createDocumentFragment();
        var p = document.createElement('P');
        p.innerHTML = '<strong>Tap on the + icon</strong>, then tap on <q>Add to ' +
          'Home Screen</q>.';
        buffer.appendChild(p);
        return buffer;
      },
      safari: function (setup) {
        var buffer = document.createDocumentFragment();
        var p = document.createElement('P');
        p.innerHTML = '<strong>Tap on the share icon</strong>, then tap on <q>Add to ' +
          'Home Screen</q>.';
        buffer.appendChild(p);
        return buffer;
      }
    }
  };
})(window, wpAddToHomescreenSetup, isMobile, localforage);
