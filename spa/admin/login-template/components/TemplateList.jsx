import React, { useMemo, useState } from 'react';
import ModalImage from 'react-modal-image';

const TemplateList = () => {
    const smalImg = 'https://placehold.co/200x200';
    const bigImg = 'https://placehold.co/800x600';

    // Read once when component mounts
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

    const [activeTemplateId] = useState(initialActive); // fixed "Activated" on load
    const [selectedId, setSelectedId] = useState(null); // changes after user clicks

    const templateList = useMemo(
        () => [
            {
                id: 'template-1',
                name: 'Template 1',
                screenshotSmall: smalImg,
                screenshot: bigImg,
            },
            {
                id: 'template-2',
                name: 'Template 2',
                screenshotSmall: smalImg,
                screenshot: bigImg,
            },
            {
                id: 'template-3',
                name: 'Template 3',
                screenshotSmall: smalImg,
                screenshot: bigImg,
            },
            {
                id: 'template-4',
                name: 'Template 4',
                screenshotSmall: smalImg,
                screenshot: bigImg,
            },
        ],
        []
    );

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
        setSelectedId(template.id); // mark new selection
        updateHiddenInput(template); // sync hidden input
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
