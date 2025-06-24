=== Themepaste Secure Admin ===
Contributors: jewelmajumder, bakulsinha
Tags: secure wordpress admin, protect wordpress admin, themepaste secure admin, wordpress site security, wordpress wp-admin plugin
Requires at least: 3.7
Tested up to: 6.7
Stable tag: 1.1
License: GPL2 or Later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Themepaste secure admin protects your wp-admin and you can change wp-admin URLs yourself, check login attempts manage users roles.

== Description ==

Themepaste secure admin protects your wp-admin and you would be able to change wp-admin URLs, check login attempts, email activation verification during login, custom layout of login form, set your logo in login form, set login only by email or userid/email, customize email template of login activation, set login captha normal captha or google captha, blocked specific users, allowled only specific users, manage users roles.

Major features in Themepaste Secure Admin include:

* Custom Layout of WP-Login, upload your logo in login form change color, background color, button, input field etc.
* Custom URL of wp-admin. ex(http://example.com/wp-admin to http://example.com/{your-text}).
* Captcha during login google captcha and custom captcha.
* Email activation during login and email template customization.
* Login attempts, Logs of login attempts, blocked ip options, allowled ip options.
* User roles manager, adding new role, adding new capability, manage roles, manage capabilities.

PS: 24/7 [Supports](https://themepaste.com/support) and [Documentation](https://themepaste.com/documentation)


== Installation ==

e.g.

1. Upload the plugin files to the `/wp-content/plugins/themepaste-secure-admin` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Plugin Name screen to configure the plugin
4. Goto settings put your licence and verify the licence.


== Frequently Asked Questions ==

= How to set custom url? =

Goto Themepaste Secure Admin->Custom URL.
* change 'wp-login.php' to for example 'myadminlogin'
* change 'wp-login.php?action=activation' to for example 'loginactivation'
* change 'wp-login.php?action=register' to for example 'register'
* change 'wp-login.php?action=lostpassword' to for example 'forgotpassword'
* change 'wp-login.php?action=logout' to for example 'logout'
* you can define your own custom paths for each URL above
* set successfull login and logout redirect URLs

= How to customized login form? =

Goto Themepaste Secure Admin->Custom Layout.
* you can set logo by uploading.
* change color of background, text, border etc as you want.
* you can reset to get default layout.

= How to work with email activation? =

Goto Themepaste Secure Admin->Email Activation.
* you can set options 'Admin Login by Email Only?' for login with email and password but not allowled userid/username.
* you can set options 'Email Varification to Admin Login?' for login with activation code which will be sent to email.
* you can change the email texts from the given fields.

= How to work with email activation? =

Goto Themepaste Secure Admin->Captcha Activation.
* There are two type of captcha Custom Captcha and Google Captcha
* You can set custom captcha by activating view option
* For google captcha you must use the google public key and secret kay from google also you can change the layout.

= How to work with login attempts? =

Goto Themepaste Secure Admin->Login Attempts.
* If checked 'Enable to Send Email' an email will be sent to user if the user is exists and limited login attempts.
* 'Max attempts time' how many time users can try to login without IP blocked.
* 'IP/User Block Duration' time for IP blocked after this time the IP will be unblocked autometically but if you leave this field with blank/empty then then Time Duration count as life time.
* You can add manually IP as blocked or allowled only.

= How to work with role manager? =

Goto Themepaste Secure Admin->User Role Manager.
* You can add/edit/remove user roles, role capabilities


== Screenshots ==

1. Custom URLs configuration page in Themepaste Secure Admin->Custom URL tab.
2. Custom layout configuration page in Themepaste Secure Admin->Custom Layout tab.
3. Email activation configuration page in Themepaste Secure Admin->Email Activation tab.
3. Captcha activation configuration page in Themepaste Secure Admin->Captcha Activation tab.
4. Login attempts configuration page in Themepaste Secure Admin->Login Attempts tab.
5. User role manager configuration page in Themepaste Secure Admin->User Role Manager tab.

== Changelog ==

= 1.0 =
* Initial release.

= 1.1 =
* Tested up to WordPress 6.7
* Bug fixes and performance improvements
