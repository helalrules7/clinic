-- Doctor-issued refunds: a refund is a NEGATIVE payment row with type='Refund'
-- (reconciles with every SUM(amount) ledger/summary already in place).
-- sql_mode is STRICT, so the value MUST exist in the enum before it can be stored.
ALTER TABLE payments
  MODIFY COLUMN type ENUM('Booking','FollowUp','Consultation','Procedure','Other','Refund') NOT NULL;
