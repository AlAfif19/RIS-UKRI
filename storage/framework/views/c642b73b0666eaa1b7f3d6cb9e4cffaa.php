<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Artikel</th>
                <th>Kategori Kegiatan</th>
                <th>Jenis</th>
                <th>Tanggal Terbit</th>
                <th>Penulis</th>
                <th>Dokumen</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $publikasiList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($publikasiList->firstItem() + $index); ?></td>
                    <td>
                        <strong><?php echo e($pub->judul); ?></strong>
                        <?php if($pub->nama_jurnal): ?>
                            <br><small class="text-muted"><i class="bi bi-journal"></i> <?php echo e($pub->nama_jurnal); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><small><?php echo e(Str::limit($pub->kategori_kegiatan, 60)); ?></small></td>
                    <td><span class="badge bg-info text-dark"><?php echo e($pub->jenis ?? '-'); ?></span></td>
                    <td><?php echo e(\Carbon\Carbon::parse($pub->tanggal_terbit)->format('d-m-Y')); ?></td>
                    <td>
                        <small>
                            <?php $__currentLoopData = $pub->penulisDosen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div><i class="bi bi-person-badge"></i> <?php echo e($pd->dosen->nama ?? '-'); ?> <?php if($pd->is_corresponding): ?> <span class="badge bg-warning text-dark">Corresponding</span> <?php endif; ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $pub->penulisMahasiswa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div><i class="bi bi-person-workspace"></i> <?php echo e($pm->mahasiswa->nama ?? '-'); ?> <?php if($pm->is_corresponding): ?> <span class="badge bg-warning text-dark">Corresponding</span> <?php endif; ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $pub->penulisLain; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div><i class="bi bi-person"></i> <?php echo e($pl->nama); ?> <?php if($pl->is_corresponding): ?> <span class="badge bg-warning text-dark">Corresponding</span> <?php endif; ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </small>
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?php echo e($pub->dokumen->count()); ?> Dokumen</span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="<?php echo e(route('publikasi.show', $pub->id)); ?>" class="btn btn-sm btn-info text-white" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?php echo e(route('publikasi.edit', $pub->id)); ?>" class="btn btn-sm btn-warning text-white" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?php echo e(route('publikasi.destroy', $pub->id)); ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus publikasi ini?')" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Belum ada data Publikasi Karya.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end">
    <?php echo e($publikasiList->links()); ?>

</div>
<?php /**PATH D:\project\simantap\ris-ukri\resources\views/publikasi/partials/table.blade.php ENDPATH**/ ?>