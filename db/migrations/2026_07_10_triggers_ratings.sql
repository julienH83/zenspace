-- =====================================================================
--  ZenSpace — Migration : note moyenne des prestations maintenue par TRIGGER
--  À exécuter APRÈS db/migrations/2026_06_01_hardening.sql.
--
--  Objectif (CP5 « Créer une base de données relationnelle ») : démontrer
--  l'usage de TRIGGERS SQL. Plutôt que de recalculer AVG(rating) par une
--  jointure à chaque affichage du catalogue, on maintient deux colonnes
--  dénormalisées sur `service` (note moyenne + nombre d'avis validés). Trois
--  triggers sur `review` les tiennent automatiquement à jour : la logique
--  d'agrégation vit dans la base, la couche PHP n'a plus rien à recalculer.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1) Colonnes dénormalisées sur `service`
--    rating_avg : moyenne des notes (avis VALIDÉS uniquement), 0 si aucun
--    rating_count : nombre d'avis validés
--    NB : MySQL ne supporte pas « ADD COLUMN IF NOT EXISTS » ; cette
--    migration s'exécute une seule fois sur une base donnée.
-- ---------------------------------------------------------------------
ALTER TABLE service
    ADD COLUMN rating_avg   DECIMAL(3,2) NOT NULL DEFAULT 0.00 AFTER is_active,
    ADD COLUMN rating_count INT          NOT NULL DEFAULT 0    AFTER rating_avg;

-- ---------------------------------------------------------------------
-- 2) Triggers de recalcul (idempotents grâce à DROP TRIGGER IF EXISTS)
--    On ne compte QUE les avis validés (is_validated = 1) : un avis en
--    attente de modération ne doit pas influencer la note publique.
--    INSERT/UPDATE ciblent NEW.service_id, DELETE cible OLD.service_id.
-- ---------------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_review_after_insert;
DROP TRIGGER IF EXISTS trg_review_after_update;
DROP TRIGGER IF EXISTS trg_review_after_delete;

DELIMITER $$

CREATE TRIGGER trg_review_after_insert
AFTER INSERT ON review
FOR EACH ROW
BEGIN
    UPDATE service SET
        rating_count = (SELECT COUNT(*)            FROM review WHERE service_id = NEW.service_id AND is_validated = 1),
        rating_avg   = (SELECT COALESCE(AVG(rating), 0) FROM review WHERE service_id = NEW.service_id AND is_validated = 1)
    WHERE id = NEW.service_id;
END$$

CREATE TRIGGER trg_review_after_update
AFTER UPDATE ON review
FOR EACH ROW
BEGIN
    UPDATE service SET
        rating_count = (SELECT COUNT(*)            FROM review WHERE service_id = NEW.service_id AND is_validated = 1),
        rating_avg   = (SELECT COALESCE(AVG(rating), 0) FROM review WHERE service_id = NEW.service_id AND is_validated = 1)
    WHERE id = NEW.service_id;
END$$

CREATE TRIGGER trg_review_after_delete
AFTER DELETE ON review
FOR EACH ROW
BEGIN
    UPDATE service SET
        rating_count = (SELECT COUNT(*)            FROM review WHERE service_id = OLD.service_id AND is_validated = 1),
        rating_avg   = (SELECT COALESCE(AVG(rating), 0) FROM review WHERE service_id = OLD.service_id AND is_validated = 1)
    WHERE id = OLD.service_id;
END$$

DELIMITER ;

-- ---------------------------------------------------------------------
-- 3) Backfill : initialise les colonnes depuis les avis déjà présents
--    (les triggers ne se déclenchent que sur les futurs changements).
-- ---------------------------------------------------------------------
UPDATE service s SET
    rating_count = (SELECT COUNT(*)            FROM review r WHERE r.service_id = s.id AND r.is_validated = 1),
    rating_avg   = (SELECT COALESCE(AVG(r.rating), 0) FROM review r WHERE r.service_id = s.id AND r.is_validated = 1);
