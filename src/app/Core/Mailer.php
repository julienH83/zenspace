<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Envoi d'e-mails (transactionnels : réinitialisation de mot de passe, etc.).
 *
 * Deux transports, choisis via MAIL_TRANSPORT :
 *  - "log" (par défaut)  : écrit l'e-mail dans storage/mail/*.eml. AUCUN secret
 *    n'est exposé dans la réponse HTTP — le développeur consulte le fichier.
 *    Idéal sans serveur SMTP (et corrige la faille du lien affiché en flash).
 *  - "mail"              : utilise la fonction mail() de PHP (SMTP configuré au
 *    niveau du serveur / sendmail). En production on brancherait ici un vrai
 *    relais SMTP (Mailgun, SES…) ou la librairie PHPMailer/Symfony Mailer.
 *
 * Le corps HTML est rendu depuis une vue (app/Views/emails/<vue>.php).
 */
final class Mailer
{
    public function send(string $to, string $subject, string $view, array $data = []): bool
    {
        $html = View::renderToString('emails/' . $view, $data);
        $transport = env('MAIL_TRANSPORT', 'log') ?? 'log';

        return $transport === 'mail'
            ? $this->sendMail($to, $subject, $html)
            : $this->sendLog($to, $subject, $html);
    }

    private function sendMail(string $to, string $subject, string $html): bool
    {
        $from = env('MAIL_FROM', 'no-reply@zenspace.fr');
        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ZenSpace <' . $from . '>',
        ]);
        // mb_encode pour un sujet correct en UTF-8.
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        return @mail($to, $encodedSubject, $html, $headers);
    }

    private function sendLog(string $to, string $subject, string $html): bool
    {
        $dir = dirname(__DIR__, 2) . '/storage/mail';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        // Nom de fichier horodaté + aléatoire (pas de Date::now interdit côté script,
        // ici on est en PHP runtime classique donc date() est disponible).
        $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.eml';
        $content = "To: {$to}\r\nSubject: {$subject}\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}";
        return (bool) @file_put_contents($dir . '/' . $name, $content);
    }
}
