#!/bin/bash

# Simple build script that works without Vite
# This creates a basic static site structure

set -e

echo "🔨 Building documentation site..."

DIST_DIR="dist"
ASSETS_DIR="$DIST_DIR/assets"

# Create directories
mkdir -p "$DIST_DIR"
mkdir -p "$ASSETS_DIR"

# Copy and process index.html
echo "📄 Processing index.html..."
cat index.html | \
    sed 's|href="./src/styles/main.css"|href="/opth/docs/assets/main.css"|g' | \
    sed 's|src="./src/main.js"|src="/opth/docs/assets/main.js"|g' > "$DIST_DIR/index.html"

# Combine CSS files
echo "🎨 Combining CSS files..."
cat src/styles/main.css src/styles/crypto-theme.css src/styles/animations.css src/styles/rtl-ltr.css > "$ASSETS_DIR/main.css"

# Combine core JS files (simple concatenation)
echo "📦 Bundling JavaScript files..."
{
    echo "// Roaya Clinic Docs - Bundled"
    echo "const BASE_PATH = '/opth/docs/';"
    echo ""
    cat src/data/translations.js | sed 's/export default/window.translations =/'
    echo ""
    cat src/data/search-index.js | sed 's/export default/window.searchIndex =/'
    echo ""
    cat src/i18n.js | sed 's/export const i18n/window.i18n = {/'
    echo ""
    cat src/components/Sidebar.js | sed 's/export class Sidebar/window.Sidebar = class Sidebar/'
    echo ""
    cat src/search.js | sed 's/export class Search/window.Search = class Search/'
    echo ""
    cat src/router.js | sed 's/export class Router/window.Router = class Router/'
    echo ""
    cat src/main.js | sed 's/import.*//g' | sed 's/export.*//g'
} > "$ASSETS_DIR/main.js"

# Copy .htaccess if exists
if [ -f ".htaccess" ]; then
    cp .htaccess "$DIST_DIR/"
    echo "✅ Copied .htaccess"
fi

echo ""
echo "✅ Build completed!"
echo "📁 Output: $DIST_DIR/"
echo ""
echo "📋 To deploy:"
echo "   cp -r $DIST_DIR/* /home/AhmedHelal/web/hclinic.clinic/public_html/opth/docs/"
