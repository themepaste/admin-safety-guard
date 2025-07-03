import React, { useState } from 'react';

const SuccessfulLogins = () => {
    const loginData = [
        {
            id: 1,
            username: 'john_doe',
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ipAddress: '192.168.0.1',
        },
        {
            id: 2,
            username: 'jane_smith',
            userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            ipAddress: '192.168.0.2',
        },
        {
            id: 3,
            username: 'admin_user',
            userAgent: 'Mozilla/5.0 (Linux; Android 10)',
            ipAddress: '10.0.0.5',
        },
        {
            id: 4,
            username: 'test_user',
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)',
            ipAddress: '172.16.0.3',
        },
        {
            id: 5,
            username: 'demo_user',
            userAgent: 'Mozilla/5.0 (Windows NT 11)',
            ipAddress: '203.0.113.55',
        },
    ];

    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 2;

    const filteredData = loginData.filter((item) =>
        Object.values(item).some((val) =>
            String(val).toLowerCase().includes(searchTerm.toLowerCase())
        )
    );

    const totalEntries = filteredData.length;
    const totalPages = Math.ceil(totalEntries / itemsPerPage);

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalEntries);
    const paginatedData = filteredData.slice(startIndex, endIndex);

    const handlePrevPage = () => {
        if (currentPage > 1) setCurrentPage(currentPage - 1);
    };

    const handleNextPage = () => {
        if (currentPage < totalPages) setCurrentPage(currentPage + 1);
    };

    return (
        <div>
            <h1>Successful Logins</h1>

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

            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                <thead>
                    <tr>
                        <th style={thStyle}>ID</th>
                        <th style={thStyle}>Username</th>
                        <th style={thStyle}>User Agent</th>
                        <th style={thStyle}>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    {paginatedData.length > 0 ? (
                        paginatedData.map((login) => (
                            <tr key={login.id}>
                                <td style={tdStyle}>{login.id}</td>
                                <td style={tdStyle}>{login.username}</td>
                                <td style={tdStyle}>{login.userAgent}</td>
                                <td style={tdStyle}>{login.ipAddress}</td>
                            </tr>
                        ))
                    ) : (
                        <tr>
                            <td colSpan="4" style={tdStyle}>
                                No results found
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>

            <div style={{ marginTop: '10px' }}>
                <span>
                    Showing {totalEntries === 0 ? 0 : startIndex + 1} to{' '}
                    {endIndex} of {totalEntries} entries
                </span>
            </div>

            <div style={{ marginTop: '10px' }}>
                <button
                    onClick={handlePrevPage}
                    disabled={currentPage === 1}
                    style={buttonStyle}
                >
                    Previous
                </button>
                <span style={{ margin: '0 10px' }}>
                    Page {currentPage} of {totalPages || 1}
                </span>
                <button
                    onClick={handleNextPage}
                    disabled={currentPage === totalPages || totalEntries === 0}
                    style={buttonStyle}
                >
                    Next
                </button>
            </div>
        </div>
    );
};

const thStyle = {
    border: '1px solid #ccc',
    padding: '8px',
    background: '#f4f4f4',
    textAlign: 'left',
};

const tdStyle = {
    border: '1px solid #ccc',
    padding: '8px',
};

const buttonStyle = {
    padding: '6px 12px',
    fontSize: '14px',
    margin: '0 5px',
};

export default SuccessfulLogins;
