import React from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';

function Main() {
    return (
        <>
            <div class="tpsa-login-log-activity-wrapper">
                <div>
                    <button>Block Users</button>
                    <button>Failed Logins</button>
                    <button>Successful Logins</button>
                </div>
            </div>
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tpsa-login-log-activity')).render(
    <Main />
);

export default Main;
