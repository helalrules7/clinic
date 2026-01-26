---
name: تحسين الأداء وسرعة التنقل في Folderview
overview: خطة شاملة لتحسين أداء folderview وسرعة التنقل من خلال تحسينات في API وFrontend وRendering
todos:
  - id: api-pagination
    content: إضافة pagination للـ API endpoint getFolderPatients
    status: pending
  - id: batch-markers-tags
    content: إنشاء batch API endpoints للـ color markers و tags
    status: pending
  - id: request-cancellation
    content: إضافة AbortController لإلغاء الطلبات القديمة
    status: pending
  - id: batch-frontend-requests
    content: تحديث fetchColorMarkersForPatients و fetchTagsForPatients لاستخدام batch API
    status: pending
  - id: virtual-scrolling
    content: تنفيذ virtual scrolling للمجلدات الكبيرة
    status: pending
  - id: document-fragment
    content: استخدام DocumentFragment لتحسين DOM updates
    status: pending
  - id: raf-rendering
    content: استخدام requestAnimationFrame للـ chunked rendering
    status: pending
  - id: prefetching
    content: إضافة prefetching للمجلدات الفرعية
    status: pending
  - id: optimistic-ui
    content: إضافة optimistic UI updates
    status: pending
  - id: improve-debouncing
    content: تحسين debouncing strategy
    status: pending
  - id: lru-cache
    content: تحسين cache strategy مع LRU
    status: pending
  - id: indexeddb-cache
    content: إضافة IndexedDB للـ persistent cache (optional)
    status: pending
isProject: false
---

# خطة تحسين الأداء وسرعة التنقل في Folderview

## الوضع الحالي

### نقاط القوة الموجودة:

- Debouncing لفتح المجلدات (100ms)
- Cache للمجلدات (1 دقيقة TTL)
- Cache للمجلدات الفرعية في treeview (1 دقيقة TTL)
- Lazy loading للصور باستخدام IntersectionObserver
- حفظ حالة التنقل في localStorage/sessionStorage

### نقاط الضعف:

- API endpoint `/api/patient-folders/{id}/patients` لا يدعم pagination - يعيد جميع المرضى دفعة واحدة
- `fetchColorMarkersForPatients` و `fetchTagsForPatients` يرسلان طلبات منفصلة لكل مريض (N requests)
- لا يوجد request cancellation عند التنقل السريع
- لا يوجد prefetching للمجلدات المحتملة
- `renderFolderPatients` يعيد بناء DOM بالكامل في كل مرة
- لا يوجد virtual scrolling للمجلدات الكبيرة

## التحسينات المقترحة

### Phase 1: تحسين API Backend

#### 1.1 إضافة Pagination للـ API Endpoint

**الملف:** [app/Controllers/ApiController.php](app/Controllers/ApiController.php)

- إضافة query parameters: `page`, `per_page` (default: 50)
- تعديل SQL queries لإضافة `LIMIT` و `OFFSET`
- إرجاع metadata: `total`, `page`, `per_page`, `total_pages`
- دعم sorting parameters: `sort_by`, `sort_order`

**التغييرات:**

```php
// في getFolderPatients()
$page = (int)($_GET['page'] ?? 1);
$perPage = min((int)($_GET['per_page'] ?? 50), 100); // Max 100 per page
$offset = ($page - 1) * $perPage;

// إضافة LIMIT و OFFSET للـ SQL queries
// إرجاع metadata في response
```

#### 1.2 Batch API للـ Color Markers و Tags

**الملف:** [app/Controllers/ApiController.php](app/Controllers/ApiController.php)

- إنشاء endpoint جديد: `POST /api/patient-color-markers/batch`
- إنشاء endpoint جديد: `POST /api/patients/tags/batch`
- قبول array من patient IDs وإرجاع جميع البيانات دفعة واحدة

**Endpoints الجديدة:**

```php
// POST /api/patient-color-markers/batch
// Body: { patient_ids: [1, 2, 3, ...] }
// Returns: { markers: { 1: '#ef4444', 2: null, ... } }

// POST /api/patients/tags/batch
// Body: { patient_ids: [1, 2, 3, ...] }
// Returns: { tags: { 1: [{id, name, color}], 2: [], ... } }
```

### Phase 2: تحسين Frontend - Request Management

#### 2.1 Request Cancellation

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- استخدام `AbortController` لإلغاء الطلبات القديمة
- تخزين `AbortController` لكل folder request
- إلغاء الطلبات عند فتح مجلد جديد

**التغييرات:**

```javascript
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

#### 2.2 Batch Requests للـ Color Markers و Tags

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- استبدال `fetchColorMarkersForPatients` و `fetchTagsForPatients` بطلبات batch
- تجميع جميع patient IDs وإرسال طلب واحد

**التغييرات:**

```javascript
async function fetchColorMarkersForPatients(patients) {
    if (!patients || patients.length === 0) return;
    
    const patientIds = patients.map(p => p.id);
    
    // Single batch request instead of N requests
    fetch('/api/patient-color-markers/batch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ patient_ids: patientIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.markers) {
            // Update all patients at once
            patients.forEach(patient => {
                if (data.markers[patient.id]) {
                    patient.color_marker = data.markers[patient.id];
                    updatePatientCardMarker(patient.id, data.markers[patient.id]);
                }
            });
        }
    });
}
```

### Phase 3: تحسين Rendering

#### 3.1 Virtual Scrolling للمرضى

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- تنفيذ virtual scrolling للمجلدات التي تحتوي على أكثر من 50 مريض
- عرض فقط العناصر المرئية + buffer
- استخدام `IntersectionObserver` لتحديد العناصر المرئية

**التنفيذ:**

```javascript
class VirtualScroller {
    constructor(container, items, renderItem, itemHeight = 200) {
        this.container = container;
        this.items = items;
        this.renderItem = renderItem;
        this.itemHeight = itemHeight;
        this.visibleStart = 0;
        this.visibleEnd = 0;
        this.buffer = 5; // Render 5 extra items above/below
    }
    
    update() {
        const scrollTop = this.container.scrollTop;
        const containerHeight = this.container.clientHeight;
        
        this.visibleStart = Math.floor(scrollTop / this.itemHeight);
        this.visibleEnd = Math.ceil((scrollTop + containerHeight) / this.itemHeight);
        
        const start = Math.max(0, this.visibleStart - this.buffer);
        const end = Math.min(this.items.length, this.visibleEnd + this.buffer);
        
        // Render only visible items
        this.render(start, end);
    }
}
```

#### 3.2 DocumentFragment للـ Batch DOM Updates

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- استخدام `DocumentFragment` في `renderFolderPatients` لتجميع جميع العناصر قبل إضافتها للـ DOM
- تقليل reflows و repaints

**التغييرات:**

```javascript
function renderFolderPatients(patients) {
    // ... existing code ...
    
    const fragment = document.createDocumentFragment();
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    
    while (tempDiv.firstChild) {
        fragment.appendChild(tempDiv.firstChild);
    }
    
    container.innerHTML = '';
    container.appendChild(fragment);
}
```

#### 3.3 requestAnimationFrame للـ Smooth Rendering

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- استخدام `requestAnimationFrame` لتقسيم rendering إلى chunks
- منع blocking للـ main thread

**التغييرات:**

```javascript
function renderFolderPatientsChunked(patients, chunkSize = 20) {
    const container = document.getElementById('folderPatientsContainer');
    let index = 0;
    
    function renderChunk() {
        const chunk = patients.slice(index, index + chunkSize);
        // Render chunk
        // ...
        
        index += chunkSize;
        if (index < patients.length) {
            requestAnimationFrame(renderChunk);
        }
    }
    
    requestAnimationFrame(renderChunk);
}
```

### Phase 4: تحسين Navigation Speed

#### 4.1 Prefetching للمجلدات المحتملة

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- عند فتح مجلد، prefetch للمجلدات الفرعية
- استخدام `link rel="prefetch"` أو fetch في background
- تخزين البيانات في cache

**التنفيذ:**

```javascript
function prefetchSubFolders(folderId) {
    // Prefetch sub-folders data in background
    fetch(`/api/patient-folders/${folderId}/sub-folders/custom`, {
        priority: 'low' // Browser hint
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok && data.sub_folders) {
            // Cache for future use
            folderTreeview.subFoldersCache[folderId] = {
                data: data.sub_folders,
                timestamp: Date.now()
            };
        }
    });
}
```

#### 4.2 Optimistic UI Updates

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- تحديث UI فوراً عند النقر على مجلد (قبل تحميل البيانات)
- إظهار skeleton/loading state
- تحديث البيانات عند وصولها

**التنفيذ:**

```javascript
function openFolder(folderId) {
    // Optimistic update: show loading state immediately
    showFolderLoadingState();
    
    // Update treeview active state immediately
    if (folderTreeview) {
        folderTreeview.highlightActive(folderId);
    }
    
    // Then load data
    fetch(...).then(data => {
        // Update with real data
    });
}
```

#### 4.3 تحسين Debouncing

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- تقليل debounce time من 100ms إلى 50ms للاستجابة الأسرع
- إضافة immediate execution للـ first click

**التغييرات:**

```javascript
let lastFolderId = null;
let lastClickTime = 0;

function openFolderDebounced(folderId) {
    const now = Date.now();
    const timeSinceLastClick = now - lastClickTime;
    
    // Immediate execution if same folder clicked quickly
    if (folderId === lastFolderId && timeSinceLastClick < 300) {
        return; // Already loading
    }
    
    // Immediate execution for first click or different folder
    if (folderId !== lastFolderId || timeSinceLastClick > 500) {
        openFolder(folderId);
        lastFolderId = folderId;
        lastClickTime = now;
        return;
    }
    
    // Debounce for rapid clicks on same folder
    pendingFolderId = folderId;
    if (folderOpenTimeout) clearTimeout(folderOpenTimeout);
    folderOpenTimeout = setTimeout(() => {
        if (pendingFolderId === folderId) {
            openFolder(folderId);
        }
    }, 50); // Reduced from 100ms
}
```

### Phase 5: تحسين Cache Strategy

#### 5.1 تحسين Cache TTL و Size Management

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- زيادة cache TTL للمجلدات المستقرة (5 دقائق)
- إضافة LRU cache للحد من استخدام الذاكرة
- Cache invalidation عند التعديلات

**التغييرات:**

```javascript
const folderCache = {
    data: new Map(),
    maxSize: 50, // Max 50 folders in cache
    maxAge: 300000, // 5 minutes
    
    set(key, data) {
        // LRU eviction if cache is full
        if (this.data.size >= this.maxSize) {
            const firstKey = this.data.keys().next().value;
            this.data.delete(firstKey);
        }
        
        this.data.set(key, {
            data,
            timestamp: Date.now(),
            accessCount: 0
        });
    },
    
    get(key) {
        const cached = this.data.get(key);
        if (cached && (Date.now() - cached.timestamp) < this.maxAge) {
            cached.accessCount++;
            // Move to end (LRU)
            this.data.delete(key);
            this.data.set(key, cached);
            return cached.data;
        }
        this.data.delete(key);
        return null;
    }
};
```

#### 5.2 IndexedDB للـ Persistent Cache

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- استخدام IndexedDB لتخزين بيانات المجلدات بشكل دائم
- تخزين حتى بعد إغلاق المتصفح
- Sync مع server عند الحاجة

**التنفيذ:**

```javascript
class FolderCacheDB {
    constructor() {
        this.dbName = 'clinicFoldersCache';
        this.version = 1;
        this.db = null;
    }
    
    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.version);
            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                this.db = request.result;
                resolve();
            };
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains('folders')) {
                    db.createObjectStore('folders', { keyPath: 'id' });
                }
            };
        });
    }
    
    async get(folderId) {
        const transaction = this.db.transaction(['folders'], 'readonly');
        const store = transaction.objectStore('folders');
        return store.get(folderId);
    }
    
    async set(folderId, data) {
        const transaction = this.db.transaction(['folders'], 'readwrite');
        const store = transaction.objectStore('folders');
        return store.put({ id: folderId, data, timestamp: Date.now() });
    }
}
```

### Phase 6: تحسينات إضافية

#### 6.1 Service Worker للـ Offline Support

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- تسجيل service worker للـ caching
- تخزين بيانات المجلدات للوصول offline
- Background sync عند الاتصال

#### 6.2 Web Workers للـ Heavy Processing

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- استخدام Web Worker لمعالجة البيانات الكبيرة
- Filtering و sorting في worker
- تقليل blocking للـ main thread

#### 6.3 تحسين Search Performance

**الملف:** [app/Views/doctor/assets/js/patients.js](app/Views/doctor/assets/js/patients.js)

- استخدام Web Worker للبحث في البيانات الكبيرة
- Debouncing محسّن (300ms → 150ms)
- Highlighting النتائج بشكل فعال

## ترتيب الأولويات

### Priority 1 (High Impact, Low Effort):

1. Batch API للـ color markers و tags
2. Request cancellation
3. DocumentFragment للـ DOM updates
4. تحسين debouncing

### Priority 2 (High Impact, Medium Effort):

5. API pagination
6. Virtual scrolling
7. Prefetching
8. تحسين cache strategy

### Priority 3 (Medium Impact, High Effort):

9. IndexedDB cache
10. Service Worker
11. Web Workers

## المقاييس المتوقعة

- **تقليل عدد API requests:** من N+1 إلى 2-3 requests لكل folder
- **تحسين وقت التحميل:** تقليل 50-70% للـ initial load
- **تحسين سرعة التنقل:** تقليل 60-80% في وقت الانتقال بين المجلدات
- **تحسين استخدام الذاكرة:** تقليل 40-50% مع LRU cache
- **تحسين smoothness:** 60 FPS مع virtual scrolling و requestAnimationFrame