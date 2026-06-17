<script>
/* ============================================================
   BANK VLT – JavaScript Logic
   ============================================================ */

var allData       = [];   // master data dari server
var filteredData  = [];   // setelah column filter
var sortColIndex  = -1;
var sortDir       = 'asc';

/* Filter dropdown (excel-like) per kolom — key = index kolom */
var colFilters    = {};   // colIndex -> Set nilai yang dipilih (kosong = semua)

/* Pagination (client-side) */
var pageSize      = 10;   // -1 = tampilkan semua
var currentPage   = 1;

/* Kolom field sesuai urutan thead (index 0-27 + aksi) */
var FIELDS = [
    'no',                    // 0
    'wos_material',          // 1
    'wos_material_description', // 2
    'sapnik',                // 3
    'sap_material',          // 4
    'engine_model',          // 5
    'engine_prefix',         // 6
    'engine_number',         // 7
    'plant_excel',           // 8
    'chassis_number',        // 9
    'lot_code',              // 10
    'lot_number',            // 11
    'katashiki',             // 12
    'katashiki_suffix',      // 13
    'adm_production_id',     // 14
    'tam_production_id',     // 15
    'plan_delivery_date',    // 16
    'plan_jig_in_date',      // 17
    'wos_release_date',      // 18
    'sapwos_des',            // 19
    'location',              // 20
    'color_code',            // 21
    'ed',                    // 22
    'order_column',          // 23
    'destination',           // 24
    'model',                 // 25
    'already_process_label', // 26
    'plant',                 // 27
    'pdd_proses',            // 28
];

/* ──────────────────────────────────────
   INIT
────────────────────────────────────── */
$(document).ready(function () {
    /* default bulan berjalan */
    var now = new Date();
    var y   = now.getFullYear();
    var m   = String(now.getMonth() + 1).padStart(2, '0');
    $('#f-month').val(y + '-' + m);

    loadSummary();
    loadData();
    initUploadZone();
});

/* ──────────────────────────────────────
   LOAD SUMMARY CARDS
────────────────────────────────────── */
function loadSummary() {
    var ym     = $('#f-month').val();
    var parts  = ym ? ym.split('-') : [];
    var params = parts.length === 2 ? { year: parts[0], month: parts[1] } : {};

    $.ajax({
        url: '<?= base_url("get_summary_bank_vlt") ?>',
        type: 'GET',
        data: params,
        dataType: 'json',
        timeout: 15000, /* batas maksimal 15 detik */
        success: function (r) {
            $('#card-total').text(r.total.toLocaleString());
            $('#card-belum').text(r.belum_proses.toLocaleString());
            $('#card-sudah').text(r.sudah_proses.toLocaleString());

            var modelHtml = '';
            $.each(r.per_model, function (i, m) {
                modelHtml += '<div class="card shadow-sm mb-2 card-filter-target" style="cursor:pointer; min-width: 170px; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; transition: all 0.2s ease-in-out;" onmouseover="this.style.transform=\'translateY(-3px)\'; this.style.boxShadow=\'0 6px 12px rgba(0,0,0,0.15)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 4px rgba(0,0,0,0.05)\';" onclick="filterByModelCard(\'' + escHtml(m.model) + '\', \'all\', this)">' +
                    '<div class="card-header text-center font-weight-bold text-light" style="font-size: 1.1rem; padding: 8px; background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); border-bottom: none;">' + escHtml(m.model) + '</div>' +
                    '<div class="card-body p-0">' +
                        '<table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem; border-left: none; border-right: none;">' +
                            '<thead style="background-color: #f8f9fa;">' +
                                '<tr class="text-center">' +
                                    '<th style="font-weight: 600; color: #555; border-top: none; border-left: none;">Plan</th>' +
                                    '<th style="font-weight: 600; color: #555; border-top: none;">Add.</th>' +
                                    '<th style="font-weight: 600; color: #555; border-top: none; border-right: none;">Total</th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>' +
                                '<tr class="text-center">' +
                                    '<td style="border-left: none; font-weight:600; color:#117a8b;">' + Number(m.plan_mdp || 0).toLocaleString() + '</td>' +
                                    '<td style="font-weight:600; color:#117a8b;">' + Number(m.add_mdp || 0).toLocaleString() + '</td>' +
                                    '<td style="border-right: none; font-weight:700; color:#0a5460;">' + Number(m.total_mdp || 0).toLocaleString() + '</td>' +
                                '</tr>' +
                            '</tbody>' +
                        '</table>' +
                        '<div style="padding: 12px 0; background-color: #ffffff; border-bottom: 1px solid #dee2e6;">' +
                            '<div class="text-center" style="font-size: 0.85rem; font-weight: 700; color: #17a2b8; text-transform: uppercase; letter-spacing: 0.5px;">Actual Receive</div>' +
                            '<div class="text-center" style="font-size: 1.7rem; font-weight: 800; color: #117a8b; line-height: 1.1; margin-top: 4px;">' + m.total.toLocaleString() + '</div>' +
                            '<div class="text-right pr-2" style="font-weight: 800; color: #117a8b; line-height: 1.1; margin-top: 4px;">0%</div>' +
                        '</div>' +
                        '<table class="table table-sm table-bordered mb-0" style="font-size: 0.95rem; border: none;">' +
                            '<thead style="background-color: #f8f9fa;">' +
                                '<tr class="text-center">' +
                                    '<th style="width: 50%; border-left: none; border-top: none;"><i class="fas fa-check-circle text-success" style="font-size:16pt;"></i></th>' +
                                    '<th style="width: 50%; border-right: none; border-top: none;"><i class="fas fa-times-circle text-danger" style="font-size:16pt;"></i></th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>' +
                                '<tr class="text-center">' +
                                    '<td onclick="event.stopPropagation(); filterByModelCard(\'' + escHtml(m.model) + '\', \'1\', this)" style="cursor:pointer; font-weight: bold; color: #28a745; border-left: none; border-bottom: none;" title="Sudah Diproses" onmouseover="this.style.backgroundColor=\'#e8f5e9\'" onmouseout="this.style.backgroundColor=\'transparent\'">' + m.sudah_proses.toLocaleString() + '</td>' +
                                    '<td onclick="event.stopPropagation(); filterByModelCard(\'' + escHtml(m.model) + '\', \'0\', this)" style="cursor:pointer; font-weight: bold; color: #dc3545; border-right: none; border-bottom: none;" title="Belum Diproses" onmouseover="this.style.backgroundColor=\'#ffebee\'" onmouseout="this.style.backgroundColor=\'transparent\'">' + m.belum_proses.toLocaleString() + '</td>' +
                                '</tr>' +
                            '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>';
            });
            $('#model-cards').html(modelHtml || '<span style="color:#aaa;font-size:.8rem;">Belum ada data model</span>');
        },
        error: function () {}
    });
}

/* ──────────────────────────────────────
   FILTERING LOGIC VIA CARDS
────────────────────────────────────── */
var activeModelFilter = null;
var activeStatusFilter = null;

function applyCardFilters() {
    /* gabungkan filter kartu (model/status) dengan filter dropdown kolom */
    applyAllFilters();
}

function filterByModelCard(model, status, el) {
    $('.card-filter-target, .model-active').removeClass('model-active card-active');
    
    // add active class
    if (el) {
        var targetCard = $(el).closest('.card');
        targetCard.addClass('model-active');
    }
    
    activeModelFilter = model;
    activeStatusFilter = status;
    applyCardFilters();
}

function filterBySummary(status, el) {
    $('.card-filter-target, .model-active').removeClass('model-active card-active');
    if (el) $(el).addClass('card-active');
    
    activeModelFilter = 'all';
    activeStatusFilter = status;
    applyCardFilters();
}

function clearActiveFilters() {
    $('.card-filter-target, .model-active').removeClass('model-active card-active');
    activeModelFilter = null;
    activeStatusFilter = null;

    /* reset semua filter dropdown kolom */
    resetAllColFilters();

    $('#btn-clear-filter').hide();
    applyAllFilters();
}

/* ──────────────────────────────────────
   LOAD DATA (server filter: bulan + status)
────────────────────────────────────── */
function monthToRange(ym) {
    if (!ym) return { start: '', end: '' };
    var parts = ym.split('-');
    var y = parseInt(parts[0]);
    var m = parseInt(parts[1]);
    var lastDay = new Date(y, m, 0).getDate();
    var pad = function(n){ return String(n).padStart(2,'0'); };
    return {
        start: y + '-' + pad(m) + '-01',
        end:   y + '-' + pad(m) + '-' + lastDay,
    };
}

$('#f-status').val("0");
function loadData() {
    var range  = monthToRange($('#f-month').val());
    var params = {
        start_date:      range.start,
        end_date:        range.end,
        already_process: $('#f-status').val(),
    };

    $('#bvlt-tbody').html(
        '<tr><td colspan="29"><div class="bvlt-loader"><i class="fas fa-circle-notch spin"></i> Memuat data...</div></td></tr>'
    );

    $.ajax({
        url: '<?= base_url("load_bank_vlt") ?>',
        type: 'GET',
        data: params,
        dataType: 'json',
        timeout: 15000, /* batas maksimal 15 detik, lebih dari itu otomatis timeout */
        success: function (r) {
            if (r.status === 'success') {
                /* tambahkan no urut dan label */
                allData = r.data.map(function (d, i) {
                    d.no                    = i + 1;
                    d.already_process_label = d.already_process == 1 ? 'Sudah Proses' : 'Belum Proses';
                    return d;
                });
                /* reset filter dropdown per kolom (daftar nilai dihitung dinamis saat dibuka) */
                resetAllColFilters();
                applyAllFilters();
            }
        },
        error: function (xhr, textStatus) {
            var msg = textStatus === 'timeout'
                ? 'Waktu muat data melebihi 15 detik (timeout). Silakan coba lagi atau persempit rentang bulan.'
                : 'Gagal memuat data';
            $('#bvlt-tbody').html(
                '<tr><td colspan="29"><div class="bvlt-loader" style="color:red"><i class="fas fa-exclamation-triangle"></i> ' + msg + '</div></td></tr>'
            );
        }
    });
}

/* ──────────────────────────────────────
   RENDER TABLE  (inline-edit support)
────────────────────────────────────── */
var editingRowId = null;

/* Helper: buat <td> yang bisa di-inline-edit */
function tde(field, val, type) {
    var v = (val === null || val === undefined) ? '' : String(val);
    var shown;
    if (type === 'status') {
        shown = v == 1
            ? '<span class="badge-proses">&#10003; Already</span>'
            : '<span class="badge-belum">&#9203; Not Yet</span>';
    } else if (field === 'model') {
        shown = v ? '<strong style="color:#1565C0">' + escHtml(v) + '</strong>' : '';
    } else {
        shown = escHtml(v);
    }
    return '<td data-field="' + field + '" data-val="' + escHtml(v) + '" data-type="' + type + '">' + shown + '</td>';
}

function renderTable() {
    if (filteredData.length === 0) {
        $('#bvlt-tbody').html(
            '<tr><td colspan="29"><div class="empty-state"><i class="fas fa-inbox"></i><br>Tidak ada data</div></td></tr>'
        );
        updateCounter();
        renderPagination();
        return;
    }

    /* hitung slice halaman saat ini */
    var totalPages = (pageSize === -1) ? 1 : Math.max(1, Math.ceil(filteredData.length / pageSize));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1)          currentPage = 1;

    var startIdx = (pageSize === -1) ? 0 : (currentPage - 1) * pageSize;
    var pageRows = (pageSize === -1) ? filteredData : filteredData.slice(startIdx, startIdx + pageSize);

    var html = '';
    $.each(pageRows, function (i, d) {
        var rowClass = d.already_process == 1 ? 'row-done' : '';

        html += '<tr class="' + rowClass + '" data-id="' + d.id + '">' +
            '<td>' + (startIdx + i + 1) + '</td>' +
            tde('wos_material',       d.wos_material,       'text') +
            '<td class="td-left" style="white-space:normal;max-width:220px;">' + escHtml(d.wos_material_description || '') + '</td>' +
            tde('sapnik',             d.sapnik,             'text') +
            tde('sap_material',       d.sap_material,       'text') +
            tde('engine_model',       d.engine_model,       'text') +
            tde('engine_prefix',      d.engine_prefix,      'text') +
            tde('engine_number',      d.engine_number,      'text') +
            tde('plant_excel',        d.plant_excel,        'text') +
            tde('chassis_number',     d.chassis_number,     'text') +
            tde('lot_code',           d.lot_code,           'text') +
            tde('lot_number',         d.lot_number,         'text') +
            tde('katashiki',          d.katashiki,          'text') +
            tde('katashiki_suffix',   d.katashiki_suffix,   'text') +
            tde('adm_production_id',  d.adm_production_id,  'text') +
            tde('tam_production_id',  d.tam_production_id,  'text') +
            tde('plan_delivery_date', d.plan_delivery_date, 'date') +
            tde('plan_jig_in_date',   d.plan_jig_in_date,   'date') +
            tde('wos_release_date',   d.wos_release_date,   'date') +
            tde('sapwos_des',         d.sapwos_des,         'text') +
            tde('location',           d.location,           'text') +
            tde('color_code',         d.color_code,         'text') +
            tde('ed',                 d.ed,                 'text') +
            tde('order_column',       d.order_column,       'text') +
            tde('destination',        d.destination,        'text') +
            tde('model',              d.model,              'text') +
            '<td>' + (d.already_process == 1 ? '<span class="badge-proses">&#10003; Already</span>' : '<span class="badge-belum">&#9203; Not Yet</span>') + '</td>' +
            '<td>KAP ' + escHtml(d.plant || '') + '</td>' +
            '<td>' + escHtml(d.pdd_proses || '') + '</td>' +
            '<td class="td-action"><button class="btn-edit-row" onclick="editRow(' + d.id + ')"><i class="fas fa-edit"></i> Edit</button><button class="btn-reason-row ml-2" onclick="reasonChange(' + d.id + ')"><i class="fas fa-history"></i> Reason Changes</button></td>' +
            '</tr>';
    });

    $('#bvlt-tbody').html(html);
    updateCounter();
    renderPagination();
}

function updateCounter() {
    var total = filteredData.length;
    var info;
    if (total === 0) {
        info = '0';
    } else if (pageSize === -1) {
        info = '1–' + total;
    } else {
        var startIdx = (currentPage - 1) * pageSize;
        info = (startIdx + 1) + '–' + Math.min(startIdx + pageSize, total);
    }
    $('#page-info').text(info);
    $('#cnt-shown').text(total.toLocaleString());
    $('#cnt-total').text(allData.length.toLocaleString());
}

/* ──────────────────────────────────────
   PAGINATION
────────────────────────────────────── */
function renderPagination() {
    var $pg = $('#bvlt-pagination');
    if (!$pg.length) return;

    var total      = filteredData.length;
    var totalPages = (pageSize === -1) ? 1 : Math.max(1, Math.ceil(total / pageSize));

    if (totalPages <= 1) { $pg.empty(); return; }

    function item(label, page, disabled, active) {
        var cls = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
        var onclick = (disabled || active) ? '' : ' onclick="goToPage(' + page + ')"';
        return '<li class="' + cls + '"><a class="page-link" href="javascript:void(0)"' + onclick + '>' + label + '</a></li>';
    }

    /* jendela nomor halaman: maksimal 5 sekitar halaman aktif */
    var win   = 2;
    var first = Math.max(1, currentPage - win);
    var last  = Math.min(totalPages, currentPage + win);

    var html = item('«', 1, currentPage === 1, false) +
               item('‹', currentPage - 1, currentPage === 1, false);

    if (first > 1) {
        html += item('1', 1, false, false);
        if (first > 2) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }
    for (var p = first; p <= last; p++) {
        html += item(p, p, false, p === currentPage);
    }
    if (last < totalPages) {
        if (last < totalPages - 1) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
        html += item(totalPages, totalPages, false, false);
    }

    html += item('›', currentPage + 1, currentPage === totalPages, false) +
            item('»', totalPages, currentPage === totalPages, false);

    $pg.html(html);
}

function goToPage(p) {
    currentPage = p;
    renderTable();
}

function changePageSize(val) {
    pageSize    = parseInt(val, 10);
    currentPage = 1;
    renderTable();
}

function reasonChange(id) {
    $.ajax({
        url: '<?= base_url("get_reason_bank_vlt") ?>',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        beforeSend: function () {
            swal.fire({ title: 'Memuat...', allowOutsideClick: false, didOpen: function () { swal.showLoading(); } });
        },
        success: function (r) {
            if (r.status === 'success') {
                var d = r.data;
                var reason = d.reason ? escHtml(d.reason) : '<i>Belum ada alasan</i>';
                var pic = d.pic ? escHtml(d.pic) : '-';
                var last_updated = d.last_updated ? d.last_updated : '-';

                var html = '<div style="text-align:left; font-size:0.9rem;">' +
                           '<table style="width:100%; border-collapse: collapse; margin-top: 15px;">' +
                           '<tr><th style="width:35%; background:#f0f4f8; padding:8px; border:1px solid #ddd;">PIC</th><td style="padding:8px; border:1px solid #ddd;">' + pic + '</td></tr>' +
                           '<tr><th style="background:#f0f4f8; padding:8px; border:1px solid #ddd;">Reason</th><td style="padding:8px; border:1px solid #ddd;">' + reason + '</td></tr>' +
                           '<tr><th style="background:#f0f4f8; padding:8px; border:1px solid #ddd;">Last Updated</th><td style="padding:8px; border:1px solid #ddd;">' + last_updated + '</td></tr>' +
                           '</table></div>';

                swal.fire({
                    title: 'Reason Changes',
                    html: html,
                    confirmButtonText: '<i class="fas fa-times"></i> Tutup',
                    confirmButtonColor: '#1565C0',
                });
            } else {
                swal.fire('Gagal', r.msg || 'Terjadi kesalahan', 'error');
            }
        },
        error: function () {
            swal.fire('Error', 'Gagal memuat data', 'error');
        }
    });
}
/* ──────────────────────────────────────
   COLUMN FILTER (excel-like checkbox dropdown)
────────────────────────────────────── */
var FILTER_COLS_MIN = 1, FILTER_COLS_MAX = 28;   // kolom yang punya filter

/* true jika baris `d` lolos semua filter KECUALI filter kolom `exceptCol`.
   exceptCol = -1 berarti memeriksa semua filter (tanpa pengecualian). */
function rowPassesExcept(d, exceptCol) {
    /* filter kartu: model */
    if (activeModelFilter !== null && activeModelFilter !== 'all' && d.model !== activeModelFilter) return false;
    /* filter kartu: status */
    if (activeStatusFilter !== null && activeStatusFilter !== 'all' &&
        String(d.already_process) !== String(activeStatusFilter)) return false;
    /* filter dropdown kolom (lewati kolom yang dikecualikan) */
    for (var col in colFilters) {
        if (parseInt(col, 10) === exceptCol) continue;
        var set = colFilters[col];
        if (set && set.size > 0) {
            var field = FIELDS[col];
            var v = (d[field] === null || d[field] === undefined) ? '' : String(d[field]);
            if (!set.has(v)) return false;
        }
    }
    return true;
}

/* Terapkan SEMUA filter (kartu model/status + dropdown kolom) → filteredData */
function applyAllFilters() {
    filteredData = allData.filter(function (d) { return rowPassesExcept(d, -1); });

    /* tampilkan tombol Clear Filter bila ada filter aktif */
    var anyCol = Object.keys(colFilters).some(function (c) { return colFilters[c] && colFilters[c].size > 0; });
    if (activeModelFilter !== null || activeStatusFilter !== null || anyCol) {
        $('#btn-clear-filter').show();
    } else {
        $('#btn-clear-filter').hide();
    }

    if (sortColIndex >= 0) doSort(sortColIndex, sortDir, false);
    currentPage = 1;
    renderTable();
}
/* alias kompatibilitas */
function applyColFilter() { applyAllFilters(); }

/* Hitung daftar nilai unik untuk kolom `col` berdasarkan data yang sudah
   difilter oleh SEMUA filter lain (kartu + kolom lain), kecuali filter kolom
   itu sendiri → daftar dropdown jadi bertingkat (cascading) seperti Excel. */
function valuesForColumn(col) {
    var field = FIELDS[col];
    var seen  = {}, arr = [];
    for (var i = 0; i < allData.length; i++) {
        var d = allData[i];
        if (!rowPassesExcept(d, col)) continue;
        var raw = d[field];
        var v   = (raw === null || raw === undefined) ? '' : String(raw);
        if (v !== '' && !seen[v]) { seen[v] = 1; arr.push(v); }
    }
    arr.sort(function (a, b) {
        var na = parseFloat(a), nb = parseFloat(b);
        if (!isNaN(na) && !isNaN(nb) && a !== '' && b !== '') return na - nb;
        return a < b ? -1 : (a > b ? 1 : 0);
    });
    return arr;
}

/* Reset semua filter dropdown + tombolnya */
function resetAllColFilters() {
    colFilters = {};
    for (var c = FILTER_COLS_MIN; c <= FILTER_COLS_MAX; c++) {
        colFilters[c] = new Set();
        updateDropdownBtn(c);
    }
    document.querySelectorAll('.dt-dd-panel').forEach(function (p) { p.style.display = 'none'; });
}

/* Buka/tutup panel dropdown */
function toggleDropdown(event, col) {
    event.stopPropagation();
    var panel = document.getElementById('dd-panel-' + col);
    var btn   = document.getElementById('dd-btn-'   + col);
    var isOpen = panel.style.display !== 'none';

    document.querySelectorAll('.dt-dd-panel').forEach(function (p) { p.style.display = 'none'; });

    if (!isOpen) {
        buildDropdownPanel(col);
        panel.style.display = 'block';
        var rect   = btn.getBoundingClientRect();
        var panelW = 240;
        var left   = rect.left;
        if (left + panelW > window.innerWidth) left = window.innerWidth - panelW - 12;
        if (left < 4) left = 4;
        panel.style.top  = (rect.bottom + 3) + 'px';
        panel.style.left = left + 'px';
    }
}

function buildDropdownPanel(col) {
    var vals       = valuesForColumn(col);
    var selected   = colFilters[col] || new Set();
    var allChecked = selected.size === 0;

    var itemsHtml = vals.length
        ? vals.map(function (v) {
            var checked = (allChecked || selected.has(v)) ? 'checked' : '';
            return '<div class="dt-dd-item"><label style="cursor:pointer;margin:0;display:flex;align-items:center;gap:6px;width:100%;">' +
                   '<input type="checkbox" class="dd-chk" value="' + escHtml(v) + '" ' + checked + '>' +
                   '<span title="' + escHtml(v) + '">' + escHtml(v) + '</span></label></div>';
          }).join('')
        : '<div class="text-muted py-2 text-center" style="font-size:8pt;">Tidak ada data</div>';

    var panel = document.getElementById('dd-panel-' + col);
    panel.innerHTML =
        '<div class="dt-dd-search"><input type="text" class="form-control form-control-sm" placeholder="Cari nilai..." oninput="ddSearch(this,' + col + ')"></div>' +
        '<div class="dt-dd-select-all" onclick="ddClickSelectAll(' + col + ')"><input type="checkbox" id="dd-all-' + col + '" ' + (allChecked ? 'checked' : '') + ' onclick="event.stopPropagation(); ddToggleAll(this,' + col + ')"><span>Pilih Semua</span></div>' +
        '<div class="dt-dd-list" id="dd-list-' + col + '">' + itemsHtml + '</div>' +
        '<div class="dt-dd-footer"><button class="btn btn-sm btn-primary" onclick="applyExcelFilter(' + col + ')">Terapkan</button><button class="btn btn-sm btn-outline-secondary" onclick="resetExcelFilter(' + col + ')">Reset</button></div>';

    panel.addEventListener('click', function (e) { e.stopPropagation(); });
    document.getElementById('dd-list-' + col).addEventListener('change', function () { syncDdSelectAll(col); });
}

function ddSearch(input, col) {
    var q = input.value.toLowerCase();
    document.querySelectorAll('#dd-list-' + col + ' .dt-dd-item').forEach(function (item) {
        item.classList.toggle('dd-hidden', item.querySelector('span').textContent.toLowerCase().indexOf(q) === -1);
    });
    syncDdSelectAll(col);
}

function ddClickSelectAll(col) {
    var chk = document.getElementById('dd-all-' + col);
    chk.checked = !chk.checked;
    ddToggleAll(chk, col);
}

function ddToggleAll(chk, col) {
    document.querySelectorAll('#dd-list-' + col + ' .dt-dd-item:not(.dd-hidden) .dd-chk').forEach(function (cb) {
        cb.checked = chk.checked;
    });
    syncDdSelectAll(col);
}

function syncDdSelectAll(col) {
    var all     = document.querySelectorAll('#dd-list-' + col + ' .dt-dd-item:not(.dd-hidden) .dd-chk');
    var checked = document.querySelectorAll('#dd-list-' + col + ' .dt-dd-item:not(.dd-hidden) .dd-chk:checked');
    var allChk  = document.getElementById('dd-all-' + col);
    if (!allChk) return;
    allChk.checked       = all.length > 0 && checked.length === all.length;
    allChk.indeterminate = checked.length > 0 && checked.length < all.length;
}

function applyExcelFilter(col) {
    var checked  = Array.prototype.slice.call(document.querySelectorAll('#dd-list-' + col + ' .dd-chk:checked'));
    var totalChk = document.querySelectorAll('#dd-list-' + col + ' .dd-chk').length;
    colFilters[col] = new Set();
    /* kalau semua nilai yang tampil tercentang → anggap "tanpa filter" */
    if (checked.length < totalChk) {
        checked.forEach(function (cb) { colFilters[col].add(cb.value); });
    }
    updateDropdownBtn(col);
    document.getElementById('dd-panel-' + col).style.display = 'none';
    applyAllFilters();
}

function resetExcelFilter(col) {
    colFilters[col] = new Set();
    updateDropdownBtn(col);
    document.getElementById('dd-panel-' + col).style.display = 'none';
    applyAllFilters();
}

function updateDropdownBtn(col) {
    var btn = document.getElementById('dd-btn-' + col);
    if (!btn) return;
    var n = (colFilters[col] && colFilters[col].size) || 0;
    btn.innerHTML = n === 0
        ? 'Semua <i class="fas fa-chevron-down fa-xs"></i>'
        : n + ' dipilih <i class="fas fa-filter fa-xs"></i>';
    btn.classList.toggle('filtered', n > 0);
}

/* klik di luar panel → tutup semua */
document.addEventListener('click', function () {
    document.querySelectorAll('.dt-dd-panel').forEach(function (p) { p.style.display = 'none'; });
});

/* ──────────────────────────────────────
   SORT
────────────────────────────────────── */
function sortTable(colIdx) {
    /* toggle direction if same column */
    if (sortColIndex === colIdx) {
        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
    } else {
        sortColIndex = colIdx;
        sortDir      = 'asc';
    }

    /* update header classes */
    $('#header-row th').removeClass('asc desc');
    $('#header-row th[data-col="' + colIdx + '"]').addClass(sortDir);

    doSort(colIdx, sortDir, true);
    currentPage = 1;
    renderTable();
}

function doSort(colIdx, dir, reRender) {
    var field = FIELDS[colIdx];
    filteredData.sort(function (a, b) {
        var va = (a[field] || '').toString().toLowerCase();
        var vb = (b[field] || '').toString().toLowerCase();
        /* numeric comparison for purely numeric fields */
        if (!isNaN(va) && !isNaN(vb) && va !== '' && vb !== '') {
            va = parseFloat(va);
            vb = parseFloat(vb);
            return dir === 'asc' ? va - vb : vb - va;
        }
        if (va < vb) return dir === 'asc' ? -1 : 1;
        if (va > vb) return dir === 'asc' ?  1 : -1;
        return 0;
    });
}

/* ──────────────────────────────────────
   RESET FILTER
────────────────────────────────────── */
function resetFilter() {
    var now = new Date();
    var y   = now.getFullYear();
    var m   = String(now.getMonth() + 1).padStart(2, '0');
    $('#f-month').val(y + '-' + m);
    $('#f-status').val('');
    resetAllColFilters();
    sortColIndex = -1;
    sortDir      = 'asc';
    $('#header-row th').removeClass('asc desc');
    loadData();
}

/* ──────────────────────────────────────
   INLINE EDIT
────────────────────────────────────── */
function editRow(id) {
    /* batalkan edit baris lain kalau ada */
    if (editingRowId !== null && editingRowId !== id) {
        cancelEdit();
    }
    editingRowId = id;

    var $row = $('tr[data-id="' + id + '"]');
    $row.addClass('editing');

    /* ubah tiap td yang punya data-field menjadi input */
    $row.find('td[data-field]').each(function () {
        var field = $(this).data('field');
        var val   = $(this).data('val');
        var type  = $(this).data('type');
        var inp;

        if (type === 'status') {
            inp = '<select class="inline-input" data-field="' + field + '">' +
                '<option value="0"' + (val == 0 ? ' selected' : '') + '>Belum Proses</option>' +
                '<option value="1"' + (val == 1 ? ' selected' : '') + '>Sudah Proses</option>' +
                '</select>';
        } else {
            inp = '<input type="' + type + '" class="inline-input" data-field="' + field + '" value="' + escHtml(val) + '">';
        }
        $(this).html(inp);
    });

    /* ganti tombol Edit → Batal */
    $row.find('.td-action').html(
        '<button class="btn-cancel-row" onclick="cancelEdit()"><i class="fas fa-times"></i> Batal</button>'
    );

    /* Enter di mana saja dalam baris → tampilkan modal reason & pic */
    $row.find('.inline-input').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            showReasonModal();
        }
    });
}

function cancelEdit() {
    editingRowId = null;
    renderTable();
}

function collectEditData() {
    var $row = $('tr[data-id="' + editingRowId + '"]');
    var data = { id: editingRowId };
    $row.find('.inline-input').each(function () {
        data[$(this).data('field')] = $(this).val();
    });
    return data;
}

function showReasonModal() {
    swal.fire({
        title: 'Konfirmasi Perubahan',
        html: '<div style="text-align:left;margin-top:4px" class="p-2">' +
              '<label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:4px">Reason <span style="color:red">*</span></label>' +
              '<input id="swal-reason" class="swal2-input" style="width:95% !important; margin:0 0 14px;font-size:.85rem" placeholder="Masukkan alasan perubahan...">' +
              '<label style="font-size:.82rem;font-weight:600;color:#444;display:block;margin-bottom:4px">PIC <span style="color:red">*</span></label>' +
              '<input id="swal-pic" class="swal2-input" style="width:95% !important; margin:0;font-size:.85rem" placeholder="Nama penanggung jawab...">' +
              '</div>',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save"></i> Simpan',
        cancelButtonText:  '<i class="fas fa-times"></i> Batal',
        confirmButtonColor: '#1565C0',
        cancelButtonColor:  '#9E9E9E',
        width: '420px',
        didOpen: function () {
            document.getElementById('swal-reason').focus();
        },
        preConfirm: function () {
            var reason = $('#swal-reason').val().trim();
            var pic    = $('#swal-pic').val().trim();
            if (!reason) { swal.showValidationMessage('Reason wajib diisi'); return false; }
            if (!pic)    { swal.showValidationMessage('PIC wajib diisi');    return false; }
            return { reason: reason, pic: pic };
        }
    }).then(function (result) {
        if (result.isConfirmed) {
            saveInlineEdit(result.value.reason, result.value.pic);
        }
    });
}

function saveInlineEdit(reason, pic) {
    var postData       = collectEditData();
    postData.reason    = reason;
    postData.pic       = pic;

    $.ajax({
        url: '<?= base_url("save_edit_bank_vlt") ?>',
        type: 'POST',
        data: postData,
        dataType: 'json',
        beforeSend: function () {
            swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: function () { swal.showLoading(); } });
        },
        success: function (r) {
            if (r.status === 'success') {
                swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data berhasil disimpan', timer: 1200, showConfirmButton: false });
                editingRowId = null;
                loadData();
                loadSummary();
            } else {
                swal.fire('Gagal', r.msg || 'Terjadi kesalahan', 'error');
            }
        },
        error: function () {
            swal.fire('Error', 'Gagal menyimpan data', 'error');
        }
    });
}

/* ──────────────────────────────────────
   DOWNLOAD EXCEL
────────────────────────────────────── */
function downloadExcel() {
    var range  = monthToRange($('#f-month').val());
    var params = $.param({
        start_date:      range.start,
        end_date:        range.end,
        already_process: $('#f-status').val(),
    });
    window.open('<?= base_url("download_bank_vlt") ?>?' + params, '_blank');
}

/* ──────────────────────────────────────
   UPLOAD
────────────────────────────────────── */
function initUploadZone() {
    $('#bvlt-file-input').on('change', function () {
        var name = this.files[0] ? this.files[0].name : 'Pilih File Excel';
        $('#bvlt-file-label').text(name);
    });

    $('#form-upload').on('submit', function () {
        if (!$('#bvlt-file-input')[0].files[0]) {
            swal.fire('Perhatian', 'Silahkan pilih file Excel terlebih dahulu', 'warning');
            return false;
        }
        $('#btn-upload').html('<i class="fas fa-circle-notch spin"></i> Mengupload...').prop('disabled', true);
    });
}

/* ──────────────────────────────────────
   UTIL
────────────────────────────────────── */
function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
