<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Client tipis untuk Master Data API UKRI (https://api.ukri.web.id/api/v1).
 *
 * API ini read-only (GET saja) dan setiap endpoint daftar dipaginasi tetap
 * 20 baris per halaman. Kelas ini menyediakan:
 *  - pemanggilan satu halaman (page/get) untuk kebutuhan ad-hoc/live,
 *  - pengambilan seluruh halaman sekaligus (all) untuk disinkronkan ke tabel
 *    mirror lokal lewat App\Console\Commands\SyncUkriMasterData,
 *  - pencarian persis by NIDN/NPM (dosenByNidn/mahasiswaByNpm),
 *  - cache singkat untuk fakultas & prodi karena datanya jarang berubah.
 *
 * Method dosen()/mahasiswa() dsb mengembalikan array asosiatif mentah sesuai
 * field respons API (nama_fakultas, nama_prodi, npm, nidn, dst) - bukan model
 * Eloquent - supaya kelas ini tidak terikat ke skema tabel lokal manapun.
 */
class UkriMasterDataService
{
    protected string $baseUrl;

    protected ?string $token;

    protected int $cacheTtlMinutes;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.ukri.base_url'), '/');
        $this->token = config('services.ukri.token');
        $this->cacheTtlMinutes = (int) config('services.ukri.cache_ttl', 1440);
    }

    protected function client()
    {
        if (empty($this->token)) {
            throw new RuntimeException('UKRI_API_TOKEN belum diisi di .env - buat API key di halaman API Key terlebih dahulu.');
        }

        return Http::baseUrl($this->baseUrl)
            ->withToken($this->token)
            ->acceptJson()
            ->timeout(30)
            // Endpoint mahasiswa/dosen bisa puluhan halaman berurutan saat
            // sinkronisasi penuh - retry otomatis kalau koneksi putus/timeout
            // di tengah jalan (mis. cURL error 35 / 28), bukan error dari API-nya.
            ->retry(3, 1000);
    }

    /**
     * GET satu halaman mentah dari sebuah endpoint. $endpoint diawali '/',
     * misal '/fakultas' atau '/mahasiswa/42'.
     */
    public function get(string $endpoint, array $query = []): array
    {
        $response = $this->client()->get($endpoint, $query);

        if ($response->failed()) {
            $this->throwForResponse($endpoint, $response);
        }

        return $response->json() ?? [];
    }

    protected function throwForResponse(string $endpoint, Response $response): never
    {
        $status = $response->status();
        $message = $response->json('message') ?? 'Unknown error';

        Log::warning('UKRI Master Data API error', [
            'endpoint' => $endpoint,
            'status' => $status,
            'message' => $message,
        ]);

        $friendly = match ($status) {
            401 => "token tidak valid atau sudah kedaluwarsa ({$message})",
            403 => "API key tidak punya ability yang dibutuhkan untuk {$endpoint} ({$message})",
            404 => "data tidak ditemukan di {$endpoint} ({$message})",
            405 => "method tidak didukung untuk {$endpoint} - API ini read-only, hanya GET ({$message})",
            default => "gagal memanggil {$endpoint} (HTTP {$status}) - {$message}",
        };

        throw new RuntimeException("UKRI Master Data API: {$friendly}");
    }

    /**
     * Ambil SEMUA halaman dari sebuah endpoint daftar dan gabungkan key
     * "data"-nya. Hanya dipakai untuk sinkronisasi ke tabel mirror lokal
     * (lihat SyncUkriMasterData), bukan untuk dipanggil langsung saat
     * merender halaman, karena bisa berarti banyak request berurutan.
     */
    public function all(string $endpoint, array $query = []): array
    {
        $items = [];
        $page = 1;
        $lastPage = 1;

        do {
            $body = $this->get($endpoint, array_merge($query, ['page' => $page]));
            $items = array_merge($items, $body['data'] ?? []);
            $lastPage = (int) ($body['meta']['last_page'] ?? $page);
            $page++;
        } while ($page <= $lastPage);

        return $items;
    }

    /**
     * Ambil satu objek dari endpoint detail (mis. /mahasiswa/42).
     */
    public function detail(string $endpoint): ?array
    {
        $body = $this->get($endpoint);

        return $body['data'] ?? null;
    }

    /* ------------------------------------------------------------------ */
    /* Fakultas - ability: fakultas:read                                   */
    /* ------------------------------------------------------------------ */

    public function fakultas(array $query = []): array
    {
        return $this->all('/fakultas', $query);
    }

    public function fakultasById(int|string $id): ?array
    {
        return $this->detail("/fakultas/{$id}");
    }

    /** Fakultas jarang berubah - aman di-cache selama cache_ttl. */
    public function cachedFakultas(): array
    {
        return Cache::remember('ukri:fakultas:all', now()->addMinutes($this->cacheTtlMinutes), fn () => $this->fakultas());
    }

    /* ------------------------------------------------------------------ */
    /* Program Studi - ability: prodi:read                                 */
    /* ------------------------------------------------------------------ */

    public function prodi(array $query = []): array
    {
        return $this->all('/prodi', $query);
    }

    public function prodiById(int|string $id): ?array
    {
        return $this->detail("/prodi/{$id}");
    }

    public function cachedProdi(): array
    {
        return Cache::remember('ukri:prodi:all', now()->addMinutes($this->cacheTtlMinutes), fn () => $this->prodi());
    }

    /* ------------------------------------------------------------------ */
    /* Angkatan - ability: angkatan:read                                   */
    /* ------------------------------------------------------------------ */

    public function angkatan(array $query = []): array
    {
        return $this->all('/angkatan', $query);
    }

    public function angkatanById(int|string $id): ?array
    {
        return $this->detail("/angkatan/{$id}");
    }

    /* ------------------------------------------------------------------ */
    /* Peminatan - ability: peminatan:read                                 */
    /* ------------------------------------------------------------------ */

    public function peminatan(array $query = []): array
    {
        return $this->all('/peminatan', $query);
    }

    public function peminatanById(int|string $id): ?array
    {
        return $this->detail("/peminatan/{$id}");
    }

    /* ------------------------------------------------------------------ */
    /* Dosen - ability: dosen:read                                         */
    /* ------------------------------------------------------------------ */

    public function dosen(array $query = []): array
    {
        return $this->all('/dosen', $query);
    }

    public function dosenById(int|string $id): ?array
    {
        return $this->detail("/dosen/{$id}");
    }

    /** Pencarian langsung by NIDN persis (live call, tidak dipaginasi/cache). */
    public function dosenByNidn(string $nidn): ?array
    {
        $body = $this->get('/dosen', ['nidn' => $nidn]);

        return $body['data'][0] ?? null;
    }

    /* ------------------------------------------------------------------ */
    /* Mahasiswa - ability: mahasiswa:read                                 */
    /* ------------------------------------------------------------------ */

    public function mahasiswa(array $query = []): array
    {
        return $this->all('/mahasiswa', $query);
    }

    public function mahasiswaById(int|string $id): ?array
    {
        return $this->detail("/mahasiswa/{$id}");
    }

    /** Pencarian langsung by NPM persis (live call, tidak dipaginasi/cache). */
    public function mahasiswaByNpm(string $npm): ?array
    {
        $body = $this->get('/mahasiswa', ['npm' => $npm]);

        return $body['data'][0] ?? null;
    }
}
