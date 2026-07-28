/**
 * Presentation helpers shared by every monitoring table.
 */

/** Parse a MySQL datetime ("2026-04-08 07:15:00") as local time. */
export function parseDate(value) {
  if (!value) return null;
  const date = new Date(String(value).replace(' ', 'T'));
  return Number.isNaN(date.getTime()) ? null : date;
}

/** Absolute, human-readable timestamp. */
export function absoluteTime(value) {
  const date = parseDate(value);
  if (!date) return '—';
  return date.toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

/**
 * "12 minutes ago" — far easier to scan than a full date when you are trying
 * to work out whether an attack is happening right now.
 */
export function relativeTime(value) {
  const date = parseDate(value);
  if (!date) return '—';

  const seconds = Math.round((Date.now() - date.getTime()) / 1000);

  if (seconds < 0) return absoluteTime(value);
  if (seconds < 60) return 'just now';

  const units = [
    ['minute', 60],
    ['hour', 3600],
    ['day', 86400],
    ['week', 604800],
    ['month', 2629800],
    ['year', 31557600],
  ];

  let label = 'minute';
  let size = 60;
  for (let i = 0; i < units.length; i += 1) {
    if (seconds < (units[i + 1] ? units[i + 1][1] : Infinity)) {
      [label, size] = units[i];
      break;
    }
  }

  const amount = Math.floor(seconds / size);
  return `${amount} ${label}${amount === 1 ? '' : 's'} ago`;
}

/**
 * Turn a raw User-Agent into something a human can read.
 *
 * The raw string is still available on hover: a 120-character
 * "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:140.0)..." tells an admin
 * nothing at a glance, but "Firefox 140 on macOS" does.
 */
export function describeUserAgent(raw) {
  const ua = String(raw || '').trim();
  if (!ua || ua === 'Unknown') return { label: 'Unknown', detail: ua };

  // Non-browser clients are the interesting ones on a login log.
  const bots = [
    [/curl\/([\d.]+)/i, 'curl'],
    [/wget/i, 'Wget'],
    [/python-requests\/([\d.]+)/i, 'Python requests'],
    [/python-urllib/i, 'Python urllib'],
    [/go-http-client/i, 'Go HTTP client'],
    [/postman/i, 'Postman'],
    [/^wordpress\//i, 'WordPress (server)'],
    [/bot|crawler|spider|scrapy/i, 'Bot / crawler'],
  ];
  for (const [pattern, name] of bots) {
    const match = ua.match(pattern);
    if (match) {
      return {
        label: match[1] ? `${name} ${match[1]}` : name,
        detail: ua,
        suspicious: true,
      };
    }
  }

  // Order matters: Edge and Chrome both claim "Chrome", Chrome claims "Safari".
  const browsers = [
    [/Edg(?:e|A|iOS)?\/([\d]+)/, 'Edge'],
    [/OPR\/([\d]+)/, 'Opera'],
    [/Firefox\/([\d]+)/, 'Firefox'],
    [/FxiOS\/([\d]+)/, 'Firefox'],
    [/CriOS\/([\d]+)/, 'Chrome'],
    [/Chrome\/([\d]+)/, 'Chrome'],
    [/Version\/([\d]+).*Safari/, 'Safari'],
    [/Safari\/([\d]+)/, 'Safari'],
    [/MSIE ([\d]+)/, 'Internet Explorer'],
    [/Trident\/.*rv:([\d]+)/, 'Internet Explorer'],
  ];

  let browser = '';
  for (const [pattern, name] of browsers) {
    const match = ua.match(pattern);
    if (match) {
      browser = `${name} ${match[1]}`;
      break;
    }
  }

  const platforms = [
    [/Windows NT 10/, 'Windows'],
    [/Windows NT/, 'Windows'],
    [/iPhone|iPad|iPod/, 'iOS'],
    [/Android/, 'Android'],
    [/Mac OS X|Macintosh/, 'macOS'],
    [/CrOS/, 'ChromeOS'],
    [/Linux/, 'Linux'],
  ];

  let platform = '';
  for (const [pattern, name] of platforms) {
    if (pattern.test(ua)) {
      platform = name;
      break;
    }
  }

  if (!browser && !platform) return { label: 'Unrecognised client', detail: ua, suspicious: true };
  if (!browser) return { label: platform, detail: ua };
  if (!platform) return { label: browser, detail: ua };

  return { label: `${browser} on ${platform}`, detail: ua };
}

/** Compact integer display. */
export function formatCount(value) {
  const number = Number(value || 0);
  if (!Number.isFinite(number)) return '0';
  if (number < 1000) return String(number);
  if (number < 1000000) return `${(number / 1000).toFixed(number < 10000 ? 1 : 0)}k`;
  return `${(number / 1000000).toFixed(1)}m`;
}
