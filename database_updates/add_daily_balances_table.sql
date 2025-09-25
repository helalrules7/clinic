-- Create daily_balances table for daily balance management
CREATE TABLE IF NOT EXISTS daily_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10,2) NOT NULL,
    balance_type ENUM('opening', 'additional', 'withdrawal', 'closing') NOT NULL,
    description TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_created_at (created_at),
    INDEX idx_balance_type (balance_type),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB;

-- Create daily_closures table for daily closure management
CREATE TABLE IF NOT EXISTS daily_closures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    closure_date DATE NOT NULL,
    opening_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_received DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    closing_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    transactions_count INT NOT NULL DEFAULT 0,
    closure_notes TEXT NULL,
    closed_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_closure_date (closure_date),
    INDEX idx_closure_date (closure_date),
    INDEX idx_closed_by (closed_by)
) ENGINE=InnoDB;

-- Add description column to payments table if it doesn't exist
-- First check if column exists, then add it
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'payments' 
     AND COLUMN_NAME = 'description') = 0,
    'ALTER TABLE payments ADD COLUMN description TEXT NULL AFTER amount',
    'SELECT "Column description already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update payments table to include more payment types
ALTER TABLE payments MODIFY COLUMN type ENUM('Booking', 'FollowUp', 'Consultation', 'Procedure', 'Other') NOT NULL;
ALTER TABLE payments MODIFY COLUMN method ENUM('Cash', 'Card', 'Transfer', 'Wallet') NOT NULL;

-- Create expenses table
CREATE TABLE IF NOT EXISTS expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10,2) NOT NULL,
    expense_name VARCHAR(255) NOT NULL,
    category ENUM('utilities', 'medical', 'maintenance', 'office', 'salary', 'other') NOT NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_created_at (created_at),
    INDEX idx_category (category),
    INDEX idx_created_by (created_by)
) ENGINE=InnoDB;

-- Update expenses table to include 'salary' in category ENUM if it doesn't exist
ALTER TABLE expenses MODIFY COLUMN category ENUM('utilities','medical','maintenance','office','salary','other') NOT NULL;
