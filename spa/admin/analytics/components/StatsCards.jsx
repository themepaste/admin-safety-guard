import React from 'react';

/**
 * TPStatsCards (React + Plain CSS, fully scoped)
 * - No Tailwind / No Bootstrap
 * - Component-specific class names prefixed with "tpStats__"
 * - CSS is scoped under ".tpStats" wrapper to avoid conflicts
 */

const STATS = [
  {
    id: 'threats',
    theme: 'purple',
    label: 'Threats Blocked',
    value: '1,234',
    trendText: '+12%',
    trendTone: 'good',
    icon: 'shield',
  },
  {
    id: 'users',
    theme: 'blue',
    label: 'Active Users',
    value: '89',
    trendText: '+5%',
    trendTone: 'good',
    icon: 'users',
  },
  {
    id: 'failed',
    theme: 'orange',
    label: 'Failed Logins',
    value: '24',
    trendText: '-8%',
    trendTone: 'bad',
    icon: 'alert',
  },
  {
    id: 'uptime',
    theme: 'green',
    label: 'Uptime',
    value: '99.9%',
    trendText: 'Excellent',
    trendTone: 'good',
    icon: 'trend',
    showCheck: true,
  },
];

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
    case 'check':
      return (
        <svg {...common}>
          <path
            d="M20 6L9 17l-5-5"
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
  return (
    <section className="tpStats">
      <style>{tpStatsCss}</style>

      <div className="tpStats__grid">
        {STATS.map((s) => (
          <div key={s.id} className={`tpStats__card tpStats__card--${s.theme}`}>
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
                {s.showCheck && (
                  // <TPStatsIcon
                  //   name="check"
                  //   className="tpStats__trendIcon tpStats__trendIcon--check"
                  // />
                  <></>
                )}
                <span className="tpStats__trendText">{s.trendText}</span>
              </div>
            </div>

            <p className="tpStats__label">{s.label}</p>
            <p className="tpStats__value">{s.value}</p>
          </div>
        ))}
      </div>
    </section>
  );
}

const tpStatsCss = `
/* =========================
   Fully scoped styles
   ========================= */
.tpStats{
  /* you can remove padding if your layout already has spacing */
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
  .tpStats__grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (min-width: 1024px){
  .tpStats__grid{ grid-template-columns: repeat(4, minmax(0, 1fr)); }
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
