import React from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import Analytics from './components/Analytics';
import LoginAttemps from './components/LoginAttemps';

function Main() {
    return (
        <>
            {/* <div className="tpsa-analytics-wrapper">
                <h1>Analytics</h1>
            </div> */}
            <LoginAttemps />
            <Analytics />
            <div className="section-container">
                <div className="section-content">
                    {/* Left side: Paragraph */}
                    <div className="left-side">
                        <h2 className="section-title">
                            Customize Your Login Page
                        </h2>
                        <p className="section-description">
                            Make your login page unique by customizing its
                            appearance, including the background, colors, and
                            content. Customize your forms, logos, and more to
                            match your website's branding and provide users with
                            a seamless experience.
                        </p>
                    </div>

                    {/* Right side: Button */}
                    <div className="right-side">
                        <a
                            href="your-configure-page-url"
                            className="configure-button"
                        >
                            Customize Now
                        </a>
                    </div>
                </div>
            </div>
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tpsa-analytics-wrapper')).render(
    <Main />
);

export default Main;
