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
      <FeatureGrid />
    </>
  );
}

ReactDOM.createRoot(document.getElementById('tpsa-analytics-wrapper')).render(
  <Main />
);

export default Main;
