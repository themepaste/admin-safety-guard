import React, { useState } from 'react';

const BlockUsers = () => {
    const loginData = [
        {
            id: 1,
            username: 'john_doe',
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            dataTime: 'July 3, 2025, 11:17 am',
            ipAddress: '192.168.0.1',
            country: 'United States',
            city: 'New York',
        },
    ];

    const [searchTerm, setSearchTerm] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;

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
            <h1>Block Users</h1>

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
                        <th style={thStyle}>User Agent / IP Address</th>
                        <th style={thStyle}>Country</th>
                        <th style={thStyle}>City / State</th>
                        <th style={thStyle}>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    {paginatedData.length > 0 ? (
                        paginatedData.map((login) => (
                            <tr key={login.id}>
                                <td style={tdStyle}>{login.id}</td>
                                <td style={tdStyle}>{login.username}</td>
                                <td style={tdStyle}>
                                    {login.userAgent} / {login.ipAddress}
                                </td>
                                <td style={tdStyle}>{login.country}</td>
                                <td style={tdStyle}>{login.city}</td>
                                <td style={tdStyle}>{login.dataTime}</td>
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

export default BlockUsers;
