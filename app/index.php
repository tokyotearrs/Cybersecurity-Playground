<?php
require __DIR__ . '/db.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$loginSuccessful = false;
$resultRows = [];
$cryptoKey = 'playground_secret_key';

$decryptInput = $_POST['encrypted_password'] ?? '';
$decryptStatus = '';
$decryptMessage = 'Gib ein verschlüsseltes Passwort ein und klicke auf Entschlüsseln.';
$decryptedPassword = '';

$formType = $_POST['form_type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shouldRunLoginQuery = $formType === 'login' || ($formType === 'decrypt' && $username !== '' && $password !== '');

    if ($shouldRunLoginQuery) {
        $query = "SELECT id, username, password, role FROM users WHERE username = '$username' AND AES_DECRYPT(FROM_BASE64(password), '$cryptoKey') = '$password'";

        try {
            $result = $conn->query($query);

            if ($result && $result->num_rows > 0) {
                $resultRows = $result->fetch_all(MYSQLI_ASSOC);
                $loginSuccessful = true;
            }
        } catch (mysqli_sql_exception) {
        }
    }

    if ($formType === 'decrypt') {
        if ($decryptInput === '') {
            $decryptStatus = 'error';
            $decryptMessage = 'Bitte zuerst ein verschlüsseltes Passwort eingeben.';
        } else {
            $decryptQuery = "SELECT CAST(AES_DECRYPT(FROM_BASE64('$decryptInput'), '$cryptoKey') AS CHAR(255)) AS plain_password";

            try {
                $decryptResult = $conn->query($decryptQuery);

                if ($decryptResult && $decryptResult->num_rows > 0) {
                    $decryptRow = $decryptResult->fetch_assoc();

                    if (!empty($decryptRow['plain_password'])) {
                        $decryptedPassword = $decryptRow['plain_password'];
                        $decryptStatus = 'success';
                        $decryptMessage = '';
                    } else {
                        $decryptStatus = 'error';
                        $decryptMessage = 'Entschlüsselung fehlgeschlagen.';
                    }
                } else {
                    $decryptStatus = 'error';
                    $decryptMessage = 'Entschlüsselung fehlgeschlagen.';
                }
            } catch (mysqli_sql_exception $exception) {
                $decryptStatus = 'error';
                $decryptMessage = 'SQL Fehler: ' . $exception->getMessage();
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybersecurity Playground</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    },
                    colors: {
                        abyss: '#050505',
                        line: '#2a2a2a',
                        accent: '#22c55e',
                        accentsoft: '#4ade80'
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen overflow-x-hidden overflow-y-auto bg-abyss text-zinc-100 font-sans antialiased sm:h-screen sm:overflow-hidden">
    <div class="fixed inset-0 -z-10 bg-[linear-gradient(180deg,rgba(255,255,255,0.03)_0%,rgba(255,255,255,0)_35%)]"></div>

    <main class="mx-auto min-h-screen w-full max-w-7xl px-4 py-4 sm:h-screen sm:overflow-hidden sm:px-5 sm:py-5">
        <header class="rounded-2xl border border-line bg-transparent px-4 py-4 sm:px-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="inline-flex items-center rounded-full border border-accent/40 bg-accent/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-accentsoft">Cybersecurity Playground</p>
                    <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-zinc-100 sm:text-3xl">SQL Injection Arena</h1>
                    <p class="mt-2 max-w-2xl text-xs leading-relaxed text-zinc-400 sm:text-sm">Aufgabe: Nutze SQL Injection im Login, erzeuge einen Treffer und entschlüssle das verschlüsselte Passwort aus der Datenbank.</p>
                </div>
                <button id="openSqliInfo" type="button" class="rounded-xl border border-accent/40 bg-accent/10 px-3 py-2 text-xs font-semibold text-accentsoft transition hover:bg-accent/20">Info</button>
            </div>
        </header>

        <section class="mt-4 rounded-2xl border border-line bg-transparent p-4 sm:p-5">
            <h2 class="text-xl font-bold tracking-tight text-zinc-100">Result aus der Datenbank</h2>

            <?php if (!empty($resultRows)): ?>
                <div class="mt-3 max-h-[45vh] overflow-auto rounded-xl border border-line sm:max-h-[30vh]">
                    <table class="min-w-full text-left">
                        <thead class="bg-black/40">
                            <tr>
                                <?php foreach (array_keys($resultRows[0]) as $column): ?>
                                    <th class="border-b border-line px-4 py-2 text-xs uppercase tracking-[0.14em] text-zinc-400"><?php echo htmlspecialchars($column, ENT_QUOTES, 'UTF-8'); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultRows as $row): ?>
                                <tr class="even:bg-black/25">
                                    <?php foreach ($row as $value): ?>
                                        <td class="border-b border-line/70 px-4 py-2 font-mono text-xs text-zinc-100"><?php echo htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <section class="rounded-2xl border <?php echo $loginSuccessful ? 'border-accent/50' : 'border-line'; ?> bg-transparent p-4 sm:p-5">
                <h2 class="text-xl font-bold tracking-tight text-zinc-100">Login</h2>
                <p class="mt-1 text-xs text-zinc-400 sm:text-sm">Führe deinen Login direkt aus.</p>

                <form class="mt-4 space-y-3" method="post" action="">
                    <input type="hidden" name="form_type" value="login">
                    <div>
                        <label for="username" class="mb-1 block text-sm font-medium text-zinc-300">Username</label>
                        <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-line bg-black/60 px-4 py-2.5 text-zinc-100 outline-none transition focus:border-accent">
                    </div>
                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-zinc-300">Password</label>
                        <input id="password" name="password" type="text" value="<?php echo htmlspecialchars($password, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-line bg-black/60 px-4 py-2.5 text-zinc-100 outline-none transition focus:border-accent">
                    </div>
                    <button type="submit" class="w-full rounded-xl border border-accent/40 bg-accent/10 px-4 py-2.5 font-semibold text-accentsoft transition hover:bg-accent/20">Login</button>
                </form>
            </section>

            <section class="rounded-2xl border border-line bg-transparent p-4 sm:p-5">
                <h2 class="text-xl font-bold tracking-tight text-zinc-100">Passwort entschlüsseln</h2>
                <p class="mt-1 text-xs text-zinc-400 sm:text-sm">Nutze ein verschlüsseltes Passwort aus dem Query Result.</p>

                <form class="mt-4 space-y-3" method="post" action="">
                    <input type="hidden" name="form_type" value="decrypt">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="password" value="<?php echo htmlspecialchars($password, ENT_QUOTES, 'UTF-8'); ?>">
                    <input name="encrypted_password" type="text" value="<?php echo htmlspecialchars($decryptInput, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Verschlüsseltes Passwort" class="w-full rounded-xl border border-line bg-black/60 px-4 py-2.5 text-zinc-100 outline-none transition focus:border-accent">
                    <button type="submit" class="w-full rounded-xl border border-accent/40 bg-accent/10 px-4 py-2.5 font-semibold text-accentsoft transition hover:bg-accent/20">Entschlüsseln</button>
                </form>

                <?php if ($decryptMessage !== ''): ?>
                    <div class="mt-4 rounded-xl border px-4 py-2.5 text-sm <?php echo $decryptStatus === 'error' ? 'border-red-400/30 bg-red-500/10 text-red-200' : 'border-line bg-black/40 text-zinc-300'; ?>">
                        <?php echo htmlspecialchars($decryptMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($decryptedPassword !== ''): ?>
                    <div class="mt-2 rounded-xl border border-accent/30 bg-black/40 px-4 py-2.5 text-sm text-zinc-100">
                        Entschlüsseltes Passwort: <span class="font-semibold text-accentsoft"><?php echo htmlspecialchars($decryptedPassword, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <div id="sqliInfoModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div id="sqliInfoBackdrop" class="absolute inset-0 bg-black/80"></div>
            <div class="relative z-10 w-full max-w-2xl rounded-2xl border border-line bg-black/95 p-5">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-zinc-100">SQLi Statements</h3>
                    <button id="closeSqliInfo" type="button" class="rounded-lg border border-line px-3 py-1 text-xs font-semibold text-zinc-300 transition hover:border-accent/40 hover:text-accentsoft">Schließen</button>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <article class="rounded-xl border border-line bg-black/50 p-3">
                        <p class="text-[11px] uppercase tracking-[0.12em] text-zinc-500">Passwort Feld</p>
                        <code id="payload1" class="mt-2 block font-mono text-xs text-zinc-100">' OR '1'='1' -- </code>
                        <button type="button" data-copy-target="payload1" class="mt-3 w-full rounded-lg border border-accent/40 bg-accent/10 px-3 py-2 text-xs font-semibold text-accentsoft transition hover:bg-accent/20">Kopieren</button>
                    </article>

                    <article class="rounded-xl border border-line bg-black/50 p-3">
                        <p class="text-[11px] uppercase tracking-[0.12em] text-zinc-500">Passwort Feld</p>
                        <code id="payload2" class="mt-2 block font-mono text-xs text-zinc-100">' OR username='admin' -- </code>
                        <button type="button" data-copy-target="payload2" class="mt-3 w-full rounded-lg border border-accent/40 bg-accent/10 px-3 py-2 text-xs font-semibold text-accentsoft transition hover:bg-accent/20">Kopieren</button>
                    </article>

                    <article class="rounded-xl border border-line bg-black/50 p-3 sm:col-span-2">
                        <p class="text-[11px] uppercase tracking-[0.12em] text-zinc-500">Username Feld</p>
                        <code id="payload3" class="mt-2 block font-mono text-xs text-zinc-100">admin' -- </code>
                        <button type="button" data-copy-target="payload3" class="mt-3 w-full rounded-lg border border-accent/40 bg-accent/10 px-3 py-2 text-xs font-semibold text-accentsoft transition hover:bg-accent/20">Kopieren</button>
                    </article>
                </div>

                <p id="copyFeedback" class="mt-3 text-xs text-zinc-400"></p>
            </div>
        </div>
    </main>

    <script>
        const sqliInfoModal = document.getElementById('sqliInfoModal');
        const openSqliInfo = document.getElementById('openSqliInfo');
        const closeSqliInfo = document.getElementById('closeSqliInfo');
        const sqliInfoBackdrop = document.getElementById('sqliInfoBackdrop');
        const copyFeedback = document.getElementById('copyFeedback');

        const showSqliInfo = () => {
            sqliInfoModal.classList.remove('hidden');
            sqliInfoModal.classList.add('flex');
        };

        const hideSqliInfo = () => {
            sqliInfoModal.classList.remove('flex');
            sqliInfoModal.classList.add('hidden');
            copyFeedback.textContent = '';
        };

        openSqliInfo.addEventListener('click', showSqliInfo);
        closeSqliInfo.addEventListener('click', hideSqliInfo);
        sqliInfoBackdrop.addEventListener('click', hideSqliInfo);

        document.querySelectorAll('[data-copy-target]').forEach((button) => {
            button.addEventListener('click', async () => {
                const targetId = button.getAttribute('data-copy-target');
                const target = document.getElementById(targetId);
                const payload = target ? target.textContent : '';

                if (!payload) {
                    copyFeedback.textContent = 'Kopieren fehlgeschlagen.';
                    return;
                }

                try {
                    await navigator.clipboard.writeText(payload);
                    copyFeedback.textContent = 'Statement kopiert.';
                } catch (error) {
                    copyFeedback.textContent = 'Kopieren fehlgeschlagen.';
                }
            });
        });
    </script>
</body>
</html>
