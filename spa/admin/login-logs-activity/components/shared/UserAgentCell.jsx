import React from 'react';
import { describeUserAgent } from './format';

/**
 * Readable device summary, with the raw User-Agent available on hover.
 *
 * Non-browser clients (curl, python-requests, bots) are flagged: a login
 * attempt from a scripted client is far more interesting than one from Chrome.
 */
export default function UserAgentCell({ raw }) {
  const { label, detail, suspicious } = describeUserAgent(raw);

  return (
    <span className="tpsa-log__ua" title={detail || label}>
      {label}
      {suspicious && (
        <span className="tpsa-log__flag is-danger" title="Not a normal web browser">
          Script
        </span>
      )}
    </span>
  );
}
