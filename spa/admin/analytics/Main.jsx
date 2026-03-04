import React from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import LoginActivity from './components/LoginActivity';
import FeatureStatusDonut from './components/FeatureStatusDonut';
import StatsCards from './components/StatsCards';
import FeatureGrid from './components/FeatureGrid';

function Main() {
  return (
    <>
      <StatsCards />
      <div className="tpsa-activity-wrapper">
        <LoginActivity />
        <FeatureStatusDonut />
      </div>
      {/* <FeatureGrid /> */}
    </>
  );
}

const initApp = () => {
  const container = document.getElementById('tpsa-analytics-wrapper');

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
