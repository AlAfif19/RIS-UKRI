{{--
    Komponen Notifikasi Global (Toast + Modal Konfirmasi)
    ------------------------------------------------------
    Dipasang sekali di layouts/app.blade.php supaya tersedia di SELURUH
    halaman yang extends layout ini - dibuat untuk menggantikan alert()/
    confirm() bawaan browser (yang tampilannya default OS, tidak mengikuti
    tema RIS UKRI) dengan toast & modal yang mengikuti warna tema (maroon
    #800202, sama seperti tombol & link di style.css) serta ikon Bootstrap
    Icons yang sudah dimuat di layout.

    Dipakai lewat 2 fungsi global:
    - appNotify(type, message, title?)
        type: 'success' | 'error' | 'warning' | 'info'
        Menampilkan toast di pojok kanan atas, otomatis hilang sendiri.
    - appConfirm({ title, message, confirmText, cancelText, variant })
        Mengembalikan Promise<boolean> - resolve(true) kalau user klik
        tombol konfirmasi, resolve(false) kalau batal/ditutup. Dipakai
        sebagai pengganti window.confirm() yang sifatnya sinkron, dengan
        pola: appConfirm({...}).then((ok) => { if (!ok) return; ... }).
--}}
<div id="appToastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>

<div class="modal fade" id="appConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title d-flex align-items-center gap-2" id="appConfirmModalTitle">
                    <i class="bi bi-question-circle-fill text-warning fs-4" id="appConfirmModalIcon"></i>
                    <span id="appConfirmModalTitleText">Konfirmasi</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0 text-muted" id="appConfirmModalMessage"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" id="appConfirmModalCancelBtn" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="appConfirmModalConfirmBtn">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<style>
    #appToastContainer .toast {
        min-width: 300px;
        border: none;
        border-left: 4px solid #800202;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.15);
    }

    #appToastContainer .toast.toast-success { border-left-color: #2ecc71; }
    #appToastContainer .toast.toast-error { border-left-color: #dc3545; }
    #appToastContainer .toast.toast-warning { border-left-color: #ffc107; }
    #appToastContainer .toast.toast-info { border-left-color: #0dcaf0; }

    #appToastContainer .toast .toast-icon {
        font-size: 1.25rem;
        line-height: 1;
    }

    #appConfirmModal .modal-content {
        border: none;
        border-radius: 0.6rem;
    }
</style>

<script>
    // appNotify(type, message, title?) - toast bertema, pengganti alert().
    window.appNotify = function (type, message, title) {
        const container = document.getElementById('appToastContainer');
        if (!container) return;

        const config = {
            success: { icon: 'bi-check-circle-fill', text: 'text-success', label: 'Berhasil' },
            error: { icon: 'bi-x-circle-fill', text: 'text-danger', label: 'Gagal' },
            warning: { icon: 'bi-exclamation-triangle-fill', text: 'text-warning', label: 'Perhatian' },
            info: { icon: 'bi-info-circle-fill', text: 'text-info', label: 'Info' },
        }[type] || { icon: 'bi-info-circle-fill', text: 'text-info', label: 'Info' };

        const toastEl = document.createElement('div');
        toastEl.className = `toast toast-${type}`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="toast-header">
                <i class="bi ${config.icon} ${config.text} toast-icon me-2"></i>
                <strong class="me-auto">${title ? escapeHtmlNotify(title) : config.label}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup"></button>
            </div>
            <div class="toast-body">${escapeHtmlNotify(message)}</div>
        `;
        container.appendChild(toastEl);

        const toast = new bootstrap.Toast(toastEl, { delay: 4500 });
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        toast.show();
    };

    // appConfirm({ title, message, confirmText, cancelText, variant }) -
    // modal konfirmasi bertema, pengganti confirm(). Mengembalikan
    // Promise<boolean>. variant: 'danger' (default, dipakai untuk aksi
    // hapus) atau 'primary' (aksi netral).
    window.appConfirm = function (opts) {
        opts = opts || {};
        const modalEl = document.getElementById('appConfirmModal');
        const titleText = document.getElementById('appConfirmModalTitleText');
        const messageEl = document.getElementById('appConfirmModalMessage');
        const iconEl = document.getElementById('appConfirmModalIcon');
        const confirmBtn = document.getElementById('appConfirmModalConfirmBtn');
        const cancelBtn = document.getElementById('appConfirmModalCancelBtn');

        titleText.textContent = opts.title || 'Konfirmasi';
        messageEl.textContent = opts.message || 'Apakah Anda yakin?';
        confirmBtn.textContent = opts.confirmText || 'Ya, Lanjutkan';
        cancelBtn.textContent = opts.cancelText || 'Batal';

        const variant = opts.variant || 'danger';
        confirmBtn.className = `btn btn-${variant}`;
        iconEl.className = variant === 'danger'
            ? 'bi bi-exclamation-triangle-fill text-danger fs-4'
            : 'bi bi-question-circle-fill text-warning fs-4';

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        return new Promise((resolve) => {
            let settled = false;

            function cleanup(result) {
                if (settled) return;
                settled = true;
                confirmBtn.removeEventListener('click', onConfirm);
                modalEl.removeEventListener('hidden.bs.modal', onHidden);
                resolve(result);
            }

            function onConfirm() {
                modal.hide();
                cleanup(true);
            }

            function onHidden() {
                cleanup(false);
            }

            confirmBtn.addEventListener('click', onConfirm);
            modalEl.addEventListener('hidden.bs.modal', onHidden);

            modal.show();
        });
    };

    function escapeHtmlNotify(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
