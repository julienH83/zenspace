-- =====================================================================
--  ZenSpace — Schéma de la base de données relationnelle (MySQL 8)
--  Création des tables, clés étrangères, contraintes et index.
--  À exécuter EN PREMIER (avant seed.sql).
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- On repart d'une base propre (utile pour réinitialiser en développement).
DROP TABLE IF EXISTS review;
DROP TABLE IF EXISTS booking_status_history;
DROP TABLE IF EXISTS booking;
DROP TABLE IF EXISTS service;
DROP TABLE IF EXISTS category;
DROP TABLE IF EXISTS password_reset;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS role;
DROP TABLE IF EXISTS contact_message;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
--  Rôles : client, employé, administrateur
-- ---------------------------------------------------------------------
CREATE TABLE role (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Utilisateurs
--  Le mot de passe n'est JAMAIS stocké en clair : on garde un hash bcrypt.
-- ---------------------------------------------------------------------
CREATE TABLE user (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    role_id       INT NOT NULL,
    first_name    VARCHAR(80)  NOT NULL,
    last_name     VARCHAR(80)  NOT NULL,
    email         VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(20)  DEFAULT NULL,
    address       VARCHAR(255) DEFAULT NULL,
    rgpd_consent  BOOLEAN      NOT NULL DEFAULT 0,
    is_active     BOOLEAN      NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES role(id),
    INDEX idx_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Jetons de réinitialisation de mot de passe
--  On stocke un hash du jeton (pas le jeton lui-même) + une expiration.
-- ---------------------------------------------------------------------
CREATE TABLE password_reset (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used       BOOLEAN  NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_reset_token (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Catégories de prestations (Massage, Soin du visage, Spa…)
-- ---------------------------------------------------------------------
CREATE TABLE category (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(80) NOT NULL,
    slug  VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Prestations (le catalogue)
-- ---------------------------------------------------------------------
CREATE TABLE service (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    category_id  INT NOT NULL,
    title        VARCHAR(150) NOT NULL,
    slug         VARCHAR(160) NOT NULL UNIQUE,
    description  TEXT NOT NULL,
    duration_min INT NOT NULL,                  -- durée en minutes
    price        DECIMAL(8,2) NOT NULL,         -- prix en euros
    image        VARCHAR(255) DEFAULT NULL,
    is_active    BOOLEAN NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_service_category FOREIGN KEY (category_id) REFERENCES category(id),
    CONSTRAINT chk_service_price CHECK (price >= 0),
    CONSTRAINT chk_service_duration CHECK (duration_min > 0),
    INDEX idx_service_category (category_id),
    INDEX idx_service_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Réservations
--  statut : pending (en attente), confirmed (confirmée),
--           completed (terminée), cancelled (annulée)
-- ---------------------------------------------------------------------
CREATE TABLE booking (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    service_id   INT NOT NULL,
    booking_date DATE NOT NULL,
    time_slot    TIME NOT NULL,
    status       ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    total_price  DECIMAL(8,2) NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_user    FOREIGN KEY (user_id)    REFERENCES user(id),
    CONSTRAINT fk_booking_service FOREIGN KEY (service_id) REFERENCES service(id),
    INDEX idx_booking_user (user_id),
    INDEX idx_booking_status (status),
    -- Un même créneau ne peut être réservé qu'une fois pour une prestation donnée.
    UNIQUE KEY uq_slot (service_id, booking_date, time_slot)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Historique des statuts d'une réservation (traçabilité du suivi)
-- ---------------------------------------------------------------------
CREATE TABLE booking_status_history (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    status     ENUM('pending','confirmed','completed','cancelled') NOT NULL,
    changed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_booking FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE,
    INDEX idx_history_booking (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Avis clients (validés par un employé avant publication)
-- ---------------------------------------------------------------------
CREATE TABLE review (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    service_id   INT NOT NULL,
    booking_id   INT NOT NULL,
    rating       TINYINT NOT NULL,
    comment      TEXT DEFAULT NULL,
    is_validated BOOLEAN NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_user    FOREIGN KEY (user_id)    REFERENCES user(id),
    CONSTRAINT fk_review_service FOREIGN KEY (service_id) REFERENCES service(id),
    CONSTRAINT fk_review_booking FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE,
    CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5),
    -- Un seul avis par réservation.
    UNIQUE KEY uq_review_booking (booking_id),
    INDEX idx_review_service (service_id),
    INDEX idx_review_validated (is_validated)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Messages du formulaire de contact
-- ---------------------------------------------------------------------
CREATE TABLE contact_message (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(180) NOT NULL,
    subject    VARCHAR(180) NOT NULL,
    message    TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
