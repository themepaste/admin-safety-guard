import React from 'react';

const FeatureItem = ({ title, description }) => {
    return (
        <div style={styles.featureContainer}>
            <h3 style={styles.featureTitle}>{title}</h3>
            <p style={styles.featureDescription}>{description}</p>
        </div>
    );
};

const Analytics = () => {
    return (
        <div style={styles.container}>
            <h1 style={styles.title}>Plugin Features Overview</h1>
            <div style={styles.featuresSection}>
                <FeatureItem
                    title="Limit Login Attempts"
                    description="Protect your site from brute force attacks by limiting login attempts."
                />
                <FeatureItem
                    title="Login Logs & Activity"
                    description="View detailed logs of all login activities for security monitoring."
                />
                <FeatureItem
                    title="Custom Login/Logout"
                    description="Customize the login and logout pages for your site."
                />
                <FeatureItem
                    title="reCAPTCHA"
                    description="Integrate Google reCAPTCHA to prevent spam and bot attacks."
                />
                <FeatureItem
                    title="Two Factor Auth"
                    description="Add an extra layer of security with two-factor authentication."
                />
                <FeatureItem
                    title="Password Protection"
                    description="Ensure strong passwords for better user account security."
                />
                <FeatureItem
                    title="Privacy Hardening"
                    description="Enhance user privacy by enforcing stricter data protection measures."
                />
                <FeatureItem
                    title="Hide Admin Bar"
                    description="Hide the admin bar for non-admin users for a cleaner experience."
                />
                <FeatureItem
                    title="Customize"
                    description="Fully customize the plugin settings to suit your needs."
                />
            </div>
        </div>
    );
};

const styles = {
    // container: {
    //     backgroundColor: '#fff',
    //     color: '#1d2327',
    //     borderRadius: '8px',
    //     padding: '20px',
    //     boxShadow: '0px 4px 12px rgba(0, 0, 0, 0.1)',
    // },
    title: {
        textAlign: 'center',
        color: '#814bfe',
        marginBottom: '20px',
    },
    featuresSection: {
        display: 'flex',
        flexDirection: 'column',
        gap: '20px',
    },
    featureContainer: {
        backgroundColor: '#e2d8fb',
        borderRadius: '8px',
        padding: '15px',
        boxShadow: '0px 2px 6px rgba(0, 0, 0, 0.1)',
        border: '1px solid #e2d8fb',
    },
    featureTitle: {
        fontSize: '1.2rem',
        color: '#494bff',
        marginBottom: '8px',
    },
    featureDescription: {
        fontSize: '1rem',
        color: '#1d2327',
    },
};

export default Analytics;
