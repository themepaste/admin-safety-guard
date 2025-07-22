// QuestionMarkTooltip.js
import React from 'react';
import './style.css';

const QuestionMarkTooltip = ({ message }) => {
    return (
        <span className="wp-tooltip-wrapper">
            <span className="wp-tooltip-icon">?</span>
            <span className="wp-tooltip-text">{message}</span>
        </span>
    );
};

export default QuestionMarkTooltip;
