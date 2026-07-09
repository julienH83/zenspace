-- =====================================================================
--  ZenSpace — Articles de démonstration pour le magazine bien-être
--  À exécuter APRÈS la migration créant la table `article`
--  (db/migrations/2026_06_01_hardening.sql).
--
--  Les dates de publication sont PASSÉES afin que les articles soient
--  immédiatement visibles via ArticleRepository::findPublished().
--  author_id = 1 correspond au compte administrateur du jeu de données
--  (db/seed.sql). La contrainte étant ON DELETE SET NULL, ce champ peut
--  rester NULL sans risque si l'utilisateur n'existe pas.
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO article (author_id, title, slug, excerpt, body, cover_image, published_at) VALUES
    (1,
     'Les bienfaits du massage californien',
     'bienfaits-massage-californien',
     'Doux et enveloppant, le massage californien relâche les tensions du corps et apaise le mental. Découvrez pourquoi il séduit autant.',
     'Né dans les années 1970 sur la côte ouest américaine, le massage californien se distingue par ses mouvements longs, lents et fluides.\n\nIl ne cherche pas à travailler le muscle en profondeur comme un massage sportif, mais plutôt à reconnecter le corps et l''esprit. Les effleurages enveloppants procurent une sensation de cocon et invitent au lâcher-prise.\n\nParmi ses bienfaits : une diminution du stress et de l''anxiété, une meilleure circulation, un sommeil plus réparateur et une véritable détente émotionnelle. C''est le soin idéal après une période chargée.\n\nPour en profiter pleinement, accordez-vous un moment sans contrainte horaire et hydratez-vous bien après la séance.',
     'massage-cali.jpg',
     '2026-05-12 09:00:00'),

    (1,
     'Le rituel du sommeil : préparer une nuit réparatrice',
     'rituel-du-sommeil',
     'Quelques gestes simples le soir suffisent à transformer vos nuits. Voici un rituel apaisant à adopter dès ce soir.',
     'Un bon sommeil se prépare bien avant de se coucher. La régularité est la première clé : se coucher et se lever à des horaires stables aide l''horloge biologique à trouver son rythme.\n\nUne heure avant le coucher, baissez progressivement la lumière et éloignez les écrans : la lumière bleue retarde la sécrétion de mélatonine, l''hormone du sommeil.\n\nUn rituel sensoriel renforce l''endormissement : une tisane tiède, quelques respirations lentes, une lumière tamisée et, pourquoi pas, un automassage des épaules et de la nuque.\n\nEnfin, gardez la chambre fraîche (autour de 18 °C) et silencieuse. Votre corps associera peu à peu cet environnement au repos.',
     'spa-hammam.jpg',
     '2026-05-20 09:00:00'),

    (1,
     'Respiration anti-stress : la cohérence cardiaque en 3 minutes',
     'respiration-anti-stress',
     'Une technique de respiration simple pour faire retomber la pression en quelques minutes, où que vous soyez.',
     'La cohérence cardiaque est un exercice de respiration qui aide à réguler le système nerveux et à apaiser le stress en quelques minutes.\n\nLe principe est simple : on respire à un rythme lent et régulier, environ six respirations par minute. Inspirez doucement par le nez pendant cinq secondes, puis expirez calmement par la bouche pendant cinq secondes.\n\nRépétez ce cycle pendant trois à cinq minutes, idéalement assis, le dos droit et les épaules relâchées. Concentrez-vous uniquement sur le va-et-vient de l''air.\n\nPratiquée trois fois par jour, cette respiration réduit la tension, clarifie l''esprit et améliore la concentration. Aucun matériel n''est nécessaire : votre souffle suffit.',
     'soin-visage.jpg',
     '2026-05-26 09:00:00');
