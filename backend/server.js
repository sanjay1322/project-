const express = require('express');
const pool = require('./db');
const bcrypt = require('bcryptjs');
const app = express();
const PORT = 4000;

app.use(express.json());

app.get('/', (req, res) => {
  res.send('Service Tracker Backend Running');
});

// Test MySQL connection
app.get('/test-db', async (req, res) => {
  try {
    const [results] = await pool.query('SELECT 1 + 1 AS result');
    res.json({ dbTest: results[0].result });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Informative GET endpoint for /register
app.get('/register', (req, res) => {
  res.send('Please use POST /register with JSON body to register a user.');
});

// User registration endpoint
app.post('/register', async (req, res) => {
  const { username, email, password, role, department_id } = req.body;
  if (!username || !email || !password || !role) {
    return res.status(400).json({ error: 'All fields are required' });
  }
  try {
    // Check if user already exists
    const [existing] = await pool.query('SELECT id FROM users WHERE username = ? OR email = ?', [username, email]);
    if (existing.length > 0) {
      return res.status(409).json({ error: 'Username or email already exists' });
    }
    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10);
    // Insert user
    await pool.query(
      'INSERT INTO users (username, email, password, role, department_id) VALUES (?, ?, ?, ?, ?)',
      [username, email, hashedPassword, role, department_id || null]
    );
    res.status(201).json({ message: 'User registered successfully' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
});