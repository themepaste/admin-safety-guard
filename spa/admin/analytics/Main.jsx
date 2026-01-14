import React from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import OverviewFeatures from './components/OverviewFeatures';
import CustomizerBanner from './components/CustomizerBanner';
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
      <OverviewFeatures />
      <CustomizerBanner />
    </>
  );
}

ReactDOM.createRoot(document.getElementById('tpsa-analytics-wrapper')).render(
  <Main />
);

export default Main;
