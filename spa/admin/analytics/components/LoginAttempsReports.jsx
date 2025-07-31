import React from 'react';

const LoginAttempsReports = () => {
    const url =
        tpsaAdmin.admin_url +
        'admin.php?page=tp-secure-admin&tpsa-setting=login-logs-activity';
    return (
        <>
            <div className="tpsa-login-attempts-analytics">
                <div className="tpsa-login-attempts-analytics-item">
                    <h2>Successful Logins</h2>
                    <p>
                        0<sub>Past 24hrs</sub>
                    </p>

                    <div className="tp-details">
                        <a href={url + '#BlockUsers'}>View Details</a>
                    </div>
                </div>
                <div className="tpsa-login-attempts-analytics-item">
                    <h2>Failed Logins</h2>
                    <p>
                        0<sub>Past 24hrs</sub>
                    </p>
                    <div className="tp-details">
                        <a href={url + '#FailedLogins'}>View Details</a>
                    </div>
                </div>
                <div className="tpsa-login-attempts-analytics-item">
                    <h2>Blocked users</h2>
                    <p>
                        0<sub>Past 24hrs</sub>
                    </p>
                    <div className="tp-details">
                        <a href={url + '#SuccessfulLogins'}>View Details</a>
                    </div>
                </div>
            </div>
        </>
    );
};

export default LoginAttempsReports;
