import React from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import Analytics from './components/Analytics';

function Main() {
    return (
        <>
            {/* <div className="tpsa-analytics-wrapper">
                <h1>Analytics</h1>
            </div> */}
            <Analytics />
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tpsa-analytics-wrapper')).render(
    <Main />
);

export default Main;
