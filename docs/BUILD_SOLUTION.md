# حل مشكلة البناء مع Node.js 12

## المشكلة
Node.js v12.22.9 قديم جداً ولا يدعم Vite (حتى 3.x يحتاج Node.js 14+).

## الحلول

### الحل 1: تحديث Node.js (موصى به)

#### استخدام NVM

```bash
# تثبيت NVM
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

# إعادة تحميل shell
source ~/.bashrc
# أو
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# تثبيت Node.js 18 LTS
nvm install 18
nvm use 18
nvm alias default 18

# التحقق
node --version  # يجب أن يكون v18.x.x

# إعادة تثبيت dependencies
cd /var/www/html/clinic/docs
rm -rf node_modules package-lock.json
npm install
npm run build
```

#### تحديث Node.js مباشرة (Ubuntu/Debian)

```bash
# إضافة NodeSource repository
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -

# تثبيت Node.js 18
sudo apt-get install -y nodejs

# التحقق
node --version
npm --version

# إعادة تثبيت
cd /var/www/html/clinic/docs
rm -rf node_modules package-lock.json
npm install
npm run build
```

### الحل 2: البناء محلياً ثم رفع الملفات (أسهل)

#### على جهازك المحلي (يجب أن يكون Node.js 14+):

```bash
# 1. استنساخ المشروع أو نسخ مجلد docs
cd /path/to/clinic/docs

# 2. تثبيت dependencies
npm install

# 3. البناء
npm run build

# 4. رفع مجلد dist/ إلى الخادم
cd dist
scp -r * root@your-server.com:/home/AhmedHelal/web/hclinic.clinic/public_html/opth/docs/
```

#### أو استخدام rsync:

```bash
# من جهازك المحلي
cd /path/to/clinic/docs/dist
rsync -avz --delete . root@your-server.com:/home/AhmedHelal/web/hclinic.clinic/public_html/opth/docs/
```

### الحل 3: استخدام Docker (اختياري)

```bash
# إنشاء Dockerfile
cat > Dockerfile << 'EOF'
FROM node:18-alpine
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build
EOF

# البناء
docker build -t docs-builder .
docker run --rm -v $(pwd)/dist:/app/dist docs-builder

# نسخ الملفات
cp -r dist/* /path/to/deployment/
```

## بعد البناء

### 1. رفع الملفات

```bash
# تأكد من المسار الصحيح
TARGET_DIR="/home/AhmedHelal/web/hclinic.clinic/public_html/opth/docs"

# إنشاء المجلد إذا لم يكن موجوداً
mkdir -p "$TARGET_DIR"

# نسخ الملفات
cp -r dist/* "$TARGET_DIR/"

# نسخ .htaccess
cp .htaccess "$TARGET_DIR/"

# تعيين الصلاحيات
chmod -R 755 "$TARGET_DIR"
```

### 2. التحقق من المسارات

بعد البناء، افتح `dist/index.html` وتحقق من أن جميع المسارات تبدأ بـ `/opth/docs/`

## ملاحظة مهمة

إذا لم تستطع تحديث Node.js على الخادم، **الحل الأفضل هو البناء محلياً** ثم رفع مجلد `dist/` فقط.
