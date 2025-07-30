import React from 'react';
import SuccessfulLogins from '../../login-logs-activity/components/SuccessfulLogins/index';
import Analytics from './Analytics';

const LoginAttemps = () => {
    return (
        <>
            <div className="tpsa-login-attempts-analytics">
                <div className="tpsa-login-attempts-analytics-item">
                    <h2>Successful Logins</h2>
                    <p>
                        1000<sub>Past 24hrs</sub>
                    </p>

                    <div className="tp-details">
                        <a href="#">View Details Analytics</a>
                    </div>
                </div>
                <div className="tpsa-login-attempts-analytics-item">
                    <h2>Failed Logins</h2>
                    <p>
                        10<sub>Past 24hrs</sub>
                    </p>
                    <div className="tp-details">
                        <a href="#">View Details Analytics</a>
                    </div>
                </div>
                <div className="tpsa-login-attempts-analytics-item">
                    <h2>Block users</h2>
                    <p>
                        10<sub>Past 24hrs</sub>
                    </p>
                    <div className="tp-details">
                        <a href="#">View Details Analytics</a>
                    </div>
                </div>
            </div>
        </>
    );
};

export default LoginAttemps;
