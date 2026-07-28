import React from 'react';
import LogTable from '../shared/LogTable';
import UserAgentCell from '../shared/UserAgentCell';
import TimeCell from '../shared/TimeCell';
import IpCell from '../shared/IpCell';

const limitLoginUrl = `${tpsaAdmin.admin_url}admin.php?page=tp-admin-safety-guard&tab=security-core&tpsa-setting=limit-login-attempts`;

const columns = [
  {
    key: 'username',
    label: 'Attempted account',
    render: (row) => (
      <span className="tpsa-log__account">
        <strong>{row.username || <em className="tpsa-log__muted">(blank)</em>}</strong>
      </span>
    ),
  },
  { key: 'ip_address', label: 'IP address', render: (row) => <IpCell ip={row.ip_address} /> },
  {
    key: 'login_attempts',
    label: 'Attempts / lockouts',
    render: (row) => (
      <span>
        <strong>{row.login_attempts}</strong>
        <span className="tpsa-log__muted"> / </span>
        <span className={Number(row.lockouts) > 0 ? 'tpsa-log__flag is-danger' : 'tpsa-log__muted'}>
          {row.lockouts}
        </span>
      </span>
    ),
  },
  { key: 'user_agent', label: 'Device', render: (row) => <UserAgentCell raw={row.user_agent} /> },
  { key: 'last_login_time', label: 'Last attempt', render: (row) => <TimeCell value={row.last_login_time} /> },
];

export default function FailedLogins({ onChanged }) {
  return (
    <>
      {!tpsaAdmin.limit_login && (
        <div className="tpsa-log__notice is-warn">
          Brute-force protection is off, so these attempts are recorded but never
          blocked. <a href={limitLoginUrl}>Enable it now</a>
        </div>
      )}
      <LogTable
        title="Failed sign-ins"
        endpoint="failed-logins"
        exportType="failed"
        columns={columns}
        action="block"
        onChanged={onChanged}
        emptyMessage="No failed sign-ins recorded."
        emptyHint="That is good news — nobody has been guessing passwords on this site."
      />
    </>
  );
}
