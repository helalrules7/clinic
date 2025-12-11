#!/bin/bash

# Simple build script for documentation
# Works without Node.js build tools

set -e

echo "🔨 Building documentation site..."

DIST_DIR="dist"
ASSETS_DIR="$DIST_DIR/assets"

# Clean and create directories
rm -rf "$DIST_DIR"
mkdir -p "$DIST_DIR"
mkdir -p "$ASSETS_DIR"

# Process index.html - fix paths
echo "📄 Processing index.html..."
sed -e 's|href="./src/styles/main.css"|href="/opth/docs/assets/main.css"|g' \
    -e 's|src="./src/main.js"|src="/opth/docs/assets/app.js"|g' \
    index.html > "$DIST_DIR/index.html"

# Combine all CSS files
echo "🎨 Combining CSS files..."
{
    echo "/* ===== main.css ===== */"
    cat src/styles/main.css
    echo ""
    echo "/* ===== crypto-theme.css ===== */"
    cat src/styles/crypto-theme.css
    echo ""
    echo "/* ===== animations.css ===== */"
    cat src/styles/animations.css
    echo ""
    echo "/* ===== rtl-ltr.css ===== */"
    cat src/styles/rtl-ltr.css
} > "$ASSETS_DIR/main.css"

# Create a simple bundled JS
echo "📦 Creating JavaScript bundle..."
cat > "$ASSETS_DIR/main.js" << 'JSBUNDLE'
// Roaya Clinic Docs - Simple Bundle
const BASE_PATH = '/opth/docs/';

// Translations
window.translations = {
    ar: {
        loading: 'جاري التحميل...',
        'app-name': 'Roaya Clinic Docs'
    },
    en: {
        loading: 'Loading...',
        'app-name': 'Roaya Clinic Docs'
    }
};

// Simple i18n
window.i18n = {
    currentLang: 'ar',
    t: function(key) {
        return this.translations[this.currentLang]?.[key] || key;
    },
    init: function(lang) {
        this.currentLang = lang || 'ar';
        document.documentElement.lang = lang;
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    }
};

// Load main modules dynamically
async function loadModule(path) {
    const response = await fetch(BASE_PATH + 'assets/' + path);
    return await response.text();
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Documentation site loaded');
    // Load main app code
    const script = document.createElement('script');
    script.type = 'module';
    script.src = BASE_PATH + 'assets/app.js';
    document.head.appendChild(script);
});
JSBUNDLE

# Copy all JS files as separate modules (for dynamic loading)
echo "📦 Copying JavaScript modules..."
mkdir -p "$ASSETS_DIR/pages/roles"
mkdir -p "$ASSETS_DIR/pages/features"
mkdir -p "$ASSETS_DIR/pages/api"
mkdir -p "$ASSETS_DIR/components"
mkdir -p "$ASSETS_DIR/data"

# Copy JS files maintaining structure
cp src/main.js "$ASSETS_DIR/app.js"
cp src/router.js "$ASSETS_DIR/router.js"
cp src/search.js "$ASSETS_DIR/search.js"
cp src/i18n.js "$ASSETS_DIR/i18n.js"
cp src/components/Sidebar.js "$ASSETS_DIR/components/Sidebar.js"
cp src/data/translations.js "$ASSETS_DIR/data/translations.js"
cp src/data/search-index.js "$ASSETS_DIR/data/search-index.js"

# Copy page files
cp src/pages/overview.js "$ASSETS_DIR/pages/overview.js"
cp src/pages/roles/*.js "$ASSETS_DIR/pages/roles/"
cp src/pages/features/*.js "$ASSETS_DIR/pages/features/"
cp src/pages/api/*.js "$ASSETS_DIR/pages/api/"

# Fix import paths in all JS files
echo "🔧 Fixing import paths..."

# Fix paths in app.js
sed -i 's|\./router\.js|/opth/docs/assets/router.js|g' "$ASSETS_DIR/app.js"
sed -i 's|\./i18n\.js|/opth/docs/assets/i18n.js|g' "$ASSETS_DIR/app.js"
sed -i 's|\./search\.js|/opth/docs/assets/search.js|g' "$ASSETS_DIR/app.js"
sed -i 's|\./components/Sidebar\.js|/opth/docs/assets/components/Sidebar.js|g' "$ASSETS_DIR/app.js"

# Fix paths in router.js - dynamic imports
sed -i "s|import('./pages/|import('/opth/docs/assets/pages/|g" "$ASSETS_DIR/router.js"
sed -i "s|import(\"\./pages/|import(\"/opth/docs/assets/pages/|g" "$ASSETS_DIR/router.js"

# Fix paths in i18n.js
sed -i 's|\./data/translations\.js|/opth/docs/assets/data/translations.js|g' "$ASSETS_DIR/i18n.js"
sed -i "s|\./data/translations\.js|/opth/docs/assets/data/translations.js|g" "$ASSETS_DIR/i18n.js"

# Fix paths in search.js
sed -i 's|\./data/search-index\.js|/opth/docs/assets/data/search-index.js|g' "$ASSETS_DIR/search.js"
sed -i "s|\./data/search-index\.js|/opth/docs/assets/data/search-index.js|g" "$ASSETS_DIR/search.js"

# Fix all relative imports in all JS files
find "$ASSETS_DIR" -name "*.js" -type f -exec sed -i \
    -e 's|from '\''\.\./\.\./data/|from '\''/opth/docs/assets/data/|g' \
    -e 's|from '\''\.\./data/|from '\''/opth/docs/assets/data/|g' \
    -e 's|from '\''\./data/|from '\''/opth/docs/assets/data/|g' \
    -e 's|from '\''\.\./i18n|from '\''/opth/docs/assets/i18n|g' \
    -e 's|from '\''\./i18n|from '\''/opth/docs/assets/i18n|g' \
    -e 's|from "\.\./\.\./data/|from "/opth/docs/assets/data/|g' \
    -e 's|from "\.\./data/|from "/opth/docs/assets/data/|g' \
    -e 's|from "\./data/|from "/opth/docs/assets/data/|g' \
    -e 's|from "\.\./i18n|from "/opth/docs/assets/i18n|g' \
    -e 's|from "\./i18n|from "/opth/docs/assets/i18n|g' \
    {} \;

# Copy .htaccess
if [ -f ".htaccess" ]; then
    cp .htaccess "$DIST_DIR/"
    echo "✅ Copied .htaccess"
fi

echo ""
echo "✅ Build completed successfully!"
echo "📁 Output directory: $DIST_DIR/"
echo ""
echo "📋 Deployment:"
echo "   cp -r $DIST_DIR/* /home/AhmedHelal/web/hclinic.clinic/public_html/opth/docs/"
echo "   chmod -R 755 /home/AhmedHelal/web/hclinic.clinic/public_html/opth/docs/"
