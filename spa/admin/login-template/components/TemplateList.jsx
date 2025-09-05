import React, { useMemo, useState } from 'react';
import ModalImage from 'react-modal-image';

const TemplateList = () => {
    let initialActive = null;
    try {
        initialActive = JSON.parse(
            document
                .getElementById('tpsa_customize_login-template')
                .value.replace(/'/g, '"')
        )[0].id;
    } catch {
        initialActive = null;
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
        const inputEl = document.getElementById(
            'tpsa_customize_login-template'
        );
        if (!inputEl) return;
        inputEl.value = JSON.stringify([
            { id: template.id, name: template.name },
        ]);
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
    );
};

export default TemplateList;
