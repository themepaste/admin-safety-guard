import React from 'react';
import ReactDOM from 'react-dom/client';
import UserTable from './components/UserTable';

function Main() {
  return (
    <>
      <UserTable />
    </>
  );
}

const initApp = () => {
  const container = document.getElementById('tpsa-render-user-table_for-2fa');

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
