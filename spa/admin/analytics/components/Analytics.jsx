import React from 'react';

const Analytics = () => {
    return (
        <div className="plugin-overview">
            <div className="feature-section">
                <h2 className="feature-title">User Login Protection</h2>
                <p className="feature-description">
                    Protect your users from unauthorized login attempts by
                    adding an extra layer of security. Enabling this feature
                    ensures that your login process is secure, making it harder
                    for attackers to gain access to your site.
                </p>
                <a href="your-configure-page-url" className="configure-button">
                    Configure
                </a>
            </div>

            <div className="feature-section">
                <h2 className="feature-title">Two-Factor Authentication</h2>
                <p className="feature-description">
                    Two-factor authentication (2FA) adds a second layer of
                    security to the login process. By requiring a second
                    verification step, you greatly reduce the risk of
                    compromised accounts.
                </p>
                <a href="your-configure-page-url" className="configure-button">
                    Enable 2FA
                </a>
            </div>

            <div className="feature-section">
                <h2 className="feature-title">Custom Login/Logout URLs</h2>
                <p className="feature-description">
                    Customize the login and logout URLs to make your site less
                    predictable and harder to target by attackers. This helps in
                    avoiding brute force attacks and unauthorized access.
                </p>
                <a href="your-configure-page-url" className="configure-button">
                    Configure URLs
                </a>
            </div>
        </div>
    );
};

export default Analytics;
