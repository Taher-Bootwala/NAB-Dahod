<?php
/**
 * Repo.php — repository layer. Reads/writes through Supabase when
 * configured; otherwise falls back to bundled seed data (reads) and a
 * local JSON store (writes) so the entire site works offline for demos.
 */

declare(strict_types=1);

class Repo
{
    private Supabase $sb;
    private array $seed;
    private string $storeDir;

    public function __construct(Supabase $sb)
    {
        $this->sb = $sb;
        $this->seed = seed_data();
        $this->storeDir = APP_ROOT . '/storage';
        if (!is_dir($this->storeDir)) {
            @mkdir($this->storeDir, 0775, true);
        }
    }

    public function live(): bool
    {
        return $this->sb->enabled();
    }

    /* ----------------- local JSON store (offline writes) ----------------- */
    private function storeFile(string $name): string
    {
        return $this->storeDir . '/' . $name . '.json';
    }

    private function storeRead(string $name): array
    {
        $f = $this->storeFile($name);
        if (!is_file($f)) {
            return [];
        }
        return json_decode((string) file_get_contents($f), true) ?: [];
    }

    private function storeAppend(string $name, array $row): array
    {
        $rows = $this->storeRead($name);
        $row['id'] = $row['id'] ?? gen_id(strtoupper(substr($name, 0, 3)));
        $row['created_at'] = $row['created_at'] ?? gmdate('c');
        array_unshift($rows, $row);
        @file_put_contents($this->storeFile($name), json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $row;
    }

    /* ----------------------- Settings / CMS ----------------------- */
    public function settings(): array
    {
        if ($this->live()) {
            $rows = $this->sb->select('site_settings', ['select' => 'key,value']);
            $out = [];
            foreach ($rows as $r) {
                $out[$r['key']] = $r['value'];
            }
            // merge defaults so missing keys still render
            return array_merge($this->seed['site_settings'], $out);
        }
        $overrides = $this->storeRead('site_settings');
        return array_merge($this->seed['site_settings'], $overrides);
    }

    public function setting(string $key, $default = '')
    {
        return $this->settings()[$key] ?? $default;
    }

    public function saveSetting(string $key, string $value): void
    {
        if ($this->live()) {
            $existing = $this->sb->select('site_settings', ['key' => 'eq.' . $key, 'select' => 'key']);
            if ($existing) {
                $this->sb->update('site_settings', ['key' => 'eq.' . $key], ['value' => $value]);
            } else {
                $this->sb->insert('site_settings', ['key' => $key, 'value' => $value]);
            }
            return;
        }
        $overrides = $this->storeRead('site_settings');
        $overrides[$key] = $value;
        @file_put_contents($this->storeFile('site_settings'), json_encode($overrides, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /* ----------------------- Activities ----------------------- */
    public function activities(array $opts = []): array
    {
        if ($this->live()) {
            $rows = $this->sb->select('activities', ['order' => 'date.desc']);
        } else {
            // Offline: merge storage writes with seed data
            $stored = $this->storeRead('activities');
            $rows = array_merge($stored, $this->seed['activities']);
        }

        if (!empty($opts['category']) && $opts['category'] !== 'All') {
            $rows = array_values(array_filter($rows, fn($r) => ($r['category'] ?? '') === $opts['category']));
        }
        if (!empty($opts['search'])) {
            $q = mb_strtolower($opts['search']);
            $rows = array_values(array_filter($rows, function ($r) use ($q) {
                return str_contains(mb_strtolower($r['title'] ?? ''), $q)
                    || str_contains(mb_strtolower($r['description'] ?? ''), $q);
            }));
        }
        if (!empty($opts['limit'])) {
            $rows = array_slice($rows, 0, (int) $opts['limit']);
        }
        return $rows;
    }

    public function activity(string $slug): ?array
    {
        foreach ($this->activities() as $a) {
            if (($a['slug'] ?? '') === $slug || ($a['id'] ?? '') === $slug) {
                return $a;
            }
        }
        return null;
    }

    public function activityCategories(): array
    {
        $cats = array_unique(array_map(fn($a) => $a['category'] ?? 'Other', $this->activities()));
        sort($cats);
        return $cats;
    }

    /* ----------------------- Gallery ----------------------- */
    public function gallery(?string $category = null): array
    {
        if ($this->live()) {
            $rows = $this->sb->select('gallery', ['order' => 'date.desc']);
        } else {
            // Offline: merge storage writes with seed data
            $stored = $this->storeRead('gallery');
            $rows = array_merge($stored, $this->seed['gallery']);
        }
        if ($category && $category !== 'All') {
            $rows = array_values(array_filter($rows, fn($r) => ($r['category'] ?? '') === $category));
        }
        return $rows;
    }

    public function galleryCategories(): array
    {
        $cats = array_unique(array_map(fn($g) => $g['category'] ?? 'Other', $this->gallery()));
        sort($cats);
        return $cats;
    }

    /* ----------------------- Home page display images ----------------------- */
    public function homeImages(): array
    {
        return $this->live()
            ? $this->sb->select('home_images', ['order' => 'created_at.desc'])
            : $this->storeRead('home_images');
    }

    /* ----------------------- Progress reports (PDFs) ----------------------- */
    public function progressReports(): array
    {
        if ($this->live()) {
            return $this->sb->select('progress_reports', ['order' => 'year.desc,created_at.desc']);
        }
        $rows = $this->storeRead('progress_reports');
        // Newest year first for the offline store too.
        usort($rows, fn($a, $b) => ((int) ($b['year'] ?? 0)) <=> ((int) ($a['year'] ?? 0)));
        return $rows;
    }

    /* ----------------------- Trustees ----------------------- */
    public function trustees(): array
    {
        if ($this->live()) {
            return $this->sb->select('trustees', ['order' => 'sort_order.asc']);
        } else {
            // Offline: merge storage writes with seed data
            $stored = $this->storeRead('trustees');
            return array_merge($stored, $this->seed['trustees']);
        }
    }

    /* ----------------------- Testimonials / timeline ----------------------- */
    public function testimonials(): array
    {
        return $this->seed['testimonials'];
    }

    public function timeline(): array
    {
        return $this->seed['timeline'];
    }

    /* ----------------------- Donations ----------------------- */
    public function recentDonations(int $limit = 6): array
    {
        if ($this->live()) {
            return $this->sb->select('donations', [
                'select' => 'donor_name,amount,created_at',
                'payment_status' => 'eq.success',
                'order' => 'created_at.desc',
                'limit' => $limit,
            ]);
        }
        $local = $this->storeRead('donations');
        $rows = array_merge($local, $this->seed['donations_recent']);
        return array_slice($rows, 0, $limit);
    }

    public function donationStats(): array
    {
        $goal = DONATION_GOAL;
        if ($this->live()) {
            $rows = $this->sb->select('donations', ['select' => 'amount', 'payment_status' => 'eq.success']);
            $total = array_sum(array_map(fn($r) => (float) ($r['amount'] ?? 0), $rows));
            $count = count($rows);
        } else {
            $local = $this->storeRead('donations');
            $all = array_merge($local, $this->seed['donations_recent']);
            // baseline historical total from settings for a realistic bar
            $baseline = (float) ($this->setting('stat_donations', 0));
            $total = $baseline + array_sum(array_map(fn($r) => (float) ($r['amount'] ?? 0), $local));
            $count = count($all) + 480; // realistic donor count baseline
        }
        return [
            'total' => $total,
            'count' => $count,
            'goal' => $goal,
            'percent' => $goal > 0 ? min(100, round($total / $goal * 100, 1)) : 0,
        ];
    }

    public function recordDonation(array $data): array
    {
        $row = [
            'donor_name' => $data['donor_name'] ?: 'Anonymous',
            'email' => $data['email'] ?? '',
            'amount' => (float) $data['amount'],
            'payment_status' => $data['payment_status'] ?? 'pending',
            'transaction_id' => $data['transaction_id'] ?? gen_id('TXN'),
            'receipt_no' => gen_id('RCPT'),
            'message' => $data['message'] ?? '',
        ];
        if ($this->live()) {
            $res = $this->sb->insert('donations', $row);
            return $res['ok'] && !empty($res['data'][0]) ? $res['data'][0] : $row;
        }
        return $this->storeAppend('donations', $row);
    }

    /* ----------------------- Contact messages ----------------------- */
    public function saveContactMessage(array $data): array
    {
        $row = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'message' => $data['message'],
            'status' => 'new',
        ];
        if ($this->live()) {
            $res = $this->sb->insert('contact_messages', $row);
            return $res['ok'] && !empty($res['data'][0]) ? $res['data'][0] : $row;
        }
        return $this->storeAppend('contact_messages', $row);
    }

    public function contactMessages(array $opts = []): array
    {
        if ($this->live()) {
            return $this->sb->select('contact_messages', ['order' => 'created_at.desc', 'limit' => $opts['limit'] ?? 100]);
        }
        $rows = $this->storeRead('contact_messages');
        return $opts['limit'] ? array_slice($rows, 0, $opts['limit']) : $rows;
    }

    /* ----------------------- Audit log ----------------------- */
    public function audit(string $action, string $detail = ''): void
    {
        $row = [
            'action' => $action,
            'detail' => $detail,
            'actor' => $_SESSION['admin']['email'] ?? 'system',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ];
        if ($this->live()) {
            $this->sb->insert('audit_logs', $row, false);
            return;
        }
        $this->storeAppend('audit_logs', $row);
    }

    public function auditLogs(int $limit = 50): array
    {
        if ($this->live()) {
            return $this->sb->select('audit_logs', ['order' => 'created_at.desc', 'limit' => $limit]);
        }
        return array_slice($this->storeRead('audit_logs'), 0, $limit);
    }

    /* ----------------------- Generic admin CRUD ----------------------- */
    public function create(string $table, array $data): array
    {
        if ($this->live()) {
            $res = $this->sb->insert($table, $data);
            return $res['ok'] && !empty($res['data'][0]) ? $res['data'][0] : [];
        }
        return $this->storeAppend($table, $data);
    }

    public function remove(string $table, string $id): bool
    {
        if ($this->live()) {
            $res = $this->sb->delete($table, ['id' => 'eq.' . $id]);
            return $res['ok'];
        }
        $rows = $this->storeRead($table);
        $rows = array_values(array_filter($rows, fn($r) => ($r['id'] ?? '') !== $id));
        @file_put_contents($this->storeFile($table), json_encode($rows, JSON_PRETTY_PRINT), LOCK_EX);
        return true;
    }

    public function update(string $table, string $id, array $data): bool
    {
        if ($this->live()) {
            $res = $this->sb->update($table, ['id' => 'eq.' . $id], $data);
            return $res['ok'];
        }
        $rows = $this->storeRead($table);
        foreach ($rows as $i => $r) {
            if (($r['id'] ?? '') === $id) {
                $rows[$i] = array_merge($r, $data);
                @file_put_contents($this->storeFile($table), json_encode($rows, JSON_PRETTY_PRINT), LOCK_EX);
                return true;
            }
        }
        return false;
    }

    public function donations(array $opts = []): array
    {
        if ($this->live()) {
            return $this->sb->select('donations', ['order' => 'created_at.desc', 'limit' => $opts['limit'] ?? 100]);
        }
        $rows = $this->storeRead('donations');
        return $opts['limit'] ? array_slice($rows, 0, $opts['limit']) : $rows;
    }
}
