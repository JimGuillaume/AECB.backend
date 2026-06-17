-- ==============================================================
-- DEV SEED — AECB Attendance
-- All user passwords : Dev1234
-- Safe to re-run: clears & rebuilds every table below
-- ==============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = '';

-- ---------------------------------------------------------------
-- Clean slate (FK order)
-- ---------------------------------------------------------------
TRUNCATE TABLE overtime_tracking;
TRUNCATE TABLE attendance_records;
TRUNCATE TABLE holidays_leaves;
TRUNCATE TABLE users_teams;
TRUNCATE TABLE users;
TRUNCATE TABLE teams;

ALTER TABLE users  AUTO_INCREMENT = 1;
ALTER TABLE teams  AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------
-- 1. Users  (all passwords = Dev1234)
-- ---------------------------------------------------------------
INSERT INTO users (user_id, first_name, last_name, email, password_hash, role) VALUES
(1,  'Admin',     'AECB',       'admin@aecb.be',            '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'admin'),
(2,  'Jean',      'Dupont',     'jean.dupont@aecb.be',      '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'manager'),
(3,  'Marie',     'Lambert',    'marie.lambert@aecb.be',    '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'manager'),
(4,  'Pierre',    'Lecomte',    'pierre.lecomte@aecb.be',   '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'team_leader'),
(5,  'Bruno',     'Dupont',     'bruno.dupont@aecb.be',     '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'worker'),
(6,  'Sophie',    'Martin',     'sophie.martin@aecb.be',    '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'worker'),
(7,  'Marc',      'Bernard',    'marc.bernard@aecb.be',     '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'worker'),
(8,  'François',  'Dubois',     'francois.dubois@aecb.be',  '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'team_leader'),
(9,  'Julie',     'Renard',     'julie.renard@aecb.be',     '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'worker'),
(10, 'Thomas',    'Petit',      'thomas.petit@aecb.be',     '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'worker'),
(11, 'Isabelle',  'Dumont',     'isabelle.dumont@aecb.be',  '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'worker'),
(12, 'Nicolas',   'Charlier',   'nicolas.charlier@aecb.be', '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'team_leader'),
(13, 'Céline',    'Maes',       'celine.maes@aecb.be',      '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'worker'),
(14, 'Antoine',   'Pirard',     'antoine.pirard@aecb.be',   '$2y$12$hwLdQuW38b7odpwT9djWF./beP6hp.UA4BJ5LOD0SL0W0QVOA2F9u', 'worker');

-- ---------------------------------------------------------------
-- 2. Teams
-- ---------------------------------------------------------------
INSERT INTO teams (team_id, name, specialization, parent_team_id, created_by) VALUES
(1, 'Direction',    'Management', NULL, 1),
(2, 'Menuiserie',   'Carpentry',  1,    1),
(3, 'Électricité',  'Electrical', 1,    1),
(4, 'Plomberie',    'Plumbing',   1,    1);

-- ---------------------------------------------------------------
-- 3. Team assignments  (user → team + schedule)
-- ---------------------------------------------------------------
INSERT INTO users_teams (user_id, team_id, schedule_id) VALUES
-- Direction
(1,  1, 1),  -- Admin        / Temps plein
(2,  1, 1),  -- Jean         / Temps plein
(3,  1, 1),  -- Marie        / Temps plein
-- Menuiserie
(4,  2, 1),  -- Pierre       / Temps plein  (chef)
(5,  2, 1),  -- Bruno        / Temps plein
(6,  2, 4),  -- Sophie       / 3/4 temps
(7,  2, 1),  -- Marc         / Temps plein
-- Électricité
(8,  3, 1),  -- François     / Temps plein  (chef)
(9,  3, 1),  -- Julie        / Temps plein
(10, 3, 1),  -- Thomas       / Temps plein
(11, 3, 7),  -- Isabelle     / 1/2 temps
-- Plomberie
(12, 4, 1),  -- Nicolas      / Temps plein  (chef)
(13, 4, 1),  -- Céline       / Temps plein
(14, 4, 2);  -- Antoine      / 9/10 temps

-- ---------------------------------------------------------------
-- 4. Belgian public holidays 2025 & 2026
-- ---------------------------------------------------------------
INSERT INTO holidays_leaves (name, holiday_date, type, user_id, team_id, created_by) VALUES
-- 2025
('Nouvel An',            '2025-01-01', 'legal', NULL, NULL, 1),
('Lundi de Pâques',      '2025-04-21', 'legal', NULL, NULL, 1),
('Fête du Travail',      '2025-05-01', 'legal', NULL, NULL, 1),
('Ascension',            '2025-05-29', 'legal', NULL, NULL, 1),
('Lundi de Pentecôte',   '2025-06-09', 'legal', NULL, NULL, 1),
('Fête Nationale',       '2025-07-21', 'legal', NULL, NULL, 1),
('Assomption',           '2025-08-15', 'legal', NULL, NULL, 1),
('Toussaint',            '2025-11-01', 'legal', NULL, NULL, 1),
('Armistice',            '2025-11-11', 'legal', NULL, NULL, 1),
('Noël',                 '2025-12-25', 'legal', NULL, NULL, 1),
-- 2026
('Nouvel An',            '2026-01-01', 'legal', NULL, NULL, 1),
('Lundi de Pâques',      '2026-04-06', 'legal', NULL, NULL, 1),
('Fête du Travail',      '2026-05-01', 'legal', NULL, NULL, 1),
('Ascension',            '2026-05-14', 'legal', NULL, NULL, 1),
('Lundi de Pentecôte',   '2026-05-25', 'legal', NULL, NULL, 1);

-- ---------------------------------------------------------------
-- 5. Attendance records  (2025-01-01 → 2026-06-30)
--    Generated via recursive CTE, one row per worker per weekday.
--    Variety per worker: overtime end-of-month, sick days, leaves.
--    The overtime trigger auto-fills overtime_tracking on INSERT.
-- ---------------------------------------------------------------

INSERT INTO attendance_records (user_id, team_id, attendance_date, code_id, hours_value, created_by)
WITH RECURSIVE

cal AS (
    SELECT DATE('2025-01-01') AS d
    UNION ALL
    SELECT DATE_ADD(d, INTERVAL 1 DAY) FROM cal WHERE d < DATE('2026-06-30')
),

working_days AS (
    SELECT d FROM cal
    WHERE DAYOFWEEK(d) NOT IN (1, 7)       -- no weekends
      AND d NOT IN (                         -- no public holidays
        '2025-01-01','2025-04-21','2025-05-01','2025-05-29',
        '2025-06-09','2025-07-21','2025-08-15',
        '2025-11-01','2025-11-11','2025-12-25',
        '2026-01-01','2026-04-06','2026-05-01',
        '2026-05-14','2026-05-25'
      )
),

-- ── Worker 5 – Bruno Dupont ──────────────────────────────────
w5 AS (
    SELECT d, 5 AS uid, 2 AS tid, 1 AS created_by,
        CASE
            WHEN d IN ('2025-04-22','2025-04-23','2025-04-24','2025-04-25') THEN 2   -- C congé Pâques
            WHEN d BETWEEN '2025-07-22' AND '2025-08-01'                   THEN 2   -- C vacances été
            WHEN d BETWEEN '2025-12-22' AND '2025-12-31'                   THEN 2   -- C vacances Noël
            WHEN d IN ('2026-04-07','2026-04-08','2026-04-09','2026-04-10') THEN 2   -- C congé Pâques
            WHEN d BETWEEN '2026-05-04' AND '2026-05-08'                   THEN 2   -- C pont Ascension
            WHEN d IN ('2025-02-10','2025-02-11','2025-05-07',
                       '2025-09-16','2025-10-28','2025-10-29',
                       '2026-01-20','2026-03-11','2026-03-12')             THEN 5   -- M maladie
            ELSE 1                                                                   -- P prestation
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-03-31','2025-04-30','2025-06-30',
                       '2025-08-29','2025-09-30','2025-10-31','2025-11-28',
                       '2026-01-30','2026-02-27','2026-03-31','2026-04-30',
                       '2026-05-29')                                       THEN 10.00
            WHEN d IN ('2025-03-12','2025-06-18','2025-09-10',
                       '2025-11-19','2026-02-11','2026-04-22')             THEN 9.50
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Worker 6 – Sophie Martin (3/4 temps, starts 08:00) ──────
w6 AS (
    SELECT d, 6 AS uid, 2 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-08-04' AND '2025-08-15'                   THEN 2   -- C vacances
            WHEN d BETWEEN '2025-12-29' AND '2025-12-31'                   THEN 2
            WHEN d IN ('2026-04-07','2026-04-08')                          THEN 2
            WHEN d IN ('2025-03-05','2025-03-06','2025-03-07',
                       '2025-11-03','2026-02-16','2026-02-17')             THEN 5   -- M
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-02-28','2025-05-30','2025-07-31',
                       '2025-10-31','2026-01-30','2026-04-30')             THEN 10.00
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Worker 7 – Marc Bernard ──────────────────────────────────
w7 AS (
    SELECT d, 7 AS uid, 2 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-07-14' AND '2025-07-25'                   THEN 2
            WHEN d BETWEEN '2025-12-22' AND '2025-12-31'                   THEN 2
            WHEN d IN ('2026-04-07','2026-04-08','2026-04-09','2026-04-10') THEN 2
            WHEN d IN ('2025-01-15','2025-01-16',
                       '2025-06-02','2025-06-03',
                       '2025-10-07','2026-03-23')                          THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-04-30','2025-07-31',
                       '2025-09-30','2025-12-19',
                       '2026-01-30','2026-03-31','2026-05-29')             THEN 10.00
            WHEN d IN ('2025-03-19','2025-08-27','2026-02-25')             THEN 9.50
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Team leader 4 – Pierre Lecomte ───────────────────────────
w4 AS (
    SELECT d, 4 AS uid, 2 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-07-28' AND '2025-08-08'                   THEN 2
            WHEN d IN ('2025-12-24','2025-12-29','2025-12-30','2025-12-31') THEN 2
            WHEN d IN ('2026-04-07','2026-04-10')                          THEN 2
            WHEN d IN ('2025-04-03','2025-09-22','2026-01-08')             THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-02-28','2025-03-31','2025-04-30',
                       '2025-05-30','2025-06-30','2025-07-31','2025-08-29',
                       '2025-09-30','2025-10-31','2025-11-28',
                       '2026-01-30','2026-02-27','2026-03-31',
                       '2026-04-30','2026-05-29','2026-06-30')             THEN 10.00
            WHEN d IN ('2025-03-12','2025-06-18','2025-09-10','2025-11-05',
                       '2026-02-11','2026-04-22','2026-06-17')             THEN 9.50
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Worker 9 – Julie Renard (Électricité) ────────────────────
w9 AS (
    SELECT d, 9 AS uid, 3 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-07-14' AND '2025-07-25'                   THEN 2
            WHEN d BETWEEN '2025-12-22' AND '2025-12-31'                   THEN 2
            WHEN d IN ('2026-04-07','2026-04-08')                          THEN 2
            WHEN d IN ('2025-02-03','2025-02-04','2025-06-09',
                       '2025-09-16','2026-02-09','2026-02-10')             THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-03-31','2025-05-30',
                       '2025-08-29','2025-10-31',
                       '2026-01-30','2026-03-31','2026-05-29')             THEN 10.00
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Worker 10 – Thomas Petit ─────────────────────────────────
w10 AS (
    SELECT d, 10 AS uid, 3 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-08-04' AND '2025-08-15'                   THEN 2
            WHEN d BETWEEN '2025-12-29' AND '2025-12-31'                   THEN 2
            WHEN d IN ('2026-04-07','2026-04-08','2026-04-09')             THEN 2
            WHEN d IN ('2025-03-17','2025-03-18',
                       '2025-07-01','2025-07-02',
                       '2025-11-17','2026-04-14')                          THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-04-30','2025-07-31',
                       '2025-09-30','2025-11-28',
                       '2026-01-30','2026-04-30','2026-06-30')             THEN 10.00
            WHEN d IN ('2025-05-21','2025-08-27','2026-03-25')             THEN 9.50
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Worker 11 – Isabelle Dumont (1/2 temps, Mon-Wed-Fri) ────
w11 AS (
    SELECT d, 11 AS uid, 3 AS tid, 1 AS created_by,
        CASE
            WHEN DAYOFWEEK(d) NOT IN (2, 4, 6) THEN NULL   -- only Mon/Wed/Fri
            WHEN d BETWEEN '2025-07-14' AND '2025-07-25'   THEN 2
            WHEN d IN ('2025-12-29','2025-12-30','2025-12-31') THEN 2
            WHEN d IN ('2025-02-19','2025-05-28','2026-01-07') THEN 5
            ELSE 1
        END AS code_id,
        8.00 AS hrs
    FROM working_days
),

-- ── Team leader 8 – François Dubois (Électricité) ────────────
w8 AS (
    SELECT d, 8 AS uid, 3 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-07-28' AND '2025-08-08'                   THEN 2
            WHEN d BETWEEN '2025-12-22' AND '2025-12-31'                   THEN 2
            WHEN d IN ('2026-04-07','2026-04-08','2026-04-09','2026-04-10') THEN 2
            WHEN d IN ('2025-04-14','2025-09-01','2026-03-02')             THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-02-28','2025-03-31','2025-04-30',
                       '2025-06-30','2025-08-29','2025-09-30','2025-10-31',
                       '2025-11-28','2026-01-30','2026-02-27',
                       '2026-03-31','2026-04-30','2026-05-29')             THEN 10.00
            WHEN d IN ('2025-03-19','2025-06-11','2025-09-10',
                       '2025-11-19','2026-02-18','2026-04-22')             THEN 9.50
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Team leader 12 – Nicolas Charlier (Plomberie) ────────────
w12 AS (
    SELECT d, 12 AS uid, 4 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-07-14' AND '2025-07-25'                   THEN 2
            WHEN d BETWEEN '2025-12-22' AND '2025-12-31'                   THEN 2
            WHEN d IN ('2026-04-07','2026-04-08','2026-04-09','2026-04-10') THEN 2
            WHEN d IN ('2025-01-27','2025-01-28',
                       '2025-06-09','2025-10-13',
                       '2026-02-23','2026-05-12')                          THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-03-31','2025-05-30',
                       '2025-07-31','2025-09-30','2025-11-28',
                       '2026-01-30','2026-03-31','2026-05-29')             THEN 10.00
            WHEN d IN ('2025-04-09','2025-08-13','2025-11-05',
                       '2026-02-04','2026-04-15')                          THEN 9.50
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Worker 13 – Céline Maes (Plomberie) ──────────────────────
w13 AS (
    SELECT d, 13 AS uid, 4 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-08-04' AND '2025-08-15'                   THEN 2
            WHEN d IN ('2025-12-29','2025-12-30','2025-12-31')             THEN 2
            WHEN d IN ('2026-04-07','2026-04-08')                          THEN 2
            WHEN d IN ('2025-02-24','2025-02-25',
                       '2025-07-07','2025-07-08',
                       '2025-11-24','2026-03-16')                          THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-04-30','2025-06-30',
                       '2025-09-30','2025-10-31',
                       '2026-01-30','2026-04-30','2026-06-30')             THEN 10.00
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Worker 14 – Antoine Pirard (Plomberie, 9/10) ─────────────
w14 AS (
    SELECT d, 14 AS uid, 4 AS tid, 1 AS created_by,
        CASE
            WHEN DAYOFWEEK(d) = 5                                          THEN NULL  -- off on Thursdays (9/10)
            WHEN d BETWEEN '2025-07-14' AND '2025-07-25'                   THEN 2
            WHEN d BETWEEN '2025-12-22' AND '2025-12-31'                   THEN 2
            WHEN d IN ('2026-04-07','2026-04-08','2026-04-09')             THEN 2
            WHEN d IN ('2025-03-10','2025-08-20',
                       '2025-10-06','2025-10-07',
                       '2026-01-13','2026-04-21')                          THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-03-31','2025-05-30',
                       '2025-08-29','2025-10-31',
                       '2026-01-30','2026-03-31','2026-05-29')             THEN 10.00
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Manager 2 – Jean Dupont (Direction) ──────────────────────
w2 AS (
    SELECT d, 2 AS uid, 1 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-07-28' AND '2025-08-08'                   THEN 2
            WHEN d BETWEEN '2025-12-22' AND '2025-12-31'                   THEN 2
            WHEN d IN ('2026-04-07','2026-04-08','2026-04-09','2026-04-10') THEN 2
            WHEN d IN ('2025-03-03','2025-09-08','2026-02-02')             THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-02-28','2025-03-31','2025-04-30',
                       '2025-05-30','2025-06-30','2025-07-31','2025-08-29',
                       '2025-09-30','2025-10-31','2025-11-28',
                       '2026-01-30','2026-02-27','2026-03-31',
                       '2026-04-30','2026-05-29','2026-06-30')             THEN 10.00
            ELSE 8.00
        END AS hrs
    FROM working_days
),

-- ── Manager 3 – Marie Lambert ────────────────────────────────
w3 AS (
    SELECT d, 3 AS uid, 1 AS tid, 1 AS created_by,
        CASE
            WHEN d BETWEEN '2025-07-14' AND '2025-07-25'                   THEN 2
            WHEN d BETWEEN '2025-12-22' AND '2025-12-31'                   THEN 2
            WHEN d IN ('2026-04-07','2026-04-08')                          THEN 2
            WHEN d IN ('2025-02-17','2025-02-18',
                       '2025-06-16','2025-10-20',
                       '2026-03-09','2026-05-19')                          THEN 5
            ELSE 1
        END AS code_id,
        CASE
            WHEN d IN ('2025-01-31','2025-03-31','2025-05-30',
                       '2025-07-31','2025-09-30','2025-11-28',
                       '2026-01-30','2026-03-31','2026-05-29')             THEN 10.00
            WHEN d IN ('2025-04-09','2025-08-27','2026-02-18','2026-04-22') THEN 9.50
            ELSE 8.00
        END AS hrs
    FROM working_days
),

all_workers AS (
    SELECT * FROM w2  WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w3  WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w4  WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w5  WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w6  WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w7  WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w8  WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w9  WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w10 WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w11 WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w12 WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w13 WHERE code_id IS NOT NULL
    UNION ALL SELECT * FROM w14 WHERE code_id IS NOT NULL
)

SELECT
    uid,
    tid,
    d,
    code_id,
    CASE WHEN code_id = 1 THEN hrs ELSE 8.00 END,
    created_by
FROM all_workers;

-- Done — overtime_tracking is auto-filled by tr_calculate_overtime_after_attendance
SELECT CONCAT(
    (SELECT COUNT(*) FROM users),           ' users  |  ',
    (SELECT COUNT(*) FROM teams),           ' teams  |  ',
    (SELECT COUNT(*) FROM attendance_records), ' attendance rows  |  ',
    (SELECT COUNT(*) FROM overtime_tracking),  ' overtime rows'
) AS seed_summary;
