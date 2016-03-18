(function (globals) {
  'use strict';

  var window = globals;
  var document = globals.document;
  var $ = window.jQuery;

  function showMediaLibrary() {
    var media = globals.wp.media({ multiple: false });
    media.on('select', updateIconUrl.bind(media));
    media.open();
  }

  function updateIconUrl() {
    var iconPreview = document.getElementById('icon-preview');
    var iconUrl = document.getElementById('icon-url');
    var iconMime = document.getElementById('icon-mime');
    var selection = this.state().get('selection').first();
    iconUrl.value = selection.get('url');
    iconMime.value = selection.get('mime');
    iconPreview.src = iconUrl.value;
  }

  window.addEventListener('DOMContentLoaded', function () {
    var iconButton = document.getElementById('select-icon-button');
    iconButton.onclick = function (event) {
      event.preventDefault();
      showMediaLibrary();
    };

    $('.color-picker').wpColorPicker();
  });

})(window);
