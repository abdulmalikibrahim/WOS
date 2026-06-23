<script>
let planData   = {};   // color => {day: plan}
let actualData = {};   // color => {day: actual}

// ── Init ──────────────────────────────────────────────────────────────────────
$(document).ready(function () {
    const now = new Date();
    const ym  = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    $('#cc-period').val(ym);
    $('#up-period').val(ym);
    loadModels();

    $('#cc-file-input').on('change', function () {
        $('#cc-file-label').text(this.files[0] ? this.files[0].name : 'Pilih file Excel...');
    });

    $("#cc-period").change(function() {
        loadModels();
    });

    $("#cc-model").change(function() {
        const p = parsePeriod($('#cc-period').val());
        if (!p) return;
        loadAll();
    });

    // Populate model dropdown di modal upload
    $('#modal-upload-cc').on('show.bs.modal', function () {
        loadUploadModels();
    });

    $('#up-period').on('change', function () {
        loadUploadModels();
    });
});

// ── Helpers ───────────────────────────────────────────────────────────────────
function parsePeriod(val) {
    if (!val) return null;
    const [y, m] = val.split('-').map(Number);
    return { year: y, month: m };
}

function daysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
}

function showLoading()  { $('#cc-loading').css('display', 'flex'); }
function hideLoading()  { $('#cc-loading').hide(); }

// ── Upload Modal Models ───────────────────────────────────────────────────────
function loadUploadModels() {
    const p = parsePeriod($('#up-period').val());
    if (!p) return;

    $.get('<?=base_url("get_all_models_color_control")?>', { month: p.month, year: p.year }, function (res) {
        const $sel = $('#up-model');
        const prev = $sel.val();
        $sel.find('option:not(:first)').remove();
        if (res.status === 'ok' && res.data.length) {
            res.data.forEach(function (m) {
                $sel.append($('<option>').val(m).text(m));
            });
            if (res.data.includes(prev)) $sel.val(prev);
        }
    }, 'json');
}

// ── Load All ──────────────────────────────────────────────────────────────────
function loadModels(selectModel) {
    const p = parsePeriod($('#cc-period').val());
    if (!p) return;

    $.get('<?=base_url("get_models_color_control")?>', { month: p.month, year: p.year }, function (res) {
        const $sel  = $('#cc-model');
        const pick  = selectModel || $sel.val();
        $sel.find('option:not(:first)').remove();
        if (res.status === 'ok' && res.data.length) {
            res.data.forEach(function (m) {
                $sel.append($('<option>').val(m).text(m));
            });
            if (res.data.includes(pick)) $sel.val(pick);
        }
    }, 'json').always(function () {
        loadAll();
    });
}

function loadAll() {
    const p = parsePeriod($('#cc-period').val());
    if (!p) { Swal.fire('Perhatian', 'Pilih periode terlebih dahulu', 'warning'); return; }

    showLoading();

    const model     = $('#cc-model').val();
    const params    = { month: p.month, year: p.year };
    if (model) params.model = model;

    const planReq   = $.get('<?=base_url("get_plan_color_control")?>', params);
    const actualReq = $.get('<?=base_url("get_actual_color_control")?>', params);

    $.when(planReq, actualReq).done(function (planRes, actualRes) {
        planData   = (planRes[0]   && planRes[0].status   === 'ok') ? planRes[0].data   : {};
        actualData = (actualRes[0] && actualRes[0].status === 'ok') ? actualRes[0].data : {};
        renderAll(p.year, p.month);
    }).fail(function () {
        Swal.fire('Error', 'Gagal memuat data dari server', 'error');
    }).always(hideLoading);
}

function loadActualAndRender(p) {
    showLoading();

    const model  = $('#cc-model').val();
    const params = { month: p.month, year: p.year };
    if (model) params.model = model;

    $.get('<?=base_url("get_actual_color_control")?>', params, function (res) {
        actualData = (res && res.status === 'ok') ? res.data : {};
        renderAll(p.year, p.month);
    }, 'json').fail(function () {
        Swal.fire('Error', 'Gagal memuat data dari server', 'error');
    }).always(hideLoading);
}

// ── Render ────────────────────────────────────────────────────────────────────
function renderAll(year, month) {
    const days   = daysInMonth(year, month);
    const colors = [...new Set([...Object.keys(planData), ...Object.keys(actualData)])].sort();

    renderTable('plan',   colors, days, planData,   planData,   false);
    renderTable('actual', colors, days, actualData, planData,   false);
    renderDiff(colors, days);
}

function buildHead(days) {
    let h = '<th class="col-color">Color</th>';
    for (let d = 1; d <= days; d++) h += `<th>${String(d).padStart(2,'0')}</th>`;
    h += '<th class="total-col">TOTAL</th>';
    return h;
}

function renderTable(prefix, colors, days, data, planRef, isDiff) {
    const head = document.getElementById(prefix + '-head');
    const body = document.getElementById(prefix + '-body');
    const foot = document.getElementById(prefix + '-foot');

    head.innerHTML = buildHead(days);

    // Column totals
    const colTotals = Array(days + 1).fill(0);

    let bodyHtml = '';
    if (colors.length === 0) {
        bodyHtml = `<tr><td colspan="${days + 2}" class="text-muted" style="padding:20px;">Tidak ada data</td></tr>`;
    } else {
        colors.forEach(color => {
            const row   = data[color]   || {};
            let rowTotal = 0;
            let cells = '';
            for (let d = 1; d <= days; d++) {
                const val = row[d] || 0;
                rowTotal += val;
                colTotals[d - 1] += val;
                cells += isDiff
                    ? `<td class="${val > 0 ? 'diff-plus' : val < 0 ? 'diff-minus' : 'diff-zero'}">${val !== 0 ? val : ''}</td>`
                    : `<td>${val !== 0 ? val : ''}</td>`;
            }
            colTotals[days] += rowTotal;
            bodyHtml += `<tr>
                <td class="col-color">${esc(color)}</td>
                ${cells}
                <td class="total-col">${rowTotal !== 0 ? rowTotal : ''}</td>
            </tr>`;
        });
    }
    body.innerHTML = bodyHtml;

    // Footer totals
    let footHtml = '<td class="col-color">TOTAL</td>';
    for (let d = 1; d <= days; d++) footHtml += `<td>${colTotals[d-1] || ''}</td>`;
    footHtml += `<td class="total-col">${colTotals[days] || ''}</td>`;
    foot.innerHTML = footHtml;
}

function renderDiff(colors, days) {
    const allColors = [...new Set([...Object.keys(planData), ...Object.keys(actualData)])].sort();
    const head = document.getElementById('diff-head');
    const body = document.getElementById('diff-body');
    const foot = document.getElementById('diff-foot');

    head.innerHTML = buildHead(days);

    const colTotals = Array(days + 1).fill(0);
    let bodyHtml = '';

    if (allColors.length === 0) {
        bodyHtml = `<tr><td colspan="${days + 2}" class="text-muted" style="padding:20px;">Tidak ada data</td></tr>`;
    } else {
        allColors.forEach(color => {
            const plan   = planData[color]   || {};
            const actual = actualData[color] || {};
            let rowTotal = 0;
            let cells = '';
            for (let d = 1; d <= days; d++) {
                const diff = (actual[d] || 0) - (plan[d] || 0);
                rowTotal += diff;
                colTotals[d - 1] += diff;
                const cls = diff > 0 ? 'diff-plus' : diff < 0 ? 'diff-minus' : 'diff-zero';
                cells += `<td class="${cls}">${diff !== 0 ? diff : ''}</td>`;
            }
            colTotals[days] += rowTotal;
            const totCls = rowTotal > 0 ? 'diff-plus total-col' : rowTotal < 0 ? 'diff-minus total-col' : 'total-col diff-zero';
            bodyHtml += `<tr>
                <td class="col-color">${esc(color)}</td>
                ${cells}
                <td class="${totCls}">${rowTotal !== 0 ? rowTotal : ''}</td>
            </tr>`;
        });
    }
    body.innerHTML = bodyHtml;

    let footHtml = '<td class="col-color">TOTAL</td>';
    for (let d = 1; d <= days; d++) {
        const v = colTotals[d - 1];
        const cls = v > 0 ? 'diff-plus' : v < 0 ? 'diff-minus' : '';
        footHtml += `<td class="${cls}">${v || ''}</td>`;
    }
    const vt = colTotals[days];
    footHtml += `<td class="total-col ${vt > 0 ? 'diff-plus' : vt < 0 ? 'diff-minus' : ''}">${vt || ''}</td>`;
    foot.innerHTML = footHtml;
}

// ── Upload ────────────────────────────────────────────────────────────────────
function doUpload() {
    const period = $('#up-period').val();
    const model  = $('#up-model').val();
    const file   = $('#cc-file-input')[0].files[0];

    if (!period) { Swal.fire('Perhatian', 'Pilih periode terlebih dahulu', 'warning'); return; }
    if (!model)  { Swal.fire('Perhatian', 'Pilih model terlebih dahulu', 'warning'); return; }
    if (!file)   { Swal.fire('Perhatian', 'Pilih file Excel terlebih dahulu', 'warning'); return; }

    const [y, m] = period.split('-');
    const fd = new FormData();
    fd.append('upload-file', file);
    fd.append('month', m);
    fd.append('year',  y);
    fd.append('model', model);

    $('#modal-upload-cc').modal('hide');
    Swal.fire({ title: 'Mengupload...', html: 'Mohon tunggu...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url:         '<?=base_url("upload_color_control")?>',
        type:        'POST',
        data:        fd,
        processData: false,
        contentType: false,
        dataType:    'json',
        success: function (res) {
            if (res.status === 'ok') {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2000, showConfirmButton: false });
                $('#cc-period').val(period);
                loadModels(model);
            } else {
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Gagal koneksi ke server', 'error');
        }
    });
}

// ── Clear Period ──────────────────────────────────────────────────────────────
function clearPeriod() {
    const p = parsePeriod($('#cc-period').val());
    if (!p) { Swal.fire('Perhatian', 'Pilih periode terlebih dahulu', 'warning'); return; }

    Swal.fire({
        title: 'Hapus Data?',
        html: `Data plan Color Control periode <b>${String(p.month).padStart(2,'0')}/${p.year}</b> akan dihapus.`,
        iconHtml: '<i class="fas fa-trash" style="font-size:2.5rem;color:#dc3545;"></i>',
        iconColor: 'transparent',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
    }).then(result => {
        if (!result.isConfirmed) return;
        showLoading();
        $.post('<?=base_url("clear_color_control")?>', { month: p.month, year: p.year }, function (res) {
            if (res.status === 'ok') {
                planData = {};
                renderAll(p.year, p.month);
                Swal.fire({ icon: 'success', title: 'Terhapus', text: res.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        }, 'json').fail(() => Swal.fire('Error', 'Gagal koneksi ke server', 'error'))
          .always(hideLoading);
    });
}

function esc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
