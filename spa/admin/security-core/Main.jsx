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

ReactDOM.createRoot(
  document.getElementById('tpsa-security-core-wrapper')
).render(<Main />);

export default Main;
