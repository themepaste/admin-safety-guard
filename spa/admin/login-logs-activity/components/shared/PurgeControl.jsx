import React, { useState } from 'react';
import { Trash2 } from 'lucide-react';
import { purgeLog } from './api';

const WINDOWS = [
  { value: '24h', label: 'Older than 24 hours' },
  { value: '7d', label: 'Older than 1 week' },
  { value: '14d', label: 'Older than 2 weeks' },
  { value: '30d', label: 'Older than 1 month' },
  { value: '90d', label: 'Older than 3 months' },
  { value: 'range', label: 'A specific date range…' },
  { value: 'all', label: 'Everything in this log' },
];

/**
 * Delete old records from one log.
 *
 * Kept behind a disclosure and a confirm step: this is the only destructive
 * control on the screen and there is no undo.
 */
export default function PurgeControl({ type, onPurged }) {
  const [open, setOpen] = useState(false);
  const [choice, setChoice] = useState('30d');
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');
  const [confirming, setConfirming] = useState(false);
  const [busy, setBusy] = useState(false);
  const [notice, setNotice] = useState(null);

  const isRange = choice === 'range';
  const canRun = isRange ? Boolean(from || to) : true;

  const label = isRange
    ? `records between ${from || 'the beginning'} and ${to || 'today'}`
    : choice === 'all'
      ? 'every record in this log'
      : `records ${WINDOWS.find((w) => w.value === choice)?.label.toLowerCase()}`;

  const run = async () => {
    setBusy(true);
    setNotice(null);
    try {
      const result = await purgeLog(type, {
        olderThan: choice,
        from: isRange ? from : '',
        to: isRange ? to : '',
      });
      setNotice({ type: 'success', text: result.message });
      setConfirming(false);
      if (onPurged) onPurged();
    } catch (e) {
      setNotice({ type: 'error', text: e.message });
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="tpsa-purge">
      <button
        type="button"
        className="tpsa-purge__toggle"
        onClick={() => setOpen((o) => !o)}
        aria-expanded={open}
      >
        <Trash2 aria-hidden="true" />
        Delete old records
      </button>

      {open && (
        <div className="tpsa-purge__panel">
          <label className="tpsa-purge__field">
            <span>Delete</span>
            <select
              value={choice}
              onChange={(e) => {
                setChoice(e.target.value);
                setConfirming(false);
              }}
            >
              {WINDOWS.map((w) => (
                <option key={w.value} value={w.value}>
                  {w.label}
                </option>
              ))}
            </select>
          </label>

          {isRange && (
            <div className="tpsa-purge__range">
              <label>
                <span>From</span>
                <input type="date" value={from} onChange={(e) => setFrom(e.target.value)} />
              </label>
              <label>
                <span>To</span>
                <input type="date" value={to} onChange={(e) => setTo(e.target.value)} />
              </label>
            </div>
          )}

          {confirming ? (
            <div className="tpsa-purge__confirm">
              <span>
                Permanently delete {label}? This cannot be undone.
              </span>
              <button type="button" className="tpsa-purge__danger" onClick={run} disabled={busy}>
                {busy ? 'Deleting…' : 'Yes, delete'}
              </button>
              <button type="button" onClick={() => setConfirming(false)} disabled={busy}>
                Cancel
              </button>
            </div>
          ) : (
            <button
              type="button"
              className="tpsa-purge__run"
              onClick={() => setConfirming(true)}
              disabled={!canRun}
              title={canRun ? undefined : 'Choose at least one date'}
            >
              Delete
            </button>
          )}

          {notice && (
            <p className={`tpsa-purge__notice is-${notice.type}`} role="status">
              {notice.text}
            </p>
          )}
        </div>
      )}
    </div>
  );
}
