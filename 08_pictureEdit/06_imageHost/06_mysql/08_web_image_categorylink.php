<?php
session_start();

$domain = 'example.com';

function decrypt($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

$key = 'signin-key-1';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (isset($_COOKIE['user_auth'])) {
        $decryptedValue = decrypt($_COOKIE['user_auth'], $key);
        if ($decryptedValue == 'mcteaone') {
            $_SESSION['loggedin'] = true;
        } else {
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('user_auth', '', time() - 3600, '/');
    header('Location: login.php');
    exit;
}

include '08_db_config.php';

function getAllCategoryLinks() {
    global $mysqli;

    $query = "SELECT id, category_name, kindID FROM Categories ORDER BY id ASC";
    $result = $mysqli->query($query);

    if (!$result) {
        die('Database query failed: ' . htmlspecialchars($mysqli->error, ENT_QUOTES, 'UTF-8'));
    }

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id' => (int)$row['id'],
            'category_name' => $row['category_name'],
            'kindID' => trim((string)($row['kindID'] ?? ''))
        ];
    }

    return $categories;
}

function buildCategoryUrl($tab, $category, $domain) {
    $id = (int)$category['id'];
    $kindID = trim((string)$category['kindID']);
    $domain = rtrim(trim($domain), '/');

    switch ($tab) {
        case 'twitter':
            return $kindID === '' ? '' : 'https://x.com/' . rawurlencode($kindID) . '/media';
        case 'sotwe':
            return $kindID === '' ? '' : 'https://www.sotwe.com/' . rawurlencode($kindID);
        case 'gallery':
            return 'https://' . $domain . '/08_picDisplay_mysql_galleryExistTab.php?page=1&category=' . $id;
        case 'order':
            return 'https://' . $domain . '/08_picDisplay_mysql_orderExistTab.php?page=1&category=' . $id;
        case 'archive':
            return $kindID === '' ? '' : 'https://web.archive.org/web/*/https://twitter.com/' . rawurlencode($kindID) . '*';
        default:
            return '#';
    }
}

$categories = getAllCategoryLinks();
$tabs = [
    'twitter' => 'Twitter',
    'sotwe' => 'Sotwe',
    'gallery' => 'Gallery',
    'order' => 'Order',
    'archive' => 'Archive'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Links</title>
    <style>
        :root {
            --bg: #f7f8fb;
            --panel: #ffffff;
            --text: #172033;
            --muted: #768094;
            --line: #e4e8f0;
            --accent: #2563eb;
            --accent-soft: #e8f0ff;
            --shadow: 0 18px 45px rgba(28, 38, 64, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.78), rgba(247, 248, 251, 0.92)),
                var(--bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .top-tabs {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            gap: 22px;
            align-items: center;
            padding: 18px 28px 14px;
            background: rgba(247, 248, 251, 0.9);
            border-bottom: 1px solid rgba(228, 232, 240, 0.78);
            backdrop-filter: blur(12px);
        }

        .tab-button {
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #526078;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            line-height: 1;
            padding: 10px 15px;
            transition: background-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        }

        .tab-button:hover,
        .tab-button.active {
            background: var(--accent-soft);
            color: var(--accent);
            box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.12);
        }

        .page {
            width: min(1180px, calc(100% - 48px));
            margin: 34px auto 56px;
        }

        .category-panel {
            display: none;
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 28px;
        }

        .category-panel.active {
            display: block;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .category-link,
        .category-disabled {
            display: flex;
            align-items: center;
            min-height: 48px;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 11px 13px;
            font-size: 15px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .category-link {
            background: #ffffff;
            color: #22304a;
            text-decoration: none;
            transition: border-color 0.18s ease, color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        .category-link:hover {
            border-color: rgba(37, 99, 235, 0.45);
            color: var(--accent);
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.08);
            transform: translateY(-1px);
        }

        .category-disabled {
            background: #f4f6fa;
            color: var(--muted);
            cursor: not-allowed;
        }

        .empty-state {
            margin: 0;
            color: var(--muted);
            font-size: 15px;
        }

        @media (max-width: 760px) {
            .top-tabs {
                gap: 8px;
                overflow-x: auto;
                padding: 14px 16px 12px;
            }

            .tab-button {
                font-size: 14px;
                padding: 9px 12px;
                white-space: nowrap;
            }

            .page {
                width: calc(100% - 24px);
                margin-top: 18px;
            }

            .category-panel {
                padding: 14px;
            }

            .category-grid {
                gap: 8px;
            }

            .category-link,
            .category-disabled {
                min-height: 42px;
                padding: 8px 7px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <nav class="top-tabs" aria-label="Category link types">
        <?php foreach ($tabs as $tabKey => $tabLabel): ?>
            <button
                type="button"
                class="tab-button<?php echo $tabKey === 'twitter' ? ' active' : ''; ?>"
                data-tab="<?php echo htmlspecialchars($tabKey, ENT_QUOTES, 'UTF-8'); ?>"
            >
                <?php echo htmlspecialchars($tabLabel, ENT_QUOTES, 'UTF-8'); ?>
            </button>
        <?php endforeach; ?>
    </nav>

    <main class="page">
        <?php foreach ($tabs as $tabKey => $tabLabel): ?>
            <section
                id="panel-<?php echo htmlspecialchars($tabKey, ENT_QUOTES, 'UTF-8'); ?>"
                class="category-panel<?php echo $tabKey === 'twitter' ? ' active' : ''; ?>"
                aria-label="<?php echo htmlspecialchars($tabLabel, ENT_QUOTES, 'UTF-8'); ?> categories"
            >
                <?php if (empty($categories)): ?>
                    <p class="empty-state">No categories found.</p>
                <?php else: ?>
                    <ul class="category-grid">
                        <?php foreach ($categories as $category): ?>
                            <?php
                                $url = buildCategoryUrl($tabKey, $category, $domain);
                                $name = htmlspecialchars($category['category_name'], ENT_QUOTES, 'UTF-8');
                            ?>
                            <li>
                                <?php if ($url === ''): ?>
                                    <span class="category-disabled" title="kindID is empty"><?php echo $name; ?></span>
                                <?php else: ?>
                                    <a
                                        class="category-link"
                                        href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <?php echo $name; ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </main>

    <script>
        const tabButtons = document.querySelectorAll('.tab-button');
        const panels = document.querySelectorAll('.category-panel');

        tabButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const activeTab = button.dataset.tab;

                tabButtons.forEach((item) => {
                    item.classList.toggle('active', item === button);
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('active', panel.id === `panel-${activeTab}`);
                });
            });
        });
    </script>
</body>
</html>
