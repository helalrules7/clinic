#!/usr/bin/env node

/**
 * Manual build script for Node.js 12 compatibility
 * This script bundles the files without Vite
 */

const fs = require('fs');
const path = require('path');

const distDir = path.join(__dirname, 'dist');
const assetsDir = path.join(distDir, 'assets');

// Create dist directory
if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
}
if (!fs.existsSync(assetsDir)) {
    fs.mkdirSync(assetsDir, { recursive: true });
}

console.log('📦 Starting manual build...');

// Copy index.html
const indexHtml = fs.readFileSync(path.join(__dirname, 'index.html'), 'utf8');
// Replace relative paths with absolute paths for production
const productionHtml = indexHtml
    .replace('./src/styles/main.css', '/opth/docs/assets/main.css')
    .replace('./src/main.js', '/opth/docs/assets/main.js');

fs.writeFileSync(path.join(distDir, 'index.html'), productionHtml);
console.log('✅ Copied index.html');

// Copy CSS files
const cssFiles = [
    'src/styles/main.css',
    'src/styles/crypto-theme.css',
    'src/styles/animations.css',
    'src/styles/rtl-ltr.css'
];

let combinedCSS = '';
cssFiles.forEach(file => {
    const content = fs.readFileSync(path.join(__dirname, file), 'utf8');
    combinedCSS += `\n/* ${file} */\n${content}\n`;
});

fs.writeFileSync(path.join(assetsDir, 'main.css'), combinedCSS);
console.log('✅ Combined CSS files');

// Copy and bundle JS files
const jsFiles = [
    'src/main.js',
    'src/router.js',
    'src/search.js',
    'src/i18n.js',
    'src/components/Sidebar.js',
    'src/data/translations.js',
    'src/data/search-index.js',
    'src/pages/overview.js',
    'src/pages/roles/doctor.js',
    'src/pages/roles/secretary.js',
    'src/pages/roles/admin.js',
    'src/pages/features/patients.js',
    'src/pages/features/appointments.js',
    'src/pages/features/prescriptions.js',
    'src/pages/features/payments.js',
    'src/pages/features/reports.js',
    'src/pages/features/alerts.js',
    'src/pages/features/forum.js',
    'src/pages/features/media.js',
    'src/pages/features/notifications.js',
    'src/pages/api/overview.js',
    'src/pages/api/authentication.js',
    'src/pages/api/endpoints.js',
    'src/pages/api/examples.js'
];

// Simple bundler - concatenate files and replace imports
let bundledJS = `// Bundled JavaScript for Roaya Clinic Docs
// Base path: /opth/docs/
const BASE_PATH = '/opth/docs/';

`;

// Read and process each JS file
const moduleMap = new Map();

jsFiles.forEach(file => {
    const content = fs.readFileSync(path.join(__dirname, file), 'utf8');
    const moduleName = file.replace('src/', '').replace('.js', '').replace(/\//g, '_');
    moduleMap.set(moduleName, content);
});

// Process imports and bundle
let mainContent = fs.readFileSync(path.join(__dirname, 'src/main.js'), 'utf8');

// Replace import statements with inline code
mainContent = mainContent.replace(/import\s+.*?from\s+['"](.*?)['"];?/g, (match, importPath) => {
    const relativePath = importPath.replace('./', 'src/');
    const fullPath = path.join(__dirname, relativePath);
    
    if (fs.existsSync(fullPath)) {
        const importedContent = fs.readFileSync(fullPath, 'utf8');
        return `// Imported from ${importPath}\n${importedContent}`;
    }
    return match;
});

// Simple approach: just concatenate main files
const coreFiles = [
    'src/data/translations.js',
    'src/data/search-index.js',
    'src/i18n.js',
    'src/components/Sidebar.js',
    'src/search.js',
    'src/router.js',
    'src/main.js'
];

let finalJS = `// Roaya Clinic Docs - Bundled
(function() {
    'use strict';
    
    const BASE_PATH = '/opth/docs/';
    
`;

coreFiles.forEach(file => {
    const content = fs.readFileSync(path.join(__dirname, file), 'utf8');
    // Remove export statements and convert to IIFE
    const processed = content
        .replace(/export\s+/g, '')
        .replace(/export default/g, 'window.')
        .replace(/import\s+.*?from\s+['"].*?['"];?/g, '// Import removed');
    
    finalJS += `\n// === ${file} ===\n${processed}\n`;
});

// Add page modules
const pageModules = {};
jsFiles.filter(f => f.startsWith('src/pages/')).forEach(file => {
    const content = fs.readFileSync(path.join(__dirname, file), 'utf8');
    const moduleName = file.replace('src/pages/', '').replace('.js', '').replace(/\//g, '_');
    pageModules[moduleName] = content.replace(/export default/g, 'function');
});

finalJS += `
    // Page modules
    window.pageModules = ${JSON.stringify(pageModules, null, 2)};
    
})();
`;

fs.writeFileSync(path.join(assetsDir, 'main.js'), finalJS);
console.log('✅ Bundled JavaScript files');

// Copy .htaccess
if (fs.existsSync(path.join(__dirname, '.htaccess'))) {
    fs.copyFileSync(
        path.join(__dirname, '.htaccess'),
        path.join(distDir, '.htaccess')
    );
    console.log('✅ Copied .htaccess');
}

console.log('\n✅ Build completed!');
console.log(`📁 Output directory: ${distDir}`);
console.log('\n📋 Next steps:');
console.log('1. Copy contents of dist/ to /home/AhmedHelal/web/hclinic.clinic/public_html/opth/docs/');
console.log('2. Set up Nginx/Apache configuration');
console.log('3. Test the website at https://yourdomain.com/opth/docs/');
