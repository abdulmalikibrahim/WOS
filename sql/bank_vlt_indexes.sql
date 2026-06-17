-- =============================================================================
-- Bank VLT — performance indexes
-- Run ONCE on the production database (the one behind 10.59.193.77).
-- These target every WHERE / JOIN / ORDER BY used when the list loads.
--
-- NOTE: MySQL has no "CREATE INDEX IF NOT EXISTS". If an index already exists
-- you'll get "Duplicate key name" — that error is harmless, just skip it.
-- =============================================================================

-- 1) Latest checking_wos row per VIN (the heavy derived join):
--    speeds up   GROUP BY vin / MAX(id)   AND   c.vin = b.sapnik
ALTER TABLE checking_wos ADD INDEX idx_cw_vin_id (vin, id);

-- 2) Month range filter + ORDER BY plan_delivery_date, id
ALTER TABLE bank_vlt ADD INDEX idx_bv_pdd_id (plan_delivery_date, id);

-- 3) Join key b.sapnik = c.vin  +  the "NOT IN (... tabungan ...)" check
ALTER TABLE bank_vlt ADD INDEX idx_bv_sapnik (sapnik);

-- 4) Model-card filter (b.model = '...')
ALTER TABLE bank_vlt ADD INDEX idx_bv_model (model);

-- 5) Status = "Not Yet Process" subquery: NOT IN (SELECT sapnik FROM tabungan_vlt ...)
ALTER TABLE tabungan_vlt      ADD INDEX idx_tv_sapnik   (sapnik);
ALTER TABLE tabungan_vlt_kap2 ADD INDEX idx_tvk2_sapnik (sapnik);
