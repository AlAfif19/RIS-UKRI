<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MasterPerguruanTinggi;
use App\Models\MasterAktivitasLitabmas;
use App\Models\Dosen;
use App\Models\Mahasiswa;

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
        if ($request->filled('perguruan_tinggi_id')) {
            $ptId = $request->perguruan_tinggi_id;
            $ptNamaNormalized = optional(
                MasterPerguruanTinggi::find($ptId)
            )->nama_pt;
            $ptNamaNormalized = $ptNamaNormalized !== null
                ? \Illuminate\Support\Str::lower(trim($ptNamaNormalized))
                : null;
            $ukriPtId = $this->ukriPtId();
            $isUkriSelected = $ukriPtId !== null && (int) $ptId === (int) $ukriPtId;

            // Sebelumnya filter ini HANYA mencocokkan lewat relasi struktural
            // (master_perguruan_tinggi_id Dosen), sementara ranking "Kontribusi
            // per Afiliasi" di bawah sudah berbasis field teks "Afiliasi" yang
            // diisi manual per Penulis (Dosen/Mahasiswa/Lain). Akibatnya kalau
            // filter ini dipakai, publikasi yang afiliasi teksnya cocok tapi
            // PT dosennya beda (atau afiliasi ditulis oleh Mahasiswa/Penulis
            // Lain yang sama sekali tidak terhubung ke Dosen manapun) jadi ikut
            // tersaring keluar - makanya jumlah yang muncul lebih kecil dari
            // jumlah afiliasi teks yang sebenarnya ada. Di bawah ini digabung:
            // publikasi ikut lolos filter kalau salah satu dari dua kondisi
            // terpenuhi.
            //
            // PERBAIKAN: ditambah kondisi ke-3 khusus saat yang dipilih adalah
            // UKRI sendiri. Dosen/mahasiswa hasil sinkronisasi Master Data API
            // UKRI (ukri_id terisi) TIDAK PERNAH punya master_perguruan_tinggi_id
            // (lihat catatan di ukriPtId()) dan juga jarang diisi field teks
            // "Afiliasi"-nya, jadi kondisi 1 & 2 di atas sama sekali tidak
            // menjaring mereka - inilah yang bikin jumlah publikasi UKRI
            // berkurang saat filter "Universitas Kebangsaan Republik Indonesia"
            // dipakai, padahal publikasinya jelas milik UKRI.
            $query->where(function ($outer) use ($ptId, $ptNamaNormalized, $isUkriSelected) {
                // 1) Relasi struktural: PT di-override langsung pada baris
                //    publikasi_penulis_dosen, atau fallback ke PT asal Dosen.
                $outer->whereExists(function ($q) use ($ptId, $isUkriSelected) {
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

                // 1b) Sama seperti (1) tapi untuk Penulis Mahasiswa - sebelumnya
                //     relasi struktural mahasiswa (master_perguruan_tinggi_id)
                //     sama sekali tidak dicek di sini (hanya field teksnya lewat
                //     kondisi 2), jadi ditambahkan juga di sini.
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

                // 2) Field teks "Afiliasi" (Dosen, Mahasiswa, maupun Penulis
                //    Lain) sama persis (case-insensitive, spasi diabaikan)
                //    dengan nama PT yang dipilih.
                if ($ptNamaNormalized !== null) {
                    foreach (['publikasi_penulis_dosen', 'publikasi_penulis_mahasiswa', 'publikasi_penulis_lain'] as $table) {
                        $outer->orWhereExists(function ($q) use ($table, $ptNamaNormalized) {
                            $q->select(DB::raw(1))
                                ->from("{$table} as p")
                                ->whereColumn('p.publikasi_id', 'publikasi.id')
                                ->whereRaw('LOWER(TRIM(p.afiliasi)) = ?', [$ptNamaNormalized]);
                        });
                    }
                }
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $data = $this->buildDashboardData($request);

        // AJAX / fetch request → kembalikan JSON saja (tanpa render ulang layout)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        // Dosen non-admin hanya perlu memilih di antara PT yang benar-benar
        // muncul di publikasinya sendiri (lihat currentAuthorIds() /
        // getFilteredPublikasiQuery()) - bukan PT dari publikasi dosen lain.
        $filteredIdsForDropdown = $this->getFilteredPublikasiQuery($request)->select('id');

        $usedPtIds = DB::table('publikasi_penulis_dosen as ppd')
            ->leftJoin('dosen', 'dosen.id', '=', 'ppd.dosen_id')
            ->whereIn('ppd.publikasi_id', $filteredIdsForDropdown)
            ->whereRaw('COALESCE(ppd.master_perguruan_tinggi_id, dosen.master_perguruan_tinggi_id) IS NOT NULL')
            ->select(DB::raw('DISTINCT COALESCE(ppd.master_perguruan_tinggi_id, dosen.master_perguruan_tinggi_id) as pt_id'))
            ->union(
                DB::table('publikasi_penulis_mahasiswa as ppm')
                    ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'ppm.mahasiswa_id')
                    ->whereIn('ppm.publikasi_id', $filteredIdsForDropdown)
                    ->whereNotNull('mahasiswa.master_perguruan_tinggi_id')
                    ->select(DB::raw('DISTINCT mahasiswa.master_perguruan_tinggi_id as pt_id'))
            );

        // PERBAIKAN: UKRI sendiri harus selalu muncul di dropdown kalau
        // memang punya publikasi (lihat catatan panjang di ukriPtId()) -
        // dosen/mahasiswa native UKRI (ukri_id terisi) sering tidak punya
        // master_perguruan_tinggi_id sama sekali, jadi tidak selalu ikut
        // ter-detect lewat $usedPtIds di atas. Untuk dosen non-admin, tetap
        // dibatasi ke publikasi miliknya sendiri (whereIn publikasi_id).
        $ukriPtId = $this->ukriPtId();
        $ukriPunyaKontribusi = $ukriPtId !== null && (
            DB::table('publikasi_penulis_dosen as ppd')
                ->leftJoin('dosen', 'dosen.id', '=', 'ppd.dosen_id')
                ->whereIn('ppd.publikasi_id', $filteredIdsForDropdown)
                ->whereNotNull('dosen.ukri_id')
                ->exists()
            || DB::table('publikasi_penulis_mahasiswa as ppm')
                ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'ppm.mahasiswa_id')
                ->whereIn('ppm.publikasi_id', $filteredIdsForDropdown)
                ->whereNotNull('mahasiswa.ukri_id')
                ->exists()
        );

        // Dropdown filter hanya menampilkan PT yang benar-benar punya kontribusi
        // publikasi (bukan sekadar ada di master data), sekaligus tetap
        // menyembunyikan PT dummy bawaan seeder kalau baris-nya belum dihapus.
        $data['perguruanTinggiList'] = MasterPerguruanTinggi::whereNotIn('kode_pt', self::DEMO_PT_KODE)
            ->where(function ($q) use ($usedPtIds, $ukriPtId, $ukriPunyaKontribusi) {
                $q->whereIn('id', $usedPtIds);
                if ($ukriPunyaKontribusi) {
                    $q->orWhere('id', $ukriPtId);
                }
            })
            ->orderBy('nama_pt')
            ->get();

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
        // maupun Penulis Lain).
        //
        // PERBAIKAN: sebelumnya ranking ini HANYA membaca field teks
        // "afiliasi" itu, padahal di praktiknya field itu nyaris selalu
        // dikosongkan saat input (termasuk untuk dosen/mahasiswa Universitas
        // Kebangsaan Republik Indonesia sendiri) - PT-nya sebenarnya sudah
        // diketahui lewat relasi terstruktur master_perguruan_tinggi_id
        // (override langsung di baris publikasi_penulis_dosen/mahasiswa, atau
        // fallback ke PT asal Dosen/Mahasiswa). Karena hanya field teks yang
        // dibaca, hampir semua baris "tidak terbaca" dan Kontribusi per
        // Afiliasi selalu tampil kosong meski publikasinya banyak. Sekarang
        // tiap baris pakai field teks itu KALAU DIISI, baru fallback ke nama
        // PT lewat relasi terstruktur kalau kosong. Penulis Lain tidak punya
        // relasi PT sama sekali sehingga tetap murni dari field teks.
        $demoPtNamaLower = MasterPerguruanTinggi::whereIn('kode_pt', self::DEMO_PT_KODE)
            ->pluck('nama_pt')
            ->map(fn($nama) => \Illuminate\Support\Str::lower(trim($nama)))
            ->all();

        // PERBAIKAN LANJUTAN: fallback di atas ternyata masih belum cukup
        // untuk dosen/mahasiswa hasil sinkronisasi Master Data API UKRI
        // (kolom `ukri_id` terisi - lihat App\Console\Commands\SyncUkriMasterData
        // & catatan panjang di ukriPtId()). Proses sync itu TIDAK PERNAH
        // mengisi master_perguruan_tinggi_id mereka, jadi mpt_ppd/mpt_dosen/
        // mpt_mhs di bawah tetap NULL untuk mereka dan baris itu masih
        // ter-COALESCE jadi NULL - lalu dibuang oleh filter(filled(...)) di
        // bawah, padahal mereka semua sebenarnya dosen/mahasiswa UKRI
        // sendiri. Ditambah satu fallback lagi ke nama UKRI kalau ukri_id
        // terisi, supaya publikasi yang penulisnya "native" UKRI tetap ikut
        // terhitung sebagai kontribusi UKRI.
        $ukriNamaPt = optional(MasterPerguruanTinggi::where('kode_pt', self::UKRI_PT_KODE)->first())->nama_pt;

        $afiliasiDosenRows = DB::table('publikasi_penulis_dosen as ppd')
            ->leftJoin('dosen', 'dosen.id', '=', 'ppd.dosen_id')
            ->leftJoin('master_perguruan_tinggi as mpt_ppd', 'mpt_ppd.id', '=', 'ppd.master_perguruan_tinggi_id')
            ->leftJoin('master_perguruan_tinggi as mpt_dosen', 'mpt_dosen.id', '=', 'dosen.master_perguruan_tinggi_id')
            ->whereIn('ppd.publikasi_id', $filteredIdsQuery)
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
            ->whereIn('ppm.publikasi_id', $filteredIdsQuery)
            ->where(function ($q) {
                $q->whereNull('mahasiswa.nim')->orWhereNotIn('mahasiswa.nim', self::DEMO_MAHASISWA_NIM);
            })
            ->selectRaw(
                "ppm.publikasi_id as publikasi_id, COALESCE(NULLIF(TRIM(ppm.afiliasi), ''), mpt_mhs.nama_pt, CASE WHEN mahasiswa.ukri_id IS NOT NULL THEN ? END) as afiliasi",
                [$ukriNamaPt]
            )
            ->get();

        $afiliasiLainRows = DB::table('publikasi_penulis_lain')
            ->whereIn('publikasi_id', $filteredIdsQuery)
            ->whereNotNull('afiliasi')
            ->where('afiliasi', '<>', '')
            ->select('publikasi_id', 'afiliasi')
            ->get();

        // Digabung & dikelompokkan di level PHP (bukan groupBy SQL) supaya
        // variasi spasi/kapitalisasi ("Universitas Kristen" vs "universitas
        // kristen ") tetap dianggap satu afiliasi yang sama. PT dummy bawaan
        // seeder ikut dibuang di sini juga (jaga-jaga kalau rownya belum
        // dihapus dari DB).
        //
        // PERBAIKAN: "jumlah" di sini harus dihitung per JURNAL/PUBLIKASI
        // unik, bukan per baris penulis. Kalau 1 publikasi punya 2 penulis
        // dengan afiliasi yang sama (mis. 2 penulis UKRI), itu tetap 1
        // publikasi buat afiliasi UKRI. Tapi kalau 1 publikasi punya 2
        // afiliasi berbeda (mis. ULBI & UKRI), publikasi itu tetap dihitung
        // di masing-masing afiliasi (1 untuk ULBI, 1 untuk UKRI). Makanya
        // di-dedupe dulu per pasangan (publikasi_id, afiliasi) sebelum
        // dihitung jumlahnya.
        $topAfiliasi = $afiliasiDosenRows
            ->concat($afiliasiMahasiswaRows)
            ->concat($afiliasiLainRows)
            ->filter(fn($row) => filled($row->afiliasi))
            ->reject(fn($row) => in_array(\Illuminate\Support\Str::lower(trim($row->afiliasi)), $demoPtNamaLower, true))
            ->unique(fn($row) => $row->publikasi_id . '|' . \Illuminate\Support\Str::lower(trim($row->afiliasi)))
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
