import React, {
    useEffect,
    useMemo,
    useState,
    useCallback,
    useRef,
} from 'react';
import ModalImage from 'react-modal-image';

/** ------------------------------
 * Utilities
 * ------------------------------ */
const getHiddenInput = () =>
    document.getElementById('tpsa_customize_login-template');

const parseHiddenJSON = () => {
    const el = getHiddenInput();
    if (!el) return null;
    try {
        const val = el.value?.trim();
        if (!val) return null;
        return JSON.parse(val.replace(/'/g, '"'));
    } catch {
        return null;
    }
};

const stringifyHiddenJSON = (obj) => JSON.stringify(obj);

/** Convert #RRGGBB + opacity 0..1 to rgba() */
const hexToRgba = (hex, opacity = 1) => {
    if (!hex) return `rgba(0,0,0,${opacity})`;
    const h = hex.replace('#', '');
    const full =
        h.length === 3 ? `${h[0]}${h[0]}${h[1]}${h[1]}${h[2]}${h[2]}` : h;
    const r = parseInt(full.slice(0, 2), 16) || 0;
    const g = parseInt(full.slice(2, 4), 16) || 0;
    const b = parseInt(full.slice(4, 6), 16) || 0;
    const a = Math.max(0, Math.min(1, Number(opacity) || 0));
    return `rgba(${r},${g},${b},${a})`;
};

/** Compile CSS string from settings (scoped to preview + usable on real login) */
const compileCSS = (s) => {
    const formBg = hexToRgba(s.form_bg, s.form_opacity);
    const bgCss =
        s.bg_type === 'image'
            ? `url(${s.bg_image}) ${s.bg_position} / ${s.bg_size} no-repeat fixed`
            : s.bg_type === 'gradient'
            ? s.bg_gradient
            : s.bg_color;

    const logoRule = s.logo_url
        ? `#login h1 a{background-image:url(${s.logo_url})!important;width:${s.logo_width}px!important;background-size:contain!important;}`
        : '';

    const layoutRule =
        s.layout === 'full-bleed'
            ? `body.login #login{width:100%;max-width:none;}`
            : '';

    // NOTE: we scope to #cdp-preview (mock) but also work for real login page (no scope)
    return `
/* === Scoped preview variables === */
#cdp-preview, #cdp-preview * { box-sizing: border-box; }
#cdp-preview {
  --cdp-primary:${s.primary_color};
  --cdp-accent:${s.accent_color};
  --cdp-link:${s.link_color};
  --cdp-font:${s.font_family};
  --cdp-form-bg:${formBg};
  --cdp-button-bg:${s.button_bg};
  --cdp-button-text:${s.button_text};
  background:${bgCss};
  font-family:var(--cdp-font);
  min-height: 520px;
  padding:24px;
  display:flex;
  align-items:center;
  justify-content:center;
}
/* Split layouts (preview) */
#cdp-preview .cdp-split {display:grid;grid-template-columns:1fr 1fr;min-height:420px;width:100%;}
#cdp-preview .cdp-illustration{background-size:cover;background-position:center;border-radius:16px;}
#cdp-preview .cdp-illustration--placeholder{background:linear-gradient(135deg,rgba(255,255,255,.08),rgba(255,255,255,.02))}
#cdp-preview .cdp-form-wrap{display:flex;align-items:center;justify-content:center;padding:20px}

/* Mock login card */
#cdp-preview #login{
  width:100%;
  max-width:${s.form_width}px;
  background:var(--cdp-form-bg);
  border-radius:${s.radius}px;
  box-shadow:${s.shadow};
  padding: 28px 24px 20px;
  color:#e5e7eb;
}
#cdp-preview #login h1{margin:0 0 12px 0;}
#cdp-preview #login h1 a{display:block;height:64px;background-repeat:no-repeat;background-position:center;margin:0 auto 8px auto}
${s.hide_wp_logo ? '#cdp-preview #login h1 a{display:none;}' : ''}
${
    s.logo_url
        ? `#cdp-preview #login h1 a{background-image:url(${s.logo_url});width:${s.logo_width}px;background-size:contain;}`
        : ''
}

#cdp-preview #login label{display:block;margin-bottom:6px;font-weight:600;color:#e5e7eb}
#cdp-preview #login .input{
  width:100%;padding:10px 12px;border-radius:10px;margin-bottom:12px;
  border:1px solid rgba(255,255,255,.08);background:#0b1220;color:#e5e7eb
}
#cdp-preview #login .button-primary{
  display:inline-block;padding:10px 16px;border-radius:10px;border:0;cursor:pointer;
  background:var(--cdp-button-bg);color:var(--cdp-button-text)
}
#cdp-preview a{color:var(--cdp-link);text-decoration:none}

/* === Unscoped rules (so this CSS can be used directly on wp-login.php) === */
body.login {
  background:${bgCss} !important;
  font-family:${s.font_family} !important;
}
#login{
  width:100%;
  max-width:${s.form_width}px;
  background:${formBg};
  border-radius:${s.radius}px;
  box-shadow:${s.shadow};
  padding: 32px 28px 24px;
}
${logoRule}
${s.hide_wp_logo ? '#login h1 a{text-indent:-9999px!important;}' : ''}
#login label{color:#e5e7eb;font-weight:600}
#login .input, #login input[type=password]{
  background:#0b1220;border:1px solid rgba(255,255,255,.08);color:#e5e7eb;border-radius:10px;padding:10px 12px
}
#login .button-primary{
  background:${s.button_bg} !important;border:0 !important;color:${
        s.button_text
    } !important;border-radius:10px;padding:10px 16px;text-shadow:none !important;box-shadow:none !important
}
#backtoblog a, #nav a{color:${s.link_color} !important}
.message, .success{background:rgba(255,255,255,.06);border-left:4px solid ${
        s.accent_color
    };color:#e5e7eb}
${layoutRule}
`.trim();
};

/** Default builder settings */
const defaultSettings = {
    name: 'Custom',
    layout: 'centered', // centered | split-left | split-right | full-bleed
    primary_color: '#4F46E5',
    accent_color: '#22C55E',
    link_color: '#2563EB',
    bg_type: 'color', // color | image | gradient
    bg_color: '#0F172A',
    bg_gradient: 'linear-gradient(135deg,#0f172a 0%,#1e293b 50%,#111827 100%)',
    bg_image: '',
    bg_size: 'cover',
    bg_position: 'center',
    logo_url: '',
    logo_width: 180,
    form_bg: '#111827',
    form_opacity: 0.9,
    form_width: 420,
    radius: 16,
    shadow: '0 10px 30px rgba(0,0,0,.35)',
    font_family:
        'Inter,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,"Helvetica Neue",Arial',
    button_bg: '#4F46E5',
    button_text: '#FFFFFF',
    hide_wp_logo: 1,
    illustration: '',
};

/** ------------------------------
 * TemplateList (unchanged behavior)
 * ------------------------------ */
const TemplateList = () => {
    // Read initial "Activated" (from hidden JSON)
    let initialActive = null;
    const parsed = parseHiddenJSON();
    if (Array.isArray(parsed) && parsed[0]?.id) {
        initialActive = parsed[0].id;
    }

    const [activeTemplateId] = useState(initialActive);
    const [selectedId, setSelectedId] = useState(null);

    // Convert tpsaAdmin.login_templates to array
    const templateList = useMemo(() => {
        if (!window.tpsaAdmin || !tpsaAdmin.login_templates) return [];
        return Object.entries(tpsaAdmin.login_templates).map(([id, tpl]) => ({
            id, // "classic", "default", "glass"
            name: tpl.label, // from "label"
            screenshotSmall: tpl.smalImg, // use given smalImg
            screenshot: tpl.bigImg, // use given bigImg
        }));
    }, []);

    const updateHiddenInput = (template) => {
        const inputEl = getHiddenInput();
        if (!inputEl) return;
        inputEl.value = stringifyHiddenJSON([
            { id: template.id, name: template.name },
        ]);
        inputEl.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const handleUseTemplate = (template) => {
        setSelectedId(template.id);
        updateHiddenInput(template);
    };

    const getButtonLabel = (templateId) => {
        if (selectedId) {
            return selectedId === templateId ? 'Selected' : 'Select Template';
        }
        if (activeTemplateId && activeTemplateId === templateId)
            return 'Activated';
        return 'Select Template';
    };

    return (
        <>
            <h3 style={{ marginTop: 0 }}>Ready made templates</h3>
            <div className="template-list">
                {templateList.map((template) => (
                    <div className="single-template" key={template.id}>
                        <ModalImage
                            className="template-screenshot"
                            small={template.screenshotSmall}
                            large={template.screenshot}
                            alt={template.name}
                            hideDownload
                            hideZoom
                        />
                        <div className="template-content">
                            <h3>{template.name}</h3>
                            <button
                                type="button"
                                className="button-primary use-template"
                                onClick={() => handleUseTemplate(template)}
                            >
                                {getButtonLabel(template.id)}
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
};

/** ------------------------------
 * CustomTemplateBuilder
 * ------------------------------ */
const CustomTemplateBuilder = () => {
    // Seed from hidden JSON if it already contains a custom object
    const existing = parseHiddenJSON();
    const existingCustom =
        Array.isArray(existing) && existing[0]?.id === 'custom'
            ? existing[0]
            : null;

    const [settings, setSettings] = useState(
        existingCustom?.settings
            ? { ...defaultSettings, ...existingCustom.settings }
            : defaultSettings
    );
    const [name, setName] = useState(existingCustom?.name || 'Custom');
    const css = useMemo(() => compileCSS(settings), [settings]);

    // Sync to hidden input whenever name/settings/css change
    useEffect(() => {
        const inputEl = getHiddenInput();
        if (!inputEl) return;
        const payload = [
            {
                id: 'custom',
                name: name || 'Custom',
                type: 'custom',
                version: '1.0',
                settings,
                css,
            },
        ];
        inputEl.value = stringifyHiddenJSON(payload);
        inputEl.dispatchEvent(new Event('change', { bubbles: true }));
    }, [name, settings, css]);

    // Optional iframe preview (same-origin only). We’ll try to inject CSS into it.
    const iframeRef = useRef(null);
    const injectCssIntoIframe = useCallback(() => {
        const iframe = iframeRef.current;
        if (!iframe) return;
        try {
            const doc =
                iframe.contentDocument || iframe.contentWindow?.document;
            if (!doc) return;
            let styleTag = doc.getElementById('cdp-live-style');
            if (!styleTag) {
                styleTag = doc.createElement('style');
                styleTag.id = 'cdp-live-style';
                doc.head.appendChild(styleTag);
            }
            styleTag.textContent = css;
        } catch {
            // cross-origin or not loaded yet: safely ignore
        }
    }, [css]);

    // Re-run CSS injection on iframe load & whenever css changes
    useEffect(() => {
        injectCssIntoIframe();
    }, [injectCssIntoIframe]);

    const onIframeLoad = () => injectCssIntoIframe();

    // WP Media helpers (if available)
    const pickMedia = (key) => {
        if (!window.wp || !window.wp.media) return;
        const frame = window.wp.media({
            title: 'Select or Upload',
            multiple: false,
        });
        frame.on('select', () => {
            const url = frame.state().get('selection').first().toJSON().url;
            setSettings((s) => ({ ...s, [key]: url }));
        });
        frame.open();
    };

    const handleChange = (key, value) =>
        setSettings((s) => ({ ...s, [key]: value }));

    const exportJSON = () => {
        const blob = new Blob(
            [
                JSON.stringify(
                    [
                        {
                            id: 'custom',
                            name: name || 'Custom',
                            type: 'custom',
                            version: '1.0',
                            settings,
                            css,
                        },
                    ],
                    null,
                    2
                ),
            ],
            { type: 'application/json' }
        );
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `login-custom-template.json`;
        a.click();
        URL.revokeObjectURL(url);
    };

    const importJSON = (file) => {
        const reader = new FileReader();
        reader.onload = () => {
            try {
                const parsed = JSON.parse(reader.result);
                const obj = Array.isArray(parsed) ? parsed[0] : parsed;
                if (obj?.id === 'custom') {
                    setName(obj.name || 'Custom');
                    if (obj.settings)
                        setSettings((s) => ({ ...s, ...obj.settings }));
                }
            } catch (e) {
                alert('Invalid JSON');
            }
        };
        reader.readAsText(file);
    };

    // Build a simple mock preview tree that we can always style
    const MockPreview = () => {
        const split =
            settings.layout === 'split-left' ||
            settings.layout === 'split-right';

        const Illustration = (
            <div
                className={`cdp-illustration ${
                    settings.illustration ? '' : 'cdp-illustration--placeholder'
                }`}
                style={{
                    backgroundImage: settings.illustration
                        ? `url(${settings.illustration})`
                        : undefined,
                    minHeight: 420,
                }}
            />
        );

        const Card = (
            <div id="login">
                {/* <h1>
                    <a href="#" title="Site" />
                </h1> */}
                <form>
                    <p>
                        <label>Username or Email Address</label>
                        <input
                            className="input"
                            type="text"
                            placeholder="admin"
                        />
                    </p>
                    <p>
                        <label>Password</label>
                        <input
                            className="input"
                            type="password"
                            placeholder="••••••••"
                        />
                    </p>
                    <p>
                        <button className="button-primary" type="button">
                            Log In
                        </button>
                    </p>
                    <p style={{ marginTop: 8 }}>
                        <a href="#">Lost your password?</a>
                    </p>
                </form>
            </div>
        );

        if (!split) {
            return (
                <div id="cdp-preview" className={`cdp-${settings.layout}`}>
                    <div className="cdp-form-wrap">{Card}</div>
                </div>
            );
        }
        const left = settings.layout === 'split-left';
        return (
            <div
                id="cdp-preview"
                className={`cdp-split cdp-${settings.layout}`}
            >
                {left ? (
                    Illustration
                ) : (
                    <div className="cdp-form-wrap">{Card}</div>
                )}
                {left ? (
                    <div className="cdp-form-wrap">{Card}</div>
                ) : (
                    Illustration
                )}
            </div>
        );
    };

    return (
        <div
            className="custom-template-builder"
            style={{
                display: 'grid',
                gridTemplateColumns: '420px 1fr',
                gap: 24,
            }}
        >
            {/* Controls */}
            <div>
                <h3 style={{ marginTop: 0 }}>Custom Builder</h3>

                <label className="field">
                    <div>Template Name</div>
                    <input
                        type="text"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        placeholder="Custom"
                    />
                </label>

                <label className="field">
                    <div>Layout</div>
                    <select
                        value={settings.layout}
                        onChange={(e) => handleChange('layout', e.target.value)}
                    >
                        <option value="centered">Centered</option>
                        <option value="split-left">Split Left</option>
                        <option value="split-right">Split Right</option>
                        <option value="full-bleed">Full Bleed</option>
                    </select>
                </label>

                <fieldset className="field">
                    <legend>Background</legend>
                    <label>
                        <input
                            type="radio"
                            name="bg_type"
                            value="color"
                            checked={settings.bg_type === 'color'}
                            onChange={(e) =>
                                handleChange('bg_type', e.target.value)
                            }
                        />{' '}
                        Color
                    </label>
                    <label style={{ marginLeft: 12 }}>
                        <input
                            type="radio"
                            name="bg_type"
                            value="image"
                            checked={settings.bg_type === 'image'}
                            onChange={(e) =>
                                handleChange('bg_type', e.target.value)
                            }
                        />{' '}
                        Image
                    </label>
                    <label style={{ marginLeft: 12 }}>
                        <input
                            type="radio"
                            name="bg_type"
                            value="gradient"
                            checked={settings.bg_type === 'gradient'}
                            onChange={(e) =>
                                handleChange('bg_type', e.target.value)
                            }
                        />{' '}
                        Gradient
                    </label>

                    {settings.bg_type === 'color' && (
                        <div style={{ marginTop: 8 }}>
                            <div>Background Color</div>
                            <input
                                type="color"
                                value={settings.bg_color}
                                onChange={(e) =>
                                    handleChange('bg_color', e.target.value)
                                }
                            />
                        </div>
                    )}

                    {settings.bg_type === 'image' && (
                        <div style={{ marginTop: 8 }}>
                            <div>Background Image URL</div>
                            <div style={{ display: 'flex', gap: 8 }}>
                                <input
                                    type="text"
                                    value={settings.bg_image}
                                    onChange={(e) =>
                                        handleChange('bg_image', e.target.value)
                                    }
                                    placeholder="https://…"
                                />
                                <button
                                    type="button"
                                    className="button"
                                    onClick={() => pickMedia('bg_image')}
                                >
                                    Choose
                                </button>
                            </div>
                            <div
                                style={{
                                    marginTop: 8,
                                    display: 'flex',
                                    gap: 8,
                                }}
                            >
                                <select
                                    value={settings.bg_size}
                                    onChange={(e) =>
                                        handleChange('bg_size', e.target.value)
                                    }
                                >
                                    <option value="cover">cover</option>
                                    <option value="contain">contain</option>
                                    <option value="auto">auto</option>
                                </select>
                                <input
                                    type="text"
                                    value={settings.bg_position}
                                    onChange={(e) =>
                                        handleChange(
                                            'bg_position',
                                            e.target.value
                                        )
                                    }
                                    placeholder="center / top / 50% 50%"
                                />
                            </div>
                        </div>
                    )}

                    {settings.bg_type === 'gradient' && (
                        <div style={{ marginTop: 8 }}>
                            <div>Gradient CSS</div>
                            <input
                                type="text"
                                value={settings.bg_gradient}
                                onChange={(e) =>
                                    handleChange('bg_gradient', e.target.value)
                                }
                                placeholder="linear-gradient(...)"
                            />
                        </div>
                    )}
                </fieldset>

                <fieldset className="field">
                    <label style={{ display: 'block', marginTop: 8 }}>
                        <input
                            type="checkbox"
                            checked={!!settings.hide_wp_logo}
                            onChange={(e) =>
                                handleChange(
                                    'hide_wp_logo',
                                    e.target.checked ? 1 : 0
                                )
                            }
                        />{' '}
                        Hide WP Logo
                    </label>
                </fieldset>

                <fieldset className="field">
                    <legend>Form Box</legend>
                    <div
                        style={{
                            display: 'grid',
                            gridTemplateColumns: '1fr 1fr',
                            gap: 8,
                        }}
                    >
                        <label>
                            Form BG
                            <input
                                type="color"
                                value={settings.form_bg}
                                onChange={(e) =>
                                    handleChange('form_bg', e.target.value)
                                }
                            />
                        </label>
                        <label>
                            Opacity (0–1)
                            <input
                                type="number"
                                min="0"
                                max="1"
                                step="0.05"
                                value={settings.form_opacity}
                                onChange={(e) =>
                                    handleChange(
                                        'form_opacity',
                                        Number(e.target.value)
                                    )
                                }
                            />
                        </label>
                        <label>
                            Width (px)
                            <input
                                type="number"
                                value={settings.form_width}
                                onChange={(e) =>
                                    handleChange(
                                        'form_width',
                                        Number(e.target.value)
                                    )
                                }
                            />
                        </label>
                        <label>
                            Radius (px)
                            <input
                                type="number"
                                value={settings.radius}
                                onChange={(e) =>
                                    handleChange(
                                        'radius',
                                        Number(e.target.value)
                                    )
                                }
                            />
                        </label>
                    </div>
                    <div style={{ marginTop: 8 }}>
                        Shadow (CSS)
                        <input
                            type="text"
                            value={settings.shadow}
                            onChange={(e) =>
                                handleChange('shadow', e.target.value)
                            }
                            placeholder="0 10px 30px rgba(0,0,0,.35)"
                        />
                    </div>
                </fieldset>

                <fieldset className="field">
                    <legend>Colors & Typography</legend>
                    <div
                        style={{
                            display: 'grid',
                            gridTemplateColumns: '1fr 1fr',
                            gap: 8,
                        }}
                    >
                        <label>
                            Primary
                            <input
                                type="color"
                                value={settings.primary_color}
                                onChange={(e) =>
                                    handleChange(
                                        'primary_color',
                                        e.target.value
                                    )
                                }
                            />
                        </label>
                        <label>
                            Accent
                            <input
                                type="color"
                                value={settings.accent_color}
                                onChange={(e) =>
                                    handleChange('accent_color', e.target.value)
                                }
                            />
                        </label>
                        <label>
                            Link
                            <input
                                type="color"
                                value={settings.link_color}
                                onChange={(e) =>
                                    handleChange('link_color', e.target.value)
                                }
                            />
                        </label>
                        <label>
                            Button BG
                            <input
                                type="color"
                                value={settings.button_bg}
                                onChange={(e) =>
                                    handleChange('button_bg', e.target.value)
                                }
                            />
                        </label>
                        <label>
                            Button Text
                            <input
                                type="color"
                                value={settings.button_text}
                                onChange={(e) =>
                                    handleChange('button_text', e.target.value)
                                }
                            />
                        </label>
                    </div>
                    <div style={{ marginTop: 8 }}>
                        Font stack
                        <input
                            type="text"
                            value={settings.font_family}
                            onChange={(e) =>
                                handleChange('font_family', e.target.value)
                            }
                            placeholder='Inter,system-ui,-apple-system,"Segoe UI",Roboto,Ubuntu,"Helvetica Neue",Arial'
                        />
                    </div>
                </fieldset>

                <div style={{ display: 'flex', gap: 8, marginTop: 12 }}>
                    <button
                        type="button"
                        className="button button-secondary"
                        onClick={exportJSON}
                    >
                        Export JSON
                    </button>
                    <label className="button">
                        Import JSON
                        <input
                            type="file"
                            accept="application/json"
                            onChange={(e) =>
                                e.target.files?.[0] &&
                                importJSON(e.target.files[0])
                            }
                            style={{ display: 'none' }}
                        />
                    </label>
                    <button
                        type="button"
                        className="button"
                        onClick={() => {
                            setName('Custom');
                            setSettings(defaultSettings);
                        }}
                    >
                        Reset
                    </button>
                </div>
            </div>

            {/* Preview column */}
            <div>
                <h3>Preview</h3>
                <style>{css}</style>
                {/* Mock preview (always works) */}
                <MockPreview />
            </div>
        </div>
    );
};

/** ------------------------------
 * TemplateStudio (Tabs wrapper)
 * ------------------------------ */
const TemplateStudio = () => {
    const [tab, setTab] = useState('ready'); // ready | custom

    // If hidden JSON already contains a custom template, default to custom tab
    useEffect(() => {
        const payload = parseHiddenJSON();
        if (Array.isArray(payload) && payload[0]?.id === 'custom') {
            setTab('custom');
        }
    }, []);

    return (
        <div className="template-studio">
            <div className="tabs" style={{ marginBottom: 12 }}>
                <button
                    type="button"
                    className={`tpsa-button-primary ${
                        tab === 'ready' ? 'active' : ''
                    }`}
                    onClick={() => setTab('ready')}
                    style={{ marginRight: 8 }}
                >
                    Ready-made Templates
                </button>
                <button
                    type="button"
                    className={`tpsa-button-primary ${
                        tab === 'custom' ? 'active' : ''
                    }`}
                    onClick={() => setTab('custom')}
                >
                    Custom Builder
                </button>
            </div>

            {tab === 'ready' ? <TemplateList /> : <CustomTemplateBuilder />}
        </div>
    );
};

export default TemplateStudio;
