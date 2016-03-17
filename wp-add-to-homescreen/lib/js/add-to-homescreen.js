
(function(globals, setup, isMobile, localforage){
  'use strict';

  var PRIVATE_NAME = '__wp-add-to-homescreen';

  var window = globals;
  var navigator = window.navigator;
  var document = window.document;

  var wpAddToHomescreen = globals.wpAddToHomescreen = {
    storage: localforage.createInstance({ name: PRIVATE_NAME }),

    stats: {
      sendEvent: function (metric, data) {
        data = data || {};
        data.metric = metric;
        var encodedData = (function () {
          var form = new FormData();
          Object.keys(data).forEach(key => form.append(key, data[key]));
          return form;
        })();
        var statsRequest = new Request(setup.statsEndPoint, { method: 'POST', body: encodedData });
        fetch(statsRequest);
      }
    },

    overlay: {
      element: null,

      show: function () {
        wpAddToHomescreen.overlay.element.classList.add('shown');
        document.body.classList.add('noscroll');
        this._registerHowToWasDisplayed();
      },

      hide: function () {
        wpAddToHomescreen.overlay.element.classList.remove('shown');
        document.body.classList.remove('noscroll');
      },

      _registerHowToWasDisplayed: function () {
        wpAddToHomescreen.storage.getItem('how-to-was-displayed')
        .then(instructionsDisplayed => {
          if (!instructionsDisplayed) {
            wpAddToHomescreen.stats.sendEvent('how-to-was-displayed');
            wpAddToHomescreen.storage.setItem('how-to-was-displayed', true);
          }
        });
      }
    },


    init: function (overlayContainer, buttonContainer) {
      if (this.isPlatformSupported()) {
        this.overlay.element = this.installOverlay(overlayContainer);
        this.installAddToHomescreenButton(buttonContainer);
      }
    },

    installAddToHomescreenButton: function (container) {
      var button = document.createElement('BUTTON');
      button.id = 'wp-add-to-homescreen-button';
      button.onclick = wpAddToHomescreen.overlay.show.bind(wpAddToHomescreen.overlay);
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

    installOverlay: function (container) {
      var browser = this.detectBrowser();
      var platform = this.detectPlatform();
      var overlay = this.buildOverlay(browser, platform);
      container.appendChild(overlay);
      return overlay;
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
      dismissButton.onclick = wpAddToHomescreen.overlay.hide.bind(wpAddToHomescreen.overlay);

      div.appendChild(instructionsSection);
      div.appendChild(explanationImage);
      div.appendChild(invitationParagraph);
      div.appendChild(dismissButton);

      return div;
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
