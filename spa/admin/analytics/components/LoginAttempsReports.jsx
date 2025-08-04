import React, { useEffect, useState } from 'react';

const LoginAttempsReports = () => {
    const [loginData, setLoginData] = useState(null);
    const [loading, setLoading] = useState(true);

    const url =
        tpsaAdmin.admin_url +
        'admin.php?page=tp-admin-safety-guard&tpsa-setting=login-logs-activity';

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
            } finally {
                setLoading(false);
            }
        };

        fetchLoginCounts();
    }, []);

    const renderCount = (key) => {
        if (loading)
            return <span className="loading-placeholder">Loading...</span>;
        return (
            <>
                {loginData?.[key] ?? 0}
                <sub> Past 24hrs</sub>
            </>
        );
    };

    return (
        <div className="tpsa-login-attempts-analytics">
            <div className="tpsa-login-attempts-analytics-item">
                <h2>Successful Logins</h2>
                <p>{renderCount('s_logins')}</p>
                <div className="tp-details">
                    <a href={url + '#SuccessfulLogins'}>View Details</a>
                </div>
            </div>
            <div className="tpsa-login-attempts-analytics-item">
                <h2>Failed Logins</h2>
                <p>{renderCount('failed_logins')}</p>
                <div className="tp-details">
                    <a href={url + '#FailedLogins'}>View Details</a>
                </div>
            </div>
            <div className="tpsa-login-attempts-analytics-item">
                <h2>Blocked Users</h2>
                <p>{renderCount('block_users')}</p>
                <div className="tp-details">
                    <a href={url + '#BlockUsers'}>View Details</a>
                </div>
            </div>
        </div>
    );
};

export default LoginAttempsReports;
