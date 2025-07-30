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
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tpsa-analytics-wrapper')).render(
    <Main />
);

export default Main;
