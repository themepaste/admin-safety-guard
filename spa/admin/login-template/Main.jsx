import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';

function Main() {
    return (
        <>
            <div className="tp-login-template">
                <h2>Login Template</h2>
            </div>
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tp-login-template')).render(
    <Main />
);

export default Main;
