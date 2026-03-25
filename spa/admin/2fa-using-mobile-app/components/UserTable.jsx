import React, { useState, useEffect } from 'react';
import '../../login-logs-activity/assets/style.css';

const UserTable = () => {
  // Dummy static data with username field
  const initialData = [
    {
      id: 1,
      name: 'John Doe',
      username: 'johnd',
      email: 'john@example.com',
      role: 'Admin',
    },
    {
      id: 2,
      name: 'Jane Smith',
      username: 'janes',
      email: 'jane@example.com',
      role: 'Editor',
    },
    {
      id: 3,
      name: 'Alice Johnson',
      username: 'alicej',
      email: 'alice@example.com',
      role: 'Subscriber',
    },
    {
      id: 4,
      name: 'Bob Brown',
      username: 'bobb',
      email: 'bob@example.com',
      role: 'Editor',
    },
    {
      id: 5,
      name: 'Charlie Davis',
      username: 'charlied',
      email: 'charlie@example.com',
      role: 'Subscriber',
    },
    {
      id: 6,
      name: 'Eve Martinez',
      username: 'evem',
      email: 'eve@example.com',
      role: 'Admin',
    },
    {
      id: 7,
      name: 'Frank Lee',
      username: 'frankl',
      email: 'frank@example.com',
      role: 'Editor',
    },
    {
      id: 8,
      name: 'Grace Kim',
      username: 'gracek',
      email: 'grace@example.com',
      role: 'Subscriber',
    },
    {
      id: 9,
      name: 'Henry Clark',
      username: 'henryc',
      email: 'henry@example.com',
      role: 'Editor',
    },
    {
      id: 10,
      name: 'Ivy Scott',
      username: 'ivys',
      email: 'ivy@example.com',
      role: 'Subscriber',
    },
    {
      id: 11,
      name: 'Jack White',
      username: 'jackw',
      email: 'jack@example.com',
      role: 'Admin',
    },
  ];

  const [data, setData] = useState(initialData);
  const [searchTerm, setSearchTerm] = useState('');
  const [roleFilter, setRoleFilter] = useState('All');
  const [itemsPerPage, setItemsPerPage] = useState(5);
  const [currentPage, setCurrentPage] = useState(1);
  const [selectedRows, setSelectedRows] = useState([]);

  // Filtered and paginated data
  const filteredData = data.filter((item) => {
    const matchesSearch =
      item.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
      item.username.toLowerCase().includes(searchTerm.toLowerCase()) || // include username in search
      item.email.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesRole = roleFilter === 'All' || item.role === roleFilter;
    return matchesSearch && matchesRole;
  });

  const totalPages = Math.ceil(filteredData.length / itemsPerPage);

  const currentData = filteredData.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage,
  );

  const handleSelectAll = (e) => {
    if (e.target.checked) {
      setSelectedRows(currentData.map((item) => item.id));
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

  // Reset selection when page changes or filter changes
  useEffect(() => {
    setSelectedRows([]);
    setCurrentPage(1);
  }, [itemsPerPage, searchTerm, roleFilter]);

  return (
    <div className="tpsa-login-log-activity">
      <h3>User Table</h3>

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
                  currentData.length > 0 &&
                  currentData.every((item) => selectedRows.includes(item.id))
                }
              />
            </th>
            <th>Name</th>
            <th>Username</th> {/* New field */}
            <th>Email</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
          {currentData.length > 0 ? (
            currentData.map((user) => (
              <tr key={user.id}>
                <td>
                  <input
                    type="checkbox"
                    checked={selectedRows.includes(user.id)}
                    onChange={() => handleSelectRow(user.id)}
                  />
                </td>
                <td>{user.name}</td>
                <td>{user.username}</td> {/* New field */}
                <td>{user.email}</td>
                <td>{user.role}</td>
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
          disabled={currentPage === totalPages || filteredData.length === 0}
        >
          Next
        </button>
      </div>
    </div>
  );
};

export default UserTable;
