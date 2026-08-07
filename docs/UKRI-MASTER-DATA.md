# Integrasi Master Data API UKRI

Aplikasi ini sekarang menarik data **fakultas, program studi, angkatan,
peminatan, dosen, dan mahasiswa** dari Master Data API UKRI
(`https://api.ukri.web.id/api/v1`) alih-alih data yang diinput manual.

## Kenapa ada tabel mirror lokal?

API-nya read-only (GET saja) dan tiap endpoint daftar dibatasi tetap 20 baris
per halaman, tanpa pencarian nama (hanya filter `nidn`/`npm` persis). Supaya
pencarian dosen/mahasiswa di form Publikasi tetap cepat dan bisa dicari per
nama, datanya di-mirror secara berkala ke tabel lokal:

| Tabel lokal      | Sumber API           | Diisi oleh                          |
|-------------------|-----------------------|--------------------------------------|
| `ukri_fakultas`   | `GET /fakultas`       | `php artisan ukri:sync fakultas`     |
| `ukri_prodi`      | `GET /prodi`          | `php artisan ukri:sync prodi`        |
| `ukri_angkatan`   | `GET /angkatan`       | `php artisan ukri:sync angkatan`     |
| `ukri_peminatan`  | `GET /peminatan`      | `php artisan ukri:sync peminatan`    |
| `dosen`           | `GET /dosen`          | `php artisan ukri:sync dosen`        |
| `mahasiswa`       | `GET /mahasiswa`      | `php artisan ukri:sync mahasiswa`    |

Tabel-tabel ini **hanya boleh ditulis lewat perintah `ukri:sync`** (lihat
`App\Console\Commands\SyncUkriMasterData`) - jangan diedit manual, karena
data master aslinya hanya bisa diubah lewat SIMANTAP.

Catatan: `dosen` tetap mendukung co-author eksternal dari perguruan tinggi
lain yang diinput manual (field `nama_pt` + NIDN bebas) - hanya baris yang
`ukri_id`-nya terisi yang benar-benar berasal dari API UKRI. Tabel
`mahasiswa` sepenuhnya mirror dari API karena mahasiswa penulis publikasi
selalu mahasiswa UKRI sendiri.

## Setup

1. Isi `.env` (sudah disiapkan di `.env.example`):
   ```env
   UKRI_API_BASE_URL=https://api.ukri.web.id/api/v1
   UKRI_API_TOKEN=isi_dengan_token_dari_halaman_API_Key
   UKRI_API_CACHE_TTL=1440
   ```
   Jangan commit token ini - `.env` sudah masuk `.gitignore`.

2. Jalankan migrasi tabel mirror:
   ```bash
   php artisan migrate
   ```

3. Tarik data pertama kali:
   ```bash
   php artisan ukri:sync
   ```
   Atau per entitas: `php artisan ukri:sync fakultas`, `... prodi`, dst.
   Urutan `all` sudah otomatis benar (fakultas → prodi → angkatan/peminatan →
   dosen/mahasiswa) supaya relasi lokalnya tersambung.

4. Sinkronisasi harian sudah dijadwalkan di `routes/console.php`
   (`Schedule::command('ukri:sync')->dailyAt('02:00')`). Pastikan scheduler
   Laravel berjalan (`* * * * * php artisan schedule:run` di cron, atau
   `php artisan schedule:work` saat development).

## Apa yang berubah di aplikasi

- **Form Publikasi (create/edit):** dropdown mahasiswa penulis sekarang
  bersumber dari `Mahasiswa::orderBy('nama')->get()`, yaitu data hasil sync
  API, bukan lagi data yang diseed manual.
- **`GET /api/dosen/search` & `GET /api/mahasiswa/search`:** query ke tabel
  mirror lokal, dengan filter tambahan `fakultas_id`/`prodi_id` (dosen) dan
  `prodi_id`/`angkatan_id` (mahasiswa) - mengikuti parameter yang sama
  seperti endpoint API aslinya.
- **`GET /api/master/fakultas|prodi|angkatan|peminatan`:** endpoint JSON baru
  di atas tabel mirror, siap dipakai untuk dropdown berjenjang
  (fakultas → prodi → angkatan/peminatan) di form-form berikutnya.
- **`App\Services\UkriMasterDataService`:** kelas client resmi untuk semua
  pemanggilan API ini (live call maupun untuk sinkronisasi) - pakai kelas ini
  kalau perlu fitur baru yang butuh data langsung dari API, jangan panggil
  `Http::` langsung ke `api.ukri.web.id` dari tempat lain.

## Keamanan

- Token disimpan di `.env` lewat `config('services.ukri.token')` - tidak
  pernah di-hardcode di kode maupun di-expose ke browser.
- Data dosen & mahasiswa tetap diperlakukan sebagai data pribadi: tampilkan
  seperlunya di UI dan jangan diteruskan ke pihak ketiga.

## Merapikan data lama: kolaborator eksternal & dosen manual duplikat

Sebelum integrasi ini, penulis yang tidak cocok dengan 4 dosen contoh di
seeder awal otomatis dicatat sebagai **Kolaborator Eksternal**
(`publikasi_penulis_lain`), dan dosen tanpa NIDN dicatat sebagai baris
`Dosen` baru dengan NIDN placeholder `TEMP_...`. Setelah `php artisan
ukri:sync` berjalan dan data dosen/mahasiswa UKRI yang sesungguhnya
tersedia, sebagian nama-nama itu ternyata cocok dengan data master asli.

Jalankan perintah berikut untuk memindahkan/menggabungkan data tersebut ke
tempat yang seharusnya:

```bash
# 1. Lihat dulu apa yang akan diubah (dry-run, tidak menyimpan apa pun)
php artisan ukri:reconcile-authors

# 2. Kalau hasilnya sudah sesuai, jalankan sungguhan
php artisan ukri:reconcile-authors --apply

# 3. (opsional) sekalian bersihkan 5 publikasi contoh + dosen/mahasiswa
#    dummy bawaan PublikasiMasterSeeder (aman - dosen/mahasiswa yang masih
#    dipakai di publikasi ASLI tidak akan dihapus, hanya digabung)
php artisan ukri:reconcile-authors --delete-demo --apply
```

Yang dilakukan perintah ini (lihat
`App\Console\Commands\ReconcileAuthorsWithMasterData`):

1. **Kolaborator eksternal → Dosen/Mahasiswa** - baris `publikasi_penulis_lain`
   yang namanya *persis sama* (setelah dirapikan spasi/huruf besar-kecil)
   dengan dosen atau mahasiswa hasil sinkronisasi API dipindahkan jadi baris
   Penulis Dosen / Penulis Mahasiswa yang sesungguhnya, baris lama dihapus.
2. **Dosen manual duplikat → digabung** - baris `Dosen` yang `ukri_id`-nya
   masih kosong (diinput manual lewat form, termasuk NIDN placeholder
   `TEMP_...`) tapi namanya persis sama dengan dosen UKRI hasil sync,
   seluruh kepenulisannya dipindah ke record UKRI yang benar, lalu
   duplikatnya dihapus.
3. **(dengan `--delete-demo`)** menghapus 5 publikasi contoh dari
   `PublikasiMasterSeeder` beserta dokumen & kepenulisannya, lalu menghapus
   dosen/mahasiswa dummy bawaannya - tapi HANYA yang sudah tidak dipakai di
   publikasi manapun (kalau nama dummy itu ternyata dipakai juga di data
   asli hasil import, dia sudah digabung di langkah 2 sehingga otomatis
   aman dari penghapusan).

Nama yang tidak cocok persis dengan data master (mis. co-author dari
perguruan tinggi lain yang memang bukan orang UKRI) dibiarkan apa adanya -
perintah ini sengaja tidak mencocokkan secara fuzzy supaya tidak salah
menggabungkan dua orang berbeda. Semua nama yang tidak cocok ditampilkan di
laporan supaya bisa dicek manual.

`DatabaseSeeder` juga sudah tidak lagi memanggil `PublikasiMasterSeeder`
secara otomatis, supaya instalasi baru tidak membuat data demo tersebut lagi.
