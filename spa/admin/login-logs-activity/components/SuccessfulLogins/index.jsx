import React from 'react';
import LogTable from '../shared/LogTable';
import UserAgentCell from '../shared/UserAgentCell';
import TimeCell from '../shared/TimeCell';
import IpCell from '../shared/IpCell';

const columns = [
  {
    key: 'username',
    label: 'Account',
    render: (row) => (
      <span className="tpsa-log__account">
        <strong>{row.username}</strong>
        {Number(row.login_count) > 1 && (
          <span className="tpsa-log__muted"> · {row.login_count} sign-ins</span>
        )}
        {Number(row.is_new_ip) === 1 && (
          <span className="tpsa-log__flag" title="This account had not signed in from this IP address before">
            New location
          </span>
        )}
      </span>
    ),
  },
  { key: 'ip_address', label: 'IP address', render: (row) => <IpCell ip={row.ip_address} /> },
  { key: 'user_agent', label: 'Device', render: (row) => <UserAgentCell raw={row.user_agent} /> },
  { key: 'login_time', label: 'When', render: (row) => <TimeCell value={row.login_time} /> },
];

export default function SuccessfulLogins({ onChanged }) {
  return (
    <LogTable
      title="Successful sign-ins"
      endpoint="success-logins"
      exportType="success"
      columns={columns}
      action="block"
      onChanged={onChanged}
      emptyMessage="No sign-ins recorded yet."
      emptyHint="Every successful sign-in is logged here, including the device and location used."
    />
  );
}
