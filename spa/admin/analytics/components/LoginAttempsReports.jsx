import React, { useEffect, useState } from 'react';

const LoginAttempsReports = () => {
    const [loginData, setLoginData] = useState({
        s_logins: 0,
        failed_logins: 0,
        block_users: 0,
    });

    const url =
        tpsaAdmin.admin_url +
        'admin.php?page=tp-secure-admin&tpsa-setting=login-logs-activity';

    useEffect(() => {
        const fetchLoginCounts = async () => {
            try {
                const response = await fetch(
                    `${tpsaAdmin.rest_url}secure-admin/v1/dahboard/limit-login-attempts?reports=s_logins,failed_logins,block_users`,
                    {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    }
                );
                const data = await response.json();
                setLoginData(data);
            } catch (err) {
                console.error('Fetch error:', err);
            }
        };

        fetchLoginCounts();
    }, []);

    return (
        <div className="tpsa-login-attempts-analytics">
            <div className="tpsa-login-attempts-analytics-item">
                <h2>Successful Logins</h2>
                <p>
                    {loginData.s_logins}
                    <sub> Past 24hrs</sub>
                </p>
                <div className="tp-details">
                    <a href={url + '#BlockUsers'}>View Details</a>
                </div>
            </div>
            <div className="tpsa-login-attempts-analytics-item">
                <h2>Failed Logins</h2>
                <p>
                    {loginData.failed_logins}
                    <sub> Past 24hrs</sub>
                </p>
                <div className="tp-details">
                    <a href={url + '#FailedLogins'}>View Details</a>
                </div>
            </div>
            <div className="tpsa-login-attempts-analytics-item">
                <h2>Blocked Users</h2>
                <p>
                    {loginData.block_users}
                    <sub> Past 24hrs</sub>
                </p>
                <div className="tp-details">
                    <a href={url + '#SuccessfulLogins'}>View Details</a>
                </div>
            </div>
        </div>
    );
};

export default LoginAttempsReports;
