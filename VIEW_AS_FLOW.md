# View As Feature Flow - مخطط عمل خاصية استعراض الأدوار

## 1. بدء الاستعراض

```
Admin Dashboard
    ↓
View As Controls Section
    ↓
Choose Role (Doctor/Secretary)
    ↓
Click "استعراض واجهة [الدور]"
    ↓
GET /admin/view-as?role=[role]
    ↓
AdminController@viewAs()
    ↓
Auth::startViewAs($role)
    ↓
Update Session Variables
    ↓
Redirect to Role Dashboard
```

## 2. أثناء الاستعراض

```
Role Dashboard (Doctor/Secretary)
    ↓
View As Indicator in Top Bar
    ↓
"View As: [Role]" Warning
    ↓
"Exit" Button Available
    ↓
All Role Functions Accessible
    ↓
Admin Retains Full Permissions
```

## 3. إنهاء الاستعراض

```
Click "Exit" or "العودة لواجهة الإدارة"
    ↓
GET /admin/stop-view-as
    ↓
AdminController@stopViewAs()
    ↓
Auth::stopViewAs()
    ↓
Restore Original Admin Role
    ↓
Redirect to Admin Dashboard
```

## 4. Session Variables

### عند بدء View As:
```php
$_SESSION['view_as_mode'] = true;
$_SESSION['view_as_role'] = 'doctor'; // أو 'secretary'
$_SESSION['original_role'] = 'admin';
$_SESSION['user']['role'] = 'doctor'; // يتم تحديثه
```

### عند إنهاء View As:
```php
$_SESSION['view_as_mode'] = false;
unset($_SESSION['view_as_role']);
$_SESSION['user']['role'] = 'admin'; // يتم استرجاعه
unset($_SESSION['original_role']);
```

## 5. Authentication Flow

```
User Login
    ↓
Check User Role
    ↓
If Admin:
    - Show View As Controls
    - Allow Role Switching
    ↓
If Other Roles:
    - Normal Access
    - No View As Available
```

## 6. Role Access Control

```
Controller Constructor
    ↓
Auth::requireRole(['doctor', 'admin'])
    ↓
Check Current Effective Role
    ↓
If Admin in View As:
    - Allow Access to Viewed Role
    ↓
If Normal User:
    - Check Actual Role
```

## 7. UI Components

### Admin Dashboard:
- View As Controls Card
- Role Selection Buttons
- Current Status Display

### Top Bar (All Pages):
- View As Indicator (when active)
- Exit Button
- Role Display

### Navigation:
- Shows Role-Specific Menu
- Admin Functions Hidden (when in View As)
- Normal Role Functions Available

## 8. Security Considerations

```
View As Request
    ↓
Check if User is Admin
    ↓
Validate Requested Role
    ↓
Check Role Permissions
    ↓
Start View As Mode
    ↓
Log Activity
    ↓
Redirect to Role Dashboard
```

## 9. Error Handling

```
Invalid Role Request
    ↓
Show Error Message
    ↓
Redirect to Admin Dashboard
    ↓
Log Security Event

Unauthorized Access
    ↓
403 Forbidden
    ↓
Log Security Event
    ↓
Redirect to Login
```

## 10. Data Flow

```
Admin User
    ↓
View As Request
    ↓
Session Update
    ↓
Role Switch
    ↓
Dashboard Load
    ↓
Data Filtered by Role
    ↓
UI Rendered for Role
    ↓
Admin Sees Role View
```

## 11. Navigation Flow

```
Admin Dashboard
    ↓
View As: Doctor
    ↓
Doctor Dashboard
    ↓
Doctor Menu Items
    ↓
Doctor Functions
    ↓
Exit View As
    ↓
Admin Dashboard
    ↓
Admin Menu Items
    ↓
Admin Functions
```

## 12. Session Management

```
Session Start
    ↓
User Authentication
    ↓
Role Assignment
    ↓
View As Check
    ↓
Effective Role Calculation
    ↓
UI Rendering
    ↓
Function Access Control
```

هذا المخطط يوضح كيفية عمل خاصية View As من البداية للنهاية، مع التركيز على الأمان والتحكم في الوصول.
