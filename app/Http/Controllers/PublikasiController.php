<?php

namespace App\Http\Controllers;

use App\Models\Publikasi;
use App\Models\PublikasiDokumen;
use App\Models\PublikasiPenulisDosen;
use App\Models\PublikasiPenulisMahasiswa;
use App\Models\PublikasiPenulisLain;
use App\Models\MasterAktivitasLitabmas;
use App\Models\MasterPerguruanTinggi;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PublikasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Publikasi::with(['aktivitasLitabmas', 'penulisDosen.dosen', 'penulisMahasiswa.mahasiswa', 'penulisLain', 'dokumen']);

        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            $userEmail = $user->email;
            $dosenIds = \App\Models\Dosen::where('email', $userEmail)->pluck('id')->toArray();
            $mahasiswaIds = \App\Models\Mahasiswa::where('email', $userEmail)->pluck('id')->toArray();

            $query->where(function($q) use ($dosenIds, $mahasiswaIds) {
                $q->whereHas('penulisDosen', function($sq) use ($dosenIds) {
                    $sq->whereIn('dosen_id', $dosenIds);
                })->orWhereHas('penulisMahasiswa', function($sq) use ($mahasiswaIds) {
                    $sq->whereIn('mahasiswa_id', $mahasiswaIds);
                });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('nama_jurnal', 'like', "%{$search}%")
                  ->orWhere('kategori_kegiatan', 'like', "%{$search}%");
            });
        }

        $publikasiList = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return view('publikasi.partials.table', compact('publikasiList'));
        }

        return view('publikasi.index', compact('publikasiList'));
    }

    public function create()
    {
        $aktivitasLitabmas = MasterAktivitasLitabmas::all();

        return view('publikasi.create', compact('aktivitasLitabmas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_kegiatan' => 'required|string',
            'judul' => 'required|string|max:255',
            'tanggal_terbit' => 'required|date',
            'tautan_jurnal' => 'nullable|url',
            'tautan_eksternal' => 'nullable|url',
            'volume' => 'nullable|integer|min:1',
            'nomor' => 'nullable|integer|min:1',
            'penulis_dosen.*.urutan' => 'nullable|integer|min:1',
            'penulis_mahasiswa.*.urutan' => 'nullable|integer|min:1',
            'penulis_lain.*.urutan' => 'nullable|integer|min:1',
        ]);

        // Validation Rules:
        // 1. Minimum 1 document required
        $hasDoc = false;
        if ($request->has('dokumen') && is_array($request->dokumen)) {
            foreach ($request->dokumen as $index => $doc) {
                if (!empty($doc['nama_dokumen']) || $request->hasFile("dokumen.{$index}.file") || !empty($doc['tautan_dokumen'])) {
                    $hasDoc = true;
                    break;
                }
            }
        }
        if (!$hasDoc) {
            return back()->withInput()->withErrors(['dokumen' => 'Minimal 1 dokumen wajib diupload atau ditautkan sebelum form bisa disimpan.']);
        }

        // 2. Minimum 1 author from Penulis Dosen OR Penulis Mahasiswa
        $dosenAuthors = array_filter($request->input('penulis_dosen', []), fn($item) => !empty($item['nama_dosen']));
        $mahasiswaAuthors = array_filter($request->input('penulis_mahasiswa', []), fn($item) => !empty($item['mahasiswa_id']));

        if (count($dosenAuthors) === 0 && count($mahasiswaAuthors) === 0) {
            return back()->withInput()->withErrors(['penulis' => 'Publikasi harus memiliki minimal 1 penulis dari Penulis Dosen atau Penulis Mahasiswa.']);
        }

        // 3. Corresponding Author check
        $correspondingCount = 0;
        $corrKey = $request->input('corresponding_author'); // e.g. "dosen_0", "mahasiswa_1", "lain_0"
        if (empty($corrKey)) {
            return back()->withInput()->withErrors(['corresponding' => 'Wajib memilih 1 Corresponding Author pada salah satu baris penulis.']);
        }

        DB::beginTransaction();
        try {
            $publikasi = Publikasi::create([
                'kategori_kegiatan' => $request->kategori_kegiatan,
                'jenis' => $request->jenis,
                'kategori_capaian' => $request->kategori_capaian,
                'aktivitas_litabmas_id' => $request->aktivitas_litabmas_id,
                'judul' => $request->judul,
                'nama_jurnal' => $request->nama_jurnal,
                'tautan_jurnal' => $request->tautan_jurnal,
                'tanggal_terbit' => $request->tanggal_terbit,
                'volume' => $request->volume,
                'nomor' => $request->nomor,
                'halaman' => $request->halaman,
                'penerbit' => $request->penerbit,
                'doi' => $request->doi,
                'issn' => $request->issn,
                'tautan_eksternal' => $request->tautan_eksternal,
                'keterangan' => $request->keterangan,
            ]);

            // Save Dokumen
            if ($request->has('dokumen') && is_array($request->dokumen)) {
                foreach ($request->dokumen as $index => $doc) {
                    if (empty($doc['nama_dokumen']) && !$request->hasFile("dokumen.{$index}.file") && empty($doc['tautan_dokumen'])) {
                        continue;
                    }

                    $fileName = null;
                    $filePath = null;
                    $jenisFile = null;

                    if ($request->hasFile("dokumen.{$index}.file")) {
                        $file = $request->file("dokumen.{$index}.file");
                        $fileName = $file->getClientOriginalName();
                        $jenisFile = $file->getClientMimeType();
                        $filePath = $file->store('dokumen_publikasi', 'public');
                    }

                    PublikasiDokumen::create([
                        'publikasi_id' => $publikasi->id,
                        'nama_dokumen' => $doc['nama_dokumen'] ?? ($fileName ?? 'Dokumen Publikasi'),
                        'nama_file' => $fileName,
                        'path_file' => $filePath,
                        'jenis_file' => $jenisFile,
                        'tanggal_upload' => now()->toDateString(),
                        'jenis_dokumen' => $doc['jenis_dokumen'] ?? 'Publikasi',
                        'tautan_dokumen' => $doc['tautan_dokumen'] ?? null,
                        'keterangan' => $doc['keterangan'] ?? null,
                    ]);
                }
            }

            // Save Penulis Dosen
            if ($request->has('penulis_dosen') && is_array($request->penulis_dosen)) {
                foreach ($request->penulis_dosen as $idx => $dosenInput) {
                    if (empty($dosenInput['nama_dosen'])) continue;

                    $ptId = null;
                    if (!empty($dosenInput['nama_pt'])) {
                        $pt = MasterPerguruanTinggi::firstOrCreate([
                            'nama_pt' => $dosenInput['nama_pt']
                        ]);
                        $ptId = $pt->id;
                    }

                    $dosen = null;
                    if (!empty($dosenInput['nidn'])) {
                        $dosen = Dosen::where('nidn', $dosenInput['nidn'])->first();
                    }

                    if (!$dosen) {
                        $nidn = !empty($dosenInput['nidn']) ? $dosenInput['nidn'] : 'TEMP_' . str_replace('.', '', microtime(true)) . '_' . mt_rand(100, 999);
                        $dosen = Dosen::create([
                            'nama' => $dosenInput['nama_dosen'],
                            'nidn' => $nidn,
                            'master_perguruan_tinggi_id' => $ptId,
                        ]);
                    } else {
                        $dosen->update([
                            'nama' => $dosenInput['nama_dosen'],
                            'master_perguruan_tinggi_id' => $ptId ?: $dosen->master_perguruan_tinggi_id,
                        ]);
                    }

                    $key = "dosen_{$idx}";
                    PublikasiPenulisDosen::create([
                        'publikasi_id' => $publikasi->id,
                        'master_perguruan_tinggi_id' => $ptId ?: $dosen->master_perguruan_tinggi_id,
                        'dosen_id' => $dosen->id,
                        'urutan' => $dosenInput['urutan'] ?? ($idx + 1),
                        'afiliasi' => $dosenInput['afiliasi'] ?? null,
                        'peran' => $dosenInput['peran'] ?? 'Penulis',
                        'is_corresponding' => ($corrKey === $key),
                    ]);
                }
            }

            // Save Penulis Mahasiswa
            if ($request->has('penulis_mahasiswa') && is_array($request->penulis_mahasiswa)) {
                foreach ($request->penulis_mahasiswa as $idx => $mhsInput) {
                    if (empty($mhsInput['mahasiswa_id'])) continue;
                    $key = "mahasiswa_{$idx}";
                    PublikasiPenulisMahasiswa::create([
                        'publikasi_id' => $publikasi->id,
                        'mahasiswa_id' => $mhsInput['mahasiswa_id'],
                        'urutan' => $mhsInput['urutan'] ?? ($idx + 1),
                        'afiliasi' => $mhsInput['afiliasi'] ?? null,
                        'peran' => $mhsInput['peran'] ?? 'Penulis',
                        'is_corresponding' => ($corrKey === $key),
                    ]);
                }
            }

            // Save Penulis Lain
            if ($request->has('penulis_lain') && is_array($request->penulis_lain)) {
                foreach ($request->penulis_lain as $idx => $lainInput) {
                    if (empty($lainInput['nama'])) continue;
                    $key = "lain_{$idx}";
                    PublikasiPenulisLain::create([
                        'publikasi_id' => $publikasi->id,
                        'nama' => $lainInput['nama'],
                        'urutan' => $lainInput['urutan'] ?? ($idx + 1),
                        'afiliasi' => $lainInput['afiliasi'] ?? null,
                        'peran' => $lainInput['peran'] ?? 'Penulis',
                        'is_corresponding' => ($corrKey === $key),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('publikasi.index')->with('success', 'Data Publikasi Karya berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function show(Publikasi $publikasi)
    {
        $this->checkAccess($publikasi);
        $publikasi->load(['aktivitasLitabmas', 'penulisDosen.dosen.perguruanTinggi', 'penulisMahasiswa.mahasiswa', 'penulisLain', 'dokumen']);
        return view('publikasi.show', compact('publikasi'));
    }

    public function edit(Request $request, Publikasi $publikasi)
    {
        $this->checkAccess($publikasi);
        $publikasi->load(['aktivitasLitabmas', 'penulisDosen.dosen', 'penulisMahasiswa.mahasiswa', 'penulisLain', 'dokumen']);
        $aktivitasLitabmas = MasterAktivitasLitabmas::all();

        // Halaman & kata pencarian tempat user datang dari daftar Publikasi
        // Karya - dibawa lewat query string dari link Edit di table.blade.php,
        // lalu disimpan sebagai hidden input di form edit supaya submit
        // update() bisa redirect balik ke halaman yang sama (bukan ke page 1).
        $returnPage = $request->query('page', '1');
        $returnSearch = $request->query('search', '');

        return view('publikasi.edit', compact('publikasi', 'aktivitasLitabmas', 'returnPage', 'returnSearch'));
    }

    public function update(Request $request, Publikasi $publikasi)
    {
        $this->checkAccess($publikasi);
        $request->validate([
            'kategori_kegiatan' => 'required|string',
            'judul' => 'required|string|max:255',
            'tanggal_terbit' => 'required|date',
            'tautan_jurnal' => 'nullable|url',
            'tautan_eksternal' => 'nullable|url',
            'volume' => 'nullable|integer|min:1',
            'nomor' => 'nullable|integer|min:1',
            'penulis_dosen.*.urutan' => 'nullable|integer|min:1',
            'penulis_mahasiswa.*.urutan' => 'nullable|integer|min:1',
            'penulis_lain.*.urutan' => 'nullable|integer|min:1',
        ]);

        $dosenAuthors = array_filter($request->input('penulis_dosen', []), fn($item) => !empty($item['nama_dosen']));
        $mahasiswaAuthors = array_filter($request->input('penulis_mahasiswa', []), fn($item) => !empty($item['mahasiswa_id']));

        if (count($dosenAuthors) === 0 && count($mahasiswaAuthors) === 0) {
            return back()->withInput()->withErrors(['penulis' => 'Publikasi harus memiliki minimal 1 penulis dari Penulis Dosen atau Penulis Mahasiswa.']);
        }

        $corrKey = $request->input('corresponding_author');
        if (empty($corrKey)) {
            return back()->withInput()->withErrors(['corresponding' => 'Wajib memilih 1 Corresponding Author pada salah satu baris penulis.']);
        }

        DB::beginTransaction();
        try {
            $publikasi->update([
                'kategori_kegiatan' => $request->kategori_kegiatan,
                'jenis' => $request->jenis,
                'kategori_capaian' => $request->kategori_capaian,
                'aktivitas_litabmas_id' => $request->aktivitas_litabmas_id,
                'judul' => $request->judul,
                'nama_jurnal' => $request->nama_jurnal,
                'tautan_jurnal' => $request->tautan_jurnal,
                'tanggal_terbit' => $request->tanggal_terbit,
                'volume' => $request->volume,
                'nomor' => $request->nomor,
                'halaman' => $request->halaman,
                'penerbit' => $request->penerbit,
                'doi' => $request->doi,
                'issn' => $request->issn,
                'tautan_eksternal' => $request->tautan_eksternal,
                'keterangan' => $request->keterangan,
            ]);

            // Re-sync Dokumen if uploaded
            if ($request->has('dokumen') && is_array($request->dokumen)) {
                foreach ($request->dokumen as $index => $doc) {
                    if (empty($doc['nama_dokumen']) && !$request->hasFile("dokumen.{$index}.file") && empty($doc['tautan_dokumen'])) {
                        continue;
                    }

                    $fileName = null;
                    $filePath = null;
                    $jenisFile = null;

                    if ($request->hasFile("dokumen.{$index}.file")) {
                        $file = $request->file("dokumen.{$index}.file");
                        $fileName = $file->getClientOriginalName();
                        $jenisFile = $file->getClientMimeType();
                        $filePath = $file->store('dokumen_publikasi', 'public');
                    }

                    PublikasiDokumen::create([
                        'publikasi_id' => $publikasi->id,
                        'nama_dokumen' => $doc['nama_dokumen'] ?? ($fileName ?? 'Dokumen Publikasi'),
                        'nama_file' => $fileName,
                        'path_file' => $filePath,
                        'jenis_file' => $jenisFile,
                        'tanggal_upload' => now()->toDateString(),
                        'jenis_dokumen' => $doc['jenis_dokumen'] ?? 'Publikasi',
                        'tautan_dokumen' => $doc['tautan_dokumen'] ?? null,
                        'keterangan' => $doc['keterangan'] ?? null,
                    ]);
                }
            }

            // Sync Penulis Dosen
            $publikasi->penulisDosen()->delete();
            if ($request->has('penulis_dosen') && is_array($request->penulis_dosen)) {
                foreach ($request->penulis_dosen as $idx => $dosenInput) {
                    if (empty($dosenInput['nama_dosen'])) continue;

                    $ptId = null;
                    if (!empty($dosenInput['nama_pt'])) {
                        $pt = MasterPerguruanTinggi::firstOrCreate([
                            'nama_pt' => $dosenInput['nama_pt']
                        ]);
                        $ptId = $pt->id;
                    }

                    $dosen = null;
                    if (!empty($dosenInput['nidn'])) {
                        $dosen = Dosen::where('nidn', $dosenInput['nidn'])->first();
                    }

                    if (!$dosen) {
                        $nidn = !empty($dosenInput['nidn']) ? $dosenInput['nidn'] : 'TEMP_' . str_replace('.', '', microtime(true)) . '_' . mt_rand(100, 999);
                        $dosen = Dosen::create([
                            'nama' => $dosenInput['nama_dosen'],
                            'nidn' => $nidn,
                            'master_perguruan_tinggi_id' => $ptId,
                        ]);
                    } else {
                        $dosen->update([
                            'nama' => $dosenInput['nama_dosen'],
                            'master_perguruan_tinggi_id' => $ptId ?: $dosen->master_perguruan_tinggi_id,
                        ]);
                    }

                    $key = "dosen_{$idx}";
                    PublikasiPenulisDosen::create([
                        'publikasi_id' => $publikasi->id,
                        'master_perguruan_tinggi_id' => $ptId ?: $dosen->master_perguruan_tinggi_id,
                        'dosen_id' => $dosen->id,
                        'urutan' => $dosenInput['urutan'] ?? ($idx + 1),
                        'afiliasi' => $dosenInput['afiliasi'] ?? null,
                        'peran' => $dosenInput['peran'] ?? 'Penulis',
                        'is_corresponding' => ($corrKey === $key),
                    ]);
                }
            }

            // Sync Penulis Mahasiswa
            $publikasi->penulisMahasiswa()->delete();
            if ($request->has('penulis_mahasiswa') && is_array($request->penulis_mahasiswa)) {
                foreach ($request->penulis_mahasiswa as $idx => $mhsInput) {
                    if (empty($mhsInput['mahasiswa_id'])) continue;
                    $key = "mahasiswa_{$idx}";
                    PublikasiPenulisMahasiswa::create([
                        'publikasi_id' => $publikasi->id,
                        'mahasiswa_id' => $mhsInput['mahasiswa_id'],
                        'urutan' => $mhsInput['urutan'] ?? ($idx + 1),
                        'afiliasi' => $mhsInput['afiliasi'] ?? null,
                        'peran' => $mhsInput['peran'] ?? 'Penulis',
                        'is_corresponding' => ($corrKey === $key),
                    ]);
                }
            }

            // Sync Penulis Lain
            $publikasi->penulisLain()->delete();
            if ($request->has('penulis_lain') && is_array($request->penulis_lain)) {
                foreach ($request->penulis_lain as $idx => $lainInput) {
                    if (empty($lainInput['nama'])) continue;
                    $key = "lain_{$idx}";
                    PublikasiPenulisLain::create([
                        'publikasi_id' => $publikasi->id,
                        'nama' => $lainInput['nama'],
                        'urutan' => $lainInput['urutan'] ?? ($idx + 1),
                        'afiliasi' => $lainInput['afiliasi'] ?? null,
                        'peran' => $lainInput['peran'] ?? 'Penulis',
                        'is_corresponding' => ($corrKey === $key),
                    ]);
                }
            }

            DB::commit();

            // Redirect balik ke halaman & kata pencarian tempat user datang
            // (dibawa lewat hidden input return_page/return_search di form
            // edit.blade.php), bukan selalu ke page 1.
            return redirect()->route('publikasi.index', array_filter([
                'page' => $request->input('return_page', '1'),
                'search' => $request->input('return_search', ''),
            ]))->with('success', 'Data Publikasi Karya berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Gagal mengupdate data: ' . $e->getMessage()]);
        }
    }

    public function destroy(Publikasi $publikasi)
    {
        $this->checkAccess($publikasi);
        $publikasi->delete();
        return redirect()->route('publikasi.index')->with('success', 'Data Publikasi Karya berhasil dihapus.');
    }

    public function destroyDokumen(PublikasiDokumen $dokumen)
    {
        $this->checkAccess($dokumen->publikasi);
        if ($dokumen->path_file) {
            Storage::disk('public')->delete($dokumen->path_file);
        }
        $dokumen->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Cari dosen dari mirror lokal Master Data API UKRI (dosen:read), plus
     * dosen eksternal yang diinput manual. Filter fakultas_id/prodi_id
     * mengikuti parameter yang sama seperti endpoint /api/v1/dosen di API.
     */
    public function apiSearchDosen(Request $request)
    {
        $query = Dosen::with(['perguruanTinggi', 'fakultas', 'prodi'])->search($request->q);

        if ($request->filled('pt_id')) {
            $query->where('master_perguruan_tinggi_id', $request->pt_id);
        }
        if ($request->filled('fakultas_id')) {
            $query->where('fakultas_id', $request->fakultas_id);
        }
        if ($request->filled('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }

        return response()->json($query->take(20)->get());
    }

    /**
     * Cari mahasiswa dari mirror lokal Master Data API UKRI (mahasiswa:read).
     * Filter prodi_id/angkatan_id mengikuti parameter yang sama seperti
     * endpoint /api/v1/mahasiswa di API.
     */
    public function apiSearchMahasiswa(Request $request)
    {
        // Sengaja TIDAK eager-load relasi prodi()/angkatan(): tabel mahasiswa
        // juga punya kolom string biasa bernama `prodi`, dan relasi dengan
        // nama sama akan menimpa nilai kolom itu saat di-serialize ke JSON
        // (jadi objek, bukan lagi teks) - makanya kemarin muncul "[object
        // Object]" di dropdown. Cukup ambil kolom yang benar-benar dipakai.
        $query = Mahasiswa::search($request->q);

        if ($request->filled('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }
        if ($request->filled('angkatan_id')) {
            $query->where('angkatan_id', $request->angkatan_id);
        }

        return response()->json(
            $query->orderBy('nama')->take(20)->get(['id', 'nim', 'nama', 'prodi'])
        );
    }

    /**
     * Semua mahasiswa (mirror lokal Master Data API UKRI), untuk dropdown
     * pencarian di form Publikasi Karya (create.blade.php / edit.blade.php).
     * Beda dengan apiSearchMahasiswa(): endpoint ini memuat SELURUH data
     * sekali saat form dibuka, lalu pencocokan NIM/nama dilakukan di
     * browser (Tom Select) - jadi hasil langsung mengecil sesuai ketikan
     * tanpa request berulang ke server tiap huruf diketik.
     */
    public function apiAllMahasiswa()
    {
        return response()->json(
            Mahasiswa::orderBy('nama')->get(['id', 'nim', 'nama', 'prodi'])
        );
    }

    private function checkAccess(Publikasi $publikasi)
    {
        $user = auth()->user();
        if ($user && !$user->hasRole('admin')) {
            $userEmail = $user->email;
            $dosenIds = \App\Models\Dosen::where('email', $userEmail)->pluck('id')->toArray();
            $mahasiswaIds = \App\Models\Mahasiswa::where('email', $userEmail)->pluck('id')->toArray();

            $isOwner = $publikasi->penulisDosen()->whereIn('dosen_id', $dosenIds)->exists() ||
                       $publikasi->penulisMahasiswa()->whereIn('mahasiswa_id', $mahasiswaIds)->exists();

            if (!$isOwner) {
                abort(403, 'Anda tidak memiliki akses ke publikasi ini.');
            }
        }
    }
}
