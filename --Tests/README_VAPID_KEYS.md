# كيفية توليد VAPID Keys لإشعارات Push

## الطريقة 1: استخدام Node.js (الأسهل والأكثر موثوقية)

إذا كان لديك Node.js مثبت:

```bash
npx web-push generate-vapid-keys
```

أو إذا كان لديك web-push مثبت محلياً:

```bash
npm install -g web-push
web-push generate-vapid-keys
```

## الطريقة 2: استخدام الأداة عبر الإنترنت

1. **ReactPWA Tools**: https://tools.reactpwa.com/vapid
2. **Web Push Book**: https://web-push-book.gauntface.com/demos/vapid-key-generator/
3. **Push Companion**: https://web-push-codelab.glitch.me/ (قد لا يعمل)

## الطريقة 3: استخدام السكريبت PHP المرفق

```bash
php generate_vapid_keys.php
```

**ملاحظة**: تأكد من تفعيل OpenSSL extension في PHP.

## الطريقة 4: استخدام Python

```python
from py_vapid import Vapid01
vapid = Vapid01()
vapid.generate_keys()
print("Public Key:", vapid.public_key)
print("Private Key:", vapid.private_key)
```

## بعد توليد المفاتيح:

1. **المفتاح العام (Public Key)**: استخدمه في `main.php` في دالة `getVapidPublicKey()`
2. **المفتاح الخاص (Private Key)**: احفظه بشكل آمن (مثل ملف `.env`) واستخدمه في الكود الخادم لإرسال الإشعارات

## مثال:

```javascript
// في main.php
function getVapidPublicKey() {
    return 'YOUR_GENERATED_PUBLIC_KEY_HERE';
}
```

```php
// في الكود الخادم (لاحقاً)
$privateKey = 'YOUR_GENERATED_PRIVATE_KEY_HERE';
```

