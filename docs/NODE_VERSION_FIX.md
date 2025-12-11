# إصلاح مشكلة إصدار Node.js

## المشكلة
Node.js v12.22.9 قديم جداً ولا يدعم Vite 7.x الذي يتطلب Node.js 18+.

## الحلول

### الحل 1: استخدام Vite 4.x (موصى به للخوادم القديمة)

تم تحديث `package.json` لاستخدام Vite 4.5.0 الذي متوافق مع Node.js 12+.

```bash
cd docs
rm -rf node_modules package-lock.json
npm install
npm run build
```

### الحل 2: تحديث Node.js (إذا كان متاحاً)

#### استخدام NVM (Node Version Manager)

```bash
# تثبيت NVM
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

# إعادة تحميل shell
source ~/.bashrc

# تثبيت Node.js 18
nvm install 18
nvm use 18

# التحقق من الإصدار
node --version

# إعادة تثبيت dependencies
cd docs
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
```

### الحل 3: البناء محلياً ثم رفع الملفات

إذا لم تستطع تحديث Node.js على الخادم:

1. **بناء المشروع محلياً** (على جهازك):
```bash
cd docs
npm install
npm run build
```

2. **رفع مجلد `dist/` فقط**:
```bash
# من جهازك المحلي
cd docs/dist
scp -r * user@server:/home/user/web/yourdomain.com/public_html/opth/docs/
```

## التحقق من التوافق

| Node.js Version | Vite Version | Status |
|----------------|--------------|--------|
| 12.x | 4.x | ✅ متوافق |
| 14.x | 4.x - 5.x | ✅ متوافق |
| 16.x | 4.x - 6.x | ✅ متوافق |
| 18.x+ | 4.x - 7.x | ✅ متوافق |

## ملاحظة

بعد تحديث `package.json`، يجب:
1. حذف `node_modules` و `package-lock.json`
2. إعادة تثبيت: `npm install`
3. إعادة البناء: `npm run build`
