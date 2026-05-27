# TFA - AECB - backend
Projet github représentant la première moitié du projet de fin d'année de SGDB
Celui-ci contient la partie Backend du projet écrite en PHP, utilisant MySQL pour réaliser la DB.

## Procédure d'ajout d'une entity
1) Création de l'entity dans src/Domain
2) Définition de l'interface dans src/Domain/Repository
3) Implementation de l'interface dans src/Infrastructure/Persistence
4) Définition des UseCases dans src/UseCase
5) Controller dans src/Controller

## Installation du projet
Lancer WampServer
Importer le SQL dans PhpMyAdmin

## Lancement projet

Lancer WampServer
Dans le projet backend en www\AECB.backend lancer la console et run "php -S 127.0.0.1:8000 -t public"


## Utilisation de l'IA

l'IA a été utilisé pour seed les données dans la db

START TRANSACTION;

-- 1) Utilisateurs
INSERT INTO users (user_id, email, password_hash, first_name, last_name, role, active, created_at, updated_at) VALUES
(1, 'admin@aecb.com',   'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Admin',   'User',   'admin',        1, NOW(), NOW()),
(2, 'manager@aecb.com',  'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Sarah',   'Martin', 'manager',      1, NOW(), NOW()),
(3, 'leader@aecb.com',   'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Karim',   'Diallo', 'team_leader',  1, NOW(), NOW()),
(4, 'worker1@aecb.com',  'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Jean',    'Dupont', 'worker',       1, NOW(), NOW()),
(5, 'worker2@aecb.com',  'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Amina',   'Bens',   'worker',       1, NOW(), NOW());

-- 2) Codes de travail
INSERT INTO work_codes (code_id, code_name, decimal_value, description, is_counted_as_worked, active, created_at, updated_at) VALUES
(1, 'P',   8.00, 'Prestation sur chantier', 1, 1, NOW(), NOW()),
(2, 'C',   8.00, 'Congé payé', 1, 1, NOW(), NOW()),
(3, 'CC',  8.00, 'Congé de circonstance', 1, 1, NOW(), NOW()),
(4, 'CS',  0.00, 'Congé sans solde', 0, 1, NOW(), NOW()),
(5, 'M',   8.00, 'Congé maladie', 1, 1, NOW(), NOW()),
(6, 'MLD', 8.00, 'Maladie longue durée', 1, 1, NOW(), NOW()),
(7, 'CE',  8.00, 'Chômage économique', 1, 1, NOW(), NOW()),
(8, 'CI',  8.00, 'Chômage intempérie', 1, 1, NOW(), NOW()),
(9, 'AT',  8.00, 'Accident de travail', 1, 1, NOW(), NOW()),
(10, 'R', -8.00, 'Récupération heures supplémentaires', 0, 1, NOW(), NOW()),
(11, 'A',  0.00, 'Absence injustifiée', 0, 1, NOW(), NOW());

-- 3) Horaires de travail
INSERT INTO work_schedules (schedule_id, name, fraction, daily_hours, active, created_at) VALUES
(1, 'Temps plein', 1.0000, 8.00, 1, NOW()),
(2, '1/2 temps',   0.5000, 4.00, 1, NOW()),
(3, '3/4 temps',   0.7500, 6.00, 1, NOW());

-- 4) Règle d'heures journalières
INSERT INTO daily_hour_requirements (requirement_id, company_id, daily_hours, commission_type, active, effective_from, created_at) VALUES
(1, 1, 8.00, 'Standard', 1, '2025-01-01', NOW());

-- 5) Équipes
INSERT INTO teams (team_id, name, specialization, parent_team_id, created_by, created_at, updated_at) VALUES
(1, 'Team A', 'Carpentry', NULL, 1, NOW(), NOW()),
(2, 'Team B', 'Masonry',   NULL, 1, NOW(), NOW());

-- 6) Affectations utilisateurs / équipes
INSERT INTO users_teams (user_team_id, user_id, team_id, schedule_id, assigned_at) VALUES
(1, 3, 1, 1, NOW()),
(2, 4, 1, 1, NOW()),
(3, 5, 2, 2, NOW());

-- 7) Jours fériés / congés
INSERT INTO holidays_leaves (holiday_id, name, holiday_date, type, user_id, team_id, created_by, created_at) VALUES
(1, 'Fête du Travail', '2026-05-01', 'legal', NULL, NULL, 1, NOW()),
(2, 'Repos équipe A',   '2026-05-15', 'company', NULL, 1, 1, NOW()),
(3, 'Congé individuel', '2026-05-20', 'company', 4,    NULL, 1, NOW());

-- 8) Présences
-- Exemple à 8.40 h pour générer 0.40 h d'overtime via le trigger
INSERT INTO attendance_records
(attendance_id, user_id, team_id, attendance_date, code_id, hours_value, notes, created_by, created_at, updated_at)
VALUES
(1, 4, 1, '2026-05-03', 1, 8.40, 'Journée avec 24 minutes supplémentaires', 1, NOW(), NOW()),
(2, 5, 2, '2026-05-03', 1, 4.00, 'Demi-journée', 1, NOW(), NOW()),
(3, 3, 1, '2026-05-04', 2, 8.00, 'Congé payé', 1, NOW(), NOW());

COMMIT;