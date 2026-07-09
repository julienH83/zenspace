<?php
/** @var string $firstName @var string $link @var string $appName */
$e = static fn(?string $v): string => htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#FBF8F3;font-family:Arial,Helvetica,sans-serif;color:#1B2D26">
  <div style="max-width:560px;margin:0 auto;padding:32px 24px">
    <h1 style="font-size:22px;color:#246A4C;margin:0 0 16px"><?= $e($appName ?? 'ZenSpace') ?></h1>
    <p>Bonjour <?= $e($firstName ?? '') ?>,</p>
    <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton
       ci-dessous pour en choisir un nouveau. Ce lien est valable <strong>1 heure</strong>
       et ne peut être utilisé qu'une seule fois.</p>
    <p style="text-align:center;margin:28px 0">
      <a href="<?= $e($link) ?>"
         style="background:#9A5B3B;color:#fff;text-decoration:none;padding:14px 28px;border-radius:999px;display:inline-block;font-weight:bold">
        Réinitialiser mon mot de passe
      </a>
    </p>
    <p style="font-size:13px;color:#2F4F43">Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
      <a href="<?= $e($link) ?>" style="color:#246A4C;word-break:break-all"><?= $e($link) ?></a>
    </p>
    <p style="font-size:13px;color:#2F4F43">Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail :
       votre mot de passe restera inchangé.</p>
    <hr style="border:none;border-top:1px solid #EFEAE2;margin:24px 0">
    <p style="font-size:12px;color:#8a9a92">© <?= date('Y') ?> <?= $e($appName ?? 'ZenSpace') ?> — Institut de bien-être, Bordeaux.</p>
  </div>
</body>
</html>
