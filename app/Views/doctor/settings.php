<?php
/**
 * Doctor Settings Template
 * صفحة إعدادات الأطباء
 */
?>

<link href="/app/Views/doctor/assets/css/settings.css?v=<?= file_exists(__DIR__ . '/assets/css/settings.css') ? filemtime(__DIR__ . '/assets/css/settings.css') : time() ?>" rel="stylesheet">

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>
                        System Settings
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['success_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['error_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>

                    <form method="POST" action="/doctor/settings" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <!-- Personal Preferences -->
                        <div class="settings-section">
                            <h5><i class="fas fa-user-cog me-2"></i>Personal Preferences</h5>
                            
                            <!-- 1. Don't create alert for new created appointments -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Don't create alert for new created appointments</label>
                                        <div class="form-text">When enabled, new appointments will not automatically create alerts</div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="dontCreateAlertForAppointments" 
                                               onchange="updatePersonalPreference('dont_create_alert_for_appointments', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Don't create notification for appointments -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Don't create notification for appointments</label>
                                        <div class="form-text">When enabled, appointment actions (create, update, delete) will not automatically create notifications</div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="dontCreateNotificationForAppointments" 
                                               onchange="updatePersonalPreference('dont_create_notification_for_appointments', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Back to top Display -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Back to Top Display</label>
                                        <div class="form-text">Show or hide the scroll to top button</div>
                                        <div class="mt-2">
                                            <div class="demo-preview" style="position: relative; height: 70px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px;">
                                                <div style="position: absolute; bottom: 10px; right: 10px; width: 50px; height: 50px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                                    <i class="bi bi-arrow-up"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="backToTopDisplay" 
                                               onchange="updatePersonalPreference('back_to_top_display', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Desktop Dock -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Desktop Dock</label>
                                        <div class="form-text">Show or hide the quick access dock on desktop</div>
                                        <div class="mt-2">
                                            <div class="demo-preview" style="position: relative; height: 80px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px;">
                                                <center>
                                                <div style="position: absolute; bottom: 10px; right: 10px; display: flex; gap: 8px; background: rgba(255,255,255,0.9); padding: 8px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="bi bi-calendar3" style="font-size: 18px;"></i>
                                                    </div>
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="bi bi-people" style="font-size: 18px;"></i>
                                                    </div>
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="bi bi-capsule" style="font-size: 18px;"></i>
                                                    </div>
                                                </div>
                                                </center>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="desktopDock" 
                                               onchange="updatePersonalPreference('desktop_dock_enabled', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Dock -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Mobile Dock</label>
                                        <div class="form-text">Show or hide the quick access dock on mobile devices</div>
                                        <div class="mt-2">
                                            <div class="demo-preview" style="position: relative; height: 70px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px;">
                                                <div style="position: absolute; bottom: 10px; right: 10px; width: 50px; height: 50px; background: var(--accent); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                                    <i class="bi bi-grid-3x3-gap" style="font-size: 24px;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="mobileDock" 
                                               onchange="updatePersonalPreference('mobile_dock_enabled', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- Dock Auto-Hide -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Dock Auto-Hide</label>
                                        <div class="form-text">Automatically hide the dock when not in use. The dock will show on hover when hidden.</div>
                                        <div class="mt-2">
                                            <div class="demo-preview" style="position: relative; height: 100px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 10px; overflow: hidden;">
                                                <!-- Hidden State -->
                                                <div id="dockAutohideDemoHidden" style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%) translateY(90%); opacity: 0.3; transition: all 0.4s ease; display: flex; gap: 8px; background: rgba(255,255,255,0.9); padding: 8px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; opacity: 0.5;">
                                                        <i class="bi bi-calendar3" style="font-size: 18px;"></i>
                                                    </div>
                                                </div>
                                                <!-- Shown State -->
                                                <div id="dockAutohideDemoShown" style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%) translateY(0); opacity: 1; transition: all 0.4s ease; display: flex; gap: 8px; background: rgba(255,255,255,0.9); padding: 8px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="bi bi-calendar3" style="font-size: 18px;"></i>
                                                    </div>
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="bi bi-people" style="font-size: 18px;"></i>
                                                    </div>
                                                    <div style="width: 40px; height: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white;">
                                                        <i class="bi bi-capsule" style="font-size: 18px;"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="dockAutohide" 
                                               onchange="updatePersonalPreference('dock_autohide', this.checked); updateDockAutohideDemo(this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Current Mode (Dark/Light) -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Current Mode</label>
                                        <div class="form-text">Switch between dark and light theme</div>
                                    </div>
                                    <label class="switch" for="currentModeInput">
                                        <input id="currentModeInput" type="checkbox" 
                                               onchange="updatePersonalPreference('theme', this.checked ? 'dark' : 'light')" />
                                        <div class="slider round">
                                            <div class="sun-moon">
                                                <svg id="settings-moon-dot-1" class="moon-dot" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-moon-dot-2" class="moon-dot" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-moon-dot-3" class="moon-dot" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-light-ray-1" class="light-ray" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-light-ray-2" class="light-ray" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-light-ray-3" class="light-ray" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-1" class="cloud-dark" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-2" class="cloud-dark" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-3" class="cloud-dark" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-4" class="cloud-light" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-5" class="cloud-light" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                                <svg id="settings-cloud-6" class="cloud-light" viewBox="0 0 100 100">
                                                    <circle cx="50" cy="50" r="50"></circle>
                                                </svg>
                                            </div>
                                            <div class="stars">
                                                <svg id="settings-star-1" class="star" viewBox="0 0 20 20">
                                                    <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                                                </svg>
                                                <svg id="settings-star-2" class="star" viewBox="0 0 20 20">
                                                    <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                                                </svg>
                                                <svg id="settings-star-3" class="star" viewBox="0 0 20 20">
                                                    <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                                                </svg>
                                                <svg id="settings-star-4" class="star" viewBox="0 0 20 20">
                                                    <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- 5. Push Notification -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Push Notification</label>
                                        <div class="form-text">Enable or disable push notifications</div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="pushNotificationsEnabled" 
                                               onchange="updatePersonalPreference('push_notifications_enabled', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 6. Subscribed push notification browsers -->
                            <div class="setting-item">
                                <label class="form-label">Subscribed Push Notification Browsers</label>
                                <div id="pushSubscriptionsList" class="list-group mb-2" style="max-height: 200px; overflow-y: auto;">
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-hourglass-split me-2"></i>Loading...
                                    </div>
                                </div>
                                <div class="form-text">List of browsers subscribed to push notifications. Click delete to remove a subscription.</div>
                            </div>

                            <!-- 7. Display Dashboard items rearrange buttons on mobile -->
                            <div class="setting-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0">Display Dashboard Items Rearrange Buttons on Mobile</label>
                                        <div class="form-text">Show or hide rearrange buttons for dashboard items on mobile devices</div>
                                    </div>
                                    <div class="toggle-switch-wrapper">
                                        <input type="checkbox" class="toggle-switch" id="dashboardRearrangeMobile" 
                                               onchange="updatePersonalPreference('dashboard_rearrange_mobile', this.checked)">
                                    </div>
                                </div>
                            </div>

                            <!-- 8. Sidebar Items -->
                            <div class="setting-item">
                                <label class="form-label">Sidebar Items</label>
                                <div class="form-text mb-2">Select which items to display in the sidebar. Some items are always enabled.</div>
                                <div id="sidebarItemsList" class="list-group sidebar-items-list" style="max-height: 300px; overflow-y: auto;">
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-hourglass-split me-2"></i>Loading...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- General Settings -->
                        <div class="settings-section">
                            <h5><i class="fas fa-info-circle me-2"></i>General Settings</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_name" class="form-label">Clinic Name</label>
                                        <input type="text" class="form-control" id="clinic_name" name="clinic_name" 
                                               value="<?= htmlspecialchars($settings['clinic_name']) ?>" required>
                                        <div class="form-text">The name of your clinic</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_email" class="form-label">Clinic Email</label>
                                        <input type="email" class="form-control" id="clinic_email" name="clinic_email" 
                                               value="<?= htmlspecialchars($settings['clinic_email']) ?>" required>
                                        <div class="form-text">Primary contact email</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_phone" class="form-label">Clinic Phone</label>
                                        <input type="text" class="form-control" id="clinic_phone" name="clinic_phone" 
                                               value="<?= htmlspecialchars($settings['clinic_phone']) ?>">
                                        <div class="form-text">Primary contact phone number</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_address" class="form-label">Clinic Address</label>
                                        <input type="text" class="form-control" id="clinic_address" name="clinic_address" 
                                               value="<?= htmlspecialchars($settings['clinic_address']) ?>">
                                        <div class="form-text">Physical address of the clinic</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_name_arabic" class="form-label">اسم العيادة بالعربية</label>
                                        <input type="text" class="form-control" id="clinic_name_arabic" name="clinic_name_arabic" 
                                               value="<?= htmlspecialchars($settings['clinic_name_arabic']) ?>" dir="rtl">
                                        <div class="form-text">اسم العيادة باللغة العربية</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-item">
                                        <label for="clinic_website" class="form-label">Website</label>
                                        <input type="text" class="form-control" id="clinic_website" name="clinic_website" 
                                               value="<?= htmlspecialchars($settings['clinic_website'] ?? '') ?>">
                                        <div class="form-text">Clinic website URL</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="clinic_logo" class="form-label">شعار العيادة</label>
                                        <input type="file" class="form-control" id="clinic_logo" name="clinic_logo" 
                                               accept="image/*" onchange="previewImage(this, 'clinic_logo_preview')" disabled>
                                        <div class="form-text">شعار العيادة العام (معطل - يمكن تحديثه من إعدادات أخرى)</div>
                                        <div class="mt-2">
                                            <input type="text" class="form-control" id="clinic_logo_path" name="clinic_logo_path" 
                                                   value="<?= htmlspecialchars($settings['clinic_logo']) ?>" placeholder="مسار الشعار الحالي" readonly>
                                        </div>
                                        <div class="mt-2" id="clinic_logo_preview">
                                            <?php if ($settings['clinic_logo'] && file_exists('/var/www/html/clinic/public' . $settings['clinic_logo'])): ?>
                                                <img src="<?= htmlspecialchars($settings['clinic_logo']) ?>" alt="Current Logo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="clinic_logo_print" class="form-label">شعار الطباعة</label>
                                        <input type="file" class="form-control" id="clinic_logo_print" name="clinic_logo_print" 
                                               accept="image/*" onchange="previewImage(this, 'clinic_logo_print_preview')">
                                        <div class="form-text">رفع شعار العيادة للطباعة (JPEG, PNG, GIF, SVG - حد أقصى 5MB)</div>
                                        <div class="mt-2">
                                            <input type="text" class="form-control" id="clinic_logo_print_path" name="clinic_logo_print_path" 
                                                   value="<?= htmlspecialchars($settings['clinic_logo_print']) ?>" placeholder="أو أدخل مسار الشعار">
                                        </div>
                                        <div class="mt-2" id="clinic_logo_print_preview">
                                            <?php if ($settings['clinic_logo_print'] && file_exists('/var/www/html/clinic/public' . $settings['clinic_logo_print'])): ?>
                                                <img src="<?= htmlspecialchars($settings['clinic_logo_print']) ?>" alt="Current Print Logo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="clinic_logo_watermark" class="form-label">شعار العلامة المائية</label>
                                        <input type="file" class="form-control" id="clinic_logo_watermark" name="clinic_logo_watermark" 
                                               accept="image/*" onchange="previewImage(this, 'clinic_logo_watermark_preview')">
                                        <div class="form-text">رفع شعار العيادة للعلامة المائية (JPEG, PNG, GIF, SVG - حد أقصى 5MB)</div>
                                        <div class="mt-2">
                                            <input type="text" class="form-control" id="clinic_logo_watermark_path" name="clinic_logo_watermark_path" 
                                                   value="<?= htmlspecialchars($settings['clinic_logo_watermark']) ?>" placeholder="أو أدخل مسار الشعار">
                                        </div>
                                        <div class="mt-2" id="clinic_logo_watermark_preview">
                                            <?php if ($settings['clinic_logo_watermark'] && file_exists('/var/www/html/clinic/public' . $settings['clinic_logo_watermark'])): ?>
                                                <img src="<?= htmlspecialchars($settings['clinic_logo_watermark']) ?>" alt="Current Watermark Logo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Visit Costs Settings -->
                        <div class="settings-section">
                            <h5><i class="fas fa-dollar-sign me-2"></i>Visit Costs</h5>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="new_visit_cost" class="form-label">New Visit Cost</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="new_visit_cost" name="new_visit_cost" 
                                                   value="<?= htmlspecialchars($settings['new_visit_cost']) ?>" min="0" step="0.01">
                                            <span class="input-group-text">EGP</span>
                                        </div>
                                        <div class="form-text">The cost required for the first visit to the patient</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="repeated_visit_cost" class="form-label">Follow-Up Visit Cost</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="repeated_visit_cost" name="repeated_visit_cost" 
                                                   value="<?= htmlspecialchars($settings['repeated_visit_cost']) ?>" min="0" step="0.01">
                                            <span class="input-group-text">EGP</span>
                                        </div>
                                        <div class="form-text">The cost required for the repeated visits to the patient</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="setting-item">
                                        <label for="consultation_cost" class="form-label">Consultation/Medical Procedure Cost</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="consultation_cost" name="consultation_cost" 
                                                   value="<?= htmlspecialchars($settings['consultation_cost'] ?? '50.00') ?>" min="0" step="0.01">
                                            <span class="input-group-text">EGP</span>
                                        </div>
                                        <div class="form-text">The cost required for consultation or medical procedure</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Drugs Database Update -->
                        <div class="settings-section">
                            <h5><i class="fas fa-database me-2"></i>Drugs Database Update</h5>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Information:</strong> Update the drugs database from the official source. 
                                This process downloads the latest drug information and updates your local database.
                            </div>
                            
                            <div class="setting-item">
                                <label class="form-label">Database Update</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success" id="settingsUpdateDatabaseBtn" onclick="showSettingsUpdateDatabaseModal()">
                                        <i class="fas fa-sync-alt me-2"></i>
                                        Update Database
                                    </button>
                                </div>
                                <div class="form-text">Click to update the drugs database from the official source</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-2"></i>
                                Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Push Subscription Modal -->
<div class="modal fade alerts-modal-glass" id="deletePushSubscriptionModal" tabindex="-1" aria-labelledby="deletePushSubscriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePushSubscriptionModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                    Delete Push Subscription
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the push notification subscription for:</p>
                <p class="fw-bold" id="deletePushSubscriptionBrowserInfo"></p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeletePushSubscription()">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Settings Update Database Modal -->
<div class="modal fade" id="settingsUpdateDatabaseModal" tabindex="-1" aria-labelledby="settingsUpdateDatabaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="settingsUpdateDatabaseModalLabel">
                    <i class="fas fa-sync-alt me-2"></i>
                    Update Drugs Database
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="settingsUpdateProgressContainer">
                    <div class="mb-3">
                        <p class="text-muted">The drugs database will be downloaded and updated from the official source.</p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This process may take a few minutes depending on the data size.
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0" id="settingsProgressLabel">Preparing...</label>
                            <div id="settingsUpdateSpinner" style="display: none;">
                                <div class="spinner-border" role="status" style="width: 1.5rem; height: 1.5rem; border-width: 3px; border-color: #0dcaf0; border-right-color: transparent;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 id="settingsUpdateProgressBar" 
                                 style="width: 0%"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span id="settingsProgressText">0%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Messages -->
                    <div id="settingsUpdateStatusMessages" class="mb-3" style="max-height: 300px; overflow-y: auto;">
                        <!-- Status messages will be added here -->
                    </div>
                    
                    <!-- Statistics -->
                    <div id="settingsUpdateStatistics" class="row mt-3" style="display: none;">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6>Total Records</h6>
                                    <h3 id="settingsTotalRecords">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h6>Inserted</h6>
                                    <h3 id="settingsInsertedRecords">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h6>Updated</h6>
                                    <h3 id="settingsUpdatedRecords">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="settingsCloseUpdateModalBtn">Cancel</button>
                <button type="button" class="btn btn-success" id="settingsStartUpdateBtn" onclick="startSettingsDatabaseUpdate()">
                    <i class="fas fa-play-circle me-2"></i>
                    Start Update
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/app/Views/doctor/assets/js/settings.js?v=<?= file_exists(__DIR__ . '/assets/js/settings.js') ? filemtime(__DIR__ . '/assets/js/settings.js') : time() ?>"></script>