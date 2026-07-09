-- =====================================================================
--  ZenSpace — Migration de durcissement & nouvelles fonctionnalités
--  À exécuter APRÈS schema.sql (sur une base déjà initialisée).
--  Idempotente autant que possible (IF NOT EXISTS).
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1) Créneau réellement libéré à l'annulation
--    Problème : la contrainte uq_slot couvrait TOUTES les réservations, mais
--    isSlotTaken() ignore les annulées → réserver un créneau annulé = erreur
--    fatale. MySQL ne supporte pas les index uniques partiels ; on contourne
--    avec une colonne générée qui vaut NULL quand la résa est annulée (NULL
--    n'entre pas dans un index UNIQUE → le créneau redevient réservable).
-- ---------------------------------------------------------------------
ALTER TABLE booking
    ADD COLUMN cancelled_at DATETIME NULL DEFAULT NULL AFTER created_at;

ALTER TABLE booking
    ADD COLUMN slot_key VARCHAR(64)
        GENERATED ALWAYS AS (
            IF(status = 'cancelled', NULL, CONCAT_WS('|', service_id, booking_date, time_slot))
        ) STORED;

-- La FK fk_booking_service s'appuyait sur l'index uq_slot (1re colonne service_id).
-- On crée un index dédié AVANT de pouvoir supprimer uq_slot.
ALTER TABLE booking ADD INDEX idx_booking_service (service_id);

-- On remplace l'ancienne contrainte par une contrainte sur la clé « active ».
ALTER TABLE booking DROP INDEX uq_slot;
ALTER TABLE booking ADD UNIQUE KEY uq_slot_active (slot_key);

-- ---------------------------------------------------------------------
-- 2) Programme de fidélité (§4.5)
--    Grand-livre de points : +gain / -dépense. Le solde = SUM(delta).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS loyalty_ledger (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    delta      INT NOT NULL,                 -- + gagnés / - dépensés
    reason     VARCHAR(120) NOT NULL,
    booking_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_loyalty_user FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_loyalty_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 3) Articles de blog / magazine bien-être (§4.7)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS article (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    author_id    INT NULL,
    title        VARCHAR(180) NOT NULL,
    slug         VARCHAR(200) NOT NULL UNIQUE,
    excerpt      VARCHAR(300) NULL,
    body         MEDIUMTEXT NOT NULL,
    cover_image  VARCHAR(255) NULL,
    published_at DATETIME NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_article_author FOREIGN KEY (author_id) REFERENCES user(id) ON DELETE SET NULL,
    INDEX idx_article_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 4) Compte applicatif dédié (moindre privilège) — À EXÉCUTER EN root.
--    Adapter le mot de passe puis renseigner DB_USER/DB_PASS dans .env.
-- ---------------------------------------------------------------------
-- CREATE USER IF NOT EXISTS 'zenspace_app'@'%' IDENTIFIED BY 'change-moi-mot-de-passe-app';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON zenspace.* TO 'zenspace_app'@'%';
-- FLUSH PRIVILEGES;
