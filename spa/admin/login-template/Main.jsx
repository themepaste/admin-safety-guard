import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import TemplateList from './components/TemplateList';

function Main() {
  return (
    <>
      <div className="tp-login-template">
        <h2>Login Template</h2>
        <TemplateList />
      </div>
    </>
  );
}

const initApp = () => {
  const container = document.getElementById('tpsa-login-template');

  if (!container) return;

  const root = ReactDOM.createRoot(container);
  root.render(<Main />);
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initApp);
} else {
  initApp();
}

export default Main;
