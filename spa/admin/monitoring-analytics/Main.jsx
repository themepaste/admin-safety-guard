import React from 'react';
import ReactDOM from 'react-dom/client';

function Main() {
  return (
    <>
      <h1>Monitoring Analytics</h1>
    </>
  );
}

const initApp = () => {
  const container = document.getElementById(
    'tpsa-monitoring-analytics-wrapper',
  );

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
