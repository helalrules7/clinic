---
name: خطة شاملة لإصلاح وتحسين Treeview مع تنسيق بطاقات المرضى
overview: خطة شاملة تجمع إصلاحات treeview، تحسينات الأداء، تنسيق بطاقات المرضى مع color markers، ومرحلة النشر
todos:
  - id: fix-toggle-render
    content: إصلاح toggleFolder لإضافة render() فوري عند expand/collapse
    status: pending
  - id: fix-auto-expand-children
    content: تغيير expandFolder إلى expandChildren=true عند النقر على المجلد
    status: pending
  - id: fix-active-state-persistence
    content: حفظ حالة active في localStorage وتحميلها في constructor
    status: pending
  - id: move-filters-div
    content: نقل filters div إلى نهاية treeview-container في patients.php
    status: pending
  - id: update-filters-css
    content: تحديث CSS لضمان موضع filters في النهاية
    status: pending
  - id: add-card-marker-styling
    content: إضافة تنسيق border وglow للبطاقات مع color markers
    status: pending
  - id: update-render-patients
    content: تحديث renderFolderPatients لإضافة data-has-color-marker
    status: pending
  - id: update-fetch-markers
    content: تحديث fetchColorMarkersForPatients لتطبيق التنسيق
    status: pending
  - id: add-request-cancellation
    content: إضافة AbortController لإلغاء الطلبات القديمة
    status: pending
  - id: improve-debouncing
    content: تحسين debouncing strategy
    status: pending
  - id: add-document-fragment
    content: استخدام DocumentFragment لتحسين DOM updates
    status: pending
  - id: deploy-files
    content: رفع الملفات المحدثة إلى السيرفر البعيد
    status: pending
isProject: false
---

# خطة شاملة لإصلاح وتحسين Treeview مع تنسيق بطاقات المرضى

## Phase 1: إصلاح مشاكل Treeview الأساسية

### 1.1 إصلاح toggleFolder - render فوري

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js) - السطر 357

- إضافة `render()` فوري عند expand/collapse
- معالجة الأخطاء بشكل صحيح

### 1.2 التوسيع التلقائي للمجلدات الفرعية

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js) - السطر 314

- تغيير `expandFolder(folderId, false)` إلى `expandFolder(folderId, true)` عند النقر على المجلد
- ضمان توسيع جميع المجلدات الفرعية تلقائياً

### 1.3 حفظ حالة active في localStorage

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js) - السطر 145 و 539

- حفظ `activeFolderId` في localStorage
- تحميل `activeFolderId` من localStorage في constructor
- ضمان استمرار حالة active بعد refresh

### 1.4 نقل filters div إلى نهاية treeview-container

**الملف:** [app/Views/doctor/patients.php](app/Views/doctor/patients.php) - السطر 691

- نقل `sidebar-filters` div إلى داخل `treeview-container`
- تحديث CSS لضمان موضع filters في النهاية

## Phase 2: تنسيق بطاقات المرضى مع Color Markers

### 2.1 إضافة border ملون وglow للبطاقات

**الملف:** [app/Views/doctor/assets/css/patients.css](app/Views/doctor/assets/css/patients.css)

- إضافة class `patient-card-has-marker` للبطاقات التي تحتوي على color marker
- border ملون بنفس لون الـ marker
- glow effect مشابه للعلامة
- دعم الوضع الداكن والفاتح

**CSS الجديد:**

```css
.patient-card[data-has-color-marker="true"] {
    border: 2px solid var(--marker-color, var(--border));
    box-shadow: 0 0 0 1px var(--marker-color, transparent),
                0 2px 8px rgba(0, 0, 0, 0.1),
                0 0 12px var(--marker-color-rgb, transparent);
    transition: all 0.3s ease;
}

.patient-card[data-has-color-marker="true"]:hover {
    box-shadow: 0 0 0 2px var(--marker-color, transparent),
                0 4px 16px rgba(0, 0, 0, 0.15),
                0 0 20px var(--marker-color-rgb, transparent);
    transform: translateY(-2px);
}
```

### 2.2 تحديث renderFolderPatients

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js) - السطر 6263

- إضافة `data-has-color-marker="true"` للبطاقات التي تحتوي على marker
- إضافة inline style للـ `--marker-color` CSS variable
- إضافة class `patient-card-has-marker`

### 2.3 تحديث fetchColorMarkersForPatients

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js) - السطر 6458

- عند تحديث color marker، تحديث border وglow للبطاقة
- إضافة/إزالة `data-has-color-marker` attribute

## Phase 3: تحسينات الأداء (Priority 1)

### 3.1 Request Cancellation

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- استخدام `AbortController` لإلغاء الطلبات القديمة
- تخزين `AbortController` لكل folder request

### 3.2 تحسين Debouncing

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js) - السطر 737

- تقليل debounce time من 100ms إلى 50ms
- إضافة immediate execution للـ first click

### 3.3 DocumentFragment للـ DOM Updates

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js) - السطر 6202

- استخدام `DocumentFragment` في `renderFolderPatients`
- تقليل reflows و repaints

## Phase 4: النشر على السيرفر البعيد

### 4.1 رفع الملفات المحدثة

- `app/Views/doctor/assets/js/patients.js`
- `app/Views/doctor/assets/css/patients.css`
- `app/Views/doctor/patients.php`

### 4.2 التحقق من النشر

- التحقق من وجود الملفات
- التحقق من الصلاحيات
- اختبار الوظائف

## تفاصيل التغييرات

### في patients.js:

1. **toggleFolder (السطر 357):**
```javascript
toggleFolder(folderId) {
    const index = this.options.expandedFolders.indexOf(folderId);
    if (index > -1) {
        // Collapse
        this.options.expandedFolders.splice(index, 1);
        this.saveExpandedState();
        this.render(); // Render immediately
    } else {
        // Expand - add to expandedFolders immediately for UI feedback
        this.options.expandedFolders.push(folderId);
        this.saveExpandedState();
        this.render(); // Render immediately to show expand icon change
        
        // Then load data
        this.expandFolder(folderId, false).then(() => {
            this.render(); // Render again with children
        }).catch(error => {
            console.error('Error expanding folder:', error);
            const errorIndex = this.options.expandedFolders.indexOf(folderId);
            if (errorIndex > -1) {
                this.options.expandedFolders.splice(errorIndex, 1);
                this.saveExpandedState();
                this.render();
            }
        });
    }
}
```

2. **attachEventListeners (السطر 314):**
```javascript
// عند النقر على folder label، استخدم expandChildren=true
this.expandFolder(folderId, true).then(() => {
    if (this.options.onFolderClick) {
        this.options.onFolderClick(folderId);
    }
});
```

3. **highlightActive (السطر 539):**
```javascript
highlightActive(folderId) {
    this.activeFolderId = folderId;
    
    // Save active folder to localStorage
    if (folderId) {
        localStorage.setItem('treeviewActiveFolder', folderId);
    } else {
        localStorage.removeItem('treeviewActiveFolder');
    }
    
    // Re-render to apply active state
    if (this.container && this.treeData) {
        this.render();
        // Scroll into view...
    }
}
```

4. **constructor (السطر 145):**
```javascript
// Load active folder from localStorage
const savedActiveFolder = localStorage.getItem('treeviewActiveFolder');
this.activeFolderId = savedActiveFolder || null;
```

5. **renderFolderPatients (السطر 6263):**
```javascript
// إضافة data-has-color-marker و CSS variables
html += `
    <div class="${sizeClass} mb-3">
        <div class="card patient-card clickable h-100 ${colorMarker ? 'patient-card-has-marker' : ''}" 
             data-patient-id="${patient.id}"
             data-has-color-marker="${colorMarker ? 'true' : 'false'}"
             style="${colorMarker ? `--marker-color: ${colorMarker}; --marker-color-rgb: ${hexToRgb(colorMarker)};` : ''}">
```

6. **fetchColorMarkersForPatients (السطر 6458):**
```javascript
// عند تحديث marker، تحديث border وglow
const card = document.querySelector(`[data-patient-id="${patient.id}"]`);
if (card && data.color_code) {
    card.setAttribute('data-has-color-marker', 'true');
    card.classList.add('patient-card-has-marker');
    card.style.setProperty('--marker-color', data.color_code);
    card.style.setProperty('--marker-color-rgb', hexToRgb(data.color_code));
}
```

7. **openFolder (السطر 5365):**
```javascript
// إضافة AbortController
let currentFolderAbortController = null;

function openFolder(folderId) {
    // Cancel previous request
    if (currentFolderAbortController) {
        currentFolderAbortController.abort();
    }
    
    currentFolderAbortController = new AbortController();
    
    fetch(`/api/patient-folders/${folderId}/patients`, {
        signal: currentFolderAbortController.signal,
        // ...
    })
}
```

8. **openFolderDebounced (السطر 737):**
```javascript
function openFolderDebounced(folderId) {
    pendingFolderId = folderId;
    
    if (folderOpenTimeout) {
        clearTimeout(folderOpenTimeout);
    }
    
    folderOpenTimeout = setTimeout(() => {
        if (pendingFolderId === folderId) {
            openFolder(folderId);
        }
    }, 50); // Reduced from 100ms
}
```

9. **renderFolderPatients - DocumentFragment (السطر 6440):**
```javascript
// استخدام DocumentFragment
const fragment = document.createDocumentFragment();
const tempDiv = document.createElement('div');
tempDiv.innerHTML = html;

while (tempDiv.firstChild) {
    fragment.appendChild(tempDiv.firstChild);
}

container.innerHTML = '';
container.appendChild(fragment);
```


### في patients.css:

1. **إضافة styles للبطاقات مع markers:**
```css
.patient-card-has-marker,
.patient-card[data-has-color-marker="true"] {
    border: 2px solid var(--marker-color, var(--border)) !important;
    box-shadow: 0 0 0 1px var(--marker-color, transparent),
                0 2px 8px rgba(0, 0, 0, 0.1),
                0 0 12px var(--marker-color-rgb, transparent) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.patient-card-has-marker::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: inherit;
    padding: 2px;
    background: var(--marker-color, transparent);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, 
                   linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0.3;
    pointer-events: none;
}

.patient-card-has-marker:hover {
    box-shadow: 0 0 0 2px var(--marker-color, transparent),
                0 4px 16px rgba(0, 0, 0, 0.15),
                0 0 20px var(--marker-color-rgb, transparent) !important;
    transform: translateY(-2px);
}

/* Dark theme adjustments */
[data-theme="dark"] .patient-card-has-marker,
.dark-theme .patient-card-has-marker {
    box-shadow: 0 0 0 1px var(--marker-color, transparent),
                0 2px 8px rgba(0, 0, 0, 0.3),
                0 0 16px var(--marker-color-rgb, transparent) !important;
}

[data-theme="dark"] .patient-card-has-marker:hover,
.dark-theme .patient-card-has-marker:hover {
    box-shadow: 0 0 0 2px var(--marker-color, transparent),
                0 4px 20px rgba(0, 0, 0, 0.4),
                0 0 24px var(--marker-color-rgb, transparent) !important;
}
```

2. **تحديث treeview-container و filters:**
```css
.treeview-container {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
}

.sidebar-filters {
    border-top: 1px solid var(--border);
    padding: 12px;
    margin-top: auto;
    flex-shrink: 0;
}
```


### في patients.php:

1. **نقل filters div:**
```php
<div class="treeview-container" id="folderTreeview">
    <!-- Treeview will be rendered here by JavaScript -->
    
    <!-- Filters Section - moved inside treeview-container -->
    <div class="sidebar-filters" id="sidebarFilters">
        <!-- Filters will be added by FilterManager -->
    </div>
</div>
```


## Phase 5: النشر

### 5.1 رفع الملفات

- `app/Views/doctor/assets/js/patients.js`
- `app/Views/doctor/assets/css/patients.css`
- `app/Views/doctor/patients.php`

### 5.2 التحقق

- التحقق من الملفات والصلاحيات
- اختبار الوظائف

## ترتيب التنفيذ

1. إصلاح toggleFolder و attachEventListeners
2. إضافة حفظ/تحميل activeFolderId
3. نقل filters div وتحديث CSS
4. إضافة تنسيق بطاقات المرضى
5. إضافة request cancellation و DocumentFragment
6. النشر على السيرفر