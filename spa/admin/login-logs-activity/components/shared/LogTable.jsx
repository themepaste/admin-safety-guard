import React, { useCallback, useEffect, useState } from 'react';
import { Download, RefreshCw, Search, ShieldBan, ShieldCheck } from 'lucide-react';
import { downloadCsv, fetchLog, setBlocked } from './api';
import PurgeControl from './PurgeControl';

const RANGES = [
  { value: '24h', label: 'Last 24 hours' },
  { value: '7d', label: 'Last 7 days' },
  { value: '30d', label: 'Last 30 days' },
  { value: 'all', label: 'All time' },
];

/**
 * One generic, paginated log table.
 *
 * The three monitoring tabs were previously three ~200-line copies of the same
 * fetch/search/paginate code. They now share this component and differ only in
 * their column definitions.
 */
export default function LogTable({
  title,
  endpoint,
  exportType,
  columns,
  rowKey = (row) => row.id,
  emptyMessage = 'Nothing recorded yet.',
  emptyHint,
  // Renders a Block/Release control per row when the row exposes an IP.
  action = null, // 'block' | 'release' | null
  onChanged,
}) {
  const [rows, setRows] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(10);
  const [search, setSearch] = useState('');
  const [range, setRange] = useState('all');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [notice, setNotice] = useState(null);
  const [busyIp, setBusyIp] = useState(null);

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const result = await fetchLog(endpoint, {
        page,
        limit: perPage,
        search,
        range,
      });
      setRows(result.rows);
      setTotal(result.total);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [endpoint, page, perPage, search, range]);

  // Debounce typing so each keystroke is not a request.
  useEffect(() => {
    const timer = setTimeout(load, search ? 350 : 0);
    return () => clearTimeout(timer);
  }, [load, search]);

  const totalPages = Math.max(1, Math.ceil(total / perPage));

  const runAction = async (ip, blocked) => {
    setBusyIp(ip);
    setNotice(null);
    try {
      const result = await setBlocked(ip, blocked);
      setNotice({ type: 'success', text: result.message });
      await load();
      if (onChanged) onChanged();
    } catch (err) {
      setNotice({ type: 'error', text: err.message });
    } finally {
      setBusyIp(null);
    }
  };

  const runExport = async () => {
    setNotice(null);
    try {
      const count = await downloadCsv(exportType);
      setNotice({ type: 'success', text: `Exported ${count} rows.` });
    } catch (err) {
      setNotice({ type: 'error', text: err.message });
    }
  };

  return (
    <div className="tpsa-log">
      <div className="tpsa-log__head">
        <h2 className="tpsa-log__title">
          {title}
          {total > 0 && <span className="tpsa-log__count">{total}</span>}
        </h2>

        <div className="tpsa-log__tools">
          <label className="tpsa-log__search">
            <Search className="tpsa-log__searchIcon" aria-hidden="true" />
            <input
              type="search"
              placeholder="Search IP, user, browser…"
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(1);
              }}
              aria-label={`Search ${title}`}
            />
          </label>

          <select
            value={range}
            onChange={(e) => {
              setRange(e.target.value);
              setPage(1);
            }}
            aria-label="Time range"
            className="tpsa-log__select"
          >
            {RANGES.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>

          <select
            value={perPage}
            onChange={(e) => {
              setPerPage(Number(e.target.value));
              setPage(1);
            }}
            aria-label="Rows per page"
            className="tpsa-log__select"
          >
            {[10, 25, 50, 100].map((n) => (
              <option key={n} value={n}>
                {n} / page
              </option>
            ))}
          </select>

          <button
            type="button"
            className="tpsa-log__iconBtn"
            onClick={load}
            title="Refresh"
            aria-label="Refresh"
          >
            <RefreshCw className={loading ? 'is-spinning' : ''} aria-hidden="true" />
          </button>

          <button
            type="button"
            className="tpsa-log__iconBtn"
            onClick={runExport}
            title="Export as CSV"
            aria-label="Export as CSV"
          >
            <Download aria-hidden="true" />
          </button>
        </div>
      </div>

      {notice && (
        <div className={`tpsa-log__notice is-${notice.type}`} role="status">
          {notice.text}
        </div>
      )}

      {error ? (
        <div className="tpsa-log__notice is-error" role="alert">
          {error}
        </div>
      ) : (
        <>
          <div className="tpsa-log__tableWrap">
            <table className="tpsa-log__table">
              <thead>
                <tr>
                  {columns.map((column) => (
                    <th key={column.key} style={column.width ? { width: column.width } : undefined}>
                      {column.label}
                    </th>
                  ))}
                  {action && <th className="tpsa-log__actionCol">Action</th>}
                </tr>
              </thead>
              <tbody>
                {loading && rows.length === 0 ? (
                  <tr>
                    <td colSpan={columns.length + (action ? 1 : 0)} className="tpsa-log__state">
                      Loading…
                    </td>
                  </tr>
                ) : rows.length === 0 ? (
                  <tr>
                    <td colSpan={columns.length + (action ? 1 : 0)} className="tpsa-log__state">
                      <strong>{emptyMessage}</strong>
                      {emptyHint && <span className="tpsa-log__stateHint">{emptyHint}</span>}
                    </td>
                  </tr>
                ) : (
                  rows.map((row) => (
                    <tr key={rowKey(row)}>
                      {columns.map((column) => (
                        <td key={column.key} data-label={column.label}>
                          {column.render ? column.render(row) : row[column.key]}
                        </td>
                      ))}
                      {action && (
                        <td className="tpsa-log__actionCol">
                          {row.ip_address ? (
                            <button
                              type="button"
                              className={`tpsa-log__action is-${action}`}
                              disabled={busyIp === row.ip_address}
                              onClick={() => runAction(row.ip_address, action === 'block')}
                            >
                              {action === 'block' ? (
                                <>
                                  <ShieldBan aria-hidden="true" />
                                  Block
                                </>
                              ) : (
                                <>
                                  <ShieldCheck aria-hidden="true" />
                                  Release
                                </>
                              )}
                            </button>
                          ) : (
                            <span className="tpsa-log__muted">—</span>
                          )}
                        </td>
                      )}
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          <PurgeControl
            type={exportType}
            onPurged={() => {
              setPage(1);
              load();
              if (onChanged) onChanged();
            }}
          />

          {total > perPage && (
            <div className="tpsa-log__pager">
              <button type="button" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>
                Previous
              </button>
              <span>
                Page {page} of {totalPages}
              </span>
              <button
                type="button"
                onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                disabled={page >= totalPages}
              >
                Next
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
}
