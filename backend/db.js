const mysql = require('mysql2');

const pool = mysql.createPool({
  host: 'localhost',
  user: 'root',
  password: '', // your MySQL password
  database: 'service_tracker'
});

module.exports = pool.promise();