import React, { useMemo, useState } from 'react';
import Chart from 'react-apexcharts';

export default function FeatureStatusDonut() {
  const [activeCount] = useState(tpsaAdmin.feature_status.active);
  const [inactiveCount] = useState(tpsaAdmin.feature_status.inactive);

  const total = activeCount + inactiveCount;

  const [focusIndex, setFocusIndex] = useState(null);
  const [hoverIndex, setHoverIndex] = useState(null);
  const [centerMode, setCenterMode] = useState('total');

  const series = useMemo(
    () => [activeCount, inactiveCount],
    [activeCount, inactiveCount]
  );

  const labels = useMemo(() => ['Active', 'Inactive'], []);

  const displayIndex = hoverIndex ?? focusIndex;

  const centerTitle = useMemo(() => {
    if (displayIndex === 0) return 'Active';
    if (displayIndex === 1) return 'Inactive';
    return centerMode === 'total' ? 'Total' : 'Active Rate';
  }, [displayIndex, centerMode]);

  const centerValue = useMemo(() => {
    if (displayIndex === 0) return activeCount;
    if (displayIndex === 1) return inactiveCount;
    if (centerMode === 'total') return total;
    return `${Math.round((activeCount / total) * 100)}%`;
  }, [displayIndex, activeCount, inactiveCount, total, centerMode]);

  const centerSub = useMemo(() => {
    if (displayIndex !== null) return 'Click slice to focus';
    return centerMode === 'total' ? 'Features in plugin' : 'Active / Total';
  }, [displayIndex, centerMode]);

  const options = useMemo(
    () => ({
      chart: {
        type: 'donut',
        height: 320,
        toolbar: { show: false },
        events: {
          dataPointMouseEnter: (_e, _ctx, cfg) =>
            setHoverIndex(cfg.dataPointIndex),
          dataPointMouseLeave: () => setHoverIndex(null),
          dataPointSelection: (_e, _ctx, cfg) => {
            const idx = cfg.dataPointIndex;
            setFocusIndex((p) => (p === idx ? null : idx));
          },
          legendClick: (_ctx, seriesIndex) => {
            setFocusIndex((p) => (p === seriesIndex ? null : seriesIndex));
            return false;
          },
        },
      },
      labels,
      colors: ['#22c55e', '#64748b'],
      stroke: { width: 5, colors: ['#ffffff'] },
      plotOptions: {
        pie: { donut: { size: '68%' } },
      },
      legend: {
        show: true,
        position: 'right',
        markers: { radius: 8 },
        formatter: (name, opts) =>
          `${name}  ${opts.w.globals.series[opts.seriesIndex]}`,
      },
      tooltip: {
        y: { formatter: (val) => val },
      },
      dataLabels: { enabled: false },
      fill: { opacity: focusIndex === null ? 1 : 0.35 },
    }),
    [labels, focusIndex]
  );

  return (
    <div className="fs-card">
      <div className="fs-header">
        <div>
          <div className="fs-title">Feature Status</div>
          <div className="fs-sub">Active vs Inactive modules</div>
        </div>

        <div className="fs-actions">
          <button
            className="fs-btn"
            onClick={() =>
              setCenterMode((m) => (m === 'total' ? 'activePct' : 'total'))
            }
          >
            {centerMode === 'total' ? 'Show Active %' : 'Show Total'}
          </button>
        </div>
      </div>

      <div className="fs-chart-wrap">
        <Chart options={options} series={series} type="donut" height={320} />

        <div className="fs-center">
          <div className="fs-center-title">{centerTitle}</div>
          <div className="fs-center-value">{centerValue}</div>
          <div className="fs-center-sub">{centerSub}</div>
        </div>
      </div>

      <div className="fs-footer">
        <span className="fs-pill">
          <span className="fs-dot fs-green" /> Active: <b>{activeCount}</b>
        </span>
        <span className="fs-pill">
          <span className="fs-dot fs-gray" /> Inactive: <b>{inactiveCount}</b>
        </span>
        <span className="fs-pill">
          Total: <b>{total}</b>
        </span>
      </div>
    </div>
  );
}
