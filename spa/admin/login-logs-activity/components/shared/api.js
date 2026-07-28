/**
 * REST helpers for the monitoring screen.
 */

const base = () => `${tpsaAdmin.rest_url}secure-admin/v1/`;

const headers = () => ({
  'X-WP-Nonce': tpsaAdmin.rest_nonce,
  'Content-Type': 'application/json',
});

/** Pull one page of a log table. */
export async function fetchLog(endpoint, { page, limit, search, range }) {
  const params = new URLSearchParams({ page, limit });

  if (search && search.trim() !== '') params.append('s', search.trim());
  if (range && range !== 'all') params.append('range', range);

  const response = await fetch(`${base()}${endpoint}?${params.toString()}`, {
    method: 'GET',
    headers: headers(),
    credentials: 'include',
  });

  const json = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error((json && json.message) || 'Could not load the log.');
  }

  return { rows: (json && json.data) || [], total: (json && json.total) || 0 };
}

/** Counters for the summary tiles. */
export async function fetchSummary() {
  const response = await fetch(`${base()}monitor/summary`, {
    method: 'GET',
    headers: headers(),
    credentials: 'include',
  });

  if (!response.ok) throw new Error('Could not load the summary.');

  return response.json();
}

/** Block or release an address. */
export async function setBlocked(ip, blocked) {
  const response = await fetch(
    `${base()}monitor/${blocked ? 'block' : 'unblock'}`,
    {
      method: 'POST',
      headers: headers(),
      credentials: 'include',
      body: JSON.stringify({ ip }),
    },
  );

  const json = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error((json && json.message) || 'The action failed.');
  }

  return json;
}

/**
 * Download a log as CSV.
 *
 * The server returns the CSV as a string rather than a file body so the request
 * can carry the REST nonce; the browser turns it into a download here.
 */
export async function downloadCsv(type) {
  const response = await fetch(`${base()}monitor/export?type=${encodeURIComponent(type)}`, {
    method: 'GET',
    headers: headers(),
    credentials: 'include',
  });

  const json = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error((json && json.message) || 'The export failed.');
  }

  const blob = new Blob([json.csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = json.filename || 'export.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);

  return json.rows || 0;
}
