=== Admin Safety Guard – WP Security: Limit Login Attempts, IP Blocking, 2FA & reCAPTCHA ===
Contributors: themepaste, habibnote
Tags: admin safety guard, limit login attempts, 2fa, recaptcha, wp security, login security, brute force, ip block, xml-rpc, social login
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.1.5
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Secure your WordPress login with Admin Safety Guard: limit login attempts, 2FA, reCAPTCHA, IP blocking, disable XML-RPC, activity tracking, custom login URL, redirects, and branded login styles.

== Description ==

**Admin Safety Guard** is a complete WordPress security helper focused on securing the login flow and hardening the admin area — without sacrificing usability or performance. It ships with a clean UI, smart defaults, and guardrails against the most common attacks (brute force, credential stuffing, bot logins, and XML-RPC abuse). You also get granular control over the login experience (custom URL, redirects, branding, and more).

[youtube https://www.youtube.com/watch?v=KFNUmTHtODE]

Whether you need to block suspicious IPs, enforce two-factor authentication, or ship a branded login for clients, **Admin Safety Guard** has you covered.

### 🌟 Admin Safety Guard Pro

**[Admin Safety Guard Pro](https://themepaste.com/product/admin-safety-guard-pro)** takes your security and customization to the next level. It strengthens defenses against unauthorized access, brute-force attacks, and data risks while giving you deeper control over how users log in and interact with your admin area. The Pro version also adds flexible design tools and smart automations — a complete solution for both **security** and **convenience**.

---

### 👥 Who Should Use Admin Safety Guard?

**Admin Safety Guard** is perfect for users who need more control, security, and customization in their WordPress admin area:

👩‍💻 **Freelancers & Developers:** Add backend security and branding to client sites—no heavy coding.  
🏢 **Agencies & Teams:** Secure multiple websites with a single workflow and consistent branding.  
🔒 **Site Owners:** Protect dashboards from brute-force attacks and unauthorized logins.  
🧩 **Plugin/Theme Authors:** Add layered protection in demo or test environments.  
📈 **Online Businesses:** Secure customer data with 2FA, CAPTCHA, and password protection.  
🎓 **Educators & Bloggers:** Maintain a professional look while increasing security.

### ✅ Free Features at a Glance
- Hide Admin Bar (with conditions)  
- Dashboard Overview (in progress)  
- Change Login URL  
- Redirect After Login / Logout  
- Limit Login Attempts  
- CAPTCHA Protection  
- Login Logs & Activity Tracking  
- IP Blocking  
- Two-Factor Authentication (2FA)  
- Password Protection  
- Disable XML-RPC  
- Add Custom Logo on Login Form  
- Custom Logo & Branding  

---

#### 💎 Premium Feature List
- Passwordless Login  
- 2FA via Mobile App (TOTP)  
- CSRF Protection  
- Database Table Prefix Check  
- Whitelist IP Addresses  
- Hide Admin Bar
- WP Directory File Permissions Check  
- Social Login (Google, Facebook, etc.)  
- Disallow Unauthorized REST Requests
- Password Strength Tool  
- Provide Login Template (ready-made)  
- Customize Design Pro (advanced styling)  
- Email Notification

---

== Free Feature Details ==

**👤 Hide Admin Bar (With Conditions):** Hide the admin bar selectively for specific users or roles.  
**📊 Dashboard Overview:** Visualize user activity and security stats in one glance.  
**🔗 Change Login URL:** Customize the default `wp-login.php` to block automated bots.  
**🔁 Redirect After Login/Logout:** Redirect users to any page after login/logout.  
**📋 Limit Login Attempts:** Block repeated failed logins to prevent brute-force attacks.  
**🤖 CAPTCHA Protection:** Stop bots with reCAPTCHA or similar human verifications.  
**🕵️‍♂️ Login Logs & Activity Tracking:** Track user login times and backend actions.  
**⛔ IP Blocking:** Block access by IP address to prevent hostile logins.  
**🔐 Two-Factor Authentication (2FA):** Add extra verification layers to secure logins.  
**🛂 Password Protection:** Protect private pages or areas with a password.  
**⚙️ Disable XML-RPC:** Disable vulnerable XML-RPC endpoints to stop exploits.  
**🖼️ Custom Logo on Login Form:** Replace WordPress logo with your brand.  
**🏷️ Custom Branding:** Apply your own design across login and admin pages.  

---

#### 🔐 Pro Feature Details
**🔑 Passwordless Login:** Secure email-based login with one-time magic links—no password required.  
**📱 2FA via Mobile App:** Add app-based Two-Factor Authentication (Google Authenticator / Authy).  
**🧩 CSRF Protection:** Prevent Cross-Site Request Forgery attacks with token verification.  
**🗃️ Database Table Prefix Check:** Detects and helps change the insecure `wp_` prefix.  
**🌐 Whitelist IP Addresses:** Restrict admin access to trusted IPs only.  
**🧑‍💻 Hide Admin Bar (Conditional):** Show or hide admin bar for specific roles or users.  
**🗂️ WP Directory File Permissions Check:** Scans and verifies file and directory permissions.  
**🌍 Social Login:** Allow users to log in with Google, Facebook, or Twitter accounts.  
**🚫 Disallow Unauthorized REST Requests:** Restrict REST API access conditionally.  
**💪 Password Strength Tool:** Enforce strong password rules for better protection.  
**🎨 Provide Login Template:** Instantly apply stylish, ready-to-use login templates.  
**🧰 Customize Design Pro:** Fully customize admin and login design with a simple UI.  
**📧 Email Notification:** Receive and customize security alerts directly to your inbox.

> Explore Pro Features: [Admin Safety Guard Pro](https://themepaste.com/product/admin-safety-guard-pro)

---

== Screenshots ==

1. Dashboard Overview  
2. Login Security Settings  
3. Limit Login Attempts  
4. Two-Factor Authentication  
5. CAPTCHA Protection  
6. IP Blocking  
7. Login URL Customization  
8. Activity & Login Logs  
9. Branding Settings  
10. XML-RPC & Advanced Settings  

---

== Installation ==

1. Download the plugin `.zip` file.  
2. Go to your **WordPress Admin → Plugins → Add New → Upload Plugin**.  
3. Choose the file and click **Install Now**.  
4. After installation, click **Activate Plugin**.  

---

== Frequently Asked Questions ==

**Q: Does changing the login URL break existing links?**  
A: Update your bookmarks to the new login URL. The plugin automatically flushes permalinks when needed.

**Q: Can I limit login attempts?**  
A: Yes. It blocks users after multiple failed attempts and logs the IP address.

**Q: Is 2FA required for everyone?**  
A: Optional. You can enable or enforce it per role or user.

**Q: Will this slow down my site?**  
A: No. It loads assets conditionally and is performance-optimized.

**Q: Where can I get help?**  
A: [Support](https://themepaste.com/contact)

---

== Changelog ==

= 1.1.5 =
* [Maintenance] - Release preparation and version alignment.
* [Maintenance] - Internal consistency checks.
* [Maintenance] - No code or feature changes in this version.


= 1.1.4 =
* [new] - [New] All Pro features are now available in the free version.
* [New] - Added a Purchase / Upgrade button to allow users to unlock premium support and future enhancements.
* [Improved] - Updated plugin UI and feature visibility for better clarity between free and premium offerings.
* [Improved] - Minor UX and performance optimisations.
* [Fixed] - Small stability issues and internal clean-ups.

= 1.1.3 =
* Fixed an issue where OTP-verified logins could result in session cookies instead of persistent cookies.
* Refactored OTP verification to run earlier in the login flow via `login_init`.
* Updated the authentication process to use `wp_signon()` so WordPress handles Remember Me cookies correctly.
* Tested across multiple environments and browsers to confirm expected cookie expiration behavior.
* Minor improvements and stability adjustments.

= 1.1.2 =
* [fix] - 2FA login cookie session issue when OTP verification completed.
* [Improved] - `wp_set_auth_cookie()` now uses correct $remember flag for persistent login.
* [Improved] - OTP authentication flow now respects the user's "Remember me" choice.
* [new] - Added a phone number field to the in-plugin support form, including country code.

= 1.1.0 =
* [fix] – Resolved several important WordPress admin warnings.
* [new] – Added an in-plugin support system.

= 1.0.9 =
[new] Added deactivation email feature on plugin activation

= 1.0.6, 1.0.8 =
[new] Release the pro version
[new] Compotable with pro version


= 1.0.5 =
[new] Added extendable action and filter hooks  
[new] Ready to integrate Pro version  
[new] Conditionally loaded all assets  
[new] Added default logo URL, width, and height  
[fix] Fixed logo issue from customizer  
[fix] General improvements and bug fixes  

= 1.0.4 =
[new] Auto permalink flush for custom login/logout URLs  
[new] Admin Notice added  
[new] Setup Wizard  
[new] Documentation link added  

= 1.0.3 =
[new] Subdirectory support  
[new] Tooltip in failed login table  
[new] Auto-redirect after max login attempts  
[fix] Custom login/logout URLs  
[fix] Lockout message  
[fix] Failed login table issues  

= 1.0.2 =
[fix] Minor bug fixes  

= 1.0.1 =
[fix] Build issue resolved  

= 1.0.0 =
* Initial release featuring 2FA, CAPTCHA, Limit Login Attempts, IP Blocking, Custom Login URL, Password Protection, and Login Logs.  

---

== Upgrade Notice ==

= 1.0.5 =
Hooks, assets, and Pro-ready support added. Update for smoother performance and future compatibility.

---

== Support ==

For any issues, questions, or feature requests, please reach out via [Support](https://themepaste.com/contact).

---

== Links ==

[Website](https://themepaste.com)  
[Documentation](https://themepaste.com/product-doc/hide-admin-bar-pro/?doc_id=389)  
[Pro Version](https://themepaste.com/product/admin-safety-guard-pro)  
[Facebook](https://www.facebook.com/themepaste)  
[Pinterest](https://uk.pinterest.com/themepaste/)  
[LinkedIn](https://www.linkedin.com/company/themepaste)  
[Instagram](https://www.instagram.com/themepasteuk)
