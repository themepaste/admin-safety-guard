import React from 'react';
import ReactDOM from 'react-dom/client';
import FeatureGrid from './components/FeatureGrid';

function Main() {
  return (
    <>
      <FeatureGrid />
    </>
  );
}

const initApp = () => {
  const container = document.getElementById('tpsa-security-core-wrapper');

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
