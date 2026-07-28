import React from 'react';
import LogTable from '../shared/LogTable';
import UserAgentCell from '../shared/UserAgentCell';
import TimeCell from '../shared/TimeCell';
import IpCell from '../shared/IpCell';

const columns = [
  { key: 'ip_address', label: 'Blocked address', render: (row) => <IpCell ip={row.ip_address} /> },
  { key: 'user_agent', label: 'Device', render: (row) => <UserAgentCell raw={row.user_agent} /> },
  { key: 'login_time', label: 'Blocked at', render: (row) => <TimeCell value={row.login_time} /> },
];

export default function BlockUsers({ onChanged }) {
  return (
    <LogTable
      title="Blocked addresses"
      endpoint="block-users"
      exportType="blocked"
      columns={columns}
      action="release"
      onChanged={onChanged}
      emptyMessage="No addresses are blocked."
      emptyHint="Addresses land here automatically after repeated lockouts, and are released again by the daily cleanup task."
    />
  );
}
