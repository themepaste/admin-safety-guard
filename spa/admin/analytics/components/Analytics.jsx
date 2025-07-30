import React from 'react';

const Analytics = () => {
    return (
        <div className="plugin-overview">
            {/* Setup reCaptcha Section */}
            <div className="feature-section">
                <h2 className="feature-title">Setup reCaptcha</h2>
                <p className="feature-description">
                    Protect your login and registration forms from bots and spam
                    by setting up Google reCaptcha. This tool ensures that only
                    real users can access your site, preventing automated
                    attacks and improving security. Simply enable it through the
                    settings and integrate with your forms for enhanced
                    protection.
                </p>
                <a href="your-configure-page-url" className="configure-button">
                    Setup reCaptcha
                </a>
            </div>

            {/* Two-Factor Authentication Section */}
            <div className="feature-section">
                <h2 className="feature-title">Two-Factor Authentication</h2>
                <p className="feature-description">
                    Add an extra layer of security with Two-Factor
                    Authentication (2FA). By requiring a one-time passcode (OTP)
                    sent to the user’s email or phone, this feature ensures that
                    only authorized users can log in. Enable it for critical
                    accounts to reduce the risk of unauthorized access.
                </p>
                <a href="your-configure-page-url" className="configure-button">
                    Enable 2FA
                </a>
            </div>

            {/* Custom Login/Logout URLs Section */}
            <div className="feature-section">
                <h2 className="feature-title">Custom Login/Logout URLs</h2>
                <p className="feature-description">
                    Make your login and logout processes more secure by
                    customizing the URLs. This prevents attackers from guessing
                    or targeting your login page. You can easily set custom
                    routes for both actions to protect your site from common
                    brute-force attacks.
                </p>
                <a href="your-configure-page-url" className="configure-button">
                    Configure URLs
                </a>
            </div>

            {/* Password Protection Section */}
            <div className="feature-section">
                <h2 className="feature-title">Password Protection</h2>
                <p className="feature-description">
                    Secure specific content or entire pages with password
                    protection. This ensures that only authorized users can
                    access sensitive content. Admins can configure roles to
                    bypass the password or require a password for all visitors,
                    giving you full control over who sees what.
                </p>
                <a href="your-configure-page-url" className="configure-button">
                    Configure Password Protection
                </a>
            </div>
        </div>
    );
};

export default Analytics;
