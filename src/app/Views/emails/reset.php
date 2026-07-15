<?php
/** @var string $firstName @var string $link @var string $appName */
$e = static fn(?string $v): string => htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#F5EFE2;font-family:Georgia,'Times New Roman',serif;color:#14201A">
  <div style="max-width:560px;margin:0 auto;padding:36px 28px;background:#FBF7EC;border:1px solid #E7DEC6">
    <h1 style="font-family:Georgia,'Times New Roman',serif;font-size:26px;color:#A9861A;margin:0 0 20px;font-weight:600;letter-spacing:-0.01em">
      <?= $e($appName ?? 'ZenSpace') ?>
    </h1>
    <p style="font-size:15px;line-height:1.55;margin:0 0 14px">Bonjour <?= $e($firstName ?? '') ?>,</p>
    <p style="font-size:15px;line-height:1.55;margin:0 0 14px">Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton
       ci-dessous pour en choisir un nouveau. Ce lien est valable <strong>1 heure</strong>
       et ne peut être utilisé qu'une seule fois.</p>
    <p style="text-align:center;margin:32px 0">
      <a href="<?= $e($link) ?>"
         style="background:#C9A227;color:#14201A;text-decoration:none;padding:14px 32px;border-radius:8px;display:inline-block;font-weight:bold;font-family:Arial,Helvetica,sans-serif;letter-spacing:.02em">
        Réinitialiser mon mot de passe
      </a>
    </p>
    <p style="font-size:13px;color:#5C6660;font-family:Arial,Helvetica,sans-serif;line-height:1.5;margin:0 0 12px">
      Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur&nbsp;:<br>
      <a href="<?= $e($link) ?>" style="color:#A9861A;word-break:break-all"><?= $e($link) ?></a>
    </p>
    <p style="font-size:13px;color:#5C6660;font-family:Arial,Helvetica,sans-serif;line-height:1.5;margin:0 0 12px">
      Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail&nbsp;:
      votre mot de passe restera inchangé.
    </p>
    <hr style="border:none;border-top:1px solid #E7DEC6;margin:28px 0">
    <p style="font-size:12px;color:#8B8672;font-family:Arial,Helvetica,sans-serif;margin:0">
      © <?= date('Y') ?> <?= $e($appName ?? 'ZenSpace') ?> — Institut de bien-être, Bordeaux.
    </p>
  </div>
</body>
</html>
