import React, { useState } from 'react';
import { Copy, Check } from 'lucide-react';

/** IP address with one-click copy, for pasting into a block list or lookup. */
export default function IpCell({ ip }) {
  const [copied, setCopied] = useState(false);

  if (!ip) return <span className="tpsa-log__muted">—</span>;

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(ip);
      setCopied(true);
      setTimeout(() => setCopied(false), 1500);
    } catch (e) {
      /* clipboard unavailable — the address is still selectable */
    }
  };

  return (
    <span className="tpsa-log__ip">
      <code>{ip}</code>
      <button
        type="button"
        onClick={copy}
        className="tpsa-log__copy"
        title={copied ? 'Copied' : 'Copy address'}
        aria-label={copied ? 'Copied' : `Copy ${ip}`}
      >
        {copied ? <Check aria-hidden="true" /> : <Copy aria-hidden="true" />}
      </button>
    </span>
  );
}
