import React, { useEffect, useMemo, useState } from 'react';
import Chart from 'react-apexcharts';

/**
 * LoginActivity (ApexCharts)
 * Fetches real data from:
 *   /wp-json/secure-admin/v1/dahboard/limit-login-attempts?reports=s_logins,failed_logins,block_users
 *
 * Expected API response:
 * [
 *   { "name": "Blocked Users", "data": [..6] },
 *   { "name": "Successful Logins", "data": [..6] },
 *   { "name": "Failed Logins", "data": [..6] }
 * ]
 * [
      {
        name: 'Blocked Users',
        data: [30, 10, 6, 3, 2, 4],
      },
      {
        name: 'Successful Logins',
        data: [60, 42, 45, 62, 28, 38],
      },
      {
        name: 'Failed Logins',
        data: [1, 1, 8, 5, 3, 7],
      },
    ]
      ['23-20', '19-16', '15-12', '11-8', '7-4', '3-0']
      ['20:00', '16:00', '12:00', '08:00', '04:00', '00:00']
 * 
 */
export default function LoginActivity() {
  const categories = useMemo(
    () => ['20:00', '16:00', '12:00', '08:00', '04:00', '00:00'],
    []
  );

  const [series, setSeries] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  // ✅ Change this if your WP site URL is different
  // If your React app is running inside WP admin, you can use relative URL.
  const endpoint = useMemo(
    () =>
      `${tpsaAdmin.rest_url}secure-admin/v1/dahboard/limit-login-attempts?reports=s_logins,failed_logins,block_users`,
    []
  );

  useEffect(() => {
    let alive = true;

    async function load() {
      try {
        setLoading(true);
        setError('');

        const res = await fetch(endpoint, {
          method: 'GET',
          headers: { 'Content-Type': 'application/json' },
          credentials: 'include', // ✅ keep cookies for WP auth if needed
        });

        if (!res.ok) {
          const text = await res.text().catch(() => '');
          throw new Error(
            `API error: ${res.status} ${res.statusText}${
              text ? ` - ${text}` : ''
            }`
          );
        }

        const json = await res.json();

        // Validate & normalize
        const safe = Array.isArray(json) ? json : [];
        const normalized = safe
          .filter(
            (x) => x && typeof x === 'object' && typeof x.name === 'string'
          )
          .map((x) => ({
            name: x.name,
            data: Array.isArray(x.data)
              ? x.data.map((n) => Number(n) || 0)
              : [0, 0, 0, 0, 0, 0],
          }))
          .map((x) => ({
            ...x,
            data:
              x.data.length === 6
                ? x.data
                : [...x.data.slice(0, 6), ...Array(6).fill(0)].slice(0, 6),
          }));

        // Keep consistent order
        const order = ['Blocked Users', 'Successful Logins', 'Failed Logins'];
        const ordered = order
          .map((name) => normalized.find((s) => s.name === name))
          .filter(Boolean);

        if (alive) {
          setSeries(ordered.length ? ordered : normalized);
        }
      } catch (e) {
        if (alive) {
          setError(e?.message || 'Failed to load data');
          setSeries([]);
        }
      } finally {
        if (alive) setLoading(false);
      }
    }

    load();
    return () => {
      alive = false;
    };
  }, [endpoint]);

  const options = useMemo(
    () => ({
      chart: {
        type: 'bar',
        height: 340,
        toolbar: { show: true },
        fontFamily:
          'ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji"',
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '45%',
          borderRadius: 2,
          borderRadiusApplication: 'end',
        },
      },
      dataLabels: { enabled: false },
      stroke: { show: false },
      colors: ['#ef4444', '#22c55e', '#f59e0b'],
      legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'center',
        markers: { radius: 3 },
        itemMargin: { horizontal: 12, vertical: 6 },
      },
      xaxis: {
        categories,
        title: { text: 'Time (24h)' },
        axisBorder: { show: true },
        axisTicks: { show: true },
      },
      yaxis: {
        title: { text: 'Count' },
        min: 0,
        forceNiceScale: true,
      },
      grid: {
        strokeDashArray: 4,
        padding: { left: 10, right: 10, top: 10, bottom: 10 },
      },
      tooltip: {
        shared: true,
        intersect: false,
        y: {
          formatter: (val) => `${val}`,
        },
      },
      responsive: [
        {
          breakpoint: 640,
          options: {
            plotOptions: { bar: { columnWidth: '60%' } },
            legend: { position: 'bottom' },
          },
        },
      ],
    }),
    [categories]
  );

  return (
    <div className="fs-card">
      <div className="fs-header">
        <div>
          <div className="fs-title">Login Activity (Last 24h)</div>
          <div className="fs-subtitle">{loading ?? 'Loading…'}</div>
        </div>

        <div className="fs-actions">
          {/* Optional: show endpoint for debugging */}
          {/* <code style={{ fontSize: 12, opacity: 0.7 }}>{endpoint}</code> */}
        </div>
      </div>

      {error ? (
        <div className="fs-error" style={{ padding: 12 }}>
          <div style={{ fontWeight: 600, marginBottom: 6 }}>Error</div>
          <div style={{ opacity: 0.9 }}>{error}</div>
        </div>
      ) : (
        <Chart options={options} series={series} type="bar" height={340} />
      )}
    </div>
  );
}
