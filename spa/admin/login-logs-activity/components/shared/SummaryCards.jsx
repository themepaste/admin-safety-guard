import React, { useCallback, useEffect, useState } from 'react';
import { CheckCircle2, Clock, ShieldBan, XCircle } from 'lucide-react';
import { fetchSummary } from './api';
import { formatCount } from './format';

/**
 * At-a-glance counters above the logs.
 *
 * A table alone makes you read rows to work out whether anything is wrong.
 * These answer "is my site under attack right now?" in one glance.
 */
export default function SummaryCards({ refreshKey, onNavigate }) {
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);

  const load = useCallback(async () => {
    try {
      setData(await fetchSummary());
      setError(null);
    } catch (err) {
      setError(err.message);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load, refreshKey]);

  if (error || !data) return null;

  const tiles = [
    {
      key: 'failed',
      label: 'Failed sign-ins',
      sub: 'last 24 hours',
      value: data.failed_24h,
      Icon: XCircle,
      tone: data.failed_24h > 0 ? 'warn' : 'ok',
      go: 'FailedLogins',
    },
    {
      key: 'locked',
      label: 'Locked out now',
      sub: 'temporarily',
      value: data.locked_now,
      Icon: Clock,
      tone: data.locked_now > 0 ? 'warn' : 'ok',
      go: 'FailedLogins',
    },
    {
      key: 'blocked',
      label: 'Blocked addresses',
      sub: 'currently denied',
      value: data.blocked_total,
      Icon: ShieldBan,
      tone: data.blocked_total > 0 ? 'danger' : 'ok',
      go: 'BlockUsers',
    },
    {
      key: 'success',
      label: 'Successful sign-ins',
      sub: 'last 24 hours',
      value: data.successful_24h,
      Icon: CheckCircle2,
      tone: 'ok',
      go: 'SuccessfulLogins',
    },
  ];

  return (
    <div className="tpsa-sum">
      {!data.protection_on && (
        <div className="tpsa-sum__alert" role="alert">
          <strong>Brute-force protection is switched off.</strong> Failed
          sign-ins are still recorded, but no address will ever be locked out.{' '}
          <a
            href={`${tpsaAdmin.admin_url}admin.php?page=tp-admin-safety-guard&tab=security-core&tpsa-setting=limit-login-attempts`}
          >
            Turn it on
          </a>
        </div>
      )}

      <div className="tpsa-sum__grid">
        {tiles.map(({ key, label, sub, value, Icon, tone, go }) => (
          <button
            type="button"
            key={key}
            className={`tpsa-sum__card is-${tone}`}
            onClick={() => onNavigate && onNavigate(go)}
          >
            <span className="tpsa-sum__icon">
              <Icon aria-hidden="true" />
            </span>
            <span className="tpsa-sum__value">{formatCount(value)}</span>
            <span className="tpsa-sum__label">{label}</span>
            <span className="tpsa-sum__sub">{sub}</span>
          </button>
        ))}
      </div>

      {data.top_offender && (
        <p className="tpsa-sum__offender">
          Most active address in the last 24 hours:{' '}
          <code>{data.top_offender.ip}</code> — {data.top_offender.attempts}{' '}
          failed attempts, {data.top_offender.lockouts} lockouts.
        </p>
      )}
    </div>
  );
}
