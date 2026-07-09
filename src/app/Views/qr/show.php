<?php /** @var array $booking @var string $payload @var int $booking_id */ ?>
<article class="form-card form-wide">
    <div class="page-head">
        <h1>QR de réservation</h1>
        <span class="badge badge-<?= e($booking['status']) ?>"><?= e(status_label($booking['status'])) ?></span>
    </div>

    <p><strong>Prestation :</strong> <?= e($booking['service_title']) ?></p>
    <p><strong>Date :</strong> <?= e(date('d/m/Y', strtotime((string) $booking['booking_date']))) ?>
       à <?= e(substr((string) $booking['time_slot'], 0, 5)) ?></p>

    <p class="muted">Présentez ce code à l'accueil. Il est <strong>signé</strong> (HMAC-SHA256)
       et lié à votre réservation #<?= (int) $booking_id ?>.</p>

    <!-- Le QR est dessiné côté client. Repli accessible : la charge utile en texte. -->
    <div id="qr" aria-label="QR code de la réservation #<?= (int) $booking_id ?>" role="img" style="margin:16px 0"></div>
    <p class="muted"><small>Code de secours : <code><?= e($payload) ?></code></small></p>

    <p style="margin-top:18px"><a href="/reservation/<?= (int) $booking_id ?>">← Retour à la réservation</a></p>
</article>

<!-- Librairie QR chargée depuis unpkg (autorisée par la CSP). -->
<script src="https://unpkg.com/qrcode/build/qrcode.min.js" defer></script>
<script>
    // Charge utile signée transmise par PHP (échappée pour le contexte script).
    const qrPayload = <?= json_encode($payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    window.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('qr');
        if (!container || typeof QRCode === 'undefined') {
            return; // dégradation propre : le code de secours en texte reste visible
        }
        const canvas = document.createElement('canvas');
        container.appendChild(canvas);
        QRCode.toCanvas(canvas, qrPayload, { width: 220 }, function (error) {
            if (error) {
                console.error(error);
            }
        });
    });
</script>
