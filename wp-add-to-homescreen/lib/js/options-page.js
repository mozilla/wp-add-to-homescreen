(function (globals) {
  'use strict';

  var window = globals;
  var document = globals.document;

  function showMediaLibrary() {
    var media = globals.wp.media({ multiple: false });
    media.on('select', updateIconUrl.bind(media));
    media.open();
  }

  function updateIconUrl() {
    var iconPreview = document.getElementById('icon-preview');
    var iconUrl = document.getElementById('icon-url');
    iconUrl.value = this.state().get('selection').first().toJSON().url;
    iconPreview.src = iconUrl.value;
  }

  window.addEventListener('DOMContentLoaded', function () {
    var iconButton = document.getElementById('select-icon-button');
    iconButton.onclick = function (event) {
      event.preventDefault();
      showMediaLibrary();
    };
  });
})(window);
