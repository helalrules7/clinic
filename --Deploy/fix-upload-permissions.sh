#!/bin/bash

echo "🔧 إصلاح صلاحيات مجلدات الرفع"
echo "============================="

TARGET_PATH="/home/AhmedHelal/web/roaya.ahmedhelal.dev/public_html"

echo "📁 إنشاء وإصلاح مجلدات الرفع..."

# إنشاء جميع مجلدات الرفع المطلوبة
mkdir -p "$TARGET_PATH/storage/uploads"
mkdir -p "$TARGET_PATH/storage/uploads/attachments"
mkdir -p "$TARGET_PATH/storage/uploads/photos"
mkdir -p "$TARGET_PATH/storage/uploads/documents"
mkdir -p "$TARGET_PATH/storage/uploads/temp"
mkdir -p "$TARGET_PATH/public/uploads"
mkdir -p "$TARGET_PATH/public/uploads/attachments"
mkdir -p "$TARGET_PATH/uploads"
mkdir -p "$TARGET_PATH/uploads/attachments"

echo "✅ تم إنشاء المجلدات"

echo ""
echo "🔒 ضبط الصلاحيات..."

# ضبط صلاحيات المجلدات للكتابة
chmod -R 755 "$TARGET_PATH/storage"
chmod -R 777 "$TARGET_PATH/storage/uploads"
chmod -R 777 "$TARGET_PATH/public/uploads" 2>/dev/null || true
chmod -R 777 "$TARGET_PATH/uploads" 2>/dev/null || true

# ضبط ownership للمجلدات
chown -R $(stat -c "%U:%G" "$TARGET_PATH") "$TARGET_PATH/storage" 2>/dev/null || true
chown -R $(stat -c "%U:%G" "$TARGET_PATH") "$TARGET_PATH/public/uploads" 2>/dev/null || true
chown -R $(stat -c "%U:%G" "$TARGET_PATH") "$TARGET_PATH/uploads" 2>/dev/null || true

echo "✅ تم ضبط الصلاحيات"

echo ""
echo "📄 إنشاء ملف .htaccess للحماية..."

# إنشاء .htaccess في مجلد storage للحماية
cat > "$TARGET_PATH/storage/.htaccess" << 'HTACCESS_STORAGE'
# Deny access to storage folder
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order deny,allow
    Deny from all
</IfModule>
HTACCESS_STORAGE

# إنشاء .htaccess في مجلد uploads للسماح بالصور والمستندات فقط
cat > "$TARGET_PATH/storage/uploads/.htaccess" << 'HTACCESS_UPLOADS'
# Allow only specific file types
<FilesMatch "\.(jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|txt)$">
    <IfModule mod_authz_core.c>
        Require all granted
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Allow from all
    </IfModule>
</FilesMatch>

# Deny everything else
<FilesMatch "^.*$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order deny,allow
        Deny from all
    </IfModule>
</FilesMatch>
HTACCESS_UPLOADS

echo "✅ تم إنشاء ملفات الحماية"

echo ""
echo "🧪 اختبار الكتابة..."

# اختبار الكتابة في المجلدات
TEST_FILE="$TARGET_PATH/storage/uploads/test_write.txt"
echo "Test file for write permissions" > "$TEST_FILE" 2>/dev/null

if [ -f "$TEST_FILE" ]; then
    echo "✅ الكتابة في storage/uploads تعمل"
    rm "$TEST_FILE"
else
    echo "❌ فشل في الكتابة في storage/uploads"
fi

# اختبار مجلدات إضافية
for dir in "public/uploads" "uploads"; do
    if [ -d "$TARGET_PATH/$dir" ]; then
        TEST_FILE="$TARGET_PATH/$dir/test_write.txt"
        echo "Test" > "$TEST_FILE" 2>/dev/null
        if [ -f "$TEST_FILE" ]; then
            echo "✅ الكتابة في $dir تعمل"
            rm "$TEST_FILE"
        else
            echo "❌ فشل في الكتابة في $dir"
        fi
    fi
done

echo ""
echo "📊 معلومات المجلدات:"
echo "===================="

ls -la "$TARGET_PATH/storage/" 2>/dev/null || echo "مجلد storage غير موجود"
ls -la "$TARGET_PATH/storage/uploads/" 2>/dev/null || echo "مجلد storage/uploads غير موجود"

echo ""
echo "🎉 إصلاح صلاحيات الرفع مكتمل!"
echo "=========================="
echo ""
echo "✅ المجلدات المنشأة:"
echo "- $TARGET_PATH/storage/uploads/"
echo "- $TARGET_PATH/storage/uploads/attachments/"
echo "- $TARGET_PATH/storage/uploads/photos/"
echo "- $TARGET_PATH/storage/uploads/documents/"
echo "- $TARGET_PATH/public/uploads/ (إذا أمكن)"
echo "- $TARGET_PATH/uploads/ (إذا أمكن)"
echo ""
echo "🔒 الصلاحيات: 777 (قابلة للكتابة)"
echo "🛡️ الحماية: .htaccess files مضافة"
echo ""
