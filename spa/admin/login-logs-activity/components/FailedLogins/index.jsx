import React, { useState, useEffect } from 'react';

const FailedLogins = () => {
    const [loginData, setLoginData] = useState([]);
    const [totalEntries, setTotalEntries] = useState(0);
    const [currentPage, setCurrentPage] = useState(1);
    const [searchTerm, setSearchTerm] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const [itemsPerPage, setItemsPerPage] = useState(3);

    const formatDate = (dateString) => {
        const date = new Date(dateString.replace(' ', 'T'));
        return date.toLocaleString('en-US', {
            month: 'long', // July
            day: 'numeric', // 3
            year: 'numeric', // 2025
            hour: 'numeric', // 11
            minute: '2-digit', // 17
            hour12: true, // am/pm
        });
    };

    const fetchData = async () => {
        setLoading(true);
        setError(null);
        try {
            const params = new URLSearchParams({
                page: currentPage,
                limit: itemsPerPage,
            });

            // Add search param only if searchTerm is not empty
            if (searchTerm.trim() !== '') {
                params.append('s', searchTerm.trim());
            }

            const response = await fetch(
                `${
                    tpsaAdmin.rest_url
                }secure-admin/v1/failed-logins?${params.toString()}`,
                {
                    method: 'GET',
                }
            );

            if (!response.ok) throw new Error('Failed to fetch data');

            const json = await response.json();

            setLoginData(json.data || []);
            setTotalEntries(json.total || 0);
        } catch (err) {
            setError(err.message || 'Unknown error');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchData();
    }, [currentPage, searchTerm, itemsPerPage]);

    const totalPages = Math.ceil(totalEntries / itemsPerPage);

    const handlePrevPage = () => {
        if (currentPage > 1) setCurrentPage(currentPage - 1);
    };

    const handleNextPage = () => {
        if (currentPage < totalPages) setCurrentPage(currentPage + 1);
    };

    return (
        <div>
            <h1>Failed Logins</h1>

            <input
                type="text"
                placeholder="Search..."
                value={searchTerm}
                onChange={(e) => {
                    setSearchTerm(e.target.value);
                    setCurrentPage(1);
                }}
                style={{
                    marginBottom: '10px',
                    padding: '8px',
                    width: '300px',
                    fontSize: '16px',
                }}
            />

            <div>
                <label>Items per page: </label>
                <select
                    value={itemsPerPage} // bind to state
                    onChange={(e) => setItemsPerPage(Number(e.target.value))} // convert string to number
                >
                    <option value="1">1</option>
                    <option value="3">3</option>
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            {loading ? (
                <p>Loading...</p>
            ) : error ? (
                <p style={{ color: 'red' }}>Error: {error}</p>
            ) : (
                <>
                    <table className="tpsa-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>User Agent</th>
                                <th>IP Address</th>
                                <th>City</th>
                                <th>Country</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loginData.length > 0 ? (
                                loginData.map((login) => (
                                    <tr key={login.id}>
                                        <td>{login.id}</td>
                                        <td>{login.username}</td>
                                        <td>{login.user_agent}</td>
                                        <td>{login.ip_address}</td>
                                        <td>{login.city}</td>
                                        <td>{login.country}</td>
                                        <td>{formatDate(login.login_time)}</td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="4">No results found</td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    {/* <div style={{ marginTop: '10px' }}>
                        <span>
                            Showing {itemsPerPage} of {totalEntries} entries
                        </span>
                    </div> */}

                    <div style={{ marginTop: '10px' }}>
                        <button
                            onClick={handlePrevPage}
                            disabled={currentPage === 1}
                        >
                            Previous
                        </button>
                        <span style={{ margin: '0 10px' }}>
                            Page {currentPage} of {totalPages || 1}
                        </span>
                        <button
                            onClick={handleNextPage}
                            disabled={
                                currentPage === totalPages || totalEntries === 0
                            }
                        >
                            Next
                        </button>
                    </div>
                </>
            )}
        </div>
    );
};

export default FailedLogins;
