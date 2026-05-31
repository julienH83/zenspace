-- =====================================================================
--  ZenSpace — Jeu de données de démonstration
--  À exécuter APRÈS schema.sql.
--
--  Les mots de passe sont stockés hachés (bcrypt). Mots de passe en clair :
--    admin@zenspace.fr    -> Admin1234!
--    employe@zenspace.fr  -> Employe1234!
--    client@zenspace.fr   -> Client1234!
-- =====================================================================

SET NAMES utf8mb4;

-- --- Rôles ---
INSERT INTO role (id, label) VALUES
    (1, 'client'),
    (2, 'employe'),
    (3, 'admin');

-- --- Utilisateurs (mots de passe hachés en bcrypt) ---
INSERT INTO user (role_id, first_name, last_name, email, password_hash, phone, address, rgpd_consent, is_active) VALUES
    (3, 'Alice',  'Admin',   'admin@zenspace.fr',   '$2b$12$3EYzhhmFTg48/7XT3vtty.bgPAwxx7k9fzmnhQIlwUWss0jm9vvwC', '0600000001', '1 rue du Spa, Bordeaux',   1, 1),
    (2, 'Élodie', 'Employé', 'employe@zenspace.fr', '$2b$12$TpL1kVEiUSEqZzubHK6yM.SzoV6n9reWcEf8pUgsAHhcg41JsEZum', '0600000002', '2 rue du Calme, Bordeaux', 1, 1),
    (1, 'Camille','Client',  'client@zenspace.fr',  '$2b$12$1FQnmOXdgXXHGL85.2U9Ou0bqeZRZxXxkZDwGEYXMRIlmiqDEC0.m', '0600000003', '3 avenue Zen, Bordeaux',   1, 1);

-- --- Catégories ---
INSERT INTO category (id, label, slug) VALUES
    (1, 'Massage',        'massage'),
    (2, 'Soin du visage', 'soin-visage'),
    (3, 'Spa & Bien-être','spa');

-- --- Prestations ---
INSERT INTO service (category_id, title, slug, description, duration_min, price, image, is_active) VALUES
    (1, 'Massage relaxant californien', 'massage-californien',
        'Un massage doux et enveloppant qui détend l''ensemble du corps et apaise le mental.',
        60, 65.00, 'massage-cali.jpg', 1),
    (1, 'Massage sportif tonifiant', 'massage-sportif',
        'Massage profond ciblé sur les tensions musculaires, idéal après l''effort.',
        45, 55.00, 'massage-sport.jpg', 1),
    (2, 'Soin éclat du visage', 'soin-eclat-visage',
        'Nettoyage, gommage et masque hydratant pour une peau lumineuse.',
        50, 49.00, 'soin-visage.jpg', 1),
    (2, 'Soin anti-âge premium', 'soin-anti-age',
        'Soin complet aux actifs raffermissants pour lisser les traits.',
        70, 89.00, 'soin-antiage.jpg', 1),
    (3, 'Accès spa & hammam (2h)', 'spa-hammam',
        'Accès libre au hammam, sauna et bassin de détente pendant 2 heures.',
        120, 39.00, 'spa-hammam.jpg', 1),
    (3, 'Rituel détente duo', 'rituel-duo',
        'Parenthèse bien-être à deux : massage en duo suivi d''un accès spa.',
        90, 149.00, 'rituel-duo.jpg', 1);

-- --- Une réservation déjà terminée (pour pouvoir tester les avis) ---
INSERT INTO booking (id, user_id, service_id, booking_date, time_slot, status, total_price) VALUES
    (1, 3, 1, '2026-05-10', '14:00:00', 'completed', 65.00);

INSERT INTO booking_status_history (booking_id, status, changed_at) VALUES
    (1, 'pending',   '2026-05-01 09:00:00'),
    (1, 'confirmed', '2026-05-02 10:00:00'),
    (1, 'completed', '2026-05-10 15:00:00');

-- --- Un avis validé (affiché sur la page d'accueil) ---
INSERT INTO review (user_id, service_id, booking_id, rating, comment, is_validated) VALUES
    (3, 1, 1, 5, 'Massage exceptionnel, je suis repartie totalement détendue. Je recommande !', 1);
