import React, { useEffect } from 'react';
import ModalImage from 'react-modal-image';

const TemplateList = () => {
    const smalImg = 'https://placehold.co/200x200';
    const bigImg = 'https://placehold.co/800x600';
    const templateList = [
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
    ];

    const handleUseTemplate = (template) => {
        const inputEl = document.getElementById(
            'tpsa_customize_login-template'
        );
        if (!inputEl) return;

        let currentValues = [];
        try {
            currentValues = JSON.parse(inputEl.value) || [];
            if (typeof currentValues === 'string') {
                // double-encoded case
                currentValues = JSON.parse(currentValues);
            }
        } catch {
            currentValues = [];
        }

        if (!Array.isArray(currentValues)) currentValues = [];

        // add if not exists
        if (!currentValues.some((item) => item.id === template.id)) {
            currentValues.push({ id: template.id, name: template.name });
        }

        inputEl.value = JSON.stringify(currentValues);
    };

    // useEffect(() => {
    //     const a = document.getElementById(
    //         'tpsa_customize_login-template'
    //     )?.value;
    //     console.log(a);
    // }, []);

    return (
        <div className="template-list">
            {templateList.map((template, index) => (
                <div className="single-template" key={index}>
                    <ModalImage
                        className="template-screenshot"
                        small={template.screenshotSmall}
                        large={template.screenshot}
                        alt={template.name}
                        hideDownload={true} // show download button
                        hideZoom={true}
                    />
                    <div className="template-content">
                        <h3>{template.name}</h3>
                        <button
                            type="button"
                            className="button-primary use-template"
                            onClick={() => handleUseTemplate(template)}
                        >
                            Use Template
                        </button>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default TemplateList;
