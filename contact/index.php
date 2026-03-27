<?php
if (!isset($lang)) $lang = 'fr';
$t    = require __DIR__ . '/../lang/' . $lang . '.php';
$home = $lang === 'en' ? '/en/' : '/';
$base = $lang === 'en' ? '/en' : '';

session_start();

// Génère un nouveau captcha (deux entiers, leur somme attendue)
function generate_captcha(): void {
    $_SESSION['captcha_a'] = random_int(1, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}

// Initialise le captcha si absent
if (empty($_SESSION['captcha_a'])) {
    generate_captcha();
}

$valid_subjects = ['just_chatting', 'data_edit'];

$status = null;
$errors = [];
$form   = ['name' => '', 'email' => '', 'subject' => 'just_chatting', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot : un bot remplit ce champ caché, un humain ne le voit pas
    if (!empty($_POST['website'])) {
        $status = 'success'; // Faux succès silencieux
    } else {
        $name    = str_replace(["\r", "\n"], '', trim($_POST['name']    ?? ''));
        $email   = str_replace(["\r", "\n"], '', trim($_POST['email']   ?? ''));
        $subject = in_array($_POST['subject'] ?? '', $valid_subjects) ? $_POST['subject'] : 'just_chatting';
        $message = trim($_POST['message'] ?? '');
        $captcha = trim($_POST['captcha'] ?? '');

        $form = ['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message];

        if (empty($name))                                                $errors[] = $t['contact_err_name'];
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = $t['contact_err_email'];
        if (empty($message))                                             $errors[] = $t['contact_err_message'];

        $expected = (int) ($_SESSION['captcha_a'] ?? 0) + (int) ($_SESSION['captcha_b'] ?? 0);
        if (!is_numeric($captcha) || (int) $captcha !== $expected) {
            $errors[] = $t['contact_err_captcha'];
        }

        if (empty($errors)) {
            $to           = 'jpi@ikmail.com';
            $subject_label = $t['contact_subject_' . $subject];
            $mail_subject  = '[AGHH][' . $subject_label . '] — ' . $name;
            $body          = $name . ' <' . $email . ">\n" . $t['contact_field_subject'] . ' : ' . $subject_label . "\n\n" . $message;
            $headers = "From: noreply@greathistory.local\r\nReply-To: " . $email . "\r\nContent-Type: text/plain; charset=UTF-8\r\n";

            $status = mail($to, $mail_subject, $body, $headers) ? 'success' : 'error_send';
        }

        // Regénère toujours un nouveau captcha après soumission
        generate_captcha();
    }
}

$captcha_a = (int) ($_SESSION['captcha_a'] ?? 1);
$captcha_b = (int) ($_SESSION['captcha_b'] ?? 1);
$captcha_label = sprintf($t['contact_captcha_label'], $captcha_a, $captcha_b);
?>
<!DOCTYPE html>
<html lang="<?php echo $t['html_lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($t['contact_title']); ?> — <?php echo htmlspecialchars($t['title']); ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .contact-form {
            max-width: 560px;
            margin: 2rem auto 0;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: .35rem;
        }
        .form-group label {
            font-size: .9rem;
            color: var(--text-muted);
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 6px;
            color: var(--text);
            font-family: inherit;
            font-size: 1rem;
            padding: .6rem .85rem;
            transition: border-color .15s;
            width: 100%;
        }
        .form-group select { cursor: pointer; }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--accent);
            outline: none;
        }
        .form-group textarea {
            min-height: 140px;
            resize: vertical;
        }
        .form-group.captcha-group input {
            max-width: 100px;
        }
        .hp-field {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            tab-index: -1;
        }
        .contact-submit {
            background: var(--accent);
            border: none;
            border-radius: 6px;
            color: #000;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            padding: .7rem 1.5rem;
            align-self: flex-start;
            transition: opacity .15s;
        }
        .contact-submit:hover { opacity: .85; }
        .contact-notice {
            border-radius: 6px;
            padding: .9rem 1.1rem;
            margin-bottom: 1rem;
            font-size: .95rem;
        }
        .contact-notice.success {
            background: hsl(140, 40%, 15%);
            border: 1px solid hsl(140, 50%, 35%);
            color: hsl(140, 60%, 70%);
        }
        .contact-notice.error {
            background: hsl(0, 40%, 15%);
            border: 1px solid hsl(0, 50%, 40%);
            color: var(--error);
        }
        .contact-notice ul { margin: .4rem 0 0 1.2rem; padding: 0; }
        .footer-meta { margin-top: .5rem; font-size: .85rem; }
        .footer-meta a { color: var(--text-muted); text-decoration: none; }
        .footer-meta a:hover { color: var(--accent); }
    </style>
</head>
<body>
    <?php
    $altUrl   = $lang === 'fr' ? '/en/contact/' : '/contact/';
    $altFlag  = $lang === 'fr' ? '🇬🇧' : '🇫🇷';
    $altLabel = $lang === 'fr' ? 'English' : 'Français';
    ?>
    <header class="header">
        <h1><a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['contact_title']); ?></a></h1>
        <a href="<?php echo $altUrl; ?>" class="lang-switch" title="<?php echo $altLabel; ?>" aria-label="<?php echo $altLabel; ?>"><?php echo $altFlag; ?></a>
    </header>

    <main class="main" style="padding: 2rem 1.5rem;">
<p class="description"><?php echo htmlspecialchars($t['contact_intro']); ?></p>

        <?php if ($status === 'success') : ?>
            <div class="contact-notice success"><?php echo htmlspecialchars($t['contact_success']); ?></div>
        <?php elseif ($status === 'error_send') : ?>
            <div class="contact-notice error"><?php echo htmlspecialchars($t['contact_err_send']); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)) : ?>
            <div class="contact-notice error">
                <ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul>
            </div>
        <?php endif; ?>

        <?php if ($status !== 'success') : ?>
        <form method="post" action="" class="contact-form" novalidate>
            <!-- Honeypot : visible uniquement des bots -->
            <div class="hp-field" aria-hidden="true">
                <label for="website">Ne pas remplir</label>
                <input type="text" id="website" name="website" autocomplete="off" tabindex="-1">
            </div>

            <div class="form-group">
                <label for="name"><?php echo htmlspecialchars($t['contact_field_name']); ?></label>
                <input type="text" id="name" name="name" required
                       value="<?php echo htmlspecialchars($form['name']); ?>"
                       autocomplete="name">
            </div>

            <div class="form-group">
                <label for="email"><?php echo htmlspecialchars($t['contact_field_email']); ?></label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($form['email']); ?>"
                       autocomplete="email">
            </div>

            <div class="form-group">
                <label for="subject"><?php echo htmlspecialchars($t['contact_field_subject']); ?></label>
                <select id="subject" name="subject">
                    <?php foreach ($valid_subjects as $key) : ?>
                        <option value="<?php echo $key; ?>"
                            <?php echo $form['subject'] === $key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['contact_subject_' . $key]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="message"><?php echo htmlspecialchars($t['contact_field_message']); ?></label>
                <textarea id="message" name="message" required><?php echo htmlspecialchars($form['message']); ?></textarea>
            </div>

            <div class="form-group captcha-group">
                <label for="captcha"><?php echo htmlspecialchars($captcha_label); ?></label>
                <input type="number" id="captcha" name="captcha" required
                       min="0" max="99" autocomplete="off">
            </div>

            <button type="submit" class="contact-submit"><?php echo htmlspecialchars($t['contact_send']); ?></button>
        </form>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <nav class="footer-nav">
            <a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['home']); ?></a>
            <a href="<?php echo $base; ?>/ma-frise/"><?php echo htmlspecialchars($t['nav_ma_frise']); ?></a>
            <a href="<?php echo $base; ?>/creer-ta-frise/"><?php echo htmlspecialchars($t['nav_creer_frise']); ?></a>
        </nav>
        <p><?php echo $t['footer']; ?></p>
        <p class="footer-meta">
            <a href="https://github.com/psic/The-Great-Approximate-History-of-Humanity"
               target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($t['footer_github']); ?></a>
            &nbsp;·&nbsp;
            <a href="http://www.wtfpl.net/" target="_blank" rel="noopener noreferrer"
               title="Do What The Fuck You Want To Public License">
                <img src="/img/wtfpl.svg" alt="WTFPL License" width="84" height="20"
                     style="vertical-align: middle;">
            </a>
        </p>
    </footer>
</body>
</html>
