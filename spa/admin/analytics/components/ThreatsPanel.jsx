import React, { useCallback, useEffect, useState } from 'react';

const base = () => `${tpsaAdmin.rest_url}secure-admin/v1/`;
const headers = () => ({
  'X-WP-Nonce': tpsaAdmin.rest_nonce,
  'Content-Type': 'application/json',
});

function when(value) {
  if (!value) return '—';
  const d = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return value;
  const secs = Math.round((Date.now() - d.getTime()) / 1000);
  if (secs < 60) return 'just now';
  if (secs < 3600) return `${Math.floor(secs / 60)} min ago`;
  if (secs < 86400) return `${Math.floor(secs / 3600)} hr ago`;
  return d.toLocaleString(undefined, {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

/**
 * Full list of everything the plugin blocked, opened from the Threats Blocked
 * tile. Includes a Clear control so the counter can be reset after review.
 */
export default function ThreatsPanel({ onClose, onCleared }) {
  const [rows, setRows] = useState([]);
  const [labels, setLabels] = useState({});
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [confirming, setConfirming] = useState(false);
  const limit = 20;

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch(`${base()}monitor/threats?page=${page}&limit=${limit}`, {
        headers: headers(),
        credentials: 'include',
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json?.message || 'Could not load the threat log.');
      setRows(json.data || []);
      setLabels(json.labels || {});
      setTotal(json.total || 0);
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, [page]);

  useEffect(() => {
    load();
  }, [load]);

  // Close on Escape, like any other dialog.
  useEffect(() => {
    const onKey = (e) => {
      if (e.key === 'Escape') onClose();
    };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const clearAll = async () => {
    try {
      const res = await fetch(`${base()}monitor/threats/clear`, {
        method: 'POST',
        headers: headers(),
        credentials: 'include',
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json?.message || 'Could not clear the log.');
      setConfirming(false);
      setPage(1);
      await load();
      if (onCleared) onCleared();
    } catch (e) {
      setError(e.message);
    }
  };

  const pages = Math.max(1, Math.ceil(total / limit));

  return (
    <div className="tpThreats__overlay" role="dialog" aria-modal="true" aria-label="Blocked threats">
      <div className="tpThreats__panel">
        <header className="tpThreats__head">
          <div>
            <h2 className="tpThreats__title">Blocked threats</h2>
            <p className="tpThreats__sub">
              {total > 0
                ? `${total} attack${total === 1 ? '' : 's'} stopped by Admin Safety Guard`
                : 'Nothing has been blocked yet'}
            </p>
          </div>
          <button type="button" className="tpThreats__close" onClick={onClose} aria-label="Close">
            &times;
          </button>
        </header>

        {error && <div className="tpThreats__error">{error}</div>}

        <div className="tpThreats__body">
          {loading ? (
            <p className="tpThreats__state">Loading…</p>
          ) : rows.length === 0 ? (
            <div className="tpThreats__state">
              <strong>No threats recorded.</strong>
              <span>
                Lockouts, blocked addresses, failed captchas and rejected
                two-factor codes will appear here as they happen.
              </span>
            </div>
          ) : (
            <table className="tpThreats__table">
              <thead>
                <tr>
                  <th>What was blocked</th>
                  <th>IP address</th>
                  <th>Details</th>
                  <th>When</th>
                </tr>
              </thead>
              <tbody>
                {rows.map((row) => (
                  <tr key={row.id}>
                    <td>
                      <span className="tpThreats__type">
                        {labels[row.threat_type] || row.threat_type}
                      </span>
                    </td>
                    <td>
                      <code>{row.ip_address}</code>
                    </td>
                    <td className="tpThreats__detail" title={row.request_uri || ''}>
                      {row.detail || <span className="tpThreats__muted">—</span>}
                    </td>
                    <td title={row.blocked_at}>{when(row.blocked_at)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>

        <footer className="tpThreats__foot">
          <div className="tpThreats__pager">
            {total > limit && (
              <>
                <button type="button" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>
                  Previous
                </button>
                <span>
                  Page {page} of {pages}
                </span>
                <button type="button" onClick={() => setPage((p) => Math.min(pages, p + 1))} disabled={page >= pages}>
                  Next
                </button>
              </>
            )}
          </div>

          {total > 0 &&
            (confirming ? (
              <div className="tpThreats__confirm">
                <span>Delete all {total} records?</span>
                <button type="button" className="tpThreats__danger" onClick={clearAll}>
                  Yes, clear
                </button>
                <button type="button" onClick={() => setConfirming(false)}>
                  Cancel
                </button>
              </div>
            ) : (
              <button type="button" className="tpThreats__clear" onClick={() => setConfirming(true)}>
                Clear log
              </button>
            ))}
        </footer>
      </div>
    </div>
  );
}
