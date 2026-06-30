<?php
/**
 * Supabase.php — thin PHP client over the Supabase REST (PostgREST),
 * Auth (GoTrue) and Storage APIs using cURL. No external dependencies.
 *
 * The service-role key is used for server-side data access and stays
 * on the server (never exposed to the browser).
 */

declare(strict_types=1);

class Supabase
{
    private string $url;
    private string $serviceKey;
    private string $anonKey;

    public function __construct()
    {
        $this->url = SUPABASE_URL;
        $this->serviceKey = SUPABASE_SERVICE_KEY;
        $this->anonKey = SUPABASE_ANON_KEY;
    }

    public function enabled(): bool
    {
        return SUPABASE_ENABLED;
    }

    /* ----------------------- low-level request ----------------------- */
    private function request(string $method, string $path, array $opts = []): array
    {
        $headers = $opts['headers'] ?? [];
        $body = $opts['body'] ?? null;
        $auth = $opts['key'] ?? $this->serviceKey;

        $defaults = [
            "apikey: {$auth}",
            "Authorization: Bearer " . ($opts['jwt'] ?? $auth),
            "Content-Type: application/json",
        ];
        $headers = array_merge($defaults, $headers);

        $ch = curl_init($this->url . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
        }
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $decoded = $raw !== false && $raw !== '' ? json_decode($raw, true) : null;

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => $decoded,
            'raw' => $raw,
            'error' => $err,
        ];
    }

    /* ----------------------- PostgREST data ----------------------- */
    /**
     * SELECT rows. $query example:
     *  ['select' => '*', 'category' => 'eq.Sports', 'order' => 'date.desc', 'limit' => 10]
     */
    public function select(string $table, array $query = []): array
    {
        $query['select'] = $query['select'] ?? '*';
        $qs = http_build_query($query);
        $res = $this->request('GET', "/rest/v1/{$table}?{$qs}");
        return $res['ok'] && is_array($res['data']) ? $res['data'] : [];
    }

    public function insert(string $table, array $data, bool $returnRow = true): array
    {
        $headers = $returnRow ? ['Prefer: return=representation'] : ['Prefer: return=minimal'];
        $res = $this->request('POST', "/rest/v1/{$table}", [
            'body' => $data,
            'headers' => $headers,
        ]);
        return $res;
    }

    public function update(string $table, array $match, array $data): array
    {
        $qs = http_build_query($match);
        return $this->request('PATCH', "/rest/v1/{$table}?{$qs}", [
            'body' => $data,
            'headers' => ['Prefer: return=representation'],
        ]);
    }

    public function delete(string $table, array $match): array
    {
        $qs = http_build_query($match);
        return $this->request('DELETE', "/rest/v1/{$table}?{$qs}");
    }

    /* ----------------------- Auth (GoTrue) ----------------------- */
    public function signInWithPassword(string $email, string $password): array
    {
        return $this->request('POST', '/auth/v1/token?grant_type=password', [
            'key' => $this->anonKey,
            'jwt' => $this->anonKey,
            'body' => ['email' => $email, 'password' => $password],
        ]);
    }

    public function getUser(string $jwt): array
    {
        return $this->request('GET', '/auth/v1/user', [
            'key' => $this->anonKey,
            'jwt' => $jwt,
        ]);
    }

    public function refreshSession(string $refreshToken): array
    {
        return $this->request('POST', '/auth/v1/token?grant_type=refresh_token', [
            'key' => $this->anonKey,
            'jwt' => $this->anonKey,
            'body' => ['refresh_token' => $refreshToken],
        ]);
    }

    /* ----------------------- Storage ----------------------- */
    public function uploadBinary(string $bucket, string $objectPath, string $bytes, string $contentType): array
    {
        $ch = curl_init($this->url . "/storage/v1/object/{$bucket}/{$objectPath}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                "apikey: {$this->serviceKey}",
                "Authorization: Bearer {$this->serviceKey}",
                "Content-Type: {$contentType}",
                "x-upsert: true",
            ],
            CURLOPT_POSTFIELDS => $bytes,
            CURLOPT_TIMEOUT => 30,
        ]);
        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $ok = $status >= 200 && $status < 300;
        return [
            'ok' => $ok,
            'status' => $status,
            'public_url' => $ok ? $this->publicUrl($bucket, $objectPath) : null,
            'raw' => $raw,
        ];
    }

    public function publicUrl(string $bucket, string $objectPath): string
    {
        return $this->url . "/storage/v1/object/public/{$bucket}/{$objectPath}";
    }

    /** List all Storage buckets in the project. */
    public function listBuckets(): array
    {
        $res = $this->request('GET', '/storage/v1/bucket');
        return $res['ok'] && is_array($res['data']) ? $res['data'] : [];
    }

    /**
     * List objects in a bucket at the given prefix (root by default).
     * Each file object carries size/mime under its `metadata` key.
     */
    public function listObjects(string $bucket, string $prefix = '', int $limit = 1000): array
    {
        $res = $this->request('POST', "/storage/v1/object/list/{$bucket}", [
            'body' => [
                'prefix' => $prefix,
                'limit' => $limit,
                'offset' => 0,
                'sortBy' => ['column' => 'name', 'order' => 'asc'],
            ],
        ]);
        return $res['ok'] && is_array($res['data']) ? $res['data'] : [];
    }

    /**
     * Aggregate Storage usage across every bucket.
     * Sums file sizes at each bucket's root (this app uploads flat, no
     * sub-folders). Returns ['total' => bytes, 'buckets' => [...]].
     */
    public function storageUsage(): array
    {
        $buckets = $this->listBuckets();
        $perBucket = [];
        $total = 0;
        foreach ($buckets as $b) {
            $name = $b['name'] ?? $b['id'] ?? '';
            if ($name === '') {
                continue;
            }
            $size = 0;
            $count = 0;
            foreach ($this->listObjects($name) as $o) {
                $meta = $o['metadata'] ?? null;
                if (is_array($meta) && isset($meta['size'])) {
                    $size += (int) $meta['size'];
                    $count++;
                }
            }
            $perBucket[] = [
                'name' => $name,
                'size' => $size,
                'count' => $count,
                'public' => !empty($b['public']),
            ];
            $total += $size;
        }
        return ['total' => $total, 'buckets' => $perBucket];
    }
}
