<link href="/app/Views/secretary/assets/css/details.css?v=<?= file_exists(__DIR__ . '/assets/css/details.css') ? filemtime(__DIR__ . '/assets/css/details.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/secretary/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/../../doctor/assets/css/dashboard.css') ? filemtime(__DIR__ . '/../../doctor/assets/css/dashboard.css') : time() ?>" rel="stylesheet">

<!-- Booking Details Header -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="d-flex align-items-center">
            <h4 class="mb-0 me-3 arabic-text">
                <i class="bi bi-calendar-check me-2"></i>
                تفاصيل الحجز
            </h4>
        </div>
        <p class="text-muted mb-0 arabic-text">عرض تفاصيل الحجز رقم <?= $booking['id'] ?></p>
    </div>
    <div class="col-md-4 text-end">
        <div class="d-flex gap-2 justify-content-end">
            <button class="btn btn-outline-primary" onclick="window.history.back()">
                <i class="bi bi-arrow-right me-2"></i>
                العودة
            </button>
            <button class="btn btn-outline-info" onclick="printBooking(<?= $booking['id'] ?>)">
                <i class="bi bi-printer me-2"></i>
                طباعة
            </button>
        </div>
    </div>
</div>

<!-- Booking Information -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
                    <i class="bi bi-info-circle me-2"></i>
                    معلومات الحجز
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">تاريخ الموعد:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-calendar me-2"></i>
                                <?= date('Y-m-d', strtotime($booking['date'])) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">وقت الموعد:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-clock me-2"></i>
                                <?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">نوع الزيارة:</label>
                            <p class="form-control-plaintext">
                                <span class="badge <?= $this->getVisitTypeBadgeClass($booking['visit_type']) ?> arabic-text">
                                    <?= $this->getVisitTypeText($booking['visit_type']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">حالة الحجز:</label>
                            <p class="form-control-plaintext">
                                <span class="badge <?= $this->getBookingStatusBadgeClass($booking['status']) ?> arabic-text">
                                    <?= $this->getBookingStatusText($booking['status']) ?>
                                </span>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">تاريخ الإنشاء:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-calendar me-2"></i>
                                <?= date('Y-m-d H:i', strtotime($booking['created_at'])) ?>
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold arabic-text">آخر تحديث:</label>
                            <p class="form-control-plaintext arabic-text">
                                <i class="bi bi-clock me-2"></i>
                                <?= date('Y-m-d H:i', strtotime($booking['updated_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($booking['notes'])): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold arabic-text">ملاحظات:</label>
                    <div class="form-control-plaintext bg-light p-3 rounded arabic-text">
                        <?= nl2br(htmlspecialchars($booking['notes'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow dashboard-card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary arabic-text">
                    <i class="bi bi-person-circle me-2"></i>
                    معلومات المريض
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-lg me-3">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 arabic-text"><?= htmlspecialchars($booking['patient_name'] ?? 'غير محدد') ?></h6>
                        <small class="text-muted arabic-text"><?= $booking['patient_phone'] ?? 'غير محدد' ?></small>
                    </div>
                </div>
                
                <div class="mb-2">
                    <span class="badge bg-info arabic-text">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= $booking['patient_address'] ?? 'غير محدد' ?>
                    </span>
                </div>
                
                <div class="mb-2">
                    <span class="badge bg-secondary arabic-text">
                        <i class="bi bi-card-text me-1"></i>
                        <?= $booking['patient_national_id'] ?? 'غير محدد' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payments Section -->
<?php if (!empty($payments)): ?>
<div class="card shadow dashboard-card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary arabic-text">
            <i class="bi bi-credit-card me-2"></i>
            المدفوعات المرتبطة
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="arabic-text text-end" dir="rtl">التاريخ</th>
                        <th class="arabic-text text-end" dir="rtl">المبلغ</th>
                        <th class="arabic-text text-end" dir="rtl">النوع</th>
                        <th class="arabic-text text-end" dir="rtl">طريقة الدفع</th>
                        <th class="arabic-text text-end" dir="rtl">الوصف</th>
                        <th class="arabic-text text-end" dir="rtl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar me-2 text-primary"></i>
                                <?= date('Y-m-d H:i', strtotime($payment['created_at'])) ?>
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold text-success"><?= number_format($payment['amount'], 2) ?> جنيه</span>
                        </td>
                        <td>
                            <span class="badge <?= $this->getPaymentTypeBadgeClass($payment['type'] ?? 'other') ?> arabic-text">
                                <?= $this->getPaymentTypeText($payment['type'] ?? 'other') ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $this->getPaymentMethodBadgeClass($payment['method'] ?? 'cash') ?> arabic-text">
                                <?= $this->getPaymentMethodText($payment['method'] ?? 'cash') ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-muted arabic-text"><?= $payment['description'] ?? 'لا يوجد وصف' ?></span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="viewPayment(<?= $payment['id'] ?>)"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="عرض تفاصيل الدفعة">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" 
                                        onclick="printReceipt(<?= $payment['id'] ?>)"
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        data-bs-title="طباعة الإيصال">
                                    <i class="bi bi-printer"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Related Bookings -->
<?php if (!empty($relatedBookings)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 arabic-text">
            <i class="bi bi-list-ul me-2"></i>
            حجوزات أخرى لنفس المريض
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="arabic-text text-end" dir="rtl">التاريخ</th>
                        <th class="arabic-text text-end" dir="rtl">الوقت</th>
                        <th class="arabic-text text-end" dir="rtl">نوع الزيارة</th>
                        <th class="arabic-text text-end" dir="rtl">الحالة</th>
                        <th class="arabic-text text-end" dir="rtl">الطبيب</th>
                        <th class="arabic-text text-end" dir="rtl">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($relatedBookings as $relatedBooking): ?>
                    <tr <?= $relatedBooking['id'] == $booking['id'] ? 'class="table-active"' : '' ?>>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar me-2 text-primary"></i>
                                <?= date('Y-m-d', strtotime($relatedBooking['date'])) ?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock me-2 text-info"></i>
                                <?= date('H:i', strtotime($relatedBooking['start_time'])) ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $this->getVisitTypeBadgeClass($relatedBooking['visit_type']) ?> arabic-text">
                                <?= $this->getVisitTypeText($relatedBooking['visit_type']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $this->getBookingStatusBadgeClass($relatedBooking['status']) ?> arabic-text">
                                <?= $this->getBookingStatusText($relatedBooking['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person me-2"></i>
                                <?= htmlspecialchars($relatedBooking['doctor_name'] ?? 'غير محدد') ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($relatedBooking['id'] != $booking['id']): ?>
                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                    onclick="viewBooking(<?= $relatedBooking['id'] ?>)"
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="top" 
                                    data-bs-title="عرض تفاصيل الحجز">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php else: ?>
                            <span class="text-muted arabic-text">الحالي</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Initialize all modals with proper backdrop configuration
document.addEventListener('DOMContentLoaded', function() {
    const initializeModals = () => {
        const modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(modalEl => {
            // Skip if already initialized
            if (bootstrap.Modal.getInstance(modalEl)) return;
            
            // Get backdrop setting from data attribute or default to true
            const backdropSetting = modalEl.dataset.bsBackdrop !== undefined 
                ? (modalEl.dataset.bsBackdrop === 'static' ? 'static' : modalEl.dataset.bsBackdrop === 'false' ? false : true)
                : true;
            
            // Get keyboard setting
            const keyboardSetting = modalEl.dataset.bsKeyboard !== undefined 
                ? modalEl.dataset.bsKeyboard !== 'false'
                : true;
            
            // Initialize modal with proper config
            new bootstrap.Modal(modalEl, {
                backdrop: backdropSetting,
                keyboard: keyboardSetting,
                focus: true
            });
        });
    };
    
    // Initialize modals immediately
    initializeModals();
    
    // Re-initialize modals if new ones are added dynamically
    const observer = new MutationObserver(() => {
        initializeModals();
        // Re-initialize draggable after modals are initialized
        setTimeout(initializeDraggableModals, 100);
    });
    observer.observe(document.body, { childList: true, subtree: true });
    
    // Make modals draggable
    function initializeDraggableModals() {
        const modals = document.querySelectorAll('.modal');
        
        modals.forEach(modal => {
            // Skip alertModal - it has its own draggable implementation
            if (modal.id === 'alertModal') {
                return;
            }
            const modalDialog = modal.querySelector('.modal-dialog');
            if (!modalDialog) return;
            
            // Skip if already initialized
            if (modalDialog.dataset.draggableInitialized === 'true') return;
            modalDialog.dataset.draggableInitialized = 'true';
            
            let isDragging = false;
            let currentX;
            let currentY;
            let initialX;
            let initialY;
            let xOffset = 0;
            let yOffset = 0;
            
            // Make modal header the drag handle
            const modalHeader = modal.querySelector('.modal-header');
            if (!modalHeader) return;
            
            modalHeader.style.cursor = 'move';
            
            // Remove existing listeners to avoid duplicates
            const newHeader = modalHeader.cloneNode(true);
            modalHeader.parentNode.replaceChild(newHeader, modalHeader);
            const freshHeader = modal.querySelector('.modal-header');
            
            freshHeader.addEventListener('mousedown', dragStart);
            
            function dragStart(e) {
                // Don't drag if clicking on buttons or inputs
                if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
                    return;
                }
                
                // Only start dragging if clicking on header (not on title text)
                if (e.target === freshHeader || (freshHeader.contains(e.target) && e.target.tagName !== 'H5' && !e.target.closest('h5'))) {
                    // Get current transform values
                    const transform = modalDialog.style.transform;
                    if (transform) {
                        const match = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
                        if (match) {
                            xOffset = parseFloat(match[1]) || 0;
                            yOffset = parseFloat(match[2]) || 0;
                        }
                    }
                    
                    initialX = e.clientX - xOffset;
                    initialY = e.clientY - yOffset;
                    
                    // Store initial mouse position to detect if it's a drag or click
                    const startX = e.clientX;
                    const startY = e.clientY;
                    
                    // Set a flag to track if mouse moved
                    let hasMoved = false;
                    
                    function checkMove(moveEvent) {
                        const deltaX = Math.abs(moveEvent.clientX - startX);
                        const deltaY = Math.abs(moveEvent.clientY - startY);
                        if (deltaX > 5 || deltaY > 5) {
                            hasMoved = true;
                            isDragging = true;
                            modalDialog.style.transition = 'none';
                            moveEvent.preventDefault();
                            moveEvent.stopPropagation();
                        }
                    }
                    
                    function handleMove(moveEvent) {
                        if (hasMoved) {
                            drag(moveEvent);
                        } else {
                            checkMove(moveEvent);
                        }
                    }
                    
                    function handleEnd(endEvent) {
                        if (!hasMoved) {
                            // It was just a click, allow normal behavior
                            document.removeEventListener('mousemove', handleMove);
                            document.removeEventListener('mouseup', handleEnd);
                            return;
                        }
                        dragEnd(endEvent);
                        document.removeEventListener('mousemove', handleMove);
                        document.removeEventListener('mouseup', handleEnd);
                    }
                    
                    document.addEventListener('mousemove', handleMove);
                    document.addEventListener('mouseup', handleEnd);
                }
            }
            
            function drag(e) {
                if (isDragging) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent modal from closing
                    currentX = e.clientX - initialX;
                    currentY = e.clientY - initialY;
                    
                    xOffset = currentX;
                    yOffset = currentY;
                    
                    setTranslate(currentX, currentY, modalDialog);
                }
            }
            
            function dragEnd(e) {
                initialX = currentX;
                initialY = currentY;
                isDragging = false;
                modalDialog.style.transition = '';
            }
            
            function setTranslate(xPos, yPos, el) {
                // Get viewport dimensions
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;
                
                // Get modal dimensions
                const modalRect = el.getBoundingClientRect();
                const modalWidth = modalRect.width;
                const modalHeight = modalRect.height;
                
                // Get the original position (center of viewport)
                const originalLeft = (viewportWidth - modalWidth) / 2;
                const originalTop = 50; // Keep at least 50px from top
                
                // Calculate boundaries relative to original position
                // Allow movement within viewport bounds
                const minX = -(originalLeft - 20); // Allow 20px from left edge
                const maxX = viewportWidth - modalWidth - originalLeft + 20; // Allow 20px from right edge
                const minY = -(originalTop - 20); // Allow 20px from top
                const maxY = viewportHeight - modalHeight - originalTop - 20; // Allow 20px from bottom
                
                // Constrain movement
                const constrainedX = Math.max(minX, Math.min(maxX, xPos));
                const constrainedY = Math.max(minY, Math.min(maxY, yPos));
                
                el.style.transform = `translate(${constrainedX}px, ${constrainedY}px)`;
            }
            
            // Reset position when modal is hidden
            modal.addEventListener('hidden.bs.modal', function() {
                xOffset = 0;
                yOffset = 0;
                modalDialog.style.transform = '';
                modalDialog.dataset.draggableInitialized = 'false';
            });
        });
    }
    
    // Initialize draggable modals after a short delay to ensure modals are ready
    setTimeout(initializeDraggableModals, 200);
});

function viewBooking(bookingId) {
    window.location.href = `/secretary/bookings/${bookingId}`;
}

function viewPayment(paymentId) {
    window.location.href = `/secretary/payments/${paymentId}`;
}

function printReceipt(paymentId) {
    window.open(`/secretary/payments/${paymentId}/receipt`, '_blank');
}

function printBooking(bookingId) {
    // Open print page in new window
    window.open(`/secretary/bookings/${bookingId}/print`, '_blank');
}
</script>

<style>
/* Additional custom styles for booking details */
p {
    color: var(--text) !important;
}

label {
    color: var(--accent) !important;
    font-weight: 600;
}

.form-control-plaintext {
    color: var(--text) !important;
}

.bg-light {
    background-color: var(--bg) !important;
    color: var(--text) !important;
}

.dark .bg-light {
    background-color: var(--card) !important;
}

/* Button group styling */
.btn-group .btn {
    border-radius: 8px;
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

body > div.modal-backdrop.fade.show{
    display: none !important;
}

/* Modal z-index and centering */
.modal {
    z-index: 1000002 !important;
    align-items: center;
    justify-content: center;
    padding: 1rem !important;
    }
    
.modal-backdrop {
    z-index: 1000000 !important;
    }
    
.modal-dialog {
    z-index: 1000002 !important;
    margin: 0 auto;
    max-width: 500px;
    }

.modal-dialog.modal-lg {
    max-width: 800px;
}

.modal-dialog.modal-xl {
    max-width: 1140px;
}

.modal-dialog.modal-sm {
    max-width: 300px;
}
</style>

