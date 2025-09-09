import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import TemplateList from './components/TemplateList';

function Main() {
    return (
        <>
            <div className="tp-login-template">
                {/* <h2>Login Template</h2> */}
                <TemplateList />
            </div>
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tp-login-template')).render(
    <Main />
);

export default Main;
