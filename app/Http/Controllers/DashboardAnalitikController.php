<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MasterPerguruanTinggi;
use App\Models\MasterAktivitasLitabmas;

class DashboardAnalitikController extends Controller
{
    private function getFilteredPublikasiQuery(Request $request)
    {
        $query = DB::table('publikasi');

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
            $query->whereExists(function ($q) use ($ptId) {
                $q->select(DB::raw(1))
                    ->from('publikasi_penulis_dosen')
                    ->whereColumn('publikasi_penulis_dosen.publikasi_id', 'publikasi.id')
                    ->where('publikasi_penulis_dosen.master_perguruan_tinggi_id', $ptId);
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        // Base query for subquery joins
        $filteredIdsQuery = $this->getFilteredPublikasiQuery($request)->select('id');

        // 1. KPI Cards
        $totalPublikasi = $this->getFilteredPublikasiQuery($request)->count();
        $publikasiTahunIni = $this->getFilteredPublikasiQuery($request)->whereYear('tanggal_terbit', date('Y'))->count();

        $totalDokumen = DB::table('publikasi_dokumen')
            ->whereIn('publikasi_id', $filteredIdsQuery)
            ->count();

        $dosenAktif = DB::table('publikasi_penulis_dosen')
            ->whereIn('publikasi_id', $filteredIdsQuery)
            ->whereNotNull('dosen_id')
            ->distinct('dosen_id')
            ->count('dosen_id');

        $mahasiswaAktif = DB::table('publikasi_penulis_mahasiswa')
            ->whereIn('publikasi_id', $filteredIdsQuery)
            ->whereNotNull('mahasiswa_id')
            ->distinct('mahasiswa_id')
            ->count('mahasiswa_id');

        $countDosen = DB::table('publikasi_penulis_dosen')->whereIn('publikasi_id', $filteredIdsQuery)->count();
        $countMhs = DB::table('publikasi_penulis_mahasiswa')->whereIn('publikasi_id', $filteredIdsQuery)->count();
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
        $topDosen = DB::table('publikasi_penulis_dosen')
            ->join('dosen', 'publikasi_penulis_dosen.dosen_id', '=', 'dosen.id')
            ->whereIn('publikasi_penulis_dosen.publikasi_id', $filteredIdsQuery)
            ->select('dosen.nama', 'dosen.nidn', DB::raw('count(*) as jumlah'))
            ->groupBy('dosen.id', 'dosen.nama', 'dosen.nidn')
            ->orderBy('jumlah', 'desc')
            ->limit(10)
            ->get();

        $topMahasiswa = DB::table('publikasi_penulis_mahasiswa')
            ->join('mahasiswa', 'publikasi_penulis_mahasiswa.mahasiswa_id', '=', 'mahasiswa.id')
            ->whereIn('publikasi_penulis_mahasiswa.publikasi_id', $filteredIdsQuery)
            ->select('mahasiswa.nama', 'mahasiswa.nim', 'mahasiswa.prodi', DB::raw('count(*) as jumlah'))
            ->groupBy('mahasiswa.id', 'mahasiswa.nama', 'mahasiswa.nim', 'mahasiswa.prodi')
            ->orderBy('jumlah', 'desc')
            ->limit(10)
            ->get();

        $topPT = DB::table('publikasi_penulis_dosen')
            ->join('master_perguruan_tinggi', 'publikasi_penulis_dosen.master_perguruan_tinggi_id', '=', 'master_perguruan_tinggi.id')
            ->whereIn('publikasi_penulis_dosen.publikasi_id', $filteredIdsQuery)
            ->select('master_perguruan_tinggi.nama_pt', DB::raw('count(*) as jumlah'))
            ->groupBy('master_perguruan_tinggi.id', 'master_perguruan_tinggi.nama_pt')
            ->orderBy('jumlah', 'desc')
            ->limit(10)
            ->get();

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
        $publikasiTanpaDoi = $tanpaDoiQuery->select('id', 'judul')->limit(10)->get();

        // Filter lists
        $perguruanTinggiList = MasterPerguruanTinggi::all();

        return view('dashboard-analitik.index', compact(
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
            'topPT',
            'topJurnal',
            'countTanpaDoi',
            'publikasiTanpaDoi',
            'perguruanTinggiList'
        ));
    }
}
