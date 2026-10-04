<?php
require __DIR__.'/includes/config.php';

try {
    $alreadyInstalled = $pdo->query("SELECT 1 FROM settings WHERE name = 'install_complete' LIMIT 1")->fetchColumn();
    if ($alreadyInstalled) {
        http_response_code(410);
        exit('Installation has already run.');
    }
} catch (PDOException $e) {
    // A fresh database does not have the settings table yet.
}

try {
    $schema = file_get_contents(__DIR__.'/schema.sql');
    foreach (preg_split('/;\s*(?:\r?\n|$)/', $schema) as $statement) {
        if (preg_match('/^\s*CREATE\s+TABLE/i', $statement)) {
            $pdo->exec($statement);
        }
    }

    $columns = [
        'about' => [
            'title' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
            'body' => 'TEXT NULL',
            'button_text' => 'VARCHAR(100) DEFAULT \'Discover more\'',
            'button_url' => 'VARCHAR(255) DEFAULT \'#tours\'',
            'image' => 'VARCHAR(255) DEFAULT \'\'',
            'title_en' => 'VARCHAR(255) NULL',
            'title_ru' => 'VARCHAR(255) NULL',
            'body_en' => 'TEXT NULL',
            'body_ru' => 'TEXT NULL',
            'button_text_en' => 'VARCHAR(100) NULL',
            'button_text_ru' => 'VARCHAR(100) NULL',
        ],
        'tours' => [
            'title' => 'VARCHAR(255) NOT NULL DEFAULT \'\'',
            'description' => 'TEXT NULL',
            'price' => 'VARCHAR(100) NULL',
            'image' => 'VARCHAR(255) NULL',
            'sort_order' => 'INT DEFAULT 0',
            'created_at' => 'TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP',
            'title_en' => 'VARCHAR(255) NULL',
            'title_ru' => 'VARCHAR(255) NULL',
            'description_en' => 'TEXT NULL',
            'description_ru' => 'TEXT NULL',
        ],
    ];

    foreach ($columns as $table => $tableColumns) {
        foreach ($tableColumns as $column => $definition) {
            $check = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
            $check->execute([$column]);
            if (!$check->fetch()) {
                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            }
        }
    }

    if ((int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO admins(username, password_hash) VALUES (?, ?)');
        $stmt->execute(['admin', $hash]);
    }

    if ((int)$pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn() === 0) {
        $stmt = $pdo->prepare('INSERT INTO settings(name, value) VALUES (?, ?)');
        foreach ([
            'site_name' => 'EVE tour team',
            'email' => 'info@example.com',
            'phone' => '+994 50 000 00 00',
            'hero_eyebrow' => 'Explore Azerbaijan',
        ] as $name => $value) {
            $stmt->execute([$name, $value]);
        }
    }

    if ((int)$pdo->query('SELECT COUNT(*) FROM about')->fetchColumn() === 0) {
        $stmt = $pdo->prepare('INSERT INTO about (id, title, body, button_text, button_url, image, title_en, body_en, button_text_en) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            'Welcome to EVE tour!',
            'Create memorable private tours and experiences with a local travel team.',
            'Explore tours',
            '#tours',
            '',
            'Welcome to EVE tour!',
            'Create memorable private tours and experiences with a local travel team.',
            'Explore tours',
        ]);
    }

    if ((int)$pdo->query('SELECT COUNT(*) FROM tours')->fetchColumn() === 0) {
        $stmt = $pdo->prepare('INSERT INTO tours (title, description, price, sort_order, title_en, description_en) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ([
            ['City Discovery', 'A private city tour tailored to your interests.', 'From €80'],
            ['Mountain Escape', 'Nature, viewpoints and local experiences.', 'From €120'],
            ['Cultural Journey', 'Museums, heritage and authentic local stories.', 'From €100'],
            ['Food & Wine', 'Taste local cuisine with a private guide.', 'From €90'],
            ['Private Adventure', 'A flexible full-day itinerary for your group.', 'From €140'],
        ] as $index => [$title, $description, $price]) {
            $stmt->execute([$title, $description, $price, $index + 1, $title, $description]);
        }
    }

    if ((int)$pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn() === 0) {
        $stmt = $pdo->prepare('INSERT INTO reviews (name, location, review, rating, sort_order) VALUES (?, ?, ?, ?, ?)');
        foreach ([
            ['Anna', 'Germany', 'Amazing organization and a wonderful guide. Everything was smooth from start to finish.'],
            ['Michael', 'Belgium', 'Great communication, beautiful places and a very personal experience.'],
            ['Sophie', 'France', 'We loved the flexibility and attention to detail.'],
            ['Daniel', 'Netherlands', 'A memorable trip with excellent service.'],
        ] as $index => [$name, $location, $review]) {
            $stmt->execute([$name, $location, $review, 5, $index + 1]);
        }
    }

    $pdo->exec("INSERT INTO settings(name, value) VALUES ('install_complete', '1') ON DUPLICATE KEY UPDATE value = '1'");
    echo '<h1>Installation complete</h1><p>Admin: <b>admin</b> / <b>admin123</b>. Change the password after first login.</p><p><a href="admin/login.php">Open admin panel</a></p>';
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Install failed</h1><pre>'.htmlspecialchars($e->getMessage()).'</pre>';
}
