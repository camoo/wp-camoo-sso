=== CAMOO SSO ===
Contributors: camoo
Tags: Camoo.Hosting, CAMOO SSO Integration, Managed Hosting with SSO, Hébergement Web avec SSO
Requires at least: 5.6
Tested up to: 6.1
Requires PHP: 7.4
Stable tag: 1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Camoo.Hosting Single sign On for WordPress websites.

== Description ==
Camoo.Hosting Single sign On for Managed WordPress sites
This plugin allows signing in Camoo.Hosting users via SSO to your managed WordPress without having to remember any password of your website.
Please note that the user information and role mappings are updated each time the user logs in via SSO. If you do not want to sync the roles from your existing system to WordPress, you can disable the functionality via the settings page.


== Installation ==

1. Upload the downloaded plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In the administration dashboard, go to "Settings" > "Single Sign On" and configure the CAMOO-SOO settings.

= Features =
* SSO for managed CAMOO.Hosting managed WordPress

== Frequently Asked Questions ==

= What is CAMOO SSO? =
CAMOO SSO is an Automatic SSO Integration for WordPress sites hosted by Camoo.Hosting

= Do I need to do something after the plugin has been activated? =
No, You don't! After you purchase a WordPress Hosting order, our system will add the CAMOO-SSO plugin automatically in your Managed WordPress installation.

= How can I purchase a WordPress Hosting order? =
All you need is just to [visit our hosting packages](https://www.camoo.hosting/wordpress-hosting).

== Screenshots ==
1. Managed WordPress Login page with SSO button
2. Go to settings for Single Sign On
3. Apply CAMOO Single Sign On Settings option

== Upgrade Notice ==
N/A

== Changelog ==

= 1.1: June 20, 2022 =
* Tweak: remove `camoo_sso` permission from administrator roles on deactivate/uninstall
* Fix: login page on mobile

= 1.0: June 11, 2022 =
* Start plugin
