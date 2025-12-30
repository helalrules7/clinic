#!/bin/bash

# Roaya Clinic Installation Script
# Quick setup script for standard PHP servers

echo "🏥 نظام عيادة طب العيون - Roaya Clinic Installation"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_warning "Running as root. This script should be run as a regular user with sudo privileges."
fi

# Get current directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
print_status "Installation directory: $SCRIPT_DIR"

# Step 1: Check system requirements
print_step "1. Checking system requirements..."

# Check PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
    print_status "PHP version: $PHP_VERSION"
    
    if (( $(echo "$PHP_VERSION >= 8.0" | bc -l) )); then
        print_status "✓ PHP version is compatible"
    else
        print_error "✗ PHP 8.0 or higher is required"
        exit 1
    fi
else
    print_error "✗ PHP is not installed"
    exit 1
fi

# Check Composer
if command -v composer &> /dev/null; then
    print_status "✓ Composer is available"
else
    print_error "✗ Composer is not installed"
    echo "Please install Composer first: https://getcomposer.org/download/"
    exit 1
fi

# Check MySQL
if command -v mysql &> /dev/null; then
    print_status "✓ MySQL is available"
else
    print_warning "MySQL client not found. Make sure MySQL/MariaDB is installed and running."
fi

# Step 2: Install PHP dependencies
print_step "2. Installing PHP dependencies..."
cd "$SCRIPT_DIR"
if composer install --no-dev --optimize-autoloader; then
    print_status "✓ Dependencies installed successfully"
else
    print_error "✗ Failed to install dependencies"
    exit 1
fi

# Step 3: Set up environment file
print_step "3. Setting up environment configuration..."
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_status "✓ Created .env file from example"
        print_warning "Please edit .env file with your database credentials"
    else
        print_warning "No .env.example file found"
    fi
else
    print_status "✓ .env file already exists"
fi

# Step 4: Set permissions
print_step "4. Setting up file permissions..."
if [ -d "storage" ]; then
    chmod -R 755 storage/
    print_status "✓ Storage permissions set"
fi

if [ -d "public" ]; then
    chmod -R 755 public/
    print_status "✓ Public permissions set"
fi

# Step 5: Database setup prompt
print_step "5. Database setup..."
echo ""
print_warning "Database setup required:"
echo "1. Create a MySQL database named 'roaya'"
echo "2. Create a database user with appropriate privileges"
echo "3. Import the database: mysql -u username -p roaya < sql/roaya_clinic.sql"
echo "4. Update database credentials in .env file"
echo ""

# Step 6: Web server configuration
print_step "6. Web server configuration..."
echo ""
print_status "Choose your web server setup:"
echo ""
echo "📁 For Apache:"
echo "   - Copy apache-virtualhost.conf to /etc/apache2/sites-available/"
echo "   - Enable the site: sudo a2ensite your-site.conf"
echo "   - Enable mod_rewrite: sudo a2enmod rewrite"
echo "   - Restart Apache: sudo systemctl restart apache2"
echo ""
echo "🌐 For Nginx:"
echo "   - Copy nginx-server.conf to /etc/nginx/sites-available/"
echo "   - Enable the site: sudo ln -s /etc/nginx/sites-available/your-site /etc/nginx/sites-enabled/"
echo "   - Test config: sudo nginx -t"
echo "   - Restart Nginx: sudo systemctl restart nginx"
echo ""
echo "🔧 For XAMPP/WAMP:"
echo "   - Place this folder in htdocs/ or www/"
echo "   - Access via: http://localhost/clinic/public/"
echo ""
echo "🚀 For quick testing:"
echo "   - Run: php -S localhost:8000 -t public/"
echo "   - Access via: http://localhost:8000"
echo ""

# Step 7: Security recommendations
print_step "7. Security recommendations..."
echo ""
print_warning "Important security steps:"
echo "1. Change default passwords immediately after first login"
echo "2. Remove or rename installation files (*.md, install.sh)"
echo "3. Set up SSL certificate for production"
echo "4. Configure firewall rules"
echo "5. Regular backups of database and files"
echo ""

# Step 8: Test credentials
print_step "8. Test credentials..."
echo ""
print_status "Default login credentials:"
echo "👨‍⚕️ Doctors:"
echo "   Username: dr_ahmed    | Password: password"
echo "   Username: dr_faramawy | Password: password"
echo ""
echo "👩‍💼 Staff:"
echo "   Username: sec   | Password: password (Secretary)"
echo "   Username: admin | Password: password (Administrator)"
echo ""

# Final message
echo ""
print_status "🎉 Installation completed!"
echo ""
print_status "Next steps:"
echo "1. Set up your database (see step 5 above)"
echo "2. Configure your web server (see step 6 above)"
echo "3. Edit .env file with your settings"
echo "4. Access the system via your web browser"
echo ""
print_warning "Don't forget to change default passwords and secure your installation!"
echo ""
print_status "For detailed instructions, check:"
echo "- STANDARD_PHP_DEPLOYMENT.md (for standard PHP servers)"
echo "- HESTIA_DEPLOYMENT.md (for Hestia control panel)"
echo ""
print_status "Happy managing! 🏥✨"
