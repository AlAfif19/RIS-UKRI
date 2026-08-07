<?php

namespace App\Http\Controllers;

use App\Models\Angkatan;
use App\Models\Fakultas;
use App\Models\Peminatan;
use App\Models\Prodi;
use Illuminate\Http\Request;

/**
 * Endpoint JSON tipis di atas mirror lokal Master Data API UKRI, untuk
 * mengisi dropdown fakultas/prodi/angkatan/peminatan (mis. dropdown
 * berjenjang di form) tanpa memanggil API eksternal di setiap request.
 * Datanya diisi berkala oleh `php artisan ukri:sync`.
 */
class MasterDataController extends Controller
{
    public function fakultas()
    {
        return response()->json(
            Fakultas::where('is_active', true)->orderBy('nama_fakultas')->get(['id', 'nama_fakultas', 'dekan'])
        );
    }

    public function prodi(Request $request)
    {
        $query = Prodi::where('is_active', true)->orderBy('nama_prodi');

        if ($request->filled('fakultas_id')) {
            $query->where('fakultas_id', $request->fakultas_id);
        }

        return response()->json($query->get(['id', 'nama_prodi', 'fakultas_id', 'kaprodi']));
    }

    public function angkatan(Request $request)
    {
        $query = Angkatan::where('is_active', true)->orderBy('angkatan', 'desc');

        if ($request->filled('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }
        if ($request->filled('fakultas_id')) {
            $query->where('fakultas_id', $request->fakultas_id);
        }

        return response()->json($query->get(['id', 'angkatan', 'status', 'prodi_id', 'fakultas_id']));
    }

    public function peminatan(Request $request)
    {
        $query = Peminatan::where('is_active', true)->orderBy('nama_peminatan');

        if ($request->filled('prodi_id')) {
            $query->where('prodi_id', $request->prodi_id);
        }
        if ($request->filled('fakultas_id')) {
            $query->where('fakultas_id', $request->fakultas_id);
        }

        return response()->json($query->get(['id', 'nama_peminatan', 'prodi_id', 'fakultas_id']));
    }
}
