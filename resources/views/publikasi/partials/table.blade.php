<div class="table-responsive">
    <table class="table table-striped align-middle" style="min-width: 1150px;">
        <thead>
            <tr>
                <th style="width: 45px;">No</th>
                <th style="width: 26%;">Judul Artikel</th>
                <th style="width: 16%;">Kategori Kegiatan</th>
                <th style="width: 90px;">Jenis</th>
                <th style="width: 110px;" class="text-nowrap">Tanggal Terbit</th>
                <th style="width: 22%;">Penulis</th>
                <th style="width: 100px;">Dokumen</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($publikasiList as $index => $pub)
                <tr>
                    <td>{{ $publikasiList->firstItem() + $index }}</td>
                    <td>
                        <strong>{{ $pub->judul }}</strong>
                        @if($pub->nama_jurnal)
                            <br><small class="text-muted"><i class="bi bi-journal"></i> {{ $pub->nama_jurnal }}</small>
                        @endif
                    </td>
                    <td><small>{{ Str::limit($pub->kategori_kegiatan, 60) }}</small></td>
                    <td>
                        <span class="badge bg-info text-dark d-inline-block text-truncate" style="max-width: 100%;"
                            title="{{ $pub->jenis }}">{{ Str::limit($pub->jenis ?? '-', 18) }}</span>
                    </td>
                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($pub->tanggal_terbit)->format('d-m-Y') }}</td>
                    <td>
                        <small>
                            @foreach($pub->penulisDosen as $pd)
                                <div><i class="bi bi-person-badge"></i> {{ $pd->dosen->nama ?? '-' }} @if($pd->is_corresponding) <span class="badge bg-warning text-dark">Corresponding</span> @endif</div>
                            @endforeach
                            @foreach($pub->penulisMahasiswa as $pm)
                                <div><i class="bi bi-person-workspace"></i> {{ $pm->mahasiswa->nama ?? '-' }} @if($pm->is_corresponding) <span class="badge bg-warning text-dark">Corresponding</span> @endif</div>
                            @endforeach
                            @foreach($pub->penulisLain as $pl)
                                <div><i class="bi bi-person"></i> {{ $pl->nama }} @if($pl->is_corresponding) <span class="badge bg-warning text-dark">Corresponding</span> @endif</div>
                            @endforeach
                        </small>
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $pub->dokumen->count() }} Dokumen</span>
                    </td>
                    <td class="text-center text-nowrap">
                        <div class="btn-group" role="group">
                            <a href="{{ route('publikasi.show', $pub->id) }}" class="btn btn-sm btn-info text-white" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('publikasi.edit', ['publikasi' => $pub->id, 'page' => $publikasiList->currentPage(), 'search' => request('search')]) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('publikasi.destroy', $pub->id) }}" method="POST" class="d-inline js-delete-publikasi-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Belum ada data Publikasi Karya.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end">
    {{ $publikasiList->links() }}
</div>
