import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import './assets/style.css';
import FailedLogins from './components/FailedLogins';
import SuccessfulLogins from './components/SuccessfulLogins';
import BlockUsers from './components/BlockUsers';
import SummaryCards from './components/shared/SummaryCards';

const TABS = [
  { id: 'SuccessfulLogins', label: 'Successful sign-ins' },
  { id: 'FailedLogins', label: 'Failed sign-ins' },
  { id: 'BlockUsers', label: 'Blocked addresses' },
];

function Main() {
  const [activeComponent, setActiveComponent] = useState('SuccessfulLogins');
  const [loading, setLoading] = useState(true);
  // Bumped whenever a block/release happens so the tiles recount.
  const [refreshKey, setRefreshKey] = useState(0);
  const onChanged = () => setRefreshKey((k) => k + 1);

  // Update the active component based on the URL hash during initial load
  useEffect(() => {
    const hash = window.location.hash.replace('#', '');
    if (hash) {
      setActiveComponent(hash);
    }

    // Set loading to false after the URL hash is processed
    setLoading(false);

    // Add event listener to detect hash changes
    const onHashChange = () => {
      const updatedHash = window.location.hash.replace('#', '');
      if (updatedHash) {
        setActiveComponent(updatedHash);
      }
    };

    window.addEventListener('hashchange', onHashChange);

    return () => {
      // Cleanup the event listener
      window.removeEventListener('hashchange', onHashChange);
    };
  }, []);

  // Update the URL hash when the active component changes
  useEffect(() => {
    if (activeComponent) {
      window.history.pushState(null, '', `#${activeComponent}`);
    }
  }, [activeComponent]);

  const renderComponent = () => {
    switch (activeComponent) {
      case 'BlockUsers':
        return <BlockUsers onChanged={onChanged} />;
      case 'FailedLogins':
        return <FailedLogins onChanged={onChanged} />;
      case 'SuccessfulLogins':
      default:
        return <SuccessfulLogins onChanged={onChanged} />;
    }
  };

  // If the page is still loading, don't render the component yet
  if (loading) {
    return null; // Or you could return a loader, e.g. <div>Loading...</div>
  }

  return (
    <>
      <div className="tpsa-login-log-activity-wrapper">
        <SummaryCards refreshKey={refreshKey} onNavigate={setActiveComponent} />

        <div className="tpsa-login-log-activity-header">
          <div className="tpsa-login-log-activity-tabs" role="tablist">
            {TABS.map((tab) => (
              <button
                key={tab.id}
                role="tab"
                aria-selected={activeComponent === tab.id}
                className={activeComponent === tab.id ? 'active' : ''}
                type="button"
                onClick={() => setActiveComponent(tab.id)}
              >
                {tab.label}
              </button>
            ))}
          </div>
          <div className="tp-feature">
            <button className="tp-help-icon" type="button">
              ?
            </button>
            <div className="tp-tooltip">
              <p>
                Every sign-in and failed attempt is recorded here with the
                address and device used. Block an address straight from the
                table, or export the log as CSV for an audit.
              </p>
            </div>
          </div>
        </div>
        <div className="tpsa-login-log-activity-container">
          {renderComponent()}
        </div>
      </div>
    </>
  );
}

const initApp = () => {
  const container = document.getElementById('tpsa-login-log-activity');

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
