<?php
// Handle error messages
$error = '';
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

// Handle success messages
$success = '';
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | Service Tracker</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }
    
    /* Animated background elements */
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.3;
      animation: float 20s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(1deg); }
    }
    
    .register-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 
        0 25px 50px rgba(0, 0, 0, 0.15),
        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
      width: 100%;
      max-width: 450px;
      position: relative;
      z-index: 1;
      animation: slideUp 0.6s ease-out;
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .register-container h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #2d3748;
      font-size: 28px;
      font-weight: 700;
      letter-spacing: -0.5px;
    }
    
    .form-group {
      margin-bottom: 24px;
      position: relative;
    }
    
    label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 8px;
      color: #4a5568;
      letter-spacing: 0.025em;
    }
    
    input, select {
      width: 100%;
      padding: 16px 20px;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 16px;
      background: rgba(255, 255, 255, 0.9);
      transition: all 0.3s ease;
      color: #2d3748;
    }
    
    input:focus, select:focus {
      outline: none;
      border-color: #4facfe;
      background: rgba(255, 255, 255, 1);
      box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
      transform: translateY(-1px);
    }
    
    input::placeholder {
      color: #a0aec0;
    }
    
    button {
      width: 100%;
      padding: 16px;
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      border: none;
      color: white;
      font-size: 16px;
      font-weight: 600;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      letter-spacing: 0.025em;
      position: relative;
      overflow: hidden;
    }
    
    button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    button:hover::before {
      left: 100%;
    }
    
    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(79, 172, 254, 0.3);
    }
    
    button:active {
      transform: translateY(0);
    }
    
    .login-link {
      text-align: center;
      margin-top: 30px;
      font-size: 14px;
    }
    
    .login-link p {
      margin: 8px 0;
      color: #718096;
    }
    
    .login-link a {
      color: #4facfe;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .login-link a:hover {
      color: #00f2fe;
      text-decoration: underline;
    }
    
    .error-message {
      background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
      color: #c53030;
      padding: 16px 20px;
      border-radius: 12px;
      margin-bottom: 24px;
      border: 1px solid #fca5a5;
      font-weight: 500;
      animation: shake 0.5s ease-in-out;
    }
    
    .success-message {
      background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
      color: #2f855a;
      padding: 16px 20px;
      border-radius: 12px;
      margin-bottom: 24px;
      border: 1px solid #68d391;
      font-weight: 500;
      animation: slideDown 0.5s ease-out;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .back-home {
      position: fixed;
      bottom: 20px;
      left: 20px;
      text-decoration: none;
      font-size: 16px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.8);
      background: rgba(255, 255, 255, 0.1);
      padding: 12px 20px;
      border-radius: 25px;
      backdrop-filter: blur(10px);
      transition: all 0.3s ease;
      z-index: 10;
    }
    
    .back-home:hover {
      color: white;
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-2px);
    }
    
    /* Responsive design */
    @media (max-width: 480px) {
      .register-container {
        padding: 30px 20px;
        margin: 10px;
      }
      
      .register-container h2 {
        font-size: 24px;
      }
      
      .back-home {
        position: relative;
        bottom: auto;
        left: auto;
        margin-top: 20px;
        display: inline-block;
      }
    }
  </style>
</head>
<body>
  <div class="register-container">
    <h2>Join Service Tracker</h2>
    
    <?php if ($error): ?>
      <div class="error-message">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
      <div class="success-message">
        <?php echo $success; ?>
      </div>
    <?php endif; ?>
    
    <form action="backend/register.php" method="POST">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email address" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Create a secure password" required>
      </div>
      <div class="form-group">
        <label for="confirm-password">Confirm Password</label>
        <input type="password" id="confirm-password" name="confirm-password" placeholder="Re-enter your password" required>
      </div>
      <button type="submit">Create Account</button>
      
      <div class="login-link">
        <p>Already have an account? <a href="login.php">Sign in here</a></p>
        <p><a href="index.html">← Back to Home</a></p>
      </div>
    </form>
  </div>
  
  <a href="index.html" class="back-home">← Back to Home</a>
</body>
</html>
