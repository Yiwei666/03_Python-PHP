#!/usr/bin/env php
<?php
/**
 * 命令行 Crossref 录入工具（PHP >= 7.4，mysqli/mysqlnd、curl）。
 * php 08_server_write_paperDOI_crossRef_api.php
 * 数据库配置沿用 08_db_config.php；每次运行执行一个菜单操作。
 * 重写仅更新 papers；新增与分类追加在同一事务中提交。
 */
namespace PaperDoiCli;

// ============================================================
// 一、部署 / 迁移配置：请优先检查本区，业务代码从后面的第二部分开始。
// ============================================================
// 外部依赖只有 DB_CONFIG_FILE：该 PHP 文件应创建 mysqli 连接变量 $mysqli。
// 不调用其他项目模块/函数，无需网页 API 密钥。字段映射参考：
// 08_web_crossRef_query.php 的 displayResults / sendPaperData，
// 08_tm_add_paper.php，以及 08_category_operations.php 的默认分类规则。
// 上述三个参考文件不需要被本脚本 require，迁移时不必随本脚本一起复制。
// PHP >= 7.4；mysqli 需含 mysqlnd；写入的表须使用 InnoDB。
// 表名可在这里调整；列名沿用项目约定，改列名时还须同步字段映射和 SQL。
final class Config {
    // 数据库账号、密码、主机和库名请在这个文件中配置，不在本脚本重复保存。
    public const DB_CONFIG_FILE = __DIR__ . '/08_db_config.php';
    public const DB_CHARSET = 'utf8mb4';
    // 保持严格写入，防止静默截断；这是当前连接的设置，不修改全局配置。
    public const DB_REQUIRED_SQL_MODE = 'STRICT_ALL_TABLES';
    public const REQUIRED_EXTENSIONS = ['mysqli', 'curl'];
    public const TABLES = [
        'papers' => 'papers',
        'categories' => 'categories',
        'paperCategories' => 'paperCategories',
        'select_paper' => 'select_paper',
        'gdfile' => 'gdfile',
    ];
    public const DEFAULT_CATEGORY_ID = 1; // 迁移后核对是否仍为“0 All papers”。

    public const CROSSREF_WORKS_URL = 'https://api.crossref.org/works/'; // 保留末尾斜杠。
    public const HTTP_USER_AGENT = 'PaperDatabase-DOI-CLI/1.0';
    public const HTTP_CONNECT_TIMEOUT_SECONDS = 10;
    public const HTTP_TIMEOUT_SECONDS = 20;
    public const HTTP_MAX_REDIRECTS = 3;
    public const REQUEST_INTERVAL_SECONDS = 1;
    public const REQUEST_MAX_ROUNDS = 4; // 包含首轮请求。
    public const RETRY_BASE_SECONDS = 2; // 指数退避；仍遵守更长的 Retry-After。

    // 锁前缀必须与其他写入脚本一致，不能只在本脚本单独修改。
    public const DOI_LOCK_PREFIX = 'paper_doi_';
    public const DOI_LOCK_TIMEOUT_SECONDS = 10;

    // 与网页版一致的缺失文本。状态、评分、doi_type 等默认值读取真实表结构，
    // 不在这里硬编码；其完整字段转换逻辑见紧接配置区的 workMetadata()。
    public const MISSING_TEXT = [
        'title' => '未找到标题', 'authors' => '未找到作者信息',
        'journal_name' => '未找到期刊名', 'volume' => '未找到卷号',
        'issue' => '未找到期号', 'pages' => '未找到页码',
        'article_number' => '未找到文章号', 'issn' => '未找到印刷版 ISSN',
        'publisher' => '未找到出版商',
    ];
}

/** 外部模块加载入口：仅选定菜单操作后调用，加载脚本本身不会连接数据库。 */
function connectDatabase(): \mysqli {
    foreach (Config::REQUIRED_EXTENSIONS as $extension) {
        if (!extension_loaded($extension)) throw new \RuntimeException('PHP 缺少扩展：' . $extension);
    }
    if (!is_file(Config::DB_CONFIG_FILE)) throw new \RuntimeException('数据库配置文件不存在：' . Config::DB_CONFIG_FILE);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require Config::DB_CONFIG_FILE;
    if (!isset($mysqli) || !($mysqli instanceof \mysqli)) throw new \RuntimeException('数据库配置文件必须创建 $mysqli 连接。');
    $mysqli->set_charset(Config::DB_CHARSET);
    $mode = $mysqli->real_escape_string(Config::DB_REQUIRED_SQL_MODE);
    $mysqli->query("SET SESSION sql_mode = CONCAT_WS(',', @@SESSION.sql_mode, '{$mode}')");
    return $mysqli;
}

/** 对齐 displayResults + sendPaperData + 08_tm_add_paper 的字段映射。 */
function workMetadata(array $item): array {
    $authors = [];
    foreach (($item['author'] ?? []) as $author) {
        $authors[] = trim(($author['given'] ?? '') . ' ' . ($author['family'] ?? ''));
    }
    $year = $item['published-print']['date-parts'][0][0]
        ?? $item['published-online']['date-parts'][0][0] ?? 0;
    $issn = Config::MISSING_TEXT['issn'];
    foreach (($item['issn-type'] ?? []) as $entry) {
        if (($entry['type'] ?? '') === 'print') { $issn = $entry['value']; break; }
    }
    $citation = $item['is-referenced-by-count'] ?? 0;
    return [
        'title' => isset($item['title']) ? implode(' ', $item['title']) : Config::MISSING_TEXT['title'],
        'authors' => implode(', ', $authors) ?: Config::MISSING_TEXT['authors'],
        'journal_name' => isset($item['container-title']) ? implode(' ', $item['container-title']) : Config::MISSING_TEXT['journal_name'],
        'publication_year' => (int)$year,
        'volume' => webValue($item['volume'] ?? null, Config::MISSING_TEXT['volume']),
        'issue' => webValue($item['issue'] ?? null, Config::MISSING_TEXT['issue']),
        'pages' => webValue($item['page'] ?? null, Config::MISSING_TEXT['pages']),
        'article_number' => webValue($item['article-number'] ?? null, Config::MISSING_TEXT['article_number']),
        'doi' => $item['DOI'], 'issn' => $issn,
        'publisher' => webValue($item['publisher'] ?? null, Config::MISSING_TEXT['publisher']),
        'citation_count' => is_int($citation) || is_float($citation) ? (int)$citation : 0,
    ];
}

// ============================================================
// 二、业务实现：一般部署无需修改以下部分。
// ============================================================

class Cancelled extends \RuntimeException {}

function ask(string $prompt): string {
    echo $prompt;
    $line = fgets(STDIN);
    if ($line === false || strtolower(trim($line)) === 'q') {
        throw new Cancelled('已退出，本次未确认的操作不会写入。');
    }
    return trim($line);
}

function printJson($value): void {
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) throw new \RuntimeException('无法生成 JSON 预览：' . json_last_error_msg());
    echo $json, "\n";
}

function validDoi($doi): bool {
    return is_string($doi) && preg_match('~^10\.\d{4,}/[!-\x7E]+$~D', $doi) === 1;
}

function doiKey(string $doi): string { return strtolower(trim($doi)); }

// JavaScript 的 || 不把字符串 "0" 当成缺失值，与网页占位值规则一致。
function webValue($value, string $fallback) {
    return $value === null || $value === '' || $value === false || $value === 0 || $value === 0.0 ? $fallback : $value;
}

/** 与网页 BatchDOI 相同：格式错误与输入内重复项跳过，保留其余项。 */
function parseBatch(string $json): array {
    $values = json_decode($json);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($values) || !$values) {
        throw new \InvalidArgumentException('请输入非空 JSON DOI 字符串数组。');
    }
    foreach ($values as $value) {
        if (!is_string($value)) throw new \InvalidArgumentException('数组每一项必须是 DOI 字符串。');
    }
    $dois = $issues = $seen = [];
    foreach ($values as $i => $doi) {
        if (!validDoi($doi)) {
            $issues[] = '第 ' . ($i + 1) . ' 项 DOI 格式错误，已跳过：' . $doi;
        } elseif (isset($seen[doiKey($doi)])) {
            $issues[] = '第 ' . ($i + 1) . ' 项重复 DOI，已跳过：' . $doi;
        } else {
            $seen[doiKey($doi)] = true;
            $dois[] = $doi;
        }
    }
    return [$dois, $issues];
}

function readBatch(): array {
    while (true) {
        echo "请输入 DOI JSON 数组，可粘贴多行；以 ] 结束，或空行结束输入；q 退出：\n";
        $json = '';
        while (true) {
            $line = ask('');
            if ($line === '' && $json !== '') break;
            if ($line === '') continue;
            $json .= $line . "\n";
            if (substr(rtrim($line), -1) === ']') break;
        }
        try {
            list($dois, $issues) = parseBatch(trim($json));
            foreach ($issues as $issue) echo $issue, "\n";
            if ($dois) return $dois;
            echo "没有有效 DOI，请重新输入。\n";
        } catch (\InvalidArgumentException $e) {
            echo $e->getMessage(), "\n";
        }
    }
}

/** 只获取单篇，不发送多 DOI 批量请求；不关闭 TLS 证书校验。 */
function httpWork(string $doi): array {
    $retryAfter = null;
    $ch = curl_init(Config::CROSSREF_WORKS_URL . rawurlencode($doi));
    if ($ch === false) throw new \RuntimeException('无法初始化 cURL。');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => Config::HTTP_CONNECT_TIMEOUT_SECONDS,
        CURLOPT_TIMEOUT => Config::HTTP_TIMEOUT_SECONDS, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => Config::HTTP_MAX_REDIRECTS,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_USERAGENT => Config::HTTP_USER_AGENT,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_HEADERFUNCTION => function ($ch, $header) use (&$retryAfter) {
            if (stripos($header, 'HTTP/') === 0) $retryAfter = null;
            if (stripos($header, 'Retry-After:') === 0) $retryAfter = trim(substr($header, 12));
            return strlen($header);
        },
    ]);
    $body = curl_exec($ch);
    $error = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'error' => $error, 'retry_after' => $retryAfter];
}

/** 默认重试和间隔与网页 BatchDOI 一致，部署参数集中在 Config。 */
function fetchWorks(array $dois, ?callable $transport = null, ?callable $wait = null, ?callable $log = null): array {
    $transport = $transport ?? __NAMESPACE__ . '\\httpWork';
    $wait = $wait ?? function ($seconds) {
        // 分段等待，便于终端 Ctrl+C；不提前忽略服务端 Retry-After。
        while ($seconds > 0) { $slice = min(1, $seconds); usleep((int)round($slice * 1000000)); $seconds -= $slice; }
    };
    $log = $log ?? function ($message) { echo $message, "\n"; };
    $pending = $dois;
    $works = $issues = [];
    $delay = 0;
    for ($round = 0; $round < Config::REQUEST_MAX_ROUNDS && $pending; $round++) {
        $retry = [];
        foreach ($pending as $doi) {
            if ($delay > 0) { $log('等待 ' . ceil($delay) . ' 秒后请求下一条…'); $wait($delay); }
            $delay = Config::REQUEST_INTERVAL_SECONDS;
            $key = doiKey($doi);
            $log('Crossref 第 ' . ($round + 1) . '/' . Config::REQUEST_MAX_ROUNDS . ' 轮：' . $doi);
            try {
                $response = $transport($doi);
                $code = $response['code'];
                if ($response['body'] === false || !empty($response['error'])) {
                    throw new \RuntimeException('网络错误或请求超时：' . ($response['error'] ?? ''));
                }
                if ($code < 200 || $code >= 300) {
                    $issues[$key] = 'HTTP ' . $code;
                    if ($code === 429 || $code === 408 || $code >= 500) $retry[] = $doi;
                    if ($code === 429 || $code === 503) {
                        $header = $response['retry_after'] ?? '';
                        $seconds = ctype_digit((string)$header) ? (float)$header : max(0, (strtotime((string)$header) ?: 0) - time());
                        $delay = max(Config::RETRY_BASE_SECONDS * (2 ** $round), $seconds);
                    }
                    $log($doi . '：' . $issues[$key]);
                    continue;
                }
                $data = json_decode($response['body'], true);
                if (json_last_error() !== JSON_ERROR_NONE) throw new \RuntimeException('Crossref 响应无法解析');
                if (($data['status'] ?? null) !== 'ok' || !is_string($data['message']['DOI'] ?? null)
                    || !validDoi($data['message']['DOI'])) {
                    throw new \RuntimeException('Crossref 响应缺少有效的 DOI 数据');
                }
                if (strtolower($data['message']['DOI']) !== $key) {
                    $issues[$key] = 'Crossref 未返回匹配的 DOI，已跳过';
                    $log($doi . '：' . $issues[$key]);
                    continue;
                }
                $works[$key] = $data['message'];
                unset($issues[$key]);
            } catch (\Throwable $e) {
                $issues[$key] = $e->getMessage();
                $retry[] = $doi;
                $log($doi . '：' . $issues[$key]);
            }
        }
        $pending = $retry;
        $delay = max($delay, Config::RETRY_BASE_SECONDS * (2 ** $round));
    }
    // 按用户输入顺序输出，而不是按重试成功的先后顺序。
    $ordered = [];
    foreach ($dois as $doi) if (isset($works[doiKey($doi)])) $ordered[doiKey($doi)] = $works[doiKey($doi)];
    return [$ordered, $issues];
}

/** 生成完整新行：不继承原论文值；非 API 字段使用线上真实列默认值。 */
function completePaper(array $metadata, array $schema): array {
    $row = [];
    foreach ($schema as $name => $column) {
        $extra = strtolower($column['Extra'] ?? '');
        if ($name === 'paperID' || strpos($extra, 'generated') !== false && strpos($extra, 'default_generated') === false) continue;
        if (array_key_exists($name, $metadata)) {
            $value = $metadata[$name];
        } elseif (strpos($extra, 'default_generated') !== false) {
            throw new \RuntimeException("字段 {$name} 有表达式默认值，无法准确预览，请先适配。 ");
        } elseif ($column['Default'] !== null) {
            $value = $column['Default'];
        } elseif ($column['Null'] === 'YES') {
            $value = null;
        } else {
            throw new \RuntimeException("字段 {$name} 没有默认值且不允许 NULL，无法生成完整新论文。 ");
        }
        $type = strtolower($column['Type']);
        if ($value !== null && preg_match('/^(tinyint|smallint|mediumint|int|bigint)\b/', $type)) {
            if (!is_numeric($value) || (float)$value < 0 && strpos($type, 'unsigned') !== false) {
                throw new \RuntimeException("字段 {$name} 的整数值不合法。");
            }
            $value = (int)$value;
        }
        if (is_string($value) && preg_match('/^(?:var)?char\((\d+)\)/', $type, $match)) {
            $length = preg_match_all('/./us', $value, $unused);
            if ($length === false || $length > (int)$match[1]) throw new \RuntimeException("字段 {$name} 超过列长度 {$match[1]}，本条不写入。 ");
        }
        $row[$name] = $value;
    }
    foreach (array_keys($metadata) as $name) {
        if (!array_key_exists($name, $row)) throw new \RuntimeException("papers 缺少可写字段 {$name}。");
    }
    return $row;
}

class MysqlRepository {
    private $db;
    public function __construct($db) { $this->db = $db; }
    private function table(string $key): string {
        return '`' . str_replace('`', '``', Config::TABLES[$key]) . '`';
    }
    private function statement(string $sql, array $values = []) {
        $stmt = $this->db->prepare($sql);
        if ($values) $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        $stmt->execute();
        return $stmt;
    }
    private function rows(string $sql, array $values = []): array {
        $stmt = $this->statement($sql, $values);
        try { return $stmt->get_result()->fetch_all(MYSQLI_ASSOC); }
        finally { $stmt->close(); }
    }
    public function schema(): array {
        $schema = [];
        foreach ($this->rows('SHOW FULL COLUMNS FROM ' . $this->table('papers')) as $column) $schema[$column['Field']] = $column;
        return $schema;
    }
    public function requireTransactions(bool $categories): void {
        $tables = $categories ? ['papers', 'paperCategories'] : ['papers'];
        foreach ($tables as $key) {
            $table = Config::TABLES[$key];
            $rows = $this->rows('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$table]);
            if (strtolower($rows[0]['ENGINE'] ?? '') !== 'innodb') throw new \RuntimeException("{$table} 需要使用 InnoDB 才能保证事务回滚。");
        }
    }
    public function find(string $doi, bool $lock = false): array {
        return $this->rows('SELECT * FROM ' . $this->table('papers') . ' WHERE LOWER(TRIM(doi)) = LOWER(TRIM(?)) ORDER BY paperID' . ($lock ? ' FOR UPDATE' : ''), [$doi]);
    }
    public function paper(int $id, bool $lock = false): ?array {
        $rows = $this->rows('SELECT * FROM ' . $this->table('papers') . ' WHERE paperID = ?' . ($lock ? ' FOR UPDATE' : ''), [$id]);
        return $rows[0] ?? null;
    }
    public function categories(bool $lock = false): array {
        return $this->rows('SELECT categoryID, category_name FROM ' . $this->table('categories') . ' ORDER BY categoryID' . ($lock ? ' LOCK IN SHARE MODE' : ''));
    }
    public function warnings(int $id): array {
        return [
            'gdfile关联数' => (int)$this->rows('SELECT COUNT(*) AS n FROM ' . $this->table('gdfile') . ' WHERE paperID = ?', [$id])[0]['n'],
            'select_paper关联数' => (int)$this->rows('SELECT COUNT(*) AS n FROM ' . $this->table('select_paper') . ' WHERE paperID = ?', [$id])[0]['n'],
        ];
    }
    public function acquire(string $doi): void {
        $name = Config::DOI_LOCK_PREFIX . sha1(doiKey($doi));
        $rows = $this->rows('SELECT GET_LOCK(?, ?) AS acquired', [$name, Config::DOI_LOCK_TIMEOUT_SECONDS]);
        if ((int)($rows[0]['acquired'] ?? 0) !== 1) throw new \RuntimeException('DOI 写入锁获取失败：' . $doi);
    }
    public function release(string $doi): void {
        $this->rows('SELECT RELEASE_LOCK(?)', [Config::DOI_LOCK_PREFIX . sha1(doiKey($doi))]);
    }
    public function begin(): void { $this->db->begin_transaction(); }
    public function commit(): void { $this->db->commit(); }
    public function rollback(): void { $this->db->rollback(); }
    private function identifiers(array $row): array {
        return array_map(function ($name) { return '`' . str_replace('`', '``', $name) . '`'; }, array_keys($row));
    }
    public function insert(array $row): int {
        $stmt = $this->statement('INSERT INTO ' . $this->table('papers') . ' (' . implode(',', $this->identifiers($row)) . ') VALUES (' . implode(',', array_fill(0, count($row), '?')) . ')', array_values($row));
        $id = (int)$this->db->insert_id;
        $stmt->close();
        return $id;
    }
    public function rewrite(int $id, array $row): void {
        $columns = array_map(function ($column) { return $column . ' = ?'; }, $this->identifiers($row));
        $stmt = $this->statement('UPDATE ' . $this->table('papers') . ' SET ' . implode(',', $columns) . ' WHERE paperID = ?', array_merge(array_values($row), [$id]));
        if ($stmt->affected_rows !== 1) throw new \RuntimeException('目标记录未成功更新。');
        $stmt->close();
    }
    public function addCategory(int $id, int $categoryID): void {
        // 仅忽略组合主键重复，不使用 INSERT IGNORE 吞掉其他约束错误。
        $stmt = $this->statement('INSERT INTO ' . $this->table('paperCategories') . ' (paperID, categoryID) VALUES (?, ?) ON DUPLICATE KEY UPDATE categoryID = VALUES(categoryID)', [$id, $categoryID]);
        $stmt->close();
    }
}

/** 一次确认对应一次原子提交；事务期间不进行网络请求或等待用户输入。 */
function applyPlan($repo, array $plan): array {
    $keys = [];
    foreach ($plan['dois'] as $doi) $keys[doiKey($doi)] = $doi;
    if ($plan['mode'] === 1 && !empty($plan['original']['doi'])) {
        $keys[doiKey($plan['original']['doi'])] = $plan['original']['doi'];
    }
    ksort($keys, SORT_STRING);
    $held = [];
    $transaction = false;
    try {
        foreach ($keys as $doi) { $repo->acquire($doi); $held[] = $doi; }
        $repo->requireTransactions($plan['mode'] !== 1);
        $repo->begin();
        $transaction = true;
        if ($plan['mode'] === 1) {
            $current = $repo->paper($plan['id'], true);
            if ($current !== $plan['original']) throw new \RuntimeException('目标论文在预览后已变化，请重新运行并确认。');
            if ($repo->find($plan['dois'][0], true)) throw new \RuntimeException('新 DOI 已存在，取消重写。');
            if ($repo->warnings($plan['id']) !== $plan['warnings']) throw new \RuntimeException('文件或选择关联在预览后发生变化，请重新核查。');
            $repo->rewrite($plan['id'], $plan['new'][doiKey($plan['dois'][0])]);
            $result = ['rewritten' => [$plan['id']], 'inserted' => [], 'existing' => [], 'failed' => []];
        } else {
            $categoryRows = $repo->categories(true);
            $validIDs = array_map('intval', array_column($categoryRows, 'categoryID'));
            foreach (array_unique(array_merge([Config::DEFAULT_CATEGORY_ID], $plan['categories'])) as $id) {
                if (!in_array($id, $validIDs, true)) throw new \RuntimeException('分类已不存在：' . $id);
            }
            $result = ['rewritten' => [], 'inserted' => [], 'existing' => [], 'failed' => []];
            foreach ($plan['dois'] as $doi) {
                $key = doiKey($doi);
                $existing = $repo->find($doi, true);
                if ($existing && $plan['mode'] === 2) throw new \RuntimeException('DOI 已被其他入口写入，本次取消，请重新输入。');
                if ($existing) {
                    $ids = array_map('intval', array_column($existing, 'paperID'));
                    $result['existing'][$doi] = $ids;
                    $categories = $plan['categories'];
                } elseif (isset($plan['new'][$key])) {
                    $ids = [$repo->insert($plan['new'][$key])];
                    $result['inserted'][$doi] = $ids[0];
                    $categories = array_unique(array_merge([Config::DEFAULT_CATEGORY_ID], $plan['categories']));
                } else {
                    $result['failed'][] = $doi; // 请求失败或原有记录已被删除，不凭空插入。
                    continue;
                }
                foreach ($ids as $id) foreach ($categories as $category) $repo->addCategory($id, $category);
            }
        }
        $repo->commit();
        $transaction = false;
        return $result;
    } catch (\Throwable $e) {
        if ($transaction) $repo->rollback();
        throw $e;
    } finally {
        foreach (array_reverse($held) as $doi) {
            try { $repo->release($doi); } catch (\Throwable $ignored) { /* 连接关闭也会释放命名锁。 */ }
        }
    }
}

function chooseCategories($repo): array {
    $categories = $repo->categories();
    $valid = array_map('intval', array_column($categories, 'categoryID'));
    if (!in_array(Config::DEFAULT_CATEGORY_ID, $valid, true)) throw new \RuntimeException('默认分类 categoryID=' . Config::DEFAULT_CATEGORY_ID . ' 不存在，取消本次操作。');
    echo "\n可选分类（新论文必定加入默认分类 ", Config::DEFAULT_CATEGORY_ID, "）：\n";
    foreach ($categories as $category) echo $category['categoryID'], ' | ', $category['category_name'], "\n";
    while (true) {
        $input = ask('附加分类 ID，多个用英文逗号分隔；直接回车不追加；q 退出：');
        if ($input === '') return [];
        if (!preg_match('/^\d+(\s*,\s*\d+)*$/D', $input)) { echo "分类格式错误。\n"; continue; }
        $ids = array_values(array_unique(array_map('intval', explode(',', $input))));
        if (array_diff($ids, $valid)) { echo "包含不存在的分类 ID，请重新输入。\n"; continue; }
        return $ids;
    }
}

function inputNewDoi($repo): string {
    while (true) {
        $doi = ask('请输入不存在于 papers 的 DOI（不加引号），q 退出：');
        if (!validDoi($doi)) { echo "DOI 格式错误，例如 10.1016/j.jhazmat.2026.142805。\n"; continue; }
        $existing = $repo->find($doi);
        if ($existing) {
            echo 'DOI 已存在，paperID：', implode(', ', array_column($existing, 'paperID')), "；请重新输入。\n";
            continue;
        }
        return $doi;
    }
}

function preparePlan($repo, int $mode): array {
    $schema = $repo->schema();
    $plan = ['mode' => $mode, 'dois' => [], 'new' => [], 'categories' => []];
    if ($mode === 1) {
        while (true) {
            $input = ask('需要重写的 paperID（q 退出）：');
            if (!ctype_digit($input) || (int)$input < 1) { echo "请输入正整数 paperID。\n"; continue; }
            $original = $repo->paper((int)$input);
            if (!$original) { echo "paperID 不存在，请重新输入。\n"; continue; }
            $plan['id'] = (int)$input;
            $plan['original'] = $original;
            $plan['warnings'] = $repo->warnings($plan['id']);
            echo "当前论文：\n"; printJson($original);
            echo "原分类全部继承。以下关联表不会修改，请核查关联风险：\n"; printJson($plan['warnings']);
            echo "改写前请确认相关状态/引用数后台任务已暂停且在途任务已结束。\n";
            break;
        }
    }
    if ($mode !== 3) {
        while (true) {
            $doi = inputNewDoi($repo);
            list($works, $issues) = fetchWorks([$doi]);
            if (!$works) { echo "未取得有效元数据，没有写入；可重新输入 DOI。\n"; printJson($issues); continue; }
            try { $row = completePaper(workMetadata(reset($works)), $schema); }
            catch (\Throwable $e) { echo $e->getMessage(), "\n请改用其他 DOI，或 q 退出后调整表结构。\n"; continue; }
            $plan['dois'] = [$doi];
            $plan['new'][doiKey($doi)] = $row;
            break;
        }
    } else {
        $plan['dois'] = readBatch();
        $missing = [];
        foreach ($plan['dois'] as $doi) {
            $existing = $repo->find($doi);
            if ($existing) echo '[已存在] ', $doi, '，paperID=', implode(',', array_column($existing, 'paperID')), "；元数据不改写\n";
            else { $missing[] = $doi; echo '[不存在] ', $doi, "\n"; }
        }
        list($works, $issues) = fetchWorks($missing);
        foreach ($works as $key => $work) {
            try { $plan['new'][$key] = completePaper(workMetadata($work), $schema); }
            catch (\Throwable $e) { $issues[$key] = $e->getMessage(); }
        }
        if ($issues) { echo "以下论文未取得可写入的数据，将跳过新增：\n"; printJson($issues); }
    }
    if ($mode !== 1) $plan['categories'] = chooseCategories($repo);
    return $plan;
}

function main(): int {
    if (PHP_SAPI !== 'cli') { echo "此脚本仅允许在终端运行。\n"; return 1; }
    $mysqli = null;
    try {
        echo "=== Crossref 论文写入工具 ===\n1. 重写指定 paperID（保留分类）\n2. 新增单篇论文\n3. 批量新增论文并追加共有分类\nq. 退出\n";
        do { $choice = ask('请选择：'); } while (!in_array($choice, ['1', '2', '3'], true));
        $mysqli = connectDatabase();
        $repo = new MysqlRepository($mysqli);
        $mode = (int)$choice;
        $repo->requireTransactions($mode !== 1);
        $plan = preparePlan($repo, $mode);
        echo "\n=== 待写入 papers 的完整论文数据（非 API 字段使用数据库默认值）===\n";
        $rows = array_values($plan['new']);
        if ($mode === 1) { $row = ['paperID' => $plan['id']] + $rows[0]; printJson($row); }
        elseif ($mode === 2) printJson($rows[0]);
        else printJson($rows);
        if ($mode !== 1) {
            echo "新论文的 paperID 由数据库自动分配；新论文默认分类为 ", Config::DEFAULT_CATEGORY_ID, "。\n";
            echo "附加分类：\n"; printJson($plan['categories']);
            echo "作用 DOI（批量模式包括数据库已有论文；不删除任何已有分类）：\n"; printJson($plan['dois']);
            echo "请求失败且数据库中不存在的论文不会插入，也无法添加分类。\n";
        }
        while (true) {
            $confirm = strtolower(ask('确认执行以上全部写入？(y=确认，n=取消，q=退出)：'));
            if ($confirm === 'n') { echo "已取消，未写入数据库。\n"; return 0; }
            if ($confirm === 'y') break;
        }
        $result = applyPlan($repo, $plan);
        echo "写入成功，事务已提交：\n"; printJson($result);
        echo "请到前端手动检查/调整分类、论文状态和评分，核对引用数，下载正确 PDF；重写模式还需核查 select_paper 与 gdfile 的旧关联。\n";
        return 0;
    } catch (Cancelled $e) {
        echo $e->getMessage(), "\n"; return 0;
    } catch (\Throwable $e) {
        fwrite(STDERR, '操作失败：' . $e->getMessage() . "\n未完成的数据库事务已回滚。\n");
        return 1;
    } finally {
        if ($mysqli instanceof \mysqli) $mysqli->close();
    }
}

// 可被离线测试加载；仅直接执行此文件时连接数据库。
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) exit(main());
