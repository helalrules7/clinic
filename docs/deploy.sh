#!/bin/bash

# Deployment script for Hestia
# Usage: ./deploy.sh

set -e  # Exit on error

echo "🚀 Starting deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Configuration
BUILD_DIR="dist"
DEPLOY_DIR="../public_html/opth/docs"

# Check if we're in the right directory
if [ ! -f "package.json" ]; then
    echo -e "${RED}Error: package.json not found. Please run this script from the docs directory.${NC}"
    exit 1
fi

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}Installing dependencies...${NC}"
    npm install
fi

# Build the project
echo -e "${YELLOW}Building project...${NC}"
npm run build

# Check if build was successful
if [ ! -d "$BUILD_DIR" ]; then
    echo -e "${RED}Error: Build failed. dist directory not found.${NC}"
    exit 1
fi

# Backup existing files (optional)
if [ -d "$DEPLOY_DIR" ] && [ "$(ls -A $DEPLOY_DIR)" ]; then
    echo -e "${YELLOW}Creating backup...${NC}"
    BACKUP_DIR="${DEPLOY_DIR}_backup_$(date +%Y%m%d_%H%M%S)"
    cp -r "$DEPLOY_DIR" "$BACKUP_DIR"
    echo -e "${GREEN}Backup created: $BACKUP_DIR${NC}"
fi

# Copy files to deployment directory
echo -e "${YELLOW}Copying files to deployment directory...${NC}"
mkdir -p "$DEPLOY_DIR"
# Copy all files from dist to deployment directory
cp -r "$BUILD_DIR"/* "$DEPLOY_DIR/" 2>/dev/null || {
    # If copy fails, try with different approach
    rsync -av "$BUILD_DIR/" "$DEPLOY_DIR/" || {
        echo -e "${RED}Error: Failed to copy files${NC}"
        exit 1
    }
}

# Copy .htaccess if exists
if [ -f ".htaccess" ]; then
    echo -e "${YELLOW}Copying .htaccess...${NC}"
    cp .htaccess "$DEPLOY_DIR/"
fi

# Set permissions
echo -e "${YELLOW}Setting permissions...${NC}"
chmod -R 755 "$DEPLOY_DIR"
find "$DEPLOY_DIR" -type f -exec chmod 644 {} \;

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "${GREEN}Files deployed to: $DEPLOY_DIR${NC}"
