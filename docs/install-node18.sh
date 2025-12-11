#!/bin/bash

# Script to install Node.js 18 on the server
# Run as: bash install-node18.sh

set -e

echo "🔧 Installing Node.js 18..."

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "⚠️  Please run as root or with sudo"
    exit 1
fi

# Detect OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
else
    echo "❌ Cannot detect OS"
    exit 1
fi

echo "📦 Detected OS: $OS"

# Install Node.js 18 based on OS
if [ "$OS" = "ubuntu" ] || [ "$OS" = "debian" ]; then
    echo "📥 Installing Node.js 18 for Ubuntu/Debian..."
    
    # Remove old Node.js if exists
    apt-get remove -y nodejs npm 2>/dev/null || true
    
    # Add NodeSource repository
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    
    # Install Node.js
    apt-get install -y nodejs
    
    echo "✅ Node.js installed successfully"
    
elif [ "$OS" = "centos" ] || [ "$OS" = "rhel" ] || [ "$OS" = "fedora" ]; then
    echo "📥 Installing Node.js 18 for CentOS/RHEL/Fedora..."
    
    # Add NodeSource repository
    curl -fsSL https://rpm.nodesource.com/setup_18.x | bash -
    
    # Install Node.js
    yum install -y nodejs || dnf install -y nodejs
    
    echo "✅ Node.js installed successfully"
else
    echo "❌ Unsupported OS: $OS"
    echo "Please install Node.js 18 manually"
    exit 1
fi

# Verify installation
echo ""
echo "🔍 Verifying installation..."
node --version
npm --version

echo ""
echo "✅ Installation complete!"
echo "Now run: cd /var/www/html/clinic/docs && npm install && npm run build"
