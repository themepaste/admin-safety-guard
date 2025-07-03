import React, { useState } from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import BlockUsers from './components/BlockUsers';
import FailedLogins from './components/FailedLogins';
import SuccessfulLogins from './components/SuccessfulLogins';

function Main() {
    const [activeComponent, setActiveComponent] = useState('SuccessfulLogins');

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

    return (
        <>
            <div className="tpsa-login-log-activity-wrapper">
                <div className="tpsa-login-log-activity-tabs">
                    <button
                        type="button"
                        onClick={() => setActiveComponent('BlockUsers')}
                    >
                        Block Users
                    </button>
                    <button
                        type="button"
                        onClick={() => setActiveComponent('FailedLogins')}
                    >
                        Failed Logins
                    </button>
                    <button
                        type="button"
                        onClick={() => setActiveComponent('SuccessfulLogins')}
                    >
                        Successful Logins
                    </button>
                </div>
                <div>{renderComponent()}</div>
            </div>
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tpsa-login-log-activity')).render(
    <Main />
);

export default Main;
