import React from 'react';
import { absoluteTime, relativeTime } from './format';

/** Relative time for scanning, exact time on hover for the record. */
export default function TimeCell({ value }) {
  return (
    <span className="tpsa-log__time" title={absoluteTime(value)}>
      {relativeTime(value)}
    </span>
  );
}
