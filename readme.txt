=== Admin Safety Guard – WP Security: Limit Login Attempts, IP Blocking, 2FA & reCAPTCHA ===
Contributors: themepaste, habibnote
Tags: admin safety guard, limit login attempts, 2fa, recaptcha, wp security, login security, brute force, ip block, xml-rpc, social login
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.0.5
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Secure your WordPress login with Admin Safety Guard: limit login attempts, 2FA, reCAPTCHA, IP blocking, disable XML-RPC, activity tracking, custom login URL, redirects, and branded login styles.

== Description ==

**Admin Safety Guard** is a complete WordPress security helper focused on securing the login flow and hardening the admin area — without sacrificing usability or performance. It ships with a clean UI, smart defaults, and guardrails against the most common attacks (brute force, credential stuffing, bot logins, and XML-RPC abuse). You also get granular control over the login experience (custom URL, redirects, branding, and more).

Whether you need to block suspicious IPs, enforce two-factor authentication, or ship a branded login for clients, **Admin Safety Guard** has you covered.

### ✨ Intro — Admin Safety Guard Pro

**Admin Safety Guard Pro** takes your security and customization to the next level. It strengthens defenses against unauthorized access, brute-force attacks, and data risks while giving you deeper control over how users log in and interact with your admin area. Pro also adds flexible design tools and smart automations — a complete solution for both **security** and **convenience**.

**Premium Feature List (Pro):**
- Passwordless Login
- 2FA via Mobile App (TOTP)
- CSRF Protection
- Database Table Prefix Check
- Whitelist IP Addresses
- Hide Admin Bar (Conditional)
- WP Directory File Permissions Check
- Social Login (Google, Facebook, etc.)
- Disallow Unauthorized REST Requests (Conditional)
- Password Strength Tool
- Provide Login Template (ready-made)
- Customize Design Pro (advanced styling)
- Email Notification (Customizable)

**Pro Feature Details:**

**🔑 Passwordless Login** – Secure email one-time links / magic links to reduce password fatigue while keeping accounts safe.  
**📱 2FA via Mobile App** – TOTP support (e.g., Google Authenticator / Authy). Even if passwords leak, accounts stay protected.  
**🧩 CSRF Protection** – Adds verification tokens to sensitive requests to ensure actions originate from trusted sessions.  
**🗃️ Database Table Prefix Check** – Detects `wp_` usage and guides you to a safer, non-default prefix.  
**🌐 Whitelist IP Addresses** – Restrict dashboard access to approved IPs to cut down brute-force attempts.  
**🧑‍💻 Hide Admin Bar (Conditional)** – Show/hide the admin bar by user/role/context for cleaner UX.  
**🗂️ WP Directory File Permissions Check** – Scan and suggest secure file/dir permissions.  
**🌍 Social Login** – Let users sign in with Google/Facebook/Twitter to simplify authentication.  
**🚫 Disallow Unauthorized REST Requests (Conditional)** – Block or restrict REST API access by rules you define.  
**💪 Password Strength Tool** – Enforce strong passwords on registration/reset.  
**🎨 Provide Login Template** – One-click, professional login templates.  
**🧰 Customize Design Pro** – Full control of login/admin look & feel (colors, layouts, backgrounds).  
**📧 Email Notification (Customizable)** – Alerts on key security events with editable subjects/bodies.

> Learn more or get Pro: https://themepaste.com/product/admin-safety-guard-pro

---

### Who Should Use Admin Safety Guard?

- **Freelance Developers & WP Pros:** Ship client sites with solid security and on-brand login pages — zero heavy coding.  
- **Agencies & Teams:** Standardize login security and styling across many installs.  
- **Security-Conscious Site Owners:** Guard against unauthorized access with IP blocking, 2FA, and activity logs.  
- **Plugin/Theme Creators:** Protect demos/sandboxes with stricter admin controls.  
- **Online Businesses & Stores:** Protect customer data and team access with CAPTCHA + 2FA.  
- **Education & Bloggers:** Improve site security and reflect your brand identity at login.

**Useful links**  
✅ Documentation: https://themepaste.com/product-doc/hide-admin-bar-pro/?doc_id=389  
✅ Support: https://themepaste.com/contact-us  
✅ Website: https://themepaste.com  
✅ Pro Version: https://themepaste.com/product/admin-safety-guard-pro  
✅ Facebook: https://www.facebook.com/themepaste  
✅ Pinterest: https://uk.pinterest.com/themepaste/  
✅ LinkedIn: https://www.linkedin.com/company/themepaste  
✅ Instagram: https://www.instagram.com/themepasteuk

---

== Features ==

**Free Features at a Glance**
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

== Feature Details ==

**👤 Hide Admin Bar (With Conditions)** – Hide the admin bar for chosen roles/users or contexts for a cleaner interface.  
**📊 Dashboard Overview** – A visual snapshot of security insights (logins, lockouts, activity).  
**🔗 Change Login URL** – Move the default login path to reduce automated probing.  
**🔁 Redirect After Login / Logout** – Send users to the right destination every time.  
**📋 Limit Login Attempts** – Lockouts on repeated failures to stop brute force.  
**🤖 CAPTCHA Protection** – Human verification to block bot logins.  
**🕵️‍♂️ Login Logs & Activity Tracking** – Audit logins and backend actions to spot anomalies fast.  
**⛔ IP Blocking** – Block abusive IPs (manual or automated rules).  
**🔐 Two-Factor Authentication (2FA)** – Add one-time codes for safer logins.  
**🛂 Password Protection** – Gate specific pages/areas by password.  
**⚙️ Disable XML-RPC** – Turn off a common abuse vector.  
**🖼️ Add Custom Logo on Login Form** – Replace the WP logo with your brand.  
**🏷️ Custom Logo & Branding** – Style admin/login to match your identity.

== Screenshots ==

1. Overview dashboard  
2. Login security settings  
3. Limit login attempts & lockouts  
4. 2FA settings  
5. CAPTCHA settings  
6. IP block/allowlist  
7. Login URL & redirects  
8. Activity & login logs  
9. Branding / custom logo  
10. XML-RPC & advanced settings

== Installation ==

1. Download the plugin zip file.  
2. Go to **Plugins → Add New → Upload Plugin** in your WordPress admin.  
3. Click **Choose File**, select the zip, and click **Install Now**.  
4. Click **Activate** to enable the plugin.

== Frequently Asked Questions ==

= Does changing the login URL break my existing links? =  
If you’ve bookmarked `/wp-login.php`, update your bookmark to the new login URL after you change it. The plugin also flushes permalinks when needed.

= Can I lock down the dashboard by IP? =  
Yes. Use IP Blocking (Free) or IP Allowlist (Pro) to control who can reach the admin.

= Is 2FA required for all users? =  
You can enforce 2FA globally or by role (depending on your configuration). Pro adds TOTP app support.

= Will the plugin slow down my site? =  
Admin Safety Guard is lightweight and conditionally loads assets only where needed.

= Where can I get help? =  
Open a ticket via **Support**: https://themepaste.com/contact

== Changelog ==

= 1.0.5 =
* [new] Added extendable action and filter hooks  
* [new] Ready to integrate Pro version  
* [new] Conditionally loaded all assets  
* [new] Added default values for logo URL, width and height  
* [fix] Fixed logo issue from customizer  
* [fix] Various improvements and fixes

= 1.0.4 =
* [new] Auto permalink flush based on custom login/logout URL  
* [new] Admin notice added  
* [new] Setup wizard added  
* [new] Documentation added

= 1.0.3 =
* [new] Subdirectory support  
* [new] Question tooltips in failed login table  
* [new] Auto-redirect after crossing login attempt limit  
* [fix] Custom login & logout URL  
* [fix] Lockout message  
* [fix] Failed login table  
* [fix] Misc issues

= 1.0.2 =
* [fix] Misc fixes

= 1.0.1 =
* [fix] Build file issue

= 1.0.0 =
* Initial release: Two-Factor Authentication, Limit Login Attempts, Login Logs & Activity, CAPTCHA, Custom Login URL, Page Password Protection, IP Blocking.  
* Dashboard Overview and customizable Login Page styling.

== Upgrade Notice ==

= 1.0.5 =
Action/filter hooks added and asset loading optimized. Update for better performance and Pro-ready extensibility.

== Support ==

If you encounter issues or need help, please contact us: https://themepaste.com/contact

== Links ==

- Website: https://themepaste.com
- Documentation: https://themepaste.com/product-doc/hide-admin-bar-pro/?doc_id=389
- Pro Version: https://themepaste.com/product/admin-safety-guard-pro
- Facebook: https://www.facebook.com/themepaste
- Pinterest: https://uk.pinterest.com/themepaste/
- LinkedIn: https://www.linkedin.com/company/themepaste
- Instagram: https://www.instagram.com/themepasteuk
