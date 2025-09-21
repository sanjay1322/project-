# Service Tracker Web Application

A comprehensive web-based service request management system for educational institutions, built with HTML, CSS, PHP, and MySQL.

## 🎯 Features

### Core Functionality
- **Multi-role Authentication**: Student, Admin, and Department user roles
- **Ticket Management**: Complete workflow from submission to resolution
- **PDF Generation**: Automatic PDF generation for all tickets
- **Status Tracking**: Real-time status updates with audit trail
- **Role-based Access Control**: Secure access based on user roles

### User Roles & Capabilities

#### Student
- Register new account
- Submit service requests with detailed descriptions
- Track request status in real-time
- Download PDF copies of their tickets
- View request history

#### Admin
- View all submitted tickets
- Assign tickets to appropriate departments
- Filter tickets by status and department
- Download PDF copies of any ticket
- Monitor system activity

#### Department Staff
- View tickets assigned to their department
- Approve or reject tickets with comments
- Download PDF copies of assigned tickets
- Track department workload

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- PDO MySQL extension

### Installation Steps

1. **Clone/Download the project**
   ```bash
   git clone <repository-url>
   cd service_tracker
   ```

2. **Database Setup**
   - Create a MySQL database named `service_tracker`
   - Import the database schema:
     ```bash
     mysql -u root -p service_tracker < backend/setup_db.sql
     ```

3. **Configure Database Connection**
   - Edit `backend/db_connect.php` if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'service_tracker');
     ```

4. **Set Permissions**
   ```bash
   chmod 755 uploads/tickets/
   ```

5. **Access the Application**
   - Open your web browser
   - Navigate to `http://localhost/service_tracker/`

## 🔐 Default Login Credentials

### Admin Account
- **Email**: admin@college.edu
- **Password**: admin123
- **Role**: Admin

### Department Accounts
- **Library Head**: library@college.edu / admin123
- **Hostel Warden**: hostel@college.edu / admin123
- **IT Support Head**: it@college.edu / admin123

### Student Registration
- Students can register new accounts through the registration page
- All new registrations default to 'student' role

## 📋 Service Categories

The system supports the following service request categories:
- ID Card Re-issue
- Fee Payment Receipt
- Library Book Renewal
- Character Certificate
- College Leaving / Transfer Certificate
- Bonafide Certificate
- Course Completion Certificate
- Hostel Room Allocation / Change
- Mess Refund / Change of Mess
- Maintenance Issues
- Wi-Fi / Internet Access
- Password Reset
- Other

## 🔄 Ticket Workflow

1. **Submission**: Student submits a ticket with title, category, and description
2. **PDF Generation**: System automatically generates a PDF copy
3. **Assignment**: Admin assigns the ticket to an appropriate department
4. **Processing**: Department staff reviews and approves/rejects the ticket
5. **Completion**: Student receives real-time status updates

## 🛡️ Security Features

- **Password Hashing**: All passwords are securely hashed using PHP's password_hash()
- **SQL Injection Protection**: PDO prepared statements prevent SQL injection
- **XSS Protection**: All user inputs are sanitized and outputs are escaped
- **CSRF Protection**: CSRF tokens protect against cross-site request forgery
- **Session Security**: Session regeneration after login prevents session fixation
- **Role-based Access**: Users can only access features appropriate to their role

## 📁 Project Structure

```
service_tracker/
├── index.html                 # Landing page
├── login.html                 # Login page
├── register.html              # Registration page
├── student_dashboard.php      # Student interface
├── admin_dashboard.php        # Admin interface
├── department_dashboard.php   # Department interface
├── backend/
│   ├── auth.php              # Authentication helper
│   ├── db_connect.php        # Database connection
│   ├── login.php             # Login handler
│   ├── register.php          # Registration handler
│   ├── pdf_generator.php     # PDF generation utility
│   ├── download_pdf.php      # PDF download handler
│   ├── logout.php            # Logout handler
│   └── setup_db.sql          # Database schema
├── uploads/
│   └── tickets/              # PDF storage directory
└── README.md                 # This file
```

## 🗄️ Database Schema

### Tables
- **users**: User accounts with role-based access
- **departments**: Department information
- **tickets**: Service request tickets
- **ticket_history**: Audit trail for status changes

### Key Relationships
- Users belong to departments (for department staff)
- Tickets are created by students and assigned to departments
- Ticket history tracks all status changes and comments

## 🎨 UI/UX Features

- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Bootstrap Integration**: Modern, clean interface
- **Real-time Feedback**: Success/error messages for all actions
- **Status Indicators**: Color-coded status badges
- **Modal Dialogs**: Confirmation dialogs for important actions
- **Data Tables**: Sortable, filterable data presentation

## 🔧 Technical Details

### Backend Technologies
- **PHP 7.4+**: Server-side scripting
- **MySQL 5.7+**: Database management
- **PDO**: Database abstraction layer
- **Sessions**: User authentication and state management

### Frontend Technologies
- **HTML5**: Semantic markup
- **CSS3**: Styling and animations
- **Bootstrap 5**: UI framework
- **JavaScript**: Interactive functionality

### Security Measures
- Input validation and sanitization
- Output escaping
- Prepared statements
- CSRF protection
- Session management
- Role-based access control

## 🚀 Future Enhancements

- Email notifications for status changes
- Advanced analytics and reporting
- File upload support for tickets
- Mobile app integration
- API endpoints for external integrations
- Advanced search and filtering
- Bulk operations for admins

## 📞 Support

For technical support or questions about the Service Tracker system, please contact the system administrator.

## 📄 License

This project is developed for educational purposes. Please ensure compliance with your institution's policies and regulations.

---

**Version**: 1.0.0  
**Last Updated**: December 2024  
**Compatibility**: PHP 7.4+, MySQL 5.7+, Modern Browsers
