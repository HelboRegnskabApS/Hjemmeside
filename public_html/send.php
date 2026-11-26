<?php
// Kontaktformular for helboregnskab.dk. Sender til kontakt@ via PHP mail().
declare(strict_types=1);
date_default_timezone_set('Europe/Copenhagen');

$TO   = 'kontakt@helboregnskab.dk';
$FROM = 'formular@helboregnskab.dk';

$wantsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

function done(bool $ok, bool $json): void {
    if ($json) {
        http_response_code($ok ? 200 : 400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok]);
    } else {
        header('Location: /kontakt.html' . ($ok ? '?sendt=1' : '?fejl=1'), true, 303);
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { done(false, $wantsJson); }

// Honeypot: robotter udfylder feltet, mennesker ser det ikke.
if (!empty($_POST['website'])) { done(true, $wantsJson); }

function field(string $k, int $max): string {
    $v = isset($_POST[$k]) ? (string)$_POST[$k] : '';
    $v = trim(str_replace(["\r", "\0"], '', $v));
    return mb_substr($v, 0, $max);
}

$navn    = field('navn', 120);
$firma   = field('firma', 160);
$email   = field('email', 160);
$telefon = field('telefon', 40);
$type    = field('type', 40);
$besked  = field('besked', 5000);

// Ingen linjeskift i felter, der ender i mail-headere.
foreach ([$navn, $email] as $h) { if (strpos($h, "\n") !== false) { done(false, $wantsJson); } }
if ($navn === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { done(false, $wantsJson); }

$subject = 'Ny henvendelse fra hjemmesiden: ' . $navn . ($firma !== '' ? ' (' . $firma . ')' : '');
$body = "Navn: $navn\n"
      . "Virksomhed: " . ($firma !== '' ? $firma : '-') . "\n"
      . "E-mail: $email\n"
      . "Telefon: " . ($telefon !== '' ? $telefon : '-') . "\n"
      . "Passer bedst: " . ($type !== '' ? $type : '-') . "\n\n"
      . "Besked:\n" . ($besked !== '' ? $besked : '-') . "\n\n"
      . "Sendt " . date('j.n.Y H:i') . " fra helboregnskab.dk/kontakt.html\n";

$headers = [
    'From: Helbo Regnskab <' . $FROM . '>',
    'Reply-To: ' . mb_encode_mimeheader($navn, 'UTF-8') . ' <' . $email . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
];

$ok = mail($TO, mb_encode_mimeheader($subject, 'UTF-8'), $body, implode("\r\n", $headers), '-f' . $FROM);
done($ok, $wantsJson);
