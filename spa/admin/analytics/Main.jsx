import React from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';

function Main() {
    return (
        <>
            <div class="tpsa-analytics-wrapper">
                <h1>Analytics</h1>
            </div>
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tpsa-analytics-wrapper')).render(
    <Main />
);

export default Main;
