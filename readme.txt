=== CAMOO SSO ===
Contributors: camoo
Tags: Camoo.Hosting, CAMOO SSO Integration, Managed Hosting with SSO, Hébergement Web avec SSO
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Camoo.Hosting Single sign On for WordPress websites.

== Description ==
Camoo.Hosting Single sign On for Managed WordPress sites,
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
3. Apply CAMOO Single Sign On a Settings option

== Upgrade Notice ==
N/A

== Changelog ==

= 1.5.4: Apr 08, 2024 =
* Tweak: Settings link added on plugin page

= 1.5.3: Mar 28, 2024 =
* Tweak: General code improvements

= 1.5.2: Nov 12, 2023 =
* Tweak: flush rewrite rule now only on plugin activation or deactivation
* Tweak: Better handle login_form_login check when password connection is disabled
* Tweak: TokenService unnecessary token injection removed
* Tweak: Helper improved by returning the singleton instance of the class.
* Tweak: General code improvements

= 1.5.1: Jul 01, 2023 =
* Tweak: replace deprecated InMemory::empty
* Tweak: adjust variable names
* Tweak: vendor packages updated

= 1.5.0: Nov 03, 2022 =
* Tweak: using is_login function
* Tweak: adjust variable names

= 1.4: July 25, 2022 =
* Tweak: Setting for disabling username and password login added
* Tweak: css ajustement on admin sso setting

= 1.4: July 21, 2022 =
* Tweak: Setting for camoo hosting login user added
* Tweak: cleanup unused settings

= 1.3: July 13, 2022 =
* Tweak: cleanup and fix html tag closure

= 1.2: June 23, 2022 =
* Tweak: check internal domain audience only with https

= 1.1: June 20, 2022 =
* Tweak: remove `camoo_sso` permission from administrator roles on deactivate/uninstall
* Tweak: Find user by email instead of by login
* Fix: login page on mobile

= 1.0: June 11, 2022 =
* Start plugin
