# Roaya Clinic Management System

A comprehensive PHP-based clinic management system designed specifically for ophthalmology practices. Features role-based access control, real-time calendar updates, patient management, and financial tracking.

## 🌟 Features

### **Two Role-Based Portals**

#### **Doctor Portal**
- **Dashboard**: Search functionality, KPIs, recent timeline events
- **Live Calendar**: Auto-refresh every 60 seconds with real-time updates
- **Patient Profiles**: Complete patient history with timeline view
- **Consultation Management**: Ophthalmology-specific fields (VA, Refraction, IOP, etc.)
- **Prescriptions**: Medication and glasses prescriptions
- **Financial Overrides**: Approve discounts and exemptions
- **Daily Closure**: Lock daily operations with audit trail

#### **Secretary Portal**
- **Bookings**: Create walk-in/phone appointments
- **Payments**: Collect and manage all payment types
- **Patient Management**: Quick patient creation and search
- **Invoice Generation**: Professional invoice creation
- **Limited Permissions**: Can edit/reschedule but cannot delete

### **Core Functionality**
- **15-minute time slots** with automatic conflict detection
- **Friday closed** - enforced system-wide
- **Separate doctor schedules** (e.g., Dr. Ahmed: Sun/Mon/Wed, Dr. Sara: Tue/Thu/Sat)
- **Working hours**: 2:00 PM → 11:00 PM
- **Timezone**: Africa/Cairo
- **Dark mode** support with CSS variables
- **Responsive design** for all devices

## 🛠️ Tech Stack

- **Backend**: PHP 8.2+ (no framework)
- **Database**: MySQL 8.0+ (InnoDB, utf8mb4)
- **Frontend**: Bootstrap 5, Bootstrap Icons, Vanilla JavaScript
- **Security**: Password hashing, CSRF protection, RBAC, login throttling
- **Autoloading**: PSR-4 with Composer

## 📁 Project Structure

```
/public              # Web root (index.php, assets)
/app
  /Config           # Database, Auth, Constants
  /Controllers      # All application controllers
  /Models          # Data models and business logic
  /Views           # Templates and layouts
  /Lib             # Core libraries (Router, Auth, etc.)
/storage            # Logs, uploads, exports
/sql               # Database schema and seed data
composer.json      # Dependencies and autoloading
```

## 🚀 Installation

### 1. Prerequisites
- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer
- Web server (Apache/Nginx)

### 2. Clone and Setup
```bash
git clone <repository-url>
cd clinic
composer install
```

### 3. Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'clinic_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON clinic_db.* TO 'clinic_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema and data
mysql -u clinic_user -p clinic_db < sql/schema.sql
mysql -u clinic_user -p clinic_db < sql/seed.sql
```

### 4. Environment Configuration
```bash
# Copy environment file
cp env.example .env

# Edit .env with your database credentials
DB_HOST=localhost
DB_NAME=clinic_db
DB_USER=clinic_user
DB_PASS=your_password
APP_ENV=local
APP_KEY=generate-a-random-32-character-string
TIMEZONE=Africa/Cairo
```

### 5. Web Server Configuration
Point your web server's document root to the `/public` directory.

#### Apache (.htaccess already included)
```apache
DocumentRoot /path/to/clinic/public
```

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/clinic/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔐 Default Login Credentials

After running the seed data, you can login with:

### Doctors
- **Dr. Ahmed Hassan**: `dr.ahmed@clinic.com` / `password`
- **Dr. Sara Mahmoud**: `dr.sara@clinic.com` / `password`

### Secretary
- **Fatima Ali**: `fatima@clinic.com` / `password`

### Admin
- **Admin User**: `admin@clinic.com` / `password`

> ⚠️ **Important**: Change default passwords immediately after first login!

## 📊 Database Schema

The system includes comprehensive tables for:
- **Users & Authentication**: Role-based access control
- **Doctors & Schedules**: Working days and hours
- **Patients & Medical History**: Complete patient records
- **Appointments**: 15-minute slot management
- **Consultations**: Ophthalmology-specific fields
- **Prescriptions**: Medications and glasses
- **Payments & Invoices**: Financial tracking
- **Timeline Events**: Complete audit trail
- **Daily Closures**: End-of-day operations

## 🔄 Auto-Refresh Calendar

The calendar automatically refreshes every 60 seconds using:
```javascript
const poll = () => fetch(`/api/calendar?doctor_id=${doctorId}&date=${selectedDate}`)
  .then(r => r.json()).then(({data}) => renderCalendar(data));

poll();
setInterval(poll, 60 * 1000);
```

## 🖨️ Printable Templates

### Medication Prescription (RTL)
- **Paper width**: 24.5 cm
- **Direction**: Right-to-Left
- **Exact positioning** for Arabic text

### Glasses Prescription
- Standard A4 format
- Complete optical measurements
- Professional layout

### Lab Tests Request
- A5 format for efficiency
- Clear test specifications

## 🔒 Security Features

- **Password Policy**: Minimum 8 characters, complexity requirements
- **CSRF Protection**: All forms protected
- **Session Management**: Secure session handling
- **RBAC**: Role-based access control
- **Login Throttling**: Prevents brute force attacks
- **Audit Logging**: Complete action tracking
- **Input Validation**: Server-side validation everywhere

## 📱 Responsive Design

- **Mobile-first** approach
- **Dark mode** support
- **CSS variables** for theming
- **Bootstrap 5** components
- **Touch-friendly** interface

## 🚀 Performance Features

- **Database indexing** on critical fields
- **ETags** for API responses
- **Efficient queries** with proper JOINs
- **Caching** strategies for static data

## 📈 Reporting

Built-in reports for:
- Daily revenue by type and doctor
- Appointment status summaries
- Patient visit analytics
- Diagnosis statistics
- Export to CSV

## 🛠️ Development

### Adding New Features
1. Create controller in `/app/Controllers/`
2. Add routes in `/public/index.php`
3. Create views in `/app/Views/`
4. Update database schema if needed

### Code Style
- PSR-4 autoloading
- PSR-12 coding standards
- Comprehensive error handling
- Security-first approach

## 🐛 Troubleshooting

### Common Issues

#### Database Connection
```bash
# Check MySQL service
sudo systemctl status mysql

# Verify credentials in .env
# Test connection manually
mysql -u clinic_user -p clinic_db
```

#### Permission Issues
```bash
# Set proper file permissions
chmod -R 755 /path/to/clinic
chmod -R 777 /path/to/clinic/storage
```

#### Composer Issues
```bash
# Clear composer cache
composer clear-cache
composer install --no-cache
```

## 📞 Support

For technical support or feature requests:
- Create an issue in the repository
- Contact the development team
- Check the documentation

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 🔄 Updates

The system is actively maintained with regular updates for:
- Security patches
- New features
- Performance improvements
- Bug fixes

---

**Built with ❤️ for the medical community**
