<script>
let scData       = {};   // model_code => { model_code, models, totals }
let scPeriod     = null; // { year, month }
let activeModel  = null; // model_code currently shown in detail modal

// ── Init ──────────────────────────────────────────────────────────────────────
$(document).ready(function () {
    const now = new Date();
    const ym  = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    $('#sc-period').val(ym);
    $('#sc-up-period').val(ym);
    loadData();

    $('#sc-file-input').on('change', function () {
        $('#sc-file-label').text(this.files[0] ? this.files[0].name : 'Pilih file Excel...');
    });

    // Blokir scroll wheel pada semua input number (termasuk add-mdp-input yang di-render dinamis)
    $(document).on('wheel', 'input[type=number]', function (e) {
        $(this).blur();
        e.preventDefault();
    });

    $('#sc-period').on('change', function () { loadData(); });
});

// ── Helpers ───────────────────────────────────────────────────────────────────
function parsePeriod(val) {
    if (!val) return null;
    const [y, m] = val.split('-').map(Number);
    return { year: y, month: m };
}

function showLoading() { $('#sc-loading').css('display', 'flex'); }
function hideLoading() { $('#sc-loading').hide(); }

function availClass(val) {
    if (val > 0) return 'avail-ok';
    if (val < 0) return 'avail-neg';
    return '';
}

// Hanya warna teks, tanpa override background (untuk baris subtotal/total)
function availStyle(val) {
    if (val > 0) return ' style="color:#1b5e20;font-weight:700;"';
    if (val < 0) return ' style="color:#b71c1c;font-weight:700;"';
    return '';
}

function esc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Load Data ─────────────────────────────────────────────────────────────────
function loadData() {
    const p = parsePeriod($('#sc-period').val());
    if (!p) { Swal.fire('Perhatian', 'Pilih periode terlebih dahulu', 'warning'); return; }
    scPeriod = p;

    showLoading();

    $.get('<?=base_url("get_data_suffix_control")?>', { month: p.month, year: p.year }, function (res) {
        if (res.status === 'ok') {
            scData = res.data || {};
            renderCards();
        } else {
            Swal.fire('Error', res.message || 'Gagal memuat data', 'error');
        }
    }, 'json')
    .fail(function () { Swal.fire('Error', 'Gagal koneksi ke server', 'error'); })
    .always(hideLoading);
}

// ── Render Cards ──────────────────────────────────────────────────────────────
function renderCards() {
    const container = $('#sc-cards');
    const empty     = $('#sc-empty');
    container.empty();

    const codes = Object.keys(scData);
    if (codes.length === 0) {
        empty.show();
        return;
    }
    empty.hide();

    codes.forEach(function (mc) {
        const d  = scData[mc];
        const t  = d.totals;
        // Collect unique model names
        const names = d.models.map(m => m.model_name).filter((v,i,a) => a.indexOf(v) === i).join(' / ');
        const ac    = availClass(t.available);

        const html = `
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
            <div class="card sc-card h-100" onclick="openDetail('${esc(mc)}')">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <div class="card-code">${esc(mc)}</div>
                        <div class="card-name">${esc(names)}</div>
                    </div>
                    <i class="fas fa-table" style="font-size:1.3rem; opacity:.6; margin-top:4px;"></i>
                </div>
                <div class="card-body py-2 px-3">
                    <div class="sc-stat d-flex justify-content-between">
                        <span class="sc-lbl">Plan MDP</span>
                        <span class="sc-val">${t.plan_mdp.toLocaleString()}</span>
                    </div>
                    <div class="sc-stat d-flex justify-content-between">
                        <span class="sc-lbl">Add. MDP</span>
                        <span class="sc-val">${t.add_mdp.toLocaleString()}</span>
                    </div>
                    <div class="sc-stat d-flex justify-content-between">
                        <span class="sc-lbl">Total Plan</span>
                        <span class="sc-val">${t.total_plan.toLocaleString()}</span>
                    </div>
                    <div class="sc-stat d-flex justify-content-between">
                        <span class="sc-lbl">Act. Receive</span>
                        <span class="sc-val">${t.act_receive.toLocaleString()}</span>
                    </div>
                    <div class="sc-stat d-flex justify-content-between">
                        <span class="sc-lbl">Remain</span>
                        <span class="sc-val ${ac}">${t.remain.toLocaleString()}</span>
                    </div>
                    <div class="sc-stat d-flex justify-content-between">
                        <span class="sc-lbl">Act. WOS</span>
                        <span class="sc-val">${t.act_wos.toLocaleString()}</span>
                    </div>
                    <hr class="my-1">
                    <div class="sc-stat d-flex justify-content-between">
                        <span class="sc-lbl">Available</span>
                        <span class="sc-val ${ac}" style="font-size:9pt;">${t.available.toLocaleString()}</span>
                    </div>
                </div>
                <div class="card-footer py-1 text-center" style="background:#f0f4ff; border-radius:0 0 10px 10px; font-size:7pt; color:#1976D2;">
                    <i class="fas fa-mouse-pointer mr-1"></i>Klik untuk lihat detail
                </div>
            </div>
        </div>`;
        container.append(html);
    });
}

// ── Open Detail Modal ─────────────────────────────────────────────────────────
function openDetail(mc) {
    const d = scData[mc];
    if (!d) return;
    activeModel = mc;

    const mStr = scPeriod ? String(scPeriod.month).padStart(2,'0') + '/' + scPeriod.year : '';
    $('#sc-detail-title').html(`<i class="fas fa-table mr-1"></i>Detail Suffix Control &mdash; <b>${esc(mc)}</b> <small class="ml-2" style="font-weight:400;">${mStr}</small>`);

    renderDetailTable(mc);
    $('#modal-sc-detail').modal('show');
}

function renderDetailTable(mc) {
    const d    = scData[mc];
    const body = $('#sc-detail-body');
    let html   = '';

    d.models.forEach(function (mn) {
        // Individual rows
        mn.rows.forEach(function (r) {
            const ac = availClass(r.available);
            html += `<tr>
                <td class="col-kts">${esc(r.katashiki)}</td>
                <td>${esc(r.suffix)}</td>
                <td>${r.plan_mdp || ''}</td>
                <td><input type="number" class="add-mdp-input" value="${r.add_mdp}"
                        data-id="${r.id}" data-mc="${esc(mc)}" data-mn="${esc(mn.model_name)}"
                        min="0" onblur="saveAddMdp(this)" onkeydown="if(event.key==='Enter')this.blur();"></td>
                <td class="total-plan-cell" data-id="${r.id}">${r.total_plan || ''}</td>
                <td>${r.act_receive || ''}</td>
                <td class="${ac}">${r.remain !== 0 ? r.remain : ''}</td>
                <td>${r.act_wos || ''}</td>
                <td class="${ac}">${r.available !== 0 ? r.available : ''}</td>
            </tr>`;
        });

        // Subtotal row per model_name
        const s = mn.subtotals;
        html += `<tr class="row-subtotal">
            <td class="col-kts">${esc(mn.model_name)}</td>
            <td></td>
            <td>${s.plan_mdp || 0}</td>
            <td>${s.add_mdp || 0}</td>
            <td>${s.total_plan || 0}</td>
            <td>${s.act_receive || 0}</td>
            <td${availStyle(s.available)}>${s.remain !== undefined ? s.remain : 0}</td>
            <td>${s.act_wos || 0}</td>
            <td${availStyle(s.available)}>${s.available !== undefined ? s.available : 0}</td>
        </tr>`;
    });

    // Grand total row
    const t = d.totals;
    html += `<tr class="row-total">
        <td class="col-kts">TOTAL ${esc(mc)}</td>
        <td></td>
        <td>${t.plan_mdp || 0}</td>
        <td>${t.add_mdp || 0}</td>
        <td>${t.total_plan || 0}</td>
        <td>${t.act_receive || 0}</td>
        <td${availStyle(t.available)}>${t.remain !== undefined ? t.remain : 0}</td>
        <td>${t.act_wos || 0}</td>
        <td${availStyle(t.available)}>${t.available !== undefined ? t.available : 0}</td>
    </tr>`;

    body.html(html);
}

// ── Save Add MDP ──────────────────────────────────────────────────────────────
function saveAddMdp(input) {
    const id      = parseInt(input.dataset.id);
    const mc      = input.dataset.mc;
    const mn      = input.dataset.mn;
    const addMdp  = parseInt(input.value) || 0;

    // Find row in scData and get current plan_mdp
    const mcData = scData[mc];
    if (!mcData) return;

    let targetRow = null;
    mcData.models.forEach(function (m) {
        m.rows.forEach(function (r) {
            if (r.id === id) targetRow = r;
        });
    });
    if (!targetRow) return;

    // No change, skip
    if (targetRow.add_mdp === addMdp) return;

    $(input).addClass('saving').prop('disabled', true);

    $.post('<?=base_url("update_add_mdp_suffix_control")?>', { id: id, add_mdp: addMdp }, function (res) {
        if (res.status === 'ok') {
            // Update scData in memory
            const oldAdd      = targetRow.add_mdp;
            const diff        = addMdp - oldAdd;
            targetRow.add_mdp    = addMdp;
            targetRow.total_plan = targetRow.plan_mdp + addMdp;
            targetRow.remain     = targetRow.total_plan - targetRow.act_receive;
            targetRow.available  = targetRow.act_receive - targetRow.act_wos;

            // Update subtotals and totals
            const mnGroup = mcData.models.find(m => m.model_name === mn);
            if (mnGroup) {
                mnGroup.subtotals.add_mdp    += diff;
                mnGroup.subtotals.total_plan += diff;
                mnGroup.subtotals.remain     += diff;
            }
            mcData.totals.add_mdp    += diff;
            mcData.totals.total_plan += diff;
            mcData.totals.remain     += diff;

            // Refresh table & total-plan cell
            const cell = $(`.total-plan-cell[data-id="${id}"]`);
            cell.text(targetRow.total_plan || '');

            // Re-render whole detail table to refresh subtotals & totals
            renderDetailTable(mc);
            // Re-render card summary
            renderCards();
            // Re-open modal (cards re-render closes event listeners outside modal)
            $('#modal-sc-detail').modal('show');
            openDetail(mc);
        } else {
            Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat menyimpan', 'error');
            // Revert
            input.value = targetRow.add_mdp;
        }
    }, 'json')
    .fail(function () {
        Swal.fire('Error', 'Gagal koneksi ke server', 'error');
        input.value = targetRow.add_mdp;
    })
    .always(function () {
        $(input).removeClass('saving').prop('disabled', false);
    });
}

// ── Upload ────────────────────────────────────────────────────────────────────
function doUpload() {
    const period = $('#sc-up-period').val();
    const file   = $('#sc-file-input')[0].files[0];

    if (!period) { Swal.fire('Perhatian', 'Pilih periode terlebih dahulu', 'warning'); return; }
    if (!file)   { Swal.fire('Perhatian', 'Pilih file Excel terlebih dahulu', 'warning'); return; }

    const [y, m] = period.split('-');
    const fd = new FormData();
    fd.append('upload-file', file);
    fd.append('month', m);
    fd.append('year',  y);

    $('#modal-upload-sc').modal('hide');
    Swal.fire({ title: 'Mengupload...', html: 'Mohon tunggu...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url:         '<?=base_url("upload_suffix_control")?>',
        type:        'POST',
        data:        fd,
        processData: false,
        contentType: false,
        dataType:    'json',
        success: function (res) {
            if (res.status === 'ok') {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2200, showConfirmButton: false });
                $('#sc-period').val(period);
                loadData();
            } else {
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
            }
        },
        error: function () { Swal.fire('Error', 'Gagal koneksi ke server', 'error'); }
    });
}

// ── Clear Period ──────────────────────────────────────────────────────────────
function clearPeriod() {
    const p = parsePeriod($('#sc-period').val());
    if (!p) { Swal.fire('Perhatian', 'Pilih periode terlebih dahulu', 'warning'); return; }

    Swal.fire({
        title: 'Hapus Data?',
        html: `Data Suffix Control periode <b>${String(p.month).padStart(2,'0')}/${p.year}</b> akan dihapus.`,
        iconHtml: '<i class="fas fa-trash" style="font-size:2.5rem;color:#dc3545;"></i>',
        iconColor: 'transparent',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
    }).then(function (result) {
        if (!result.isConfirmed) return;
        showLoading();
        $.post('<?=base_url("clear_suffix_control")?>', { month: p.month, year: p.year }, function (res) {
            if (res.status === 'ok') {
                scData = {};
                renderCards();
                Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        }, 'json')
        .fail(function () { Swal.fire('Error', 'Gagal koneksi ke server', 'error'); })
        .always(hideLoading);
    });
}
</script>
