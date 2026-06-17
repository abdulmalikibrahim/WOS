<script>
const KAP         = new URLSearchParams(window.location.search).get('kap') || 'kap1';
let allData       = [];
let dt            = null;
let selectedIds   = new Set();
let activeModel   = '';
let sapnikSet     = new Set();

const excelFilters = { suffix: new Set(), pdd: new Set(), color: new Set(), dest: new Set(), model: new Set() };
const excelValues  = { suffix: [], pdd: [], color: [], dest: [], model: [] };
const excelColMap  = {
    suffix : 'katashiki_suffix',
    pdd    : 'plan_delivery_date',
    color  : 'color_code',
    dest   : 'destination',
    model  : 'model'
};

// ── Init ─────────────────────────────────────────────────────────────────────
$(document).ready(function () {
    const now = new Date();
    $('#f-month').val(now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0'));
    $('#kap-badge').text(KAP.toUpperCase());
    initDataTable();
    loadData();
    $('#f-month').on('change', loadData);
});

// ── DataTable ─────────────────────────────────────────────────────────────────
function initDataTable() {
    dt = $('#pick-table').DataTable({
        data: [],
        columns: [
            { data: null, orderable: false, searchable: false, render: (d, t, r, m) => m.row + 1 },
            { data: 'id',  orderable: false, searchable: false, render: id => `<input type="checkbox" class="row-chk" value="${id}">` },
            { data: 'sapnik',             defaultContent: '-' },
            { data: 'katashiki_suffix',   defaultContent: '-' },
            { data: 'plan_delivery_date', defaultContent: '-' },
            { data: 'color_code',         defaultContent: '-' },
            { data: 'destination',        defaultContent: '-' },
            { data: 'model', defaultContent: '-', render: m => `<b style="color:#007bff;">${m || '-'}</b>` },
        ],
        orderCellsTop: true,
        pageLength: 50,
        lengthMenu: [[25, 50, 100, 200, -1], ['25', '50', '100', '200', 'Semua']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ total)',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' },
            zeroRecords: 'Tidak ada data yang cocok',
            emptyTable: 'Tidak ada data'
        },
        drawCallback: function () {
            const api = this.api();
            $('#pick-table .row-chk').each(function () {
                $(this).prop('checked', selectedIds.has(this.value));
            });
            syncCheckAll(api);
        }
    });

    // Custom filter: sapnik list + excel dropdowns
    $.fn.dataTable.ext.search.push(function (settings, searchData, index, rowData) {
        if (settings.nTable.id !== 'pick-table') return true;
        if (sapnikSet.size > 0 && !sapnikSet.has((rowData.sapnik || '').toUpperCase())) return false;
        for (const [key, field] of Object.entries(excelColMap)) {
            if (excelFilters[key].size > 0 && !excelFilters[key].has(rowData[field] || '')) return false;
        }
        return true;
    });

    // SAPNIK: Enter in modal textarea applies filter
    $('#fc-sapnik').on('keydown', function (e) {
        if (e.key === 'Enter' && e.ctrlKey) { e.preventDefault(); applySapnikModal(); }
    });

    // Checkbox delegation
    $('#pick-table tbody').on('change', '.row-chk', function () {
        if (this.checked) selectedIds.add(this.value);
        else              selectedIds.delete(this.value);
        updateSelectedCount();
        syncCheckAll();
    });

    // Row click toggles checkbox (ignore clicks directly on the checkbox itself)
    $('#pick-table tbody').on('click', 'tr', function (e) {
        if ($(e.target).is('.row-chk')) return;
        const chk = $(this).find('.row-chk');
        if (!chk.length) return;
        chk.prop('checked', !chk.prop('checked')).trigger('change');
    });
}

// ── Load data ─────────────────────────────────────────────────────────────────
function loadData() {
    const ym = $('#f-month').val();
    let params = 'already_process=0';
    if (ym) {
        const r = monthToRange(ym);
        params += `&start_date=${r.start}&end_date=${r.end}`;
    }

    selectedIds.clear();
    updateSelectedCount();

    // Reset all excel filters
    Object.keys(excelFilters).forEach(key => {
        excelFilters[key].clear();
        updateDropdownBtn(key);
        const panel = document.getElementById('dd-panel-' + key);
        if (panel) panel.style.display = 'none';
    });
    $('#fc-sapnik').val('');
    if (dt) dt.columns().search('').draw();

    $('#table-loading').css('display', 'flex');

    $.get(`<?=base_url("load_bank_vlt")?>?${params}`, function (res) {
        allData     = res.data || [];
        activeModel = '';

        // Build unique value lists for every excel dropdown
        Object.entries(excelColMap).forEach(([key, field]) => {
            excelValues[key] = [...new Set(allData.map(r => r[field] || '').filter(Boolean))].sort();
        });

        renderModelCards();
        if (dt) dt.clear().rows.add(allData).draw();
    }).fail(function () {
        if (dt) dt.clear().draw();
    }).always(function () {
        $('#table-loading').hide();
    });
}

// ── Excel dropdown ────────────────────────────────────────────────────────────
function toggleDropdown(event, key) {
    event.stopPropagation();
    const panel = document.getElementById('dd-panel-' + key);
    const btn   = document.getElementById('dd-btn-'   + key);
    const isOpen = panel.style.display !== 'none';

    document.querySelectorAll('.dt-dd-panel').forEach(p => p.style.display = 'none');

    if (!isOpen) {
        buildDropdownPanel(key);
        panel.style.display = 'block';
        const rect = btn.getBoundingClientRect();
        panel.style.top  = (rect.bottom + 3) + 'px';
        panel.style.left = rect.left + 'px';
    }
}

function buildDropdownPanel(key) {
    const vals       = excelValues[key];
    const selected   = excelFilters[key];
    const allChecked = selected.size === 0;

    const itemsHtml = vals.length
        ? vals.map(v => `<div class="dt-dd-item">
                <label style="cursor:pointer;margin:0;display:flex;align-items:center;gap:6px;width:100%;">
                    <input type="checkbox" class="dd-chk" value="${esc(v)}" ${allChecked || selected.has(v) ? 'checked' : ''}>
                    <span title="${esc(v)}">${esc(v)}</span>
                </label>
            </div>`).join('')
        : '<div class="text-muted py-2 text-center" style="font-size:8pt;">Tidak ada data</div>';

    document.getElementById('dd-panel-' + key).innerHTML = `
        <div class="dt-dd-search">
            <input type="text" class="form-control form-control-sm" placeholder="Cari nilai..." oninput="ddSearch(this,'${key}')">
        </div>
        <div class="dt-dd-select-all" onclick="ddClickSelectAll('${key}')">
            <input type="checkbox" id="dd-all-${key}" ${allChecked ? 'checked' : ''} onclick="event.stopPropagation(); ddToggleAll(this,'${key}')">
            <span>Pilih Semua</span>
        </div>
        <div class="dt-dd-list" id="dd-list-${key}">${itemsHtml}</div>
        <div class="dt-dd-footer">
            <button class="btn btn-sm btn-primary"          onclick="applyExcelFilter('${key}')">Terapkan</button>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetExcelFilter('${key}')">Reset</button>
        </div>`;

    document.getElementById('dd-panel-' + key).addEventListener('click', e => e.stopPropagation());
    document.getElementById('dd-list-'  + key).addEventListener('change', () => syncDdSelectAll(key));
}

function esc(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function ddSearch(input, key) {
    const q = input.value.toLowerCase();
    document.querySelectorAll('#dd-list-' + key + ' .dt-dd-item').forEach(item => {
        item.classList.toggle('dd-hidden', !item.querySelector('span').textContent.toLowerCase().includes(q));
    });
    syncDdSelectAll(key);
}

function ddClickSelectAll(key) {
    const chk = document.getElementById('dd-all-' + key);
    chk.checked = !chk.checked;
    ddToggleAll(chk, key);
}

function ddToggleAll(chk, key) {
    document.querySelectorAll('#dd-list-' + key + ' .dt-dd-item:not(.dd-hidden) .dd-chk').forEach(cb => {
        cb.checked = chk.checked;
    });
    syncDdSelectAll(key);
}

function syncDdSelectAll(key) {
    const all     = document.querySelectorAll('#dd-list-' + key + ' .dt-dd-item:not(.dd-hidden) .dd-chk');
    const checked = document.querySelectorAll('#dd-list-' + key + ' .dt-dd-item:not(.dd-hidden) .dd-chk:checked');
    const allChk  = document.getElementById('dd-all-' + key);
    if (!allChk) return;
    allChk.checked       = all.length > 0 && checked.length === all.length;
    allChk.indeterminate = checked.length > 0 && checked.length < all.length;
}

function applyExcelFilter(key) {
    const checked = [...document.querySelectorAll('#dd-list-' + key + ' .dd-chk:checked')];
    excelFilters[key].clear();
    if (checked.length < excelValues[key].length) {
        checked.forEach(cb => excelFilters[key].add(cb.value));
    }
    updateDropdownBtn(key);
    document.getElementById('dd-panel-' + key).style.display = 'none';
    if (dt) dt.draw();
}

function resetExcelFilter(key) {
    excelFilters[key].clear();
    updateDropdownBtn(key);
    document.getElementById('dd-panel-' + key).style.display = 'none';
    if (dt) dt.draw();
}

function updateDropdownBtn(key) {
    const btn = document.getElementById('dd-btn-' + key);
    if (!btn) return;
    const n = excelFilters[key].size;
    btn.innerHTML = n === 0
        ? `Semua <i class="fas fa-chevron-down fa-xs"></i>`
        : `${n} dipilih <i class="fas fa-filter fa-xs"></i>`;
    btn.classList.toggle('filtered', n > 0);
}

document.addEventListener('click', () => {
    document.querySelectorAll('.dt-dd-panel').forEach(p => p.style.display = 'none');
});

// ── Model cards ───────────────────────────────────────────────────────────────
function renderModelCards() {
    const counts = {};
    allData.forEach(r => { const m = r.model || 'N/A'; counts[m] = (counts[m] || 0) + 1; });

    let html = `<div class="mr-2 mb-2"><div class="model-card all-card ${activeModel === '' ? 'active' : ''}" onclick="filterByModel('')"><div class="mc-name">Semua</div><div class="mc-count">${allData.length} unit</div></div></div>`;
    Object.entries(counts).sort().forEach(([m, c]) => {
        html += `<div class="mr-2 mb-2"><div class="model-card ${activeModel === m ? 'active' : ''}" onclick="filterByModel('${m}')"><div class="mc-name">${m}</div><div class="mc-count">${c} unit</div></div></div>`;
    });
    $('#model-cards').html(html);
}

function filterByModel(model) {
    activeModel = model;
    renderModelCards();
    excelFilters.model.clear();
    if (model !== '') excelFilters.model.add(model);
    updateDropdownBtn('model');
    if (dt) dt.draw();
}

// ── Checkboxes ────────────────────────────────────────────────────────────────
function toggleAll(chk) {
    if (!dt) return;
    if (chk.checked) {
        dt.rows({ search: 'applied' }).data().each(r => selectedIds.add(String(r.id)));
    } else {
        dt.rows({ search: 'applied' }).data().each(r => selectedIds.delete(String(r.id)));
    }
    $('#pick-table .row-chk').each(function () { $(this).prop('checked', selectedIds.has(this.value)); });
    updateSelectedCount();
}

function syncCheckAll(api) {
    const _api = api || dt;
    if (!_api) return;
    const ids    = _api.rows({ search: 'applied' }).data().map(r => String(r.id)).toArray();
    const chkAll = document.getElementById('chk-all');
    if (!chkAll) return;
    const allC  = ids.length > 0 && ids.every(id => selectedIds.has(id));
    const someC = ids.some(id => selectedIds.has(id));
    chkAll.checked       = allC;
    chkAll.indeterminate = !allC && someC;
}

function updateSelectedCount() {
    const n = selectedIds.size;
    $('#selected-info').text(`${n} terpilih`);
    $('#btn-gunakan').prop('disabled', n === 0);
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function monthToRange(ym) {
    const [y, m] = ym.split('-').map(Number);
    const last   = new Date(y, m, 0).getDate();
    return { start: `${y}-${String(m).padStart(2,'0')}-01`, end: `${y}-${String(m).padStart(2,'0')}-${String(last).padStart(2,'0')}` };
}

function resetFilter() {
    const now = new Date();
    $('#f-month').val(now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0'));
    clearSapnikFilter();
    loadData();
}

function openSapnikModal() {
    $('#modal-sapnik').modal('show');
    setTimeout(() => $('#fc-sapnik').focus(), 400);
}

function applySapnikModal() {
    const lines = $('#fc-sapnik').val().split(/[\n\r,]+/).map(s => s.trim().toUpperCase()).filter(Boolean);
    sapnikSet = new Set(lines);
    updateSapnikCount();
    if (dt) dt.draw();
    $('#modal-sapnik').modal('hide');
}

function clearSapnikFilter() {
    $('#fc-sapnik').val('');
    sapnikSet = new Set();
    updateSapnikCount();
    if (dt) dt.draw();
}

function updateSapnikCount() {
    const n = sapnikSet.size;
    $('#sapnik-count').text(n > 0 ? n + ' VIN aktif' : '');
    if (n > 0) {
        $('#sapnik-badge').text(n).show();
        $('#btn-sapnik-filter').removeClass('btn-outline-primary').addClass('btn-primary');
    } else {
        $('#sapnik-badge').hide();
        $('#btn-sapnik-filter').removeClass('btn-primary').addClass('btn-outline-primary');
    }
}

// ── Gunakan Data ──────────────────────────────────────────────────────────────
function gunakanData() {
    const ids = [...selectedIds];
    if (ids.length === 0) return;

    Swal.fire({
        title: '<div style="margin-bottom: 15px;"><i class="fas fa-question-circle" style="font-size: 4.5rem; color: #1976D2;"></i></div>' +
               '<span style="color:#2c3e50; font-size:1.4rem;">Konfirmasi Penggunaan Data</span>',
        html: '<div style="font-size: 0.95rem; color: #444; line-height: 1.5; margin-top: 10px;">' +
              'Anda akan menyalin sebanyak <b><span style="font-size: 1.15rem; color: #1976D2;">' + ids.length + ' data</span></b> ' +
              'dari Bank VLT ke Tabungan VLT <b>' + KAP.toUpperCase() + '</b>.<br><br>' +
              '<div style="background: #fff3cd; color: #856404; padding: 12px; border-radius: 6px; border-left: 4px solid #ffc107; font-size: 0.85rem; text-align: left;">' +
              '<i class="fas fa-exclamation-triangle"></i> <b>Perhatian:</b> Data tabungan sebelumnya pada <b>' + KAP.toUpperCase() + '</b> akan ditimpa dengan data ini.' +
              '</div></div>',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check"></i> Ya, Gunakan',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        confirmButtonColor: '#1976D2',
        cancelButtonColor: '#858796',
        reverseButtons: true,
        width: '480px'
    }).then(result => {
        if (!result.isConfirmed) return;
        Swal.fire({ title: 'Memproses...', html: 'Mohon tunggu...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.post('<?=base_url("use_bank_vlt")?>', { ids: ids, kap: KAP }, function (res) {
            if (res.status === 'ok') {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2000, showConfirmButton: false })
                    .then(() => { window.location.href = '<?=base_url("docking_wos_dummy")?>'; });
            } else {
                Swal.fire('Gagal', res.message || 'Terjadi kesalahan', 'error');
            }
        }, 'json').fail(() => Swal.fire('Error', 'Gagal koneksi ke server', 'error'));
    });
}
</script>
