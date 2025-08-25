<?php

namespace App\Config;

class Constants
{
    // Application
    const APP_NAME = 'Roaya Ophthalmology Clinic';
    const APP_VERSION = '1.0.0';
    const TIMEZONE = 'Africa/Cairo';
    
    // Working Hours
    const CLINIC_START_TIME = '14:00:00';
    const CLINIC_END_TIME = '23:00:00';
    const SLOT_DURATION_MINUTES = 15;
    const FRIDAY_CLOSED = true;
    
    // Default Fees (in EGP)
    const DEFAULT_BOOKING_FEE = 50.00;
    const DEFAULT_CONSULTATION_FEE = 200.00;
    const DEFAULT_FOLLOWUP_FEE = 150.00;
    const DEFAULT_PROCEDURE_FEE = 500.00;
    
    // Status Enums
    const APPOINTMENT_STATUSES = [
        'Booked' => 'Booked',
        'CheckedIn' => 'Checked In',
        'InProgress' => 'In Progress',
        'Completed' => 'Completed',
        'Cancelled' => 'Cancelled',
        'NoShow' => 'No Show',
        'Rescheduled' => 'Rescheduled'
    ];
    
    const VISIT_TYPES = [
        'New' => 'New',
        'FollowUp' => 'Follow Up',
        'Procedure' => 'Procedure'
    ];
    
    const PAYMENT_METHODS = [
        'Cash' => 'Cash',
        'Card' => 'Card',
        'Wallet' => 'Wallet',
        'Transfer' => 'Transfer'
    ];
    
    const PAYMENT_TYPES = [
        'Booking' => 'Booking',
        'Consultation' => 'Consultation',
        'FollowUp' => 'Follow Up',
        'Procedure' => 'Procedure',
        'Other' => 'Other'
    ];
    
    const LENS_TYPES = [
        'Single Vision' => 'Single Vision',
        'Bifocal' => 'Bifocal',
        'Progressive' => 'Progressive',
        'Reading' => 'Reading'
    ];
    
    // Roles
    const ROLES = [
        'doctor' => 'Doctor',
        'secretary' => 'Secretary',
        'admin' => 'Administrator'
    ];
    
    // Pagination
    const ITEMS_PER_PAGE = 20;
    
    // Auto-refresh intervals (in seconds)
    const CALENDAR_REFRESH_INTERVAL = 60;
    
    // File upload limits
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf'];
    
    // Session timeout (in seconds)
    const SESSION_TIMEOUT = 3600; // 1 hour
    
    // Password policy
    const MIN_PASSWORD_LENGTH = 8;
    const PASSWORD_REQUIRE_UPPERCASE = true;
    const PASSWORD_REQUIRE_LOWERCASE = true;
    const PASSWORD_REQUIRE_NUMBERS = true;
    const PASSWORD_REQUIRE_SPECIAL = false;
}
