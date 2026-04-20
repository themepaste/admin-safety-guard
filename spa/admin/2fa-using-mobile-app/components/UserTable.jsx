import React, { useState, useEffect } from 'react';
import '../../login-logs-activity/assets/style.css';

const UserTable = () => {
  const [data, setData] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [roleFilter, setRoleFilter] = useState('All');
  const [itemsPerPage, setItemsPerPage] = useState(5);
  const [currentPage, setCurrentPage] = useState(1);
  const [selectedRows, setSelectedRows] = useState([]);
  const [totalEntries, setTotalEntries] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchUsers = async () => {
    setLoading(true);
    setError(null);

    try {
      const params = new URLSearchParams({
        per_page: itemsPerPage,
        page: currentPage,
      });

      if (searchTerm.trim() !== '') params.append('s', searchTerm.trim());
      if (roleFilter !== 'All') params.append('role', roleFilter.toLowerCase());

      const response = await fetch(
        `${tpsaAdmin.rest_url}secure-admin/v1/2fa/app/users?${params.toString()}`,
        {
          method: 'GET',
          headers: { 'X-WP-Nonce': tpsaAdmin.rest_nonce },
          credentials: 'include',
        },
      );

      if (!response.ok) throw new Error('Failed to fetch users');

      const json = await response.json();
      setData(json.data || []);
      setTotalEntries(json.total || 0);
    } catch (err) {
      setError(err.message || 'Unknown error');
    } finally {
      setLoading(false);
      setSelectedRows([]); // reset selection on fetch
    }
  };

  useEffect(() => {
    fetchUsers();
  }, [currentPage, itemsPerPage, searchTerm, roleFilter]);

  const totalPages = Math.ceil(totalEntries / itemsPerPage);

  const handleSelectAll = (e) => {
    if (e.target.checked) {
      setSelectedRows(data.map((item) => item.ID));
    } else {
      setSelectedRows([]);
    }
  };

  const handleSelectRow = (id) => {
    setSelectedRows((prev) =>
      prev.includes(id) ? prev.filter((rowId) => rowId !== id) : [...prev, id],
    );
  };

  const handlePrevPage = () => {
    if (currentPage > 1) setCurrentPage(currentPage - 1);
  };

  const handleNextPage = () => {
    if (currentPage < totalPages) setCurrentPage(currentPage + 1);
  };

  return (
    <div className="tpsa-login-log-activity">
      <h3>User Table</h3>

      {loading && <p>Loading...</p>}
      {error && <p style={{ color: 'red' }}>{error}</p>}

      <div className="tpsa-login-log-activity-header">
        <div className="tpsa-login-log-activity-items-per-page">
          <label>Items per page: </label>
          <select
            value={itemsPerPage}
            onChange={(e) => setItemsPerPage(Number(e.target.value))}
          >
            <option value={1}>1</option>
            <option value={3}>3</option>
            <option value={5}>5</option>
            <option value={10}>10</option>
          </select>
        </div>

        <div className="tpsa-login-log-activity-search">
          <input
            type="text"
            placeholder="Search by name, username, or email..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>

        <div className="tpsa-login-log-activity-role-filter">
          <label>Filter by role: </label>
          <select
            value={roleFilter}
            onChange={(e) => setRoleFilter(e.target.value)}
          >
            <option value="All">All</option>
            <option value="Admin">Admin</option>
            <option value="Editor">Editor</option>
            <option value="Subscriber">Subscriber</option>
          </select>
        </div>
      </div>

      <table className="tpsa-table">
        <thead>
          <tr>
            <th>
              <input
                type="checkbox"
                onChange={handleSelectAll}
                checked={
                  data.length > 0 &&
                  data.every((item) => selectedRows.includes(item.ID))
                }
              />
            </th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
          {data.length > 0 ? (
            data.map((user) => (
              <tr key={user.ID}>
                <td>
                  <input
                    type="checkbox"
                    checked={selectedRows.includes(user.ID)}
                    onChange={() => handleSelectRow(user.ID)}
                  />
                </td>
                <td>{user.name}</td>
                <td>{user.username}</td>
                <td>{user.email}</td>
                <td>{user.roles.join(', ')}</td>
              </tr>
            ))
          ) : (
            <tr>
              <td colSpan="5">No results found</td>
            </tr>
          )}
        </tbody>
      </table>

      <div className="tpsa-login-log-activity-pagination">
        <button onClick={handlePrevPage} disabled={currentPage === 1}>
          Previous
        </button>
        <span>
          Page {currentPage} of {totalPages || 1}
        </span>
        <button
          onClick={handleNextPage}
          disabled={currentPage === totalPages || totalEntries === 0}
        >
          Next
        </button>
      </div>
    </div>
  );
};

export default UserTable;
