# Changes and Integration Plan for Service Tracker (PHP Backend)

## 1. PHP Backend Scripts
- Create `register.php` for user registration
- Create `login.php` for user authentication
- Create `service_request.php` for handling service requests
- Use `db_connect.php` for database connection in all backend scripts

## 2. Frontend Form Updates
- Update `register.html` form to submit to `register.php` (method POST)
- Update `login.html` form to submit to `login.php` (method POST)
- Update dashboard forms to submit to `service_request.php` (method POST)

## 3. PHP Logic
- Validate form data in PHP scripts
- Insert and fetch data from MySQL
- Display success/error messages on frontend

## 4. Dashboard Integration
- Fetch and display requests dynamically using PHP in dashboard HTML files

## 5. Testing
- Test registration, login, and service request flows
- Verify data is stored and retrieved correctly

---
This file will be updated as changes are made to the codebase.
