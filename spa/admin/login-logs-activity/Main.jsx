import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import FailedLogins from './components/FailedLogins';
import SuccessfulLogins from './components/SuccessfulLogins';
import BlockUsers from './components/BlockUsers';

function Main() {
    const [activeComponent, setActiveComponent] = useState('SuccessfulLogins');
    const [loading, setLoading] = useState(true);

    // Update the active component based on the URL hash during initial load
    useEffect(() => {
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            setActiveComponent(hash);
        }

        // Set loading to false after the URL hash is processed
        setLoading(false);

        // Add event listener to detect hash changes
        const onHashChange = () => {
            const updatedHash = window.location.hash.replace('#', '');
            if (updatedHash) {
                setActiveComponent(updatedHash);
            }
        };

        window.addEventListener('hashchange', onHashChange);

        return () => {
            // Cleanup the event listener
            window.removeEventListener('hashchange', onHashChange);
        };
    }, []);

    // Update the URL hash when the active component changes
    useEffect(() => {
        if (activeComponent) {
            window.history.pushState(null, '', `#${activeComponent}`);
        }
    }, [activeComponent]);

    const renderComponent = () => {
        switch (activeComponent) {
            case 'BlockUsers':
                return <BlockUsers />;
            case 'FailedLogins':
                return <FailedLogins />;
            case 'SuccessfulLogins':
                return <SuccessfulLogins />;
            default:
                return <SuccessfulLogins />;
        }
    };

    // If the page is still loading, don't render the component yet
    if (loading) {
        return null; // Or you could return a loader, e.g. <div>Loading...</div>
    }

    return (
        <>
            <div className="tpsa-login-log-activity-wrapper">
                <div className="tpsa-login-log-activity-header">
                    <div className="tpsa-login-log-activity-tabs">
                        <button
                            className={
                                activeComponent === 'BlockUsers' ? 'active' : ''
                            }
                            type="button"
                            onClick={() => setActiveComponent('BlockUsers')}
                        >
                            Blocked Users
                        </button>
                        <button
                            className={
                                activeComponent === 'FailedLogins'
                                    ? 'active'
                                    : ''
                            }
                            type="button"
                            onClick={() => setActiveComponent('FailedLogins')}
                        >
                            Failed Logins
                        </button>
                        <button
                            className={
                                activeComponent === 'SuccessfulLogins'
                                    ? 'active'
                                    : ''
                            }
                            type="button"
                            onClick={() =>
                                setActiveComponent('SuccessfulLogins')
                            }
                        >
                            Successful Logins
                        </button>
                    </div>
                    <div class="tp-feature">
                        <button class="tp-help-icon">?</button>
                        <div class="tp-tooltip">
                            <p>
                                This feature records login attempts and admin
                                interactions, empowering you to audit usage
                                patterns and identify suspicious behaviors fast.
                            </p>
                        </div>
                    </div>
                </div>
                <div className="tpsa-login-log-activity-container">
                    {renderComponent()}
                </div>
            </div>
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tpsa-login-log-activity')).render(
    <Main />
);

export default Main;
