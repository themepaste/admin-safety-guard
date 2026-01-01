import React, { useMemo } from 'react';
import Chart from 'react-apexcharts';

export default function LoginActivity() {
  // ✅ Demo time buckets (as you requested)
  const categories = useMemo(
    () => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
    []
  );

  // ✅ Demo data (edit as needed)
  const series = useMemo(
    () => [
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
    ],
    []
  );

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
      // ✅ 3 colors: Red=Blocked, Green=Success, Yellow=Failed
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
      // ✅ Nice UI feel
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
    <div style={styles.card}>
      <div style={styles.header}>
        <div>
          <div style={styles.title}>Login Activity (24h)</div>
        </div>
      </div>

      <Chart options={options} series={series} type="bar" height={340} />
    </div>
  );
}

const styles = {
  card: {
    background: '#fff',
    border: '1px solid #e5e7eb',
    borderRadius: 14,
    padding: 16,
    boxShadow: '0 6px 18px rgba(17, 24, 39, 0.06)',
    width: '100%',
    marginBottom: 30,
  },
  header: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  title: { fontSize: 16, fontWeight: 700, color: '#111827' },
  sub: { fontSize: 12, color: '#6b7280', marginTop: 3 },
};
