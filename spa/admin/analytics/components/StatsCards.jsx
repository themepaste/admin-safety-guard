import React, { useEffect, useState } from 'react';
import ThreatsPanel from './ThreatsPanel';

/**
 * TPStatsCards (React + Plain CSS, fully scoped)
 * - No Tailwind / No Bootstrap
 * - Component-specific class names prefixed with "tpStats__"
 * - CSS is scoped under ".tpStats" wrapper to avoid conflicts
 */

const summaryUrl = tpsaAdmin.rest_url + 'secure-admin/v1/monitor/summary';

/** Percentage change between two periods, as a display string. */
function trend(now, before) {
  const a = Number(now) || 0;
  const b = Number(before) || 0;
  if (a === b) return { text: 'No change', tone: 'good' };
  if (b === 0) return { text: `+${a}`, tone: 'bad' };
  const pct = Math.round(((a - b) / b) * 100);
  return { text: `${pct > 0 ? '+' : ''}${pct}%`, tone: pct > 0 ? 'bad' : 'good' };
}

function TPStatsIcon({ name, className }) {
  const common = {
    className,
    viewBox: '0 0 24 24',
    fill: 'none',
    xmlns: 'http://www.w3.org/2000/svg',
    'aria-hidden': true,
  };

  switch (name) {
    case 'shield':
      return (
        <svg {...common}>
          <path
            d="M12 3l8 4v6c0 5-3.5 9-8 10-4.5-1-8-5-8-10V7l8-4z"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinejoin="round"
          />
          <path
            d="M9.5 12l1.8 1.8L15 10"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      );
    case 'users':
      return (
        <svg {...common}>
          <path
            d="M15.5 11a3.5 3.5 0 10-7 0 3.5 3.5 0 007 0z"
            stroke="currentColor"
            strokeWidth="2"
          />
          <path
            d="M4 20a8 8 0 0116 0"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
          />
          <path
            d="M19.5 20a6.2 6.2 0 00-5.2-5.6"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            opacity="0.55"
          />
        </svg>
      );
    case 'alert':
      return (
        <svg {...common}>
          <path
            d="M12 3l10 18H2L12 3z"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinejoin="round"
          />
          <path
            d="M12 9v5"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
          />
          <path
            d="M12 17h.01"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
          />
        </svg>
      );
    case 'trend':
      return (
        <svg {...common}>
          <path
            d="M3 17l6-6 4 4 8-8"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
          <path
            d="M21 7v6h-6"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      );
    default:
      return null;
  }
}

export default function StatsCards() {
  const [data, setData] = useState(null);
  const [showThreats, setShowThreats] = useState(false);
  const [reloadKey, setReloadKey] = useState(0);

  useEffect(() => {
    let cancelled = false;

    fetch(summaryUrl, {
      headers: { 'X-WP-Nonce': tpsaAdmin.rest_nonce },
      credentials: 'include',
    })
      .then((r) => r.json())
      .then((json) => {
        if (!cancelled) setData(json);
      })
      .catch(() => {
        if (!cancelled) setData({});
      });

    return () => {
      cancelled = true;
    };
  }, [reloadKey]);

  const d = data || {};
  const threatTrend = trend(d.threats_24h, d.threats_prev);
  const failedTrend = trend(d.failed_24h, d.failed_prev);

  const STATS = [
    {
      id: 'threats',
      theme: 'purple',
      label: 'Threats Blocked',
      value: d.threats_total ?? 0,
      sub: `${d.threats_24h ?? 0} in the last 24 hours`,
      trendText: threatTrend.text,
      trendTone: threatTrend.tone,
      icon: 'shield',
      // The only tile with a drill-down: click to see what was blocked.
      onClick: () => setShowThreats(true),
      actionLabel: 'View details',
    },
    {
      id: 'users',
      theme: 'blue',
      label: 'Active Users',
      value: d.active_users ?? 0,
      sub: `signed in of ${d.total_users ?? 0} registered`,
      trendText: 'Last 24 hours',
      trendTone: 'good',
      icon: 'users',
    },
    {
      id: 'failed',
      theme: 'orange',
      label: 'Failed Logins',
      value: d.failed_24h ?? 0,
      sub: 'in the last 24 hours',
      trendText: failedTrend.text,
      trendTone: failedTrend.tone,
      icon: 'alert',
    },
    // {
    //   id: 'uptime',
    //   theme: 'green',
    //   label: 'Monitoring',
    //   value: '98%',
    //   trendText: 'Excellent',
    //   trendTone: 'good',
    //   icon: 'trend',
    //   showCheck: true,
    // },
  ];

  return (
    <section className="tpStats">
      <style>{tpStatsCss}</style>

      {showThreats && (
        <ThreatsPanel
          onClose={() => setShowThreats(false)}
          onCleared={() => setReloadKey((k) => k + 1)}
        />
      )}

      <div className="tpStats__grid">
        {STATS.map((s) => (
          <div
            key={s.id}
            className={`tpStats__card tpStats__card--${s.theme}${s.onClick ? ' is-clickable' : ''}`}
            {...(s.onClick
              ? {
                  role: 'button',
                  tabIndex: 0,
                  onClick: s.onClick,
                  onKeyDown: (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                      e.preventDefault();
                      s.onClick();
                    }
                  },
                }
              : {})}
          >
            <div className="tpStats__top">
              <div className={`tpStats__iconBox tpStats__iconBox--${s.theme}`}>
                <TPStatsIcon
                  name={s.icon}
                  className={`tpStats__icon tpStats__icon--${s.theme}`}
                />
              </div>

              <div
                className={`tpStats__trend ${
                  s.trendTone === 'bad'
                    ? 'tpStats__trend--bad'
                    : 'tpStats__trend--good'
                }`}
              >
                {/* left mini icon */}
                <TPStatsIcon name="trend" className="tpStats__trendIcon" />
                {s.showCheck && <></>}
                <span className="tpStats__trendText">{s.trendText}</span>
              </div>
            </div>

            <p className="tpStats__label">{s.label}</p>
            <p className="tpStats__value">{s.value}</p>
            {s.sub && <p className="tpStats__sub">{s.sub}</p>}
            {s.actionLabel && (
              <span className="tpStats__action">{s.actionLabel} &rarr;</span>
            )}
          </div>
        ))}

        {/* New Card */}
        <div className={`tpStats__card tpStats__card--green`}>
          <div className="tpStats__top">
            <div className={`tpStats__iconBox tpStats__iconBox--green`}>
              <TPStatsIcon
                name="trend"
                className={`tpStats__icon tpStats__icon--green`}
              />
            </div>

            <div className={`tpStats__trend tpStats__trend--good`}>
              <TPStatsIcon name="trend" className="tpStats__trendIcon" />
              <span className="tpStats__trendText">Server Info</span>
            </div>
          </div>

          <div className="tpStats__details">
            <p>
              <strong>PHP Version:</strong> {tpsaAdmin.php_version}
            </p>
            <p>
              <strong>Server:</strong> {tpsaAdmin.server_software}
            </p>
            <p>
              <strong>Operating System:</strong> {tpsaAdmin.server_os}
            </p>
            <p>
              <strong>Memory Limit:</strong> {tpsaAdmin.memory_limit}
            </p>
            <p>
              <strong>Max Execution Time:</strong>
              {tpsaAdmin.max_execution}
            </p>
          </div>
        </div>

        {/* end of new card */}
      </div>
    </section>
  );
}

const tpStatsCss = `
.tpStats__sub{ margin:4px 0 0; font-size:12px; color:#8a94a6; }
.tpStats__action{ display:inline-block; margin-top:10px; font-size:12px; font-weight:700; color:#9333ea; }
.tpStats__card.is-clickable{ cursor:pointer; }
.tpStats__card.is-clickable:hover{ transform:translateY(-2px); }
.tpStats__card.is-clickable:focus-visible{ outline:2px solid #814bfe; outline-offset:2px; }

.tpThreats__overlay{ position:fixed; inset:0; background:rgba(15,23,42,.5); display:flex; align-items:center; justify-content:center; z-index:100000; padding:20px; }
.tpThreats__panel{ background:#fff; border-radius:12px; width:100%; max-width:900px; max-height:82vh; display:flex; flex-direction:column; box-shadow:0 24px 60px rgba(15,23,42,.3); }
.tpThreats__head{ display:flex; align-items:flex-start; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #e2d8fb; }
.tpThreats__title{ margin:0; font-size:18px; font-weight:700; color:#1d2327; }
.tpThreats__sub{ margin:4px 0 0; font-size:13px; color:#9e8cca; }
.tpThreats__close{ background:none; border:0; font-size:28px; line-height:1; cursor:pointer; color:#9e8cca; padding:0 4px; }
.tpThreats__close:hover{ color:#1d2327; }
.tpThreats__body{ overflow:auto; padding:0 24px; flex:1; }
.tpThreats__error{ margin:16px 24px 0; padding:10px 14px; background:#fdf0f0; color:#c62828; border-radius:6px; font-size:13px; }
.tpThreats__state{ text-align:center; padding:44px 16px; color:#9e8cca; font-size:13px; display:flex; flex-direction:column; gap:6px; }
.tpThreats__state strong{ color:#3d444b; }
.tpThreats__table{ width:100%; border-collapse:collapse; font-size:13px; }
.tpThreats__table th{ text-align:left; padding:12px 10px; background:#f8f5ff; color:#6d4fd0; font-weight:600; font-size:12px; position:sticky; top:0; }
.tpThreats__table td{ padding:12px 10px; border-bottom:1px solid #f3f1ff; color:#3d444b; vertical-align:middle; }
.tpThreats__type{ font-weight:600; color:#1d2327; }
.tpThreats__table code{ background:#f3f1ff; color:#6d4fd0; padding:2px 7px; border-radius:4px; font-size:12px; }
.tpThreats__detail{ max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.tpThreats__muted{ color:#9e8cca; }
.tpThreats__foot{ display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 24px; border-top:1px solid #e2d8fb; }
.tpThreats__pager{ display:flex; align-items:center; gap:12px; font-size:13px; color:#64748b; }
.tpThreats__pager button, .tpThreats__clear, .tpThreats__confirm button{ padding:7px 14px; border:1px solid #bba8e7; background:#fff; color:#814bfe; border-radius:4px; cursor:pointer; font-size:13px; }
.tpThreats__pager button:disabled{ opacity:.45; cursor:default; }
.tpThreats__confirm{ display:flex; align-items:center; gap:10px; font-size:13px; color:#3d444b; }
.tpThreats__danger{ background:#fdf0f0 !important; color:#c62828 !important; border-color:#f5b5b5 !important; font-weight:600; }

/* =========================
   Fully scoped styles
   ========================= */
.tpStats{
  padding: 6px 2px;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
  margin-bottom: 30px;
}

.tpStats__grid{
  display: grid;
  grid-template-columns: 1fr;
  gap: 22px;
}

@media (min-width: 768px){
  .tpStats__grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (min-width: 1600px){
  .tpStats__grid{ grid-template-columns: repeat(4, minmax(0, 1fr)); }
}

/* On 3-column layout: 4th stat card becomes full row with horizontal/flex style */
@media (min-width: 768px) and (max-width: 1399px){
  .tpStats__card:nth-child(4){
    grid-column: 1 / -1;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    min-height: auto;
    padding: 20px 32px;
  }
  .tpStats__card:nth-child(4) .tpStats__top{
    margin-bottom: 0;
    flex-direction: row;
    align-items: center;
    gap: 20px;
  }
  .tpStats__card:nth-child(4) .tpStats__label{
    margin: 0;
  }
}

/* Last item (Server Info) always full row */
.tpStats__card--full{
  grid-column: 1 / -1;
  min-height: auto;
}

/* Card base */
.tpStats__card{
  border-radius: 26px;
  background: #fff;
  padding: 20px;
  border: 2px solid #e2e8f0;
  box-sizing: border-box;
  min-height: 210px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.tpStats__card--purple{ border-color: #d946ef; }
.tpStats__card--blue{ border-color: #0ea5e9; }
.tpStats__card--orange{ border-color: #f97316; }
.tpStats__card--green{ border-color: #22c55e; }

/* Top row */
.tpStats__top{
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
}

/* Icon box (rounded square) */
.tpStats__iconBox{
  width: 48px;
  height: 48px;
  border-radius: 22px;
  display: grid;
  place-items: center;
}

.tpStats__icon{
  width: 34px;
  height: 34px;
}

.tpStats__iconBox--purple{ background: rgba(217, 70, 239, 0.10); }
.tpStats__iconBox--blue{ background: rgba(14, 165, 233, 0.12); }
.tpStats__iconBox--orange{ background: rgba(249, 115, 22, 0.12); }
.tpStats__iconBox--green{ background: rgba(34, 197, 94, 0.14); }

.tpStats__icon--purple{ color: #a21caf; }
.tpStats__icon--blue{ color: #0369a1; }
.tpStats__icon--orange{ color: #c2410c; }
.tpStats__icon--green{ color: #15803d; }

/* Trend on the right */
.tpStats__trend{
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
  font-size: 14px;
  letter-spacing: -0.02em;
  white-space: nowrap;
}

.tpStats__trendIcon{
  width: 28px;
  height: 28px;
}

.tpStats__trendIcon--check{
  width: 28px;
  height: 28px;
  margin-left: -4px;
}

.tpStats__trend--good{ color: #16a34a; }
.tpStats__trend--bad{ color: #dc2626; }

/* Label + value */
.tpStats__label{
  margin: 0 0 16px 0;
  font-size: 16px;
  color: #64748b;
  font-weight: 500;
  letter-spacing: -0.02em;
}

.tpStats__value{
  margin: 0;
  font-size: 30px;
  line-height: 1.02;
  color: #0f172a;
  font-weight: 900;
  letter-spacing: -0.03em;
}

/* Responsive tuning */
@media (max-width: 420px){
  .tpStats__card{ padding: 26px; min-height: 220px; }
  .tpStats__label{ font-size: 22px; }
  .tpStats__value{ font-size: 48px; }
  .tpStats__trend{ font-size: 20px; }
  .tpStats__trendIcon{ width: 22px; height: 22px; }
  .tpStats__iconBox{ width: 64px; height: 64px; border-radius: 20px; }
  .tpStats__icon{ width: 28px; height: 28px; }
}

/* Reduce motion */
@media (prefers-reduced-motion: reduce){
  .tpStats *{ transition: none !important; }
}
`;
