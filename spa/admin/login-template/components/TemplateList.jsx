import React from 'react';
import ModalImage from 'react-modal-image';

const TemplateList = () => {
    const smalImg = 'https://placehold.co/200x200';
    const bigImg = 'https://placehold.co/800x600';
    const templateList = [
        {
            id: 1,
            name: 'Template 1',
            screenshotSmall: smalImg,
            screenshot: bigImg,
        },
        {
            id: 2,
            name: 'Template 2',
            screenshotSmall: smalImg,
            screenshot: bigImg,
        },
        {
            id: 3,
            name: 'Template 3',
            screenshotSmall: smalImg,
            screenshot: bigImg,
        },
        {
            id: 4,
            name: 'Template 4',
            screenshotSmall: smalImg,
            screenshot: bigImg,
        },
    ];

    return (
        <div className="template-list">
            {templateList.map((template) => (
                <div className="single-template" key={template.id}>
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
