=== Add To Home Screen ===
Contributors: delapuente, mozillawebapps
Tags: progressive, web, applications, progressive web applications, homescreen, banner, home, screen
Requires at least: 3.5
Tested up to: 4.4.1
Stable tag: 0.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Make your WordPress sites to appear in the home screen of mobile devices.

== Description ==
Add To Home Screen enables your WordPress installation to appear in the mobile home screen of your readers, among their favorite applications. This is not just a bookmark to your site, Add To Home Screen is the first step for your site to leverage a complete app-like experience.

Add To Home Screen plugin uses the [W3C Web App Manifest](https://www.w3.org/TR/appmanifest/) and [Service Worker API](https://slightlyoff.github.io/ServiceWorker/spec/service_worker/index.html) in addition to some browser specific technologies to place your WordPress in the same location of native applications.

Complete native experience by offering [push notifications](https://es.wordpress.org/plugins/web-push/), [instant loading](https://es.wordpress.org/plugins/web-push/) and [offline content](https://es.wordpress.org/plugins/offline-content/).

== Installation ==
1. Install the plugin from the WordPress.org plugin directory
2. Activate the plugin

Alternatively,

1. Clone or download the project repository
2. [Install bower](http://bower.io/) and [composer](https://getcomposer.org/), enter the `wp-add-to-homescreen` folder of the repository and run `composer install && bower install`.
3. Copy (or symlink) the `wp-add-to-homescreen` folder inside your WordPress installation plugins directory.
4. Enable the plugin from the admin panel.

== Frequently Asked Questions ==
= What platforms support Add To Home Screen feature? =
Almost all browsers support adding bookmarks to the home screen but complete Add To Home Screen feature is currently supported by Chrome and Opera. [Add To Home Screen banner](https://developers.google.com/web/updates/2015/03/increasing-engagement-with-app-install-banners-in-chrome-for-android) is only supported by Chrome right now.

== Change log ==

= 0.3.1 =
Updated dependencies include performance improvements and error fixing.

= 0.3.0 =
Relying on composer's autoload to manage plugin dependencies.

= 0.2.0 =
Fixed a case typo in a route which caused the plugin to not work.

= 0.1.0 =
Initial release
