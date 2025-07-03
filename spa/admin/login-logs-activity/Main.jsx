import React from 'react';
import ReactDOM from 'react-dom/client';

function Main() {
    return (
        <>
            <h1>Hello I am from react</h1>
        </>
    );
}

ReactDOM.createRoot(document.getElementById('tpsa-login-log-activity')).render(
    <Main />
);

export default Main;
