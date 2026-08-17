<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MasterPerguruanTinggi;
use App\Models\MasterAktivitasLitabmas;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Prodi;

class DashboardAnalitikController extends Controller
{
    /**
     * kode_pt dummy bawaan PublikasiMasterSeeder (lihat juga
     * ReconcileAuthorsWithMasterData::DEMO_DOSEN_NIDN) - sengaja disembunyikan
     * dari dropdown filter dashboard, meskipun rownya belum dihapus dari DB.
     */
    private const DEMO_PT_KODE = [
        '071020', // Universitas PGRI Ronggolawe
        '041012', // Universitas Nurtanio Bandung
        '111005', // Universitas Muhammadiyah Banjarmasin
    ];

    /**
     * NIDN dosen dummy bawaan PublikasiMasterSeeder - harus sinkron dengan
     * ReconcileAuthorsWithMasterData::DEMO_DOSEN_NIDN. Disembunyikan dari
     * ranking dashboard meskipun rownya belum dihapus lewat
     * "php artisan ukri:reconcile-authors --delete-demo --apply".
     */
    private const DEMO_DOSEN_NIDN = [
        '0454768669130262', // SUBHANJAYA ANGGA ATMAJA
        '6955745646230092', // CANDRA AENI
        '6338746647130093', // ENCEP SOPANDI
        '8736763665300082', // DEWI MAHARANI
    ];

    /**
     * NIM mahasiswa dummy bawaan PublikasiMasterSeeder - harus sinkron dengan
     * ReconcileAuthorsWithMasterData::DEMO_MAHASISWA_NIM.
     */
    private const DEMO_MAHASISWA_NIM = [
        '20221310001', // Ahmad Rizky Pratama
        '20221310002', // Siti Nurhaliza
        '20221310003', // Budi Santoso
    ];

    /**
     * Judul 5 publikasi contoh dari PublikasiMasterSeeder (harus sinkron
     * dengan ReconcileAuthorsWithMasterData::DEMO_PUBLIKASI_TITLES). Sebelum
     * perbaikan ini, KPI cards (Total Publikasi, Dosen Aktif, Mahasiswa
     * Aktif, dst) TIDAK mengecualikan baris-baris ini - beda dengan ranking
     * di bawahnya yang sudah mengecualikan lewat DEMO_DOSEN_NIDN /
     * DEMO_MAHASISWA_NIM - sehingga angka KPI berubah-ubah tergantung apakah
     * data dummy ini sudah dihapus dari DB atau belum (lihat
     * "php artisan ukri:reconcile-authors --delete-demo --apply"). Dengan
     * dikecualikan langsung di getFilteredPublikasiQuery(), semua angka di
     * halaman ini konsisten walau baris dummy-nya belum sempat dihapus.
     */
    private const DEMO_PUBLIKASI_TITLES = [
        'Sistem Informasi Geografis untuk Pemetaan Lokasi Bencana Alam',
        'Deep Learning for Automated Diagnosis of Skin Lesions',
        'Buku Ajar: Pemrograman Web Dinamis Menggunakan Laravel 11',
        'Pemberdayaan UMKM Melalui Digital Marketing di Jawa Barat',
        'Penerjemahan Buku: Artificial Intelligence Modern Approach (4th Edition)',
    ];

    /**
     * kode_pt UKRI sendiri (lihat PublikasiMasterSeeder / ReconcileAuthorsWithMasterData).
     */
    private const UKRI_PT_KODE = '041065';

    private ?int $ukriPtIdCache = null;
    private bool $ukriPtIdResolved = false;

    /**
     * Dosen & mahasiswa hasil sinkronisasi Master Data API UKRI (kolom
     * `ukri_id` terisi - lihat App\Console\Commands\SyncUkriMasterData)
     * TIDAK PERNAH diisi `master_perguruan_tinggi_id`-nya oleh proses sync
     * itu, padahal mereka semua adalah dosen/mahasiswa UKRI sendiri (lihat
     * catatan di App\Models\Dosen@fakultas dan App\Models\Mahasiswa@prodi).
     * Akibatnya: baris publikasi_penulis_dosen/mahasiswa milik mereka selalu
     * "tidak diketahui" PT-nya kalau hanya mengandalkan
     * master_perguruan_tinggi_id (baik di override baris penulis maupun di
     * tabel dosen/mahasiswa) - inilah sebabnya jumlah publikasi UKRI
     * berkurang saat difilter per PT, dan "Kontribusi per Afiliasi" hilang/
     * kurang untuk UKRI sendiri. Method ini me-resolve id
     * master_perguruan_tinggi milik UKRI (kode_pt 041065) supaya bisa
     * dipakai sebagai fallback default untuk dosen/mahasiswa "native" UKRI
     * tersebut (ditandai dengan ukri_id IS NOT NULL).
     */
    private function ukriPtId(): ?int
    {
        if (! $this->ukriPtIdResolved) {
            $this->ukriPtIdCache = MasterPerguruanTinggi::where('kode_pt', self::UKRI_PT_KODE)->value('id');
            $this->ukriPtIdResolved = true;
        }

        return $this->ukriPtIdCache;
    }

    /**
     * ID dosen (dan mahasiswa, untuk jaga-jaga akun dengan email yang sama
     * juga tercatat sebagai mahasiswa) milik user yang sedang login, dipakai
     * untuk membatasi seluruh angka dashboard supaya dosen hanya melihat
     * datanya sendiri.
     *
     * Konsisten dengan PublikasiController::index()/checkAccess(): dosen_id
     * yang sudah ditautkan saat login (SSO/dev-login) diprioritaskan,
     * fallback ke pencarian by email HANYA kalau dosen_id kosong.
     *
     * Return null kalau user adalah admin (artinya: tidak perlu dibatasi,
     * lihat seluruh data).
     */
    private function currentAuthorIds(): ?array
    {
        $user = auth()->user();

        if (! $user || $user->hasRole('admin')) {
            return null;
        }

        $userEmail = $user->email;
        $dosenIds = filled($user->dosen_id)
            ? [$user->dosen_id]
            : Dosen::where('email', $userEmail)->pluck('id')->toArray();
        $mahasiswaIds = Mahasiswa::where('email', $userEmail)->pluck('id')->toArray();

        return ['dosen' => $dosenIds, 'mahasiswa' => $mahasiswaIds];
    }

    private function getFilteredPublikasiQuery(Request $request)
    {
        $query = DB::table('publikasi')
            ->whereNotIn('judul', self::DEMO_PUBLIKASI_TITLES);

        // PERBAIKAN: sebelumnya dosen non-admin tetap melihat dashboard
        // menyeluruh (seluruh publikasi, semua dosen) karena tidak ada
        // pembatasan sama sekali di sini - beda dengan PublikasiController
        // yang memang sudah membatasi. Sekarang publikasi yang masuk hitungan
        // dibatasi hanya yang dosen (atau mahasiswa, dengan email yang sama)
        // login ini tercantum sebagai penulisnya.
        $authorIds = $this->currentAuthorIds();
        if ($authorIds !== null) {
            $dosenIds = $authorIds['dosen'];
            $mahasiswaIds = $authorIds['mahasiswa'];

            $query->where(function ($q) use ($dosenIds, $mahasiswaIds) {
                $q->whereExists(function ($sq) use ($dosenIds) {
                    $sq->select(DB::raw(1))
                        ->from('publikasi_penulis_dosen as ppd')
                        ->whereColumn('ppd.publikasi_id', 'publikasi.id')
                        ->whereIn('ppd.dosen_id', $dosenIds);
                })->orWhereExists(function ($sq) use ($mahasiswaIds) {
                    $sq->select(DB::raw(1))
                        ->from('publikasi_penulis_mahasiswa as ppm')
                        ->whereColumn('ppm.publikasi_id', 'publikasi.id')
                        ->whereIn('ppm.mahasiswa_id', $mahasiswaIds);
                });
            });
        }

        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_terbit', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_terbit', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('kategori_kegiatan')) {
            $query->where('kategori_kegiatan', $request->kategori_kegiatan);
        }
        if ($request->filled('kategori_capaian')) {
            $query->where('kategori_capaian', $request->kategori_capaian);
        }
        if ($request->filled('dosen_id')) {
            // Filter "Nama Dosen" - hanya relevan untuk admin (dosen
            // non-admin sudah otomatis dibatasi ke datanya sendiri lewat
            // currentAuthorIds() di atas, jadi filter ini tidak perlu
            // ditampilkan untuknya, tapi tetap aman diproses di sini kalau
            // parameternya dikirim manual).
            $dosenId = $request->dosen_id;
            $query->whereExists(function ($sq) use ($dosenId) {
                $sq->select(DB::raw(1))
                    ->from('publikasi_penulis_dosen as ppd')
                    ->whereColumn('ppd.publikasi_id', 'publikasi.id')
                    ->where('ppd.dosen_id', $dosenId);
            });
        }
        // PERBAIKAN: filter ini sebelumnya bernama `perguruan_tinggi_id` dan
        // nilainya HARUS berupa id baris master_perguruan_tinggi - padahal
        // dropdown-nya berlabel "Afiliasi" dan di praktiknya banyak afiliasi
        // (terutama yang diisi manual di Penulis Lain, mis. "ULBI") sama
        // sekali TIDAK punya baris master_perguruan_tinggi. Akibatnya afiliasi
        // seperti itu mustahil dipilih dari dropdown sama sekali (tidak ada
        // id untuk dikirim), walau field teksnya sudah benar tersimpan dan
        // sudah muncul di ranking "Kontribusi per Afiliasi". Sekarang filter
        // dikirim sebagai NAMA (teks) afiliasi, dicocokkan lewat
        // getAfiliasiRows() - sumber yang sama persis dengan yang dipakai
        // untuk mengisi dropdown-nya (lihat index()) maupun ranking-nya (lihat
        // buildDashboardData()), supaya ketiganya selalu konsisten.
        if ($request->filled('afiliasi')) {
            $afiliasiNormalized = \Illuminate\Support\Str::lower(trim($request->afiliasi));

            // Kalau nama yang dipilih persis cocok salah satu PT di master
            // data, ambil juga id-nya supaya tetap bisa dicocokkan lewat
            // relasi struktural (master_perguruan_tinggi_id) - bukan cuma
            // field teks "Afiliasi" manual, yang seringkali dikosongkan
            // walau PT dosen/mahasiswanya sudah jelas lewat relasi.
            $matchedPt = MasterPerguruanTinggi::whereRaw('LOWER(TRIM(nama_pt)) = ?', [$afiliasiNormalized])->first();
            $ukriPtId = $this->ukriPtId();
            $isUkriSelected = $matchedPt !== null && $ukriPtId !== null && (int) $matchedPt->id === (int) $ukriPtId;

            $query->where(function ($outer) use ($matchedPt, $afiliasiNormalized, $isUkriSelected) {
                if ($matchedPt !== null) {
                    $ptId = $matchedPt->id;

                    // 1) Relasi struktural: PT di-override langsung pada baris
                    //    publikasi_penulis_dosen, atau fallback ke PT asal Dosen.
                    //    Ditambah kondisi khusus dosen/mahasiswa native UKRI
                    //    (ukri_id terisi tapi master_perguruan_tinggi_id-nya
                    //    NULL - lihat catatan panjang di ukriPtId()) kalau yang
                    //    dipilih adalah UKRI sendiri.
                    $outer->orWhereExists(function ($q) use ($ptId, $isUkriSelected) {
                        $q->select(DB::raw(1))
                            ->from('publikasi_penulis_dosen as ppd')
                            ->leftJoin('dosen', 'dosen.id', '=', 'ppd.dosen_id')
                            ->whereColumn('ppd.publikasi_id', 'publikasi.id')
                            ->where(function ($qq) use ($ptId, $isUkriSelected) {
                                $qq->whereRaw('COALESCE(ppd.master_perguruan_tinggi_id, dosen.master_perguruan_tinggi_id) = ?', [$ptId]);
                                if ($isUkriSelected) {
                                    $qq->orWhere(function ($native) {
                                        $native->whereNull('ppd.master_perguruan_tinggi_id')
                                            ->whereNull('dosen.master_perguruan_tinggi_id')
                                            ->whereNotNull('dosen.ukri_id');
                                    });
                                }
                            });
                    });

                    // 1b) Sama seperti (1) tapi untuk Penulis Mahasiswa.
                    $outer->orWhereExists(function ($q) use ($ptId, $isUkriSelected) {
                        $q->select(DB::raw(1))
                            ->from('publikasi_penulis_mahasiswa as ppm')
                            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'ppm.mahasiswa_id')
                            ->whereColumn('ppm.publikasi_id', 'publikasi.id')
                            ->where(function ($qq) use ($ptId, $isUkriSelected) {
                                $qq->where('mahasiswa.master_perguruan_tinggi_id', $ptId);
                                if ($isUkriSelected) {
                                    $qq->orWhere(function ($native) {
                                        $native->whereNull('mahasiswa.master_perguruan_tinggi_id')
                                            ->whereNotNull('mahasiswa.ukri_id');
                                    });
                                }
                            });
                    });
                }

                // 2) Field teks "Afiliasi" (Dosen, Mahasiswa, maupun Penulis
                //    Lain) sama persis (case-insensitive, spasi diabaikan)
                //    dengan nama afiliasi yang dipilih - inilah satu-satunya
                //    cara mencocokkan afiliasi seperti "ULBI" yang diisi
                //    manual di Penulis Lain dan tidak punya padanan di
                //    master_perguruan_tinggi sama sekali.
                foreach (['publikasi_penulis_dosen', 'publikasi_penulis_mahasiswa', 'publikasi_penulis_lain'] as $table) {
                    $outer->orWhereExists(function ($q) use ($table, $afiliasiNormalized) {
                        $q->select(DB::raw(1))
                            ->from("{$table} as p")
                            ->whereColumn('p.publikasi_id', 'publikasi.id')
                            ->whereRaw('LOWER(TRIM(p.afiliasi)) = ?', [$afiliasiNormalized]);
                    });
                }
            });
        }

        // Filter per Program Studi (khusus dosen/mahasiswa UKRI sendiri hasil
        // sinkronisasi Master Data API - lihat App\Models\Dosen::prodi() /
        // App\Models\Mahasiswa::prodi(); co-author eksternal dari PT lain
        // tidak punya prodi_id sehingga otomatis tidak ikut ke sini).
        if ($request->filled('prodi_id')) {
            $prodiId = $request->prodi_id;
            $query->where(function ($outer) use ($prodiId) {
                $outer->whereExists(function ($q) use ($prodiId) {
                    $q->select(DB::raw(1))
                        ->from('publikasi_penulis_dosen as ppd')
                        ->join('dosen', 'dosen.id', '=', 'ppd.dosen_id')
                        ->whereColumn('ppd.publikasi_id', 'publikasi.id')
                        ->where('dosen.prodi_id', $prodiId);
                })->orWhereExists(function ($q) use ($prodiId) {
                    $q->select(DB::raw(1))
                        ->from('publikasi_penulis_mahasiswa as ppm')
                        ->join('mahasiswa', 'mahasiswa.id', '=', 'ppm.mahasiswa_id')
                        ->whereColumn('ppm.publikasi_id', 'publikasi.id')
                        ->where('mahasiswa.prodi_id', $prodiId);
                });
            });
        }

        return $query;
    }

    /**
     * Ambil seluruh baris (publikasi_id, afiliasi) dari Penulis Dosen,
     * Mahasiswa, dan Lain yang publikasi_id-nya ada di $idsQuery - sudah
     * di-dedupe per pasangan (publikasi_id, afiliasi) dan sudah mengecualikan
     * PT dummy seeder & baris dosen/mahasiswa dummy. Dipakai bersama oleh
     * ranking "Kontribusi per Afiliasi" (buildDashboardData()) MAUPUN daftar
     * pilihan dropdown filter "Afiliasi" (index()), supaya keduanya selalu
     * konsisten - termasuk untuk afiliasi teks bebas seperti "ULBI" yang
     * diisi manual di Penulis Lain dan tidak punya padanan di
     * master_perguruan_tinggi.
     */
    private function getAfiliasiRows($idsQuery): \Illuminate\Support\Collection
    {
        $demoPtNamaLower = MasterPerguruanTinggi::whereIn('kode_pt', self::DEMO_PT_KODE)
            ->pluck('nama_pt')
            ->map(fn($nama) => \Illuminate\Support\Str::lower(trim($nama)))
            ->all();
        $ukriNamaPt = optional(MasterPerguruanTinggi::where('kode_pt', self::UKRI_PT_KODE)->first())->nama_pt;

        $afiliasiDosenRows = DB::table('publikasi_penulis_dosen as ppd')
            ->leftJoin('dosen', 'dosen.id', '=', 'ppd.dosen_id')
            ->leftJoin('master_perguruan_tinggi as mpt_ppd', 'mpt_ppd.id', '=', 'ppd.master_perguruan_tinggi_id')
            ->leftJoin('master_perguruan_tinggi as mpt_dosen', 'mpt_dosen.id', '=', 'dosen.master_perguruan_tinggi_id')
            ->whereIn('ppd.publikasi_id', $idsQuery)
            ->where(function ($q) {
                $q->whereNull('dosen.nidn')->orWhereNotIn('dosen.nidn', self::DEMO_DOSEN_NIDN);
            })
            ->selectRaw(
                "ppd.publikasi_id as publikasi_id, COALESCE(NULLIF(TRIM(ppd.afiliasi), ''), mpt_ppd.nama_pt, mpt_dosen.nama_pt, CASE WHEN dosen.ukri_id IS NOT NULL THEN ? END) as afiliasi",
                [$ukriNamaPt]
            )
            ->get();

        $afiliasiMahasiswaRows = DB::table('publikasi_penulis_mahasiswa as ppm')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'ppm.mahasiswa_id')
            ->leftJoin('master_perguruan_tinggi as mpt_mhs', 'mpt_mhs.id', '=', 'mahasiswa.master_perguruan_tinggi_id')
            ->whereIn('ppm.publikasi_id', $idsQuery)
            ->where(function ($q) {
                $q->whereNull('mahasiswa.nim')->orWhereNotIn('mahasiswa.nim', self::DEMO_MAHASISWA_NIM);
            })
            ->selectRaw(
                "ppm.publikasi_id as publikasi_id, COALESCE(NULLIF(TRIM(ppm.afiliasi), ''), mpt_mhs.nama_pt, CASE WHEN mahasiswa.ukri_id IS NOT NULL THEN ? END) as afiliasi",
                [$ukriNamaPt]
            )
            ->get();

        $afiliasiLainRows = DB::table('publikasi_penulis_lain')
            ->whereIn('publikasi_id', $idsQuery)
            ->whereNotNull('afiliasi')
            ->where('afiliasi', '<>', '')
            ->select('publikasi_id', 'afiliasi')
            ->get();

        return $afiliasiDosenRows
            ->concat($afiliasiMahasiswaRows)
            ->concat($afiliasiLainRows)
            ->filter(fn($row) => filled($row->afiliasi))
            ->reject(fn($row) => in_array(\Illuminate\Support\Str::lower(trim($row->afiliasi)), $demoPtNamaLower, true))
            ->unique(fn($row) => $row->publikasi_id . '|' . \Illuminate\Support\Str::lower(trim($row->afiliasi)));
    }

    public function index(Request $request)
    {
        $data = $this->buildDashboardData($request);

        // AJAX / fetch request → kembalikan JSON saja (tanpa render ulang layout)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        // Dosen non-admin hanya perlu memilih di antara afiliasi/prodi yang
        // benar-benar muncul di publikasinya sendiri (lihat currentAuthorIds() /
        // getFilteredPublikasiQuery()) - bukan milik dosen lain.
        $filteredIdsForDropdown = $this->getFilteredPublikasiQuery($request)->select('id');

        // Dropdown "Afiliasi" - dibangun dari sumber yang SAMA PERSIS dengan
        // ranking "Kontribusi per Afiliasi" (lihat getAfiliasiRows()), supaya
        // afiliasi teks bebas seperti "ULBI" yang diisi manual di Penulis
        // Lain juga ikut muncul sebagai pilihan filter - sebelumnya dropdown
        // ini hanya berisi baris master_perguruan_tinggi, jadi afiliasi tanpa
        // padanan PT sama sekali tidak bisa dipilih.
        $data['afiliasiList'] = $this->getAfiliasiRows($filteredIdsForDropdown)
            ->map(fn($row) => trim($row->afiliasi))
            ->unique(fn($nama) => \Illuminate\Support\Str::lower($nama))
            ->sort(fn($a, $b) => strcasecmp($a, $b))
            ->values();

        // Dropdown "Program Studi" - hanya prodi UKRI sendiri yang benar-benar
        // punya kontribusi publikasi (lewat dosen atau mahasiswa native UKRI).
        $usedProdiIds = DB::table('publikasi_penulis_dosen as ppd')
            ->join('dosen', 'dosen.id', '=', 'ppd.dosen_id')
            ->whereIn('ppd.publikasi_id', $filteredIdsForDropdown)
            ->whereNotNull('dosen.prodi_id')
            ->select(DB::raw('DISTINCT dosen.prodi_id as prodi_id'))
            ->union(
                DB::table('publikasi_penulis_mahasiswa as ppm')
                    ->join('mahasiswa', 'mahasiswa.id', '=', 'ppm.mahasiswa_id')
                    ->whereIn('ppm.publikasi_id', $filteredIdsForDropdown)
                    ->whereNotNull('mahasiswa.prodi_id')
                    ->select(DB::raw('DISTINCT mahasiswa.prodi_id as prodi_id'))
            );

        $data['prodiList'] = Prodi::whereIn('id', $usedProdiIds)
            ->orderBy('nama_prodi')
            ->get();

        // Dropdown filter "Nama Dosen" - dibatasi ke dosen yang benar-benar
        // punya publikasi (sesuai publikasi yang sudah lolos filter lain,
        // sama seperti pola $usedProdiIds di atas), sekaligus tetap
        // menyembunyikan dosen dummy bawaan seeder. Hanya berguna untuk
        // admin (lihat kondisi $isAdmin di view) - dosen non-admin sudah
        // otomatis melihat datanya sendiri saja lewat currentAuthorIds().
        $dosenIdsWithPublikasi = DB::table('publikasi_penulis_dosen as ppd')
            ->whereIn('ppd.publikasi_id', $filteredIdsForDropdown)
            ->select('ppd.dosen_id')
            ->distinct();

        $data['dosenList'] = Dosen::whereIn('id', $dosenIdsWithPublikasi)
            ->whereNotIn('nidn', self::DEMO_DOSEN_NIDN)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nidn']);

        return view('dashboard-analitik.index', $data);
    }

    private function buildDashboardData(Request $request): array
    {
        // Base query for subquery joins
        $filteredIdsQuery = $this->getFilteredPublikasiQuery($request)->select('id');

        // 1. KPI Cards
        $totalPublikasi = $this->getFilteredPublikasiQuery($request)->count();
        $publikasiTahunIni = $this->getFilteredPublikasiQuery($request)->whereYear('tanggal_terbit', date('Y'))->count();

        $totalDokumen = DB::table('publikasi_dokumen')
            ->whereIn('publikasi_id', $filteredIdsQuery)
            ->count();

        // Dosen/mahasiswa dummy bawaan seeder (lihat catatan DEMO_PUBLIKASI_TITLES
        // di atas) dikecualikan di sini juga, supaya konsisten dengan ranking
        // "Top Dosen"/"Top Mahasiswa" yang sudah lebih dulu mengecualikannya.
        $dosenAktif = DB::table('publikasi_penulis_dosen as ppd')
            ->leftJoin('dosen', 'dosen.id', '=', 'ppd.dosen_id')
            ->whereIn('ppd.publikasi_id', $filteredIdsQuery)
            ->whereNotNull('ppd.dosen_id')
            ->where(function ($q) {
                $q->whereNull('dosen.nidn')->orWhereNotIn('dosen.nidn', self::DEMO_DOSEN_NIDN);
            })
            ->distinct('ppd.dosen_id')
            ->count('ppd.dosen_id');

        $mahasiswaAktif = DB::table('publikasi_penulis_mahasiswa as ppm')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'ppm.mahasiswa_id')
            ->whereIn('ppm.publikasi_id', $filteredIdsQuery)
            ->whereNotNull('ppm.mahasiswa_id')
            ->where(function ($q) {
                $q->whereNull('mahasiswa.nim')->orWhereNotIn('mahasiswa.nim', self::DEMO_MAHASISWA_NIM);
            })
            ->distinct('ppm.mahasiswa_id')
            ->count('ppm.mahasiswa_id');

        $countDosen = DB::table('publikasi_penulis_dosen as ppd')
            ->leftJoin('dosen', 'dosen.id', '=', 'ppd.dosen_id')
            ->whereIn('ppd.publikasi_id', $filteredIdsQuery)
            ->where(function ($q) {
                $q->whereNull('dosen.nidn')->orWhereNotIn('dosen.nidn', self::DEMO_DOSEN_NIDN);
            })
            ->count();
        $countMhs = DB::table('publikasi_penulis_mahasiswa as ppm')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'ppm.mahasiswa_id')
            ->whereIn('ppm.publikasi_id', $filteredIdsQuery)
            ->where(function ($q) {
                $q->whereNull('mahasiswa.nim')->orWhereNotIn('mahasiswa.nim', self::DEMO_MAHASISWA_NIM);
            })
            ->count();
        $countLain = DB::table('publikasi_penulis_lain')->whereIn('publikasi_id', $filteredIdsQuery)->count();

        $rataPenulis = $totalPublikasi > 0 ? round(($countDosen + $countMhs + $countLain) / $totalPublikasi, 2) : 0;

        // 2. Line Chart: Tren Publikasi per Bulan
        $trenQuery = $this->getFilteredPublikasiQuery($request)
            ->select(DB::raw("DATE_FORMAT(tanggal_terbit, '%Y-%m') as bulan"), DB::raw("COUNT(*) as jumlah"))
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc');

        if (!$request->filled('tanggal_dari') && !$request->filled('tanggal_sampai')) {
            $trenQuery->where('tanggal_terbit', '>=', now()->subMonths(11)->startOfMonth());
        }
        $trenRaw = $trenQuery->get();

        $trenLabels = [];
        $trenData = [];

        if ($request->filled('tanggal_dari')) {
            $start = \Carbon\Carbon::parse($request->tanggal_dari)->startOfMonth();
        } else {
            $start = now()->subMonths(11)->startOfMonth();
        }

        if ($request->filled('tanggal_sampai')) {
            $end = \Carbon\Carbon::parse($request->tanggal_sampai)->endOfMonth();
        } else {
            $end = now()->endOfMonth();
        }

        $rawLookup = $trenRaw->pluck('jumlah', 'bulan')->toArray();
        $current = clone $start;
        while ($current->lte($end)) {
            $monthKey = $current->format('Y-m');
            $trenLabels[] = $current->translatedFormat('F Y');
            $trenData[] = $rawLookup[$monthKey] ?? 0;
            $current->addMonth();
        }

        // 3. Bar/Donut Chart: Kategori Kegiatan (grouped into 4 big groups)
        $kategoriRaw = $this->getFilteredPublikasiQuery($request)
            ->select('kategori_kegiatan', DB::raw('count(*) as jumlah'))
            ->groupBy('kategori_kegiatan')
            ->get();

        $grupKategori = [
            'Karya Ilmiah Sesuai Bidang' => 0,
            'Penerjemahan/Editing Buku' => 0,
            'Hasil Penelitian yang Didesiminasikan' => 0,
            'Pengabdian Masyarakat' => 0,
        ];

        foreach ($kategoriRaw as $item) {
            $kat = $item->kategori_kegiatan;
            $jumlah = $item->jumlah;

            if (str_contains($kat, 'Menghasilkan Karya Ilmiah') || str_contains($kat, 'monograf') || str_contains($kat, 'buku referensi') || str_contains($kat, 'book chapter') || str_contains($kat, 'jurnal ilmiah') || str_contains($kat, 'jurnal internasional') || str_contains($kat, 'jurnal nasional')) {
                $grupKategori['Karya Ilmiah Sesuai Bidang'] += $jumlah;
            } elseif (str_contains($kat, 'Menerjemahkan') || str_contains($kat, 'Mengedit') || str_contains($kat, 'menyunting') || str_contains($kat, 'menyadur')) {
                $grupKategori['Penerjemahan/Editing Buku'] += $jumlah;
            } elseif (str_contains($kat, 'didesiminasikan') || str_contains($kat, 'Dipresentasikan') || str_contains($kat, 'poster') || str_contains($kat, 'Disajikan') || str_contains($kat, 'koran') || str_contains($kat, 'majalah') || str_contains($kat, 'prosiding')) {
                $grupKategori['Hasil Penelitian yang Didesiminasikan'] += $jumlah;
            } elseif (str_contains($kat, 'pengabdian') || str_contains($kat, 'Pengabdian')) {
                $grupKategori['Pengabdian Masyarakat'] += $jumlah;
            } else {
                $grupKategori['Karya Ilmiah Sesuai Bidang'] += $jumlah;
            }
        }

        // 4. Donut Chart: Kategori Capaian
        $capaianRaw = $this->getFilteredPublikasiQuery($request)
            ->select('kategori_capaian', DB::raw('count(*) as jumlah'))
            ->groupBy('kategori_capaian')
            ->get();
        $capaianLabels = $capaianRaw->pluck('kategori_capaian')->map(fn($item) => $item ?: 'Lainnya')->toArray();
        $capaianData = $capaianRaw->pluck('jumlah')->toArray();

        // 5. Donut Chart: Proporsi Jenis Penulis
        $proporsiLabels = ['Dosen', 'Mahasiswa', 'Kolaborator Eksternal'];
        $proporsiData = [$countDosen, $countMhs, $countLain];

        // 6. Donut Chart: Distribusi Jenis Dokumen
        $docDistRaw = DB::table('publikasi_dokumen')
            ->whereIn('publikasi_id', $filteredIdsQuery)
            ->select('jenis_dokumen', DB::raw('count(*) as jumlah'))
            ->groupBy('jenis_dokumen')
            ->get();
        $docDistLabels = $docDistRaw->pluck('jenis_dokumen')->map(fn($item) => $item ?: 'Lainnya')->toArray();
        $docDistData = $docDistRaw->pluck('jumlah')->toArray();

        // 6b. Bar Chart: Distribusi Publikasi Berdasarkan Jenis Publikasi
        $jenisPublikasiRaw = $this->getFilteredPublikasiQuery($request)
            ->select('jenis', DB::raw('count(*) as jumlah'))
            ->whereNotNull('jenis')
            ->where('jenis', '<>', '')
            ->groupBy('jenis')
            ->orderBy('jumlah', 'desc')
            ->get();
        $jenisPublikasiLabels = $jenisPublikasiRaw->pluck('jenis')->toArray();
        $jenisPublikasiData = $jenisPublikasiRaw->pluck('jumlah')->toArray();

        // 7. Rankings (Top 10)
        // Dosen dummy bawaan seeder (ReconcileAuthorsWithMasterData::DEMO_DOSEN_NIDN)
        // juga dikecualikan di sini - kalau baris dummy-nya belum benar-benar
        // dihapus dari DB (lihat "php artisan ukri:reconcile-authors --delete-demo"),
        // publikasi contoh yang menempel padanya jangan sampai muncul di ranking.
        $topDosen = DB::table('publikasi_penulis_dosen')
            ->join('dosen', 'publikasi_penulis_dosen.dosen_id', '=', 'dosen.id')
            ->whereIn('publikasi_penulis_dosen.publikasi_id', $filteredIdsQuery)
            ->whereNotIn('dosen.nidn', self::DEMO_DOSEN_NIDN)
            ->select('dosen.nama', 'dosen.nidn', DB::raw('count(*) as jumlah'))
            ->groupBy('dosen.id', 'dosen.nama', 'dosen.nidn')
            ->orderBy('jumlah', 'desc')
            ->limit(10)
            ->get();

        $topMahasiswa = DB::table('publikasi_penulis_mahasiswa')
            ->join('mahasiswa', 'publikasi_penulis_mahasiswa.mahasiswa_id', '=', 'mahasiswa.id')
            ->whereIn('publikasi_penulis_mahasiswa.publikasi_id', $filteredIdsQuery)
            ->whereNotIn('mahasiswa.nim', self::DEMO_MAHASISWA_NIM)
            ->select('mahasiswa.nama', 'mahasiswa.nim', 'mahasiswa.prodi', DB::raw('count(*) as jumlah'))
            ->groupBy('mahasiswa.id', 'mahasiswa.nama', 'mahasiswa.nim', 'mahasiswa.prodi')
            ->orderBy('jumlah', 'desc')
            ->limit(10)
            ->get();

        // 7b. Kontribusi per Afiliasi (field teks "Afiliasi" yang diisi manual
        // di form Publikasi Karya - untuk Penulis Dosen, Penulis Mahasiswa,
        // maupun Penulis Lain - dengan fallback ke PT lewat relasi struktural
        // kalau field teksnya kosong). Baris mentahnya diambil dari
        // getAfiliasiRows() supaya konsisten dengan dropdown filter
        // "Afiliasi" di index() maupun dengan filter `afiliasi` di
        // getFilteredPublikasiQuery().
        //
        // "jumlah" di sini dihitung per JURNAL/PUBLIKASI unik, bukan per
        // baris penulis - kalau 1 publikasi punya 2 penulis dengan afiliasi
        // yang sama (mis. 2 penulis UKRI), itu tetap 1 publikasi buat
        // afiliasi UKRI. Tapi kalau 1 publikasi punya 2 afiliasi berbeda
        // (mis. ULBI & UKRI), publikasi itu tetap dihitung di masing-masing
        // afiliasi (1 untuk ULBI, 1 untuk UKRI) - makanya getAfiliasiRows()
        // sudah men-dedupe dulu per pasangan (publikasi_id, afiliasi)
        // sebelum sampai di sini.
        $topAfiliasi = $this->getAfiliasiRows($filteredIdsQuery)
            ->groupBy(fn($row) => \Illuminate\Support\Str::lower(trim($row->afiliasi)))
            ->map(function ($rows) {
                return (object) [
                    'afiliasi' => trim($rows->first()->afiliasi),
                    'jumlah' => $rows->count(),
                ];
            })
            ->sortByDesc('jumlah')
            ->take(10)
            ->values();

        $topJurnal = $this->getFilteredPublikasiQuery($request)
            ->select('nama_jurnal', DB::raw('count(*) as jumlah'))
            ->whereNotNull('nama_jurnal')
            ->where('nama_jurnal', '<>', '')
            ->groupBy('nama_jurnal')
            ->orderBy('jumlah', 'desc')
            ->limit(10)
            ->get();

        // 8. Kelengkapan Data: Publikasi Tanpa DOI
        $tanpaDoiQuery = $this->getFilteredPublikasiQuery($request)
            ->where(function ($q) {
                $q->whereNull('doi')->orWhere('doi', '');
            });
        $countTanpaDoi = $tanpaDoiQuery->count();
        $publikasiTanpaDoi = $tanpaDoiQuery->select('id', 'judul')->limit(10)->get()
            ->map(function ($item) {
                $item->edit_url = route('publikasi.edit', $item->id);
                return $item;
            });

        return compact(
            'totalPublikasi',
            'publikasiTahunIni',
            'totalDokumen',
            'dosenAktif',
            'mahasiswaAktif',
            'rataPenulis',
            'trenLabels',
            'trenData',
            'grupKategori',
            'capaianLabels',
            'capaianData',
            'proporsiLabels',
            'proporsiData',
            'docDistLabels',
            'docDistData',
            'jenisPublikasiLabels',
            'jenisPublikasiData',
            'topDosen',
            'topMahasiswa',
            'topAfiliasi',
            'topJurnal',
            'countTanpaDoi',
            'publikasiTanpaDoi'
        );
    }
}
