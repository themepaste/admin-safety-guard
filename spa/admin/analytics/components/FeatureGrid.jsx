import { useMemo, useState } from 'react';
import {
  Lock,
  ShieldAlert,
  Activity,
  TrendingUp,
  Shield,
  Check,
  X,
  ChevronRight,
  Scan,
} from 'lucide-react';
import './FeatureGrid.css';

const features = [
  {
    id: 'limit-login',
    category: 'security-core',
    name: 'Limit Login Attempts',
    description: 'Prevent brute force attacks',
    status: 'active',
    icon: Lock,
    stats: { value: '156', label: 'Attacks Blocked' },
  },
  {
    id: '2fa',
    category: 'security-core',
    name: 'Two-Factor Authentication',
    description: 'Extra layer of security',
    status: 'active',
    icon: Shield,
    stats: { value: '23', label: 'Users Enabled' },
  },
  {
    id: 'password',
    category: 'security-core',
    name: 'Password Protection',
    description: 'Enforce strong passwords',
    status: 'active',
    icon: Lock,
    stats: { value: '12+', label: 'Min Characters' },
  },
  {
    id: 'recaptcha',
    category: 'security-core',
    name: 'Google reCAPTCHA',
    description: 'Bot protection',
    status: 'active',
    icon: Shield,
    stats: { value: '89', label: 'Bots Blocked' },
  },
  {
    id: 'waf',
    category: 'firewall',
    name: 'Web Application Firewall',
    description: 'Real-time protection',
    status: 'active',
    icon: ShieldAlert,
    stats: { value: '1.2K', label: 'Threats Blocked' },
  },
  {
    id: 'malware',
    category: 'firewall',
    name: 'Malware Scanner',
    description: 'Automated scanning',
    status: 'active',
    icon: Scan,
    stats: { value: '1,423', label: 'Files Scanned' },
  },
  {
    id: 'login-logs',
    category: 'monitoring',
    name: 'Login Logs & Activity',
    description: 'Track all login attempts',
    status: 'active',
    icon: Activity,
    stats: { value: '245', label: 'Today' },
  },
  {
    id: 'analytics',
    category: 'monitoring',
    name: 'Safety Analytics',
    description: 'Security insights',
    status: 'active',
    icon: TrendingUp,
    stats: { value: '98%', label: 'Score' },
  },
];

export default function FeatureGrid({
  // optional: pass your own filtered list if you already have it
  filteredFeatures: filteredFeaturesProp,
}) {
  const [selectedFeature, setSelectedFeature] = useState(null);

  const filteredFeatures = useMemo(() => {
    return filteredFeaturesProp ?? features;
  }, [filteredFeaturesProp]);

  return (
    <div className="fg-grid">
      {filteredFeatures.map((feature) => {
        const FeatureIcon = feature.icon;
        const isSelected = selectedFeature === feature.id;

        return (
          <button
            key={feature.id}
            type="button"
            onClick={() => setSelectedFeature(isSelected ? null : feature.id)}
            className={`fg-card ${isSelected ? 'isSelected' : ''}`}
          >
            <div className="fg-cardInner">
              <div className="fg-topRow">
                <div
                  className={`fg-iconWrap ${isSelected ? 'isSelected' : ''}`}
                >
                  <FeatureIcon
                    className={`fg-icon ${isSelected ? 'isSelected' : ''}`}
                  />
                </div>

                <span
                  className={`fg-badge ${
                    feature.status === 'active' ? 'isActive' : 'isInactive'
                  }`}
                >
                  {feature.status === 'active' ? (
                    <span className="fg-badgeInner">
                      <Check className="fg-badgeIcon" /> Active
                    </span>
                  ) : (
                    <span className="fg-badgeInner">
                      <X className="fg-badgeIcon" /> Inactive
                    </span>
                  )}
                </span>
              </div>

              <h4 className="fg-title">{feature.name}</h4>
              <p className="fg-desc">{feature.description}</p>

              <div className="fg-bottomRow">
                <div>
                  <p className="fg-statValue">{feature.stats.value}</p>
                  <p className="fg-statLabel">{feature.stats.label}</p>
                </div>

                <ChevronRight
                  className={`fg-chevron ${isSelected ? 'isSelected' : ''}`}
                />
              </div>
            </div>

            {isSelected && (
              <div className="fg-configPanel">
                <button type="button" className="fg-primaryBtn">
                  Configure Settings
                </button>
                <button type="button" className="fg-secondaryBtn">
                  View Details
                </button>
              </div>
            )}
          </button>
        );
      })}
    </div>
  );
}
