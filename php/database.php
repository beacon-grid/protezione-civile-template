<?php

const APP_DB_PATH = __DIR__ . '/../data/site.sqlite';

function app_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('È necessaria l’estensione PHP pdo_sqlite per usare il database del sito.');
    }

    $directory = dirname(APP_DB_PATH);
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('Impossibile creare la cartella data/ per il database SQLite.');
    }

    $databaseExisted = is_file(APP_DB_PATH);
    $previousUmask = umask(0007);
    try {
        $pdo = new PDO('sqlite:' . APP_DB_PATH, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA synchronous = NORMAL');
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS app_data (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS app_users (
                username TEXT COLLATE NOCASE NOT NULL,
                role TEXT NOT NULL,
                record TEXT NOT NULL,
                PRIMARY KEY (role, username)
            );
            CREATE TABLE IF NOT EXISTS app_cache (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL,
                updated_at INTEGER NOT NULL
            );
            CREATE TABLE IF NOT EXISTS app_locks (
                key TEXT PRIMARY KEY,
                owner TEXT NOT NULL,
                locked_until INTEGER NOT NULL
            );
            CREATE TABLE IF NOT EXISTS app_cooldowns (
                key TEXT PRIMARY KEY,
                last_attempt INTEGER NOT NULL
            );
            CREATE TABLE IF NOT EXISTS app_migrations (
                migration TEXT PRIMARY KEY,
                applied_at TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS app_news (
                id TEXT PRIMARY KEY,
                title TEXT NOT NULL,
                published_on TEXT NOT NULL,
                category TEXT NOT NULL,
                summary TEXT NOT NULL,
                content TEXT NOT NULL,
                image TEXT NOT NULL,
                image_alt TEXT NOT NULL,
                featured INTEGER NOT NULL CHECK (featured IN (0, 1)),
                published INTEGER NOT NULL CHECK (published IN (0, 1)),
                sort_order INTEGER NOT NULL,
                extra_json TEXT NOT NULL DEFAULT \'{}\'
            );
            CREATE INDEX IF NOT EXISTS app_news_publication_date
                ON app_news (published, published_on DESC);
            CREATE TABLE IF NOT EXISTS app_glossary_entries (
                term_key TEXT PRIMARY KEY,
                term TEXT NOT NULL,
                definition TEXT NOT NULL,
                sort_order INTEGER NOT NULL
            );
            CREATE TABLE IF NOT EXISTS app_glossary_settings (
                id INTEGER PRIMARY KEY CHECK (id = 1),
                enabled INTEGER NOT NULL CHECK (enabled IN (0, 1))
            )'
        );
    } finally {
        umask($previousUmask);
    }

    if (!$databaseExisted && !chmod(APP_DB_PATH, 0660)) {
        throw new RuntimeException('Impossibile impostare i permessi sicuri del database SQLite.');
    }
    $permissions = fileperms(APP_DB_PATH);
    if ($permissions === false || ($permissions & 0007) !== 0) {
        throw new RuntimeException('Il database SQLite non deve essere accessibile agli altri utenti del sistema.');
    }
    foreach (['-wal', '-shm'] as $suffix) {
        $sidecar = APP_DB_PATH . $suffix;
        if (is_file($sidecar)) {
            $permissions = fileperms($sidecar);
            if ($permissions === false || ($permissions & 0007) !== 0) {
                throw new RuntimeException('I file temporanei SQLite non devono essere accessibili agli altri utenti del sistema.');
            }
        }
    }

    app_db_import_legacy($pdo);
    app_db_migrate_relational_content($pdo);
    return $pdo;
}

function app_db_import_legacy(PDO $pdo): void
{
    $migration = 'legacy_files_v1';
    $dataDirectory = dirname(__DIR__) . '/data';
    $legacyJson = [
        'news.json' => 'news',
        'glossario.json' => 'glossary_entries',
        'glossario_settings.json' => 'glossary_settings',
        'emergency_status.json' => 'emergency_status',
        'allerte_cache.json' => 'alert_cache',
        'allerte_force_rate.json' => 'alert_force_rate',
        'login_attempts.json' => 'login_attempts',
    ];

    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $check = $pdo->prepare('SELECT 1 FROM app_migrations WHERE migration = ?');
        $check->execute([$migration]);
        if ($check->fetchColumn()) {
            $pdo->exec('COMMIT');
            return;
        }

        foreach ($legacyJson as $filename => $key) {
            $path = $dataDirectory . '/' . $filename;
            if (!file_exists($path)) {
                continue;
            }
            if (!is_readable($path)) {
                throw new RuntimeException('Impossibile leggere il file da migrare: ' . $path);
            }
            $contents = file_get_contents($path);
            if ($contents === false) {
                throw new RuntimeException('Errore durante la lettura del file da migrare: ' . $path);
            }
            if (trim($contents) === '') {
                continue;
            }
            $value = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            app_data_set_with_pdo($pdo, $key, $value);
        }

        $syncLockPath = $dataDirectory . '/allerte_sync_lock.json';
        if (file_exists($syncLockPath)) {
            if (!is_readable($syncLockPath)) {
                throw new RuntimeException('Impossibile leggere il file da migrare: ' . $syncLockPath);
            }
            $contents = file_get_contents($syncLockPath);
            if ($contents === false) {
                throw new RuntimeException('Errore durante la lettura del file da migrare: ' . $syncLockPath);
            }
            if (trim($contents) !== '') {
                $value = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
                app_cooldown_set_with_pdo($pdo, 'alert_sync', (int) ($value['ultimo_tentativo'] ?? 0));
            }
        }

        foreach ([
            'users.php' => false,
            'superadmin.php' => true,
        ] as $filename => $isSuperadmin) {
            $path = $dataDirectory . '/' . $filename;
            if (!file_exists($path)) {
                continue;
            }
            if (!is_readable($path)) {
                throw new RuntimeException('Impossibile leggere il file da migrare: ' . $path);
            }
            $legacyUsers = require $path;
            if (!is_array($legacyUsers) || !is_array($legacyUsers['utenti'] ?? null)) {
                throw new RuntimeException('Formato non valido nel file utenti da migrare: ' . $path);
            }
            app_users_replace_with_pdo($pdo, $legacyUsers['utenti'], $isSuperadmin);
        }

        $insert = $pdo->prepare('INSERT INTO app_migrations (migration, applied_at) VALUES (?, ?)');
        $insert->execute([$migration, date('Y-m-d H:i:s')]);
        $pdo->exec('COMMIT');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->exec('ROLLBACK');
        }
        error_log('[database] Migrazione iniziale SQLite non riuscita: ' . $exception->getMessage());
        throw $exception;
    }
}

function app_db_migrate_relational_content(PDO $pdo): void
{
    $migration = 'relational_content_v1';
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        $check = $pdo->prepare('SELECT 1 FROM app_migrations WHERE migration = ?');
        $check->execute([$migration]);
        if ($check->fetchColumn()) {
            $pdo->exec('COMMIT');
            return;
        }

        $legacy = [];
        $read = $pdo->prepare('SELECT value FROM app_data WHERE key = ?');
        foreach (['news', 'glossary_entries', 'glossary_settings'] as $key) {
            $read->execute([$key]);
            $value = $read->fetchColumn();
            $legacy[$key] = $value === false
                ? null
                : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        }

        $news = $legacy['news'] ?? [];
        $entries = $legacy['glossary_entries'] ?? [];
        $settings = $legacy['glossary_settings'] ?? ['enabled' => false];
        if (!is_array($news) || !is_array($entries) || !is_array($settings)) {
            throw new RuntimeException('Formato non valido nei contenuti da migrare alle tabelle relazionali.');
        }

        $insertNews = $pdo->prepare(
            'INSERT INTO app_news
                (id, title, published_on, category, summary, content, image, image_alt,
                 featured, published, sort_order, extra_json)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $newsFields = [
            'id', 'titolo', 'data', 'categoria', 'sommario', 'contenuto',
            'immagine', 'immagine_alt', 'in_evidenza', 'pubblicato',
        ];
        foreach (array_values($news) as $index => $item) {
            if (!is_array($item) || trim((string) ($item['id'] ?? '')) === '') {
                throw new RuntimeException('Una news senza ID valido impedisce la migrazione relazionale.');
            }
            $extra = array_diff_key($item, array_flip($newsFields));
            $insertNews->execute([
                (string) $item['id'],
                (string) ($item['titolo'] ?? ''),
                (string) ($item['data'] ?? ''),
                (string) ($item['categoria'] ?? ''),
                (string) ($item['sommario'] ?? ''),
                (string) ($item['contenuto'] ?? ''),
                (string) ($item['immagine'] ?? ''),
                (string) ($item['immagine_alt'] ?? ''),
                !empty($item['in_evidenza']) ? 1 : 0,
                !empty($item['pubblicato']) ? 1 : 0,
                $index,
                json_encode($extra, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);
        }

        $insertEntry = $pdo->prepare(
            'INSERT INTO app_glossary_entries (term_key, term, definition, sort_order)
             VALUES (?, ?, ?, ?)'
        );
        foreach (array_values($entries) as $index => $item) {
            if (!is_array($item)) {
                throw new RuntimeException('Una voce del glossario non valida impedisce la migrazione relazionale.');
            }
            $term = trim((string) ($item['termine'] ?? ''));
            $definition = trim((string) ($item['definizione'] ?? ''));
            if ($term === '' || $definition === '') {
                throw new RuntimeException('Una voce del glossario incompleta impedisce la migrazione relazionale.');
            }
            $insertEntry->execute([
                function_exists('mb_strtolower') ? mb_strtolower($term, 'UTF-8') : strtolower($term),
                $term,
                $definition,
                $index,
            ]);
        }

        $insertSettings = $pdo->prepare(
            'INSERT INTO app_glossary_settings (id, enabled) VALUES (1, ?)'
        );
        $insertSettings->execute([!empty($settings['enabled']) ? 1 : 0]);

        $delete = $pdo->prepare('DELETE FROM app_data WHERE key IN (?, ?, ?)');
        $delete->execute(['news', 'glossary_entries', 'glossary_settings']);
        $insert = $pdo->prepare('INSERT INTO app_migrations (migration, applied_at) VALUES (?, ?)');
        $insert->execute([$migration, date('Y-m-d H:i:s')]);
        $pdo->exec('COMMIT');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->exec('ROLLBACK');
        }
        error_log('[database] Migrazione relazionale dei contenuti non riuscita: ' . $exception->getMessage());
        throw $exception;
    }
}

function app_news_row_to_array(array $row): array
{
    $extra = json_decode($row['extra_json'], true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($extra)) {
        throw new RuntimeException('Dati aggiuntivi news non validi nel database.');
    }

    return array_merge([
        'id' => $row['id'],
        'titolo' => $row['title'],
        'data' => $row['published_on'],
        'categoria' => $row['category'],
        'sommario' => $row['summary'],
        'contenuto' => $row['content'],
        'immagine' => $row['image'],
        'immagine_alt' => $row['image_alt'],
        'in_evidenza' => (bool) $row['featured'],
        'pubblicato' => (bool) $row['published'],
    ], $extra);
}

function app_news_load(): array
{
    $statement = app_db()->query('SELECT * FROM app_news ORDER BY sort_order, id');
    return array_map('app_news_row_to_array', $statement->fetchAll());
}

function app_news_find(string $id): ?array
{
    $statement = app_db()->prepare('SELECT * FROM app_news WHERE id = ?');
    $statement->execute([$id]);
    $row = $statement->fetch();
    return $row ? app_news_row_to_array($row) : null;
}

function app_news_insert_with_pdo(PDO $pdo, array $item, int $sortOrder): void
{
    $knownFields = [
        'id', 'titolo', 'data', 'categoria', 'sommario', 'contenuto',
        'immagine', 'immagine_alt', 'in_evidenza', 'pubblicato',
    ];
    $extra = array_diff_key($item, array_flip($knownFields));
    $statement = $pdo->prepare(
        'INSERT INTO app_news
            (id, title, published_on, category, summary, content, image, image_alt,
             featured, published, sort_order, extra_json)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([
        (string) ($item['id'] ?? ''),
        (string) ($item['titolo'] ?? ''),
        (string) ($item['data'] ?? ''),
        (string) ($item['categoria'] ?? ''),
        (string) ($item['sommario'] ?? ''),
        (string) ($item['contenuto'] ?? ''),
        (string) ($item['immagine'] ?? ''),
        (string) ($item['immagine_alt'] ?? ''),
        !empty($item['in_evidenza']) ? 1 : 0,
        !empty($item['pubblicato']) ? 1 : 0,
        $sortOrder,
        json_encode($extra, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
    ]);
}

function app_news_save(array $items): bool
{
    $pdo = app_db();
    $pdo->beginTransaction();
    try {
        $pdo->exec('DELETE FROM app_news');
        foreach (array_values($items) as $index => $item) {
            if (!is_array($item) || trim((string) ($item['id'] ?? '')) === '') {
                throw new RuntimeException('Una news senza ID valido non può essere salvata.');
            }
            app_news_insert_with_pdo($pdo, $item, $index);
        }
        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[database] Salvataggio delle news non riuscito: ' . $exception->getMessage());
        return false;
    }
}

function app_news_upsert(array $item): bool
{
    $pdo = app_db();
    $pdo->beginTransaction();
    try {
        $id = (string) ($item['id'] ?? '');
        if ($id === '') {
            throw new RuntimeException('Una news senza ID valido non può essere salvata.');
        }
        $find = $pdo->prepare('SELECT sort_order FROM app_news WHERE id = ?');
        $find->execute([$id]);
        $sortOrder = $find->fetchColumn();
        if ($sortOrder === false) {
            $sortOrder = (int) $pdo->query('SELECT COALESCE(MIN(sort_order), 0) - 1 FROM app_news')->fetchColumn();
        } else {
            $delete = $pdo->prepare('DELETE FROM app_news WHERE id = ?');
            $delete->execute([$id]);
        }
        app_news_insert_with_pdo($pdo, $item, (int) $sortOrder);
        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[database] Salvataggio della news non riuscito: ' . $exception->getMessage());
        return false;
    }
}

function app_news_delete(string $id): bool
{
    $statement = app_db()->prepare('DELETE FROM app_news WHERE id = ?');
    return $statement->execute([$id]);
}

function app_glossary_load_entries(): array
{
    $statement = app_db()->query(
        'SELECT term, definition FROM app_glossary_entries ORDER BY sort_order, term_key'
    );
    return array_map(
        static fn(array $row): array => ['termine' => $row['term'], 'definizione' => $row['definition']],
        $statement->fetchAll()
    );
}

function app_glossary_save_entries(array $entries): bool
{
    $pdo = app_db();
    $pdo->beginTransaction();
    try {
        $pdo->exec('DELETE FROM app_glossary_entries');
        $insert = $pdo->prepare(
            'INSERT INTO app_glossary_entries (term_key, term, definition, sort_order)
             VALUES (?, ?, ?, ?)'
        );
        foreach (array_values($entries) as $index => $item) {
            if (!is_array($item)) {
                throw new RuntimeException('Voce del glossario non valida.');
            }
            $term = trim((string) ($item['termine'] ?? ''));
            $definition = trim((string) ($item['definizione'] ?? ''));
            if ($term === '' || $definition === '') {
                throw new RuntimeException('Termine e definizione sono obbligatori.');
            }
            $insert->execute([
                function_exists('mb_strtolower') ? mb_strtolower($term, 'UTF-8') : strtolower($term),
                $term,
                $definition,
                $index,
            ]);
        }
        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[database] Salvataggio delle voci del glossario non riuscito: ' . $exception->getMessage());
        return false;
    }
}

function app_glossary_load_settings(): array
{
    $value = app_db()->query('SELECT enabled FROM app_glossary_settings WHERE id = 1')->fetchColumn();
    return ['enabled' => $value !== false && (bool) $value];
}

function app_glossary_save_settings(array $settings): bool
{
    $statement = app_db()->prepare(
        'INSERT INTO app_glossary_settings (id, enabled) VALUES (1, ?)
         ON CONFLICT(id) DO UPDATE SET enabled = excluded.enabled'
    );
    return $statement->execute([!empty($settings['enabled']) ? 1 : 0]);
}

function app_data_set_with_pdo(PDO $pdo, string $key, mixed $value): void
{
    $statement = $pdo->prepare(
        'INSERT INTO app_data (key, value) VALUES (?, ?)
         ON CONFLICT(key) DO UPDATE SET value = excluded.value'
    );
    $statement->execute([$key, json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)]);
}

function app_cooldown_set_with_pdo(PDO $pdo, string $key, int $lastAttempt): void
{
    $statement = $pdo->prepare(
        'INSERT INTO app_cooldowns (key, last_attempt) VALUES (?, ?)
         ON CONFLICT(key) DO UPDATE SET last_attempt = excluded.last_attempt'
    );
    $statement->execute([$key, $lastAttempt]);
}

function app_users_replace_with_pdo(PDO $pdo, array $users, bool $superadmin): void
{
    $roleCondition = $superadmin ? "role = 'superadmin'" : "role != 'superadmin'";
    $pdo->exec('DELETE FROM app_users WHERE ' . $roleCondition);
    $insert = $pdo->prepare('INSERT INTO app_users (username, role, record) VALUES (?, ?, ?)');
    foreach ($users as $user) {
        if (!is_array($user) || trim((string) ($user['username'] ?? '')) === '') {
            continue;
        }
        $role = $superadmin ? 'superadmin' : (string) ($user['role'] ?? 'user');
        $user['role'] = $role;
        $insert->execute([
            (string) $user['username'],
            $role,
            json_encode($user, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }
}

function app_data_get(string $key, mixed $default = null): mixed
{
    $statement = app_db()->prepare('SELECT value FROM app_data WHERE key = ?');
    $statement->execute([$key]);
    $value = $statement->fetchColumn();
    return $value === false ? $default : json_decode($value, true, 512, JSON_THROW_ON_ERROR);
}

function app_data_set(string $key, mixed $value): bool
{
    app_data_set_with_pdo(app_db(), $key, $value);
    return true;
}

function app_users_load(bool $superadmin = false): array
{
    $condition = $superadmin ? "role = 'superadmin'" : "role != 'superadmin'";
    $statement = app_db()->query('SELECT record FROM app_users WHERE ' . $condition . ' ORDER BY rowid');
    return array_map(
        static fn(array $row): array => json_decode($row['record'], true, 512, JSON_THROW_ON_ERROR),
        $statement->fetchAll()
    );
}

function app_users_replace(array $users, bool $superadmin = false): bool
{
    $pdo = app_db();
    $pdo->beginTransaction();
    try {
        app_users_replace_with_pdo($pdo, $users, $superadmin);
        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[database] Salvataggio account non riuscito: ' . $exception->getMessage());
        return false;
    }
}

function app_users_replace_all(array $users, array $superadmins): bool
{
    $pdo = app_db();
    $pdo->beginTransaction();
    try {
        app_users_replace_with_pdo($pdo, $users, false);
        app_users_replace_with_pdo($pdo, $superadmins, true);
        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[database] Creazione degli account iniziali non riuscita: ' . $exception->getMessage());
        return false;
    }
}

function app_cache_read(string $key, ?int $ttl = null): ?string
{
    $statement = app_db()->prepare('SELECT value, updated_at FROM app_cache WHERE key = ?');
    $statement->execute([$key]);
    $row = $statement->fetch();
    if (!$row || ($ttl !== null && time() - (int) $row['updated_at'] >= $ttl)) {
        return null;
    }
    return $row['value'];
}

function app_cache_write(string $key, string $value): bool
{
    $statement = app_db()->prepare(
        'INSERT INTO app_cache (key, value, updated_at) VALUES (?, ?, ?)
         ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at'
    );
    return $statement->execute([$key, $value, time()]);
}

function app_lock_acquire(string $key, int $leaseSeconds = 120): ?string
{
    $owner = bin2hex(random_bytes(16));
    $now = time();
    $statement = app_db()->prepare(
        'INSERT INTO app_locks (key, owner, locked_until) VALUES (?, ?, ?)
         ON CONFLICT(key) DO UPDATE SET owner = excluded.owner, locked_until = excluded.locked_until
         WHERE app_locks.locked_until <= ?'
    );
    $statement->execute([$key, $owner, $now + $leaseSeconds, $now]);
    return $statement->rowCount() === 1 ? $owner : null;
}

function app_lock_release(string $key, string $owner): void
{
    $statement = app_db()->prepare('DELETE FROM app_locks WHERE key = ? AND owner = ?');
    $statement->execute([$key, $owner]);
}

function app_cooldown_try_start(string $key, int $now, int $interval): bool
{
    $statement = app_db()->prepare(
        'INSERT INTO app_cooldowns (key, last_attempt) VALUES (?, ?)
         ON CONFLICT(key) DO UPDATE SET last_attempt = excluded.last_attempt
         WHERE app_cooldowns.last_attempt <= ?'
    );
    $statement->execute([$key, $now, $now - $interval]);
    return $statement->rowCount() === 1;
}
