import React, { useMemo, useState } from 'react';
import Chart from 'react-apexcharts';

export default function FeatureStatusDonut() {
  // Demo data
  const [activeCount] = useState(12);
  const [inactiveCount] = useState(3);

  const total = activeCount + inactiveCount;

  // UI state
  const [focusIndex, setFocusIndex] = useState(null); // 0 or 1
  const [hoverIndex, setHoverIndex] = useState(null); // 0 or 1
  const [centerMode, setCenterMode] = useState('total'); // "total" | "activePct"

  const series = useMemo(
    () => [activeCount, inactiveCount],
    [activeCount, inactiveCount]
  );

  const labels = useMemo(() => ['Active', 'Inactive'], []);
  const colors = useMemo(() => ['#22c55e', '#64748b'], []); // green + slate

  const displayIndex = hoverIndex ?? focusIndex; // hover wins

  const centerTitle = useMemo(() => {
    if (displayIndex === 0) return 'Active';
    if (displayIndex === 1) return 'Inactive';
    return centerMode === 'total' ? 'Total' : 'Active Rate';
  }, [displayIndex, centerMode]);

  const centerValue = useMemo(() => {
    if (displayIndex === 0) return `${activeCount}`;
    if (displayIndex === 1) return `${inactiveCount}`;
    if (centerMode === 'total') return `${total}`;
    const pct = total === 0 ? 0 : Math.round((activeCount / total) * 100);
    return `${pct}%`;
  }, [displayIndex, activeCount, inactiveCount, total, centerMode]);

  const centerSub = useMemo(() => {
    if (displayIndex === 0 || displayIndex === 1) return 'Click slice to focus';
    return centerMode === 'total' ? 'Features in plugin' : 'Active / Total';
  }, [displayIndex, centerMode]);

  const options = useMemo(
    () => ({
      chart: {
        type: 'donut',
        height: 320,
        toolbar: { show: false },
        animations: { enabled: true },
        events: {
          dataPointMouseEnter: (_e, _ctx, config) =>
            setHoverIndex(config.dataPointIndex),
          dataPointMouseLeave: () => setHoverIndex(null),
          dataPointSelection: (_e, _ctx, config) => {
            const idx = config.dataPointIndex;
            setFocusIndex((prev) => (prev === idx ? null : idx));
          },
          legendClick: (_ctx, seriesIndex) => {
            setFocusIndex((prev) =>
              prev === seriesIndex ? null : seriesIndex
            );
            return false; // prevent default toggling (we handle focus ourselves)
          },
        },
      },

      labels,
      colors,

      stroke: { width: 5, colors: ['#ffffff'] },

      // Donut thickness
      plotOptions: {
        pie: {
          donut: {
            size: '68%',
          },
        },
      },

      // Clean tooltip like your screenshot
      tooltip: {
        y: {
          formatter: (val) => `${val}`,
          title: {
            formatter: (seriesName) => `${seriesName}`,
          },
        },
      },

      // Right side legend
      legend: {
        show: true,
        position: 'right',
        fontSize: '13px',
        markers: { radius: 8 },
        itemMargin: { vertical: 10 },
        onItemClick: { toggleDataSeries: false },
        formatter: (seriesName, opts) => {
          const val = opts.w.globals.series[opts.seriesIndex];
          return `${seriesName}  ${val}`;
        },
      },

      dataLabels: { enabled: false },

      // Make non-focused slices fade (effective UI)
      states: {
        normal: { filter: { type: 'none' } },
        hover: { filter: { type: 'none' } },
        active: { filter: { type: 'none' } },
      },

      // Dynamic slice opacity via custom fill
      fill: {
        opacity: focusIndex === null ? 1 : 0.35,
      },

      // When focused, we’ll "re-color" by using theme + CSS overlay approach:
      // easiest is to rely on Apex selection highlight + our opacity rule.
    }),
    [labels, colors, focusIndex]
  );

  // Small helper for quick actions
  const resetFocus = () => setFocusIndex(null);

  return (
    <div style={styles.card}>
      <div style={styles.header}>
        <div>
          <div style={styles.title}>Feature Status</div>
          <div style={styles.sub}>Active vs Inactive modules</div>
        </div>

        <div style={styles.actions}>
          <button
            type="button"
            onClick={() =>
              setCenterMode((m) => (m === 'total' ? 'activePct' : 'total'))
            }
            style={styles.ghostBtn}
            title="Toggle center metric"
          >
            {centerMode === 'total' ? 'Show Active %' : 'Show Total'}
          </button>

          <button
            type="button"
            onClick={resetFocus}
            style={styles.ghostBtn}
            title="Reset focus"
          >
            Reset
          </button>
        </div>
      </div>

      <div style={styles.body}>
        {/* Chart */}
        <div style={{ position: 'relative', width: '100%' }}>
          <Chart options={options} series={series} type="donut" height={320} />

          {/* Center label overlay (more premium than default donut labels) */}
          <div style={styles.centerOverlay}>
            <div style={styles.centerTitle}>{centerTitle}</div>
            <div style={styles.centerValue}>{centerValue}</div>
            <div style={styles.centerSub}>{centerSub}</div>

            {focusIndex !== null && (
              <div style={styles.focusHint}>
                Focus: <b>{labels[focusIndex]}</b>
              </div>
            )}
          </div>
        </div>
      </div>

      <div style={styles.footer}>
        <div style={styles.pill}>
          <span style={{ ...styles.dot, background: '#22c55e' }} /> Active:{' '}
          <b>{activeCount}</b>
        </div>
        <div style={styles.pill}>
          <span style={{ ...styles.dot, background: '#64748b' }} /> Inactive:{' '}
          <b>{inactiveCount}</b>
        </div>
        <div style={styles.pill}>
          Total: <b>{total}</b>
        </div>
      </div>
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
  },
  header: {
    display: 'flex',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 12,
    marginBottom: 12,
  },
  title: { fontSize: 16, fontWeight: 700, color: '#111827' },
  sub: { fontSize: 12, color: '#6b7280', marginTop: 3 },
  actions: { display: 'flex', gap: 8, flexWrap: 'wrap' },
  ghostBtn: {
    border: '1px solid #e5e7eb',
    background: '#fff',
    borderRadius: 10,
    padding: '8px 10px',
    fontSize: 12,
    cursor: 'pointer',
    color: '#111827',
  },
  body: {
    display: 'grid',
    gridTemplateColumns: '1fr',
    gap: 12,
    alignItems: 'center',
  },
  centerOverlay: {
    position: 'absolute',
    inset: 0,
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center',
    pointerEvents: 'none',
    textAlign: 'center',
  },
  centerTitle: { fontSize: 12, color: '#6b7280', fontWeight: 600 },
  centerValue: {
    fontSize: 28,
    color: '#111827',
    fontWeight: 800,
    marginTop: 4,
  },
  centerSub: { fontSize: 12, color: '#9ca3af', marginTop: 4 },
  focusHint: {
    marginTop: 10,
    fontSize: 12,
    color: '#111827',
    background: '#f3f4f6',
    border: '1px solid #e5e7eb',
    borderRadius: 999,
    padding: '6px 10px',
  },
  footer: { display: 'flex', gap: 10, flexWrap: 'wrap', marginTop: 10 },
  pill: {
    display: 'inline-flex',
    alignItems: 'center',
    gap: 8,
    fontSize: 12,
    color: '#111827',
    background: '#f9fafb',
    border: '1px solid #e5e7eb',
    borderRadius: 999,
    padding: '6px 10px',
  },
  dot: { width: 10, height: 10, borderRadius: 999, display: 'inline-block' },
};
