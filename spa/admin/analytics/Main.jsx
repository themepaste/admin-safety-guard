import React from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import LoginAttempsReports from './components/LoginAttempsReports';
import OverviewFeatures from './components/OverviewFeatures';
import CustomizerBanner from './components/CustomizerBanner';

function Main() {
    return (
        <>
            {/* <div className="tpsa-analytics-wrapper">
                <h1>Get insights into your site's safety</h1>
            </div> */}
            <LoginAttempsReports />
            <OverviewFeatures />
            <CustomizerBanner />
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tpsa-analytics-wrapper')).render(
    <Main />
);

export default Main;
