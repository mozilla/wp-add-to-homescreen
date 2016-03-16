(function(globals, wpAddToHomescreen, isMobile){
  'use strict';
  if (isMobile.any) {
    wpAddToHomescreen.init(document.body, document.body);
  }
})(window, wpAddToHomescreen, isMobile);
