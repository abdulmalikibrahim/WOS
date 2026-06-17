<script>
// ─── State ────────────────────────────────────────────────────────────────────
var aocData          = [];   // 5000 rows from checking_wos + overflow_preview rows
var selectedIds      = {};   // { id: rowObj }
var lastClickedIndex = -1;   // anchor index for Shift+Click range selection
var shiftWasHeld     = false;

// ─── Load data ────────────────────────────────────────────────────────────────
function loadAocData() {
    var kap = $("#filter-kap").val();
    $("#table-title").text("DATA AOC KAP " + kap);
    selectedIds      = {};
    lastClickedIndex = -1;
    updateConfirmInfo();
    hideContinuationInfo();
    disableConfirmButtons();

    $.ajax({
        url: '<?= base_url("getAocData") ?>',
        type: 'GET',
        data: { kap: kap },
        dataType: 'json',
        beforeSend: function () {
            $("#aoc-table-body").html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat...</td></tr>');
            $("#total-count").text("...");
        },
        success: function (res) {
            aocData = res.data || [];
            applyModelFilter();
        }
    });
}

// ─── Client-side model filter ─────────────────────────────────────────────────
function applyModelFilter() {
    var checked = $(".model-cb:checked").map(function () { return this.value; }).get();
    var filtered = aocData.filter(function (r) { return checked.indexOf(r.model) !== -1; });
    renderTable(filtered);
    $("#total-count").text(filtered.length + " unit");
    updateConfirmInfo();
}

// ─── Render table ─────────────────────────────────────────────────────────────
function renderTable(data) {
    if (!data || data.length === 0) {
        $("#aoc-table-body").html('<tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data</td></tr>');
        return;
    }
    var html = '';
    for (var i = 0; i < data.length; i++) {
        var r       = data[i];
        var isFirm  = r.firm && r.firm !== '0000-00-00';
        var isTent  = r.tentative && r.tentative !== '0000-00-00';
        var isTemp  = r.source === 'temporary' || r.source === 'overflow_preview';
        var isSel   = !!selectedIds[r.id];

        var rowCls  = isTemp ? 'row-temporary' : (isFirm ? 'row-firm' : (isTent ? 'row-tentative' : ''));
        if (isSel) rowCls += ' row-selected';

        html += '<tr class="cb-row ' + rowCls + '" data-id="' + r.id + '">';
        html += '<td class="text-center">' + (i + 1) + '</td>';
        html += '<td class="text-center"><input type="checkbox" class="row-cb" data-id="' + r.id + '"' + (isSel ? ' checked' : '') + '></td>';
        html += '<td class="text-center">' + (r.vin   || '-') + '</td>';
        html += '<td class="text-center">' + (r.suffix || '-') + '</td>';
        html += '<td class="text-center">' + (r.color  || '-') + '</td>';
        html += '<td class="text-center">' + (r.model  || '-') + '</td>';
        html += '<td class="text-center">' + (r.pdd_input || '-') + '</td>';
        html += '<td class="text-center">' + (isTent ? r.tentative : '-') + '</td>';
        html += '<td class="text-center">' + (isFirm ? r.firm      : '-') + '</td>';
        html += '</tr>';
    }
    $("#aoc-table-body").html(html);
    bindTableEvents();
}

function bindTableEvents() {
    // ── Click on row (not checkbox) ──────────────────────────────────────────
    $("#aoc-table-body").off("click", ".cb-row").on("click", ".cb-row", function (e) {
        if ($(e.target).is("input[type=checkbox]")) return; // let checkbox path handle it

        var rows     = $("#aoc-table-body .cb-row");
        var idx      = rows.index(this);
        var cb       = $(this).find(".row-cb");
        var newState = !cb.prop("checked");

        if (e.shiftKey && lastClickedIndex >= 0 && idx !== lastClickedIndex) {
            applyRange(rows, lastClickedIndex, idx, newState);
        } else {
            cb.prop("checked", newState).trigger("change");
            lastClickedIndex = idx;
        }
    });

    // ── Checkbox click: capture Shift before browser toggles ─────────────────
    $("#aoc-table-body").off("click", ".row-cb").on("click", ".row-cb", function (e) {
        e.stopPropagation();
        shiftWasHeld = e.shiftKey;
    });

    // ── Checkbox change: fires right after click; browser has already toggled ─
    $("#aoc-table-body").off("change", ".row-cb").on("change", ".row-cb", function () {
        var $cb      = $(this);
        var id       = parseInt($cb.data("id"));
        var $tr      = $cb.closest("tr");
        var rows     = $("#aoc-table-body .cb-row");
        var idx      = rows.index($tr);
        var newState = this.checked;

        if (shiftWasHeld && lastClickedIndex >= 0 && idx !== lastClickedIndex) {
            applyRange(rows, lastClickedIndex, idx, newState);
            shiftWasHeld = false;
            return; // applyRange handles updateConfirmInfo/updateCbAll
        }
        shiftWasHeld  = false;
        lastClickedIndex = idx;

        // Apply single row state
        var row = aocData.find(function (r) { return parseInt(r.id) === id; });
        if (newState) {
            if (row) selectedIds[id] = row;
            $tr.addClass("row-selected");
        } else {
            delete selectedIds[id];
            $tr.removeClass("row-selected");
        }
        updateConfirmInfo();
        updateCbAll();
    });

    // ── Select-all checkbox ───────────────────────────────────────────────────
    $("#cb-all").off("change").on("change", function () {
        lastClickedIndex = -1;
        var checked = this.checked;
        $("#aoc-table-body .row-cb").each(function () {
            $(this).prop("checked", checked).trigger("change");
        });
    });
}

// Apply check state to a range of visible rows [fromIdx … toIdx] inclusive
function applyRange(rows, fromIdx, toIdx, state) {
    var lo = Math.min(fromIdx, toIdx);
    var hi = Math.max(fromIdx, toIdx);
    rows.slice(lo, hi + 1).each(function () {
        var $cb  = $(this).find(".row-cb");
        var id   = parseInt($cb.data("id"));
        var row  = aocData.find(function (r) { return parseInt(r.id) === id; });
        $cb.prop("checked", state);
        if (state) {
            if (row) selectedIds[id] = row;
            $(this).addClass("row-selected");
        } else {
            delete selectedIds[id];
            $(this).removeClass("row-selected");
        }
    });
    updateConfirmInfo();
    updateCbAll();
}

function updateCbAll() {
    var total   = $("#aoc-table-body .row-cb").length;
    var checked = $("#aoc-table-body .row-cb:checked").length;
    $("#cb-all").prop("indeterminate", checked > 0 && checked < total);
    $("#cb-all").prop("checked", total > 0 && checked === total);
}

// ─── Confirmation info bar ────────────────────────────────────────────────────
function updateConfirmInfo() {
    var count        = Object.keys(selectedIds).length;
    if (count === 0) {
        $("#confirm-info").html('<span class="text-muted">Belum ada baris terpilih</span>');
        $("#selected-count-label").text("");
    } else {
        var overflowRows = Object.values(selectedIds).filter(function (r) { return r.source === 'overflow_preview'; }).length;
        var overflowNote = overflowRows > 0
            ? '<br><span class="text-warning font-weight-bold">+ ' + overflowRows + ' dari WOS -2 hari</span>'
            : '';
        $("#confirm-info").html(
            '<span class="font-weight-bold" style="font-size:14pt;">' + count + '</span> baris dipilih' + overflowNote
        );
        $("#selected-count-label").text(count + " dipilih");
    }
}

function hideContinuationInfo() {
    $("#cont-info").hide();
}

function disableConfirmButtons() {
    $("#btn-confirm-tentative").prop("disabled", true);
    $("#btn-confirm-firm").prop("disabled", true);
}

function setActiveConfirm(type) {
    if (type === "tentative") {
        $("#btn-confirm-tentative").prop("disabled", false);
        $("#btn-confirm-firm").prop("disabled", true);
    } else {
        $("#btn-confirm-firm").prop("disabled", false);
        $("#btn-confirm-tentative").prop("disabled", true);
    }
}

// ─── Auto Select ─────────────────────────────────────────────────────────────
function autoSelect(type) {
    var jumlah = parseInt($("#input-jumlah").val());
    if (!jumlah || jumlah < 1) {
        Swal.fire("Perhatian", "Masukkan jumlah unit terlebih dahulu", "warning");
        return;
    }

    var kap  = $("#filter-kap").val();
    var date = $("#input-date").val();

    // Only pick from currently checked models (the model filter constrains the selection)
    var checked = $(".model-cb:checked").map(function () { return this.value; }).get();
    if (checked.length === 0) {
        Swal.fire("Perhatian", "Pilih minimal satu model pada Filter Model terlebih dahulu", "warning");
        return;
    }

    $.ajax({
        url: '<?= base_url("getContinuationPoint") ?>',
        type: 'GET',
        data: { kap: kap, type: type },
        dataType: 'json',
        beforeSend: function () { $("#cont-info").hide(); },
        success: function (res) {
            if (res.statusCode !== 200) return;

            var startPos  = res.start_pos;

            // Remove any overflow_preview rows appended in a previous auto-select
            aocData = aocData.filter(function (r) { return r.source !== 'overflow_preview'; });

            // Available = units of the CHECKED models from the continuation point onward
            var available = 0;
            for (var k = startPos - 1; k < aocData.length; k++) {
                if (aocData[k].source === 'overflow_preview') continue;
                if (checked.indexOf(aocData[k].model) !== -1) available++;
            }
            var overflow = Math.max(0, jumlah - available);

            // Show info bar
            $("#info-start").text(startPos);
            $("#info-available").text(available + " unit");
            if (overflow > 0) {
                $("#info-overflow").text(overflow);
                $("#info-overflow-row").show();
            } else {
                $("#info-overflow-row").hide();
            }
            $("#cont-info").show();

            // Clear previous selection
            selectedIds = {};

            // Select sequential rows from startPos, skipping models not in the filter
            var toSelect = Math.min(jumlah, available);
            var counted  = 0;
            for (var i = startPos - 1; i < aocData.length && counted < toSelect; i++) {
                if (aocData[i].source === 'overflow_preview') continue;
                if (checked.indexOf(aocData[i].model) === -1) continue;
                selectedIds[aocData[i].id] = aocData[i];
                counted++;
            }

            if (overflow > 0 && date) {
                // Fetch overflow preview rows from checking_wos pdd_input -2 days (same model filter)
                $.ajax({
                    url: '<?= base_url("getOverflowPreview") ?>',
                    type: 'GET',
                    data: { kap: kap, date: date, count: overflow, models: JSON.stringify(checked) },
                    dataType: 'json',
                    success: function (ovRes) {
                        if (ovRes.statusCode === 200 && ovRes.data && ovRes.data.length) {
                            for (var j = 0; j < ovRes.data.length; j++) {
                                var row = ovRes.data[j];
                                row.source = 'overflow_preview';
                                aocData.push(row);
                                selectedIds[row.id] = row;
                            }
                        }
                        applyModelFilter();
                        updateConfirmInfo();
                        setActiveConfirm(type);
                        scrollToFirstSelected();
                    }
                });
            } else {
                applyModelFilter();
                updateConfirmInfo();
                setActiveConfirm(type);
                scrollToFirstSelected();
            }
        }
    });
}

function scrollToFirstSelected() {
    var $first = $("#aoc-table-body .row-selected").first();
    if ($first.length) {
        var $wrap  = $(".aoc-wrap");
        var offset = $first.position().top + $wrap.scrollTop() - 60;
        $wrap.animate({ scrollTop: offset }, 350);
    }
}

// ─── Confirm Update ───────────────────────────────────────────────────────────
function confirmUpdate(type) {
    var allSelected  = Object.values(selectedIds);
    var regularIds   = allSelected.filter(function (r) { return r.source !== 'overflow_preview'; }).map(function (r) { return parseInt(r.id); });
    var overflowIds  = allSelected.filter(function (r) { return r.source === 'overflow_preview'; }).map(function (r) { return parseInt(r.id); });
    var date  = $("#input-date").val();
    var kap   = $("#filter-kap").val();
    var label = type === "firm" ? "Firm" : "Tentative";

    if (regularIds.length === 0 && overflowIds.length === 0) {
        Swal.fire("Perhatian", "Tidak ada baris terpilih", "warning");
        return;
    }
    if (!date) {
        Swal.fire("Perhatian", "Tanggal order harus diisi", "warning");
        return;
    }

    var overflowNote = overflowIds.length > 0
        ? "<br><small class='text-warning'>+ " + overflowIds.length + " unit dari WOS -2 hari (temporary)</small>"
        : "";

    Swal.fire({
        title: "Konfirmasi " + label,
        html: "Update <b>" + (regularIds.length + overflowIds.length) + " unit</b> sebagai <b>" + label + "</b><br>"
            + "Tanggal Order: <b>" + date + "</b>" + overflowNote,
        iconHtml: '<div style="width:100%;text-align:center;"><i class="fas fa-exclamation-triangle" style="font-size:102px;color:#f8a100;"></i></div>',
        customClass: { icon: 'border-0 shadow-none' },
        showCancelButton: true,
        confirmButtonText: "Ya, Confirm",
        cancelButtonText: "Batal"
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.ajax({
            url: '<?= base_url("confirmAocUpdate") ?>',
            type: 'POST',
            data: {
                kap:          kap,
                ids:          JSON.stringify(regularIds),
                overflow_ids: JSON.stringify(overflowIds),
                type:         type,
                date:         date
            },
            dataType: 'json',
            beforeSend: function () {
                Swal.fire({ title: "Menyimpan...", allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
            },
            success: function (res) {
                Swal.close();
                if (res.statusCode !== 200) {
                    Swal.fire("Gagal", res.msg || "Terjadi kesalahan", "error");
                    return;
                }
                showSummary(res, label);
            },
            error: function () {
                Swal.fire("Gagal", "Terjadi kesalahan server", "error");
            }
        });
    });
}

// ─── Summary Modal ────────────────────────────────────────────────────────────
function showSummary(res, label) {
    var summary          = res.summary          || {};
    var updated          = res.updated          || 0;
    var overflowInserted = res.overflow_inserted || 0;
    var total            = updated + overflowInserted;

    var html = '<p class="mb-2">Update <b>' + label + '</b> berhasil. Total: <b>' + total + ' unit</b>';
    if (overflowInserted > 0) html += ' <small class="text-warning">(' + overflowInserted + ' dari WOS -2 hari)</small>';
    html += '</p>';
    html += '<table class="table table-bordered table-sm" style="font-size:10pt;">';
    html += '<thead class="thead-light"><tr><th>Model</th><th class="text-right">Jumlah</th></tr></thead><tbody>';

    var grandTotal = 0;
    $.each(summary, function (model, count) {
        html += '<tr><td>' + model + '</td><td class="text-right font-weight-bold">' + count + '</td></tr>';
        grandTotal += count;
    });
    html += '<tr class="table-dark"><td><b>TOTAL</b></td><td class="text-right font-weight-bold">' + grandTotal + '</td></tr>';
    html += '</tbody></table>';

    $("#summary-body").html(html);
    $("#modal-summary").modal("show");

    // Reset state
    selectedIds = {};
    // Remove overflow_preview rows from aocData (will be re-fetched on next auto-select)
    aocData = aocData.filter(function (r) { return r.source !== 'overflow_preview'; });
    hideContinuationInfo();
    loadHistory();
}

// ─── History ──────────────────────────────────────────────────────────────────
function loadHistory() {
    var kap = $("#filter-kap").val();
    $("#history-kap-label").text("KAP " + kap);

    $.ajax({
        url: '<?= base_url("getAocHistory") ?>',
        type: 'GET',
        data: { kap: kap },
        dataType: 'json',
        beforeSend: function () {
            $("#history-table-body").html('<tr><td colspan="4" class="text-center py-2"><i class="fas fa-spinner fa-spin"></i></td></tr>');
        },
        success: function (res) {
            var data = res.data || [];
            if (data.length === 0) {
                $("#history-table-body").html('<tr><td colspan="4" class="text-center text-muted py-2">Belum ada data</td></tr>');
                return;
            }
            var html = '';
            for (var i = 0; i < data.length; i++) {
                var r = data[i];
                html += '<tr>';
                html += '<td class="text-center font-weight-bold">' + r.order_date + '</td>';
                html += '<td class="text-center">' + (r.total_tentative > 0 ? '<span class="badge badge-warning text-dark">' + r.total_tentative + '</span>' : '-') + '</td>';
                html += '<td class="text-center">' + (r.total_firm > 0 ? '<span class="badge badge-success">' + r.total_firm + '</span>' : '-') + '</td>';
                html += '<td class="text-center"><button class="btn btn-xs btn-outline-primary px-2 py-0" style="font-size:8pt;" onclick="showHistoryDetail(\'' + r.order_date + '\')">Detail</button></td>';
                html += '</tr>';
            }
            $("#history-table-body").html(html);
        }
    });
}

var currentHistoryDetail = { kap: null, date: null, hasData: false };

function showHistoryDetail(date) {
    var kap = $("#filter-kap").val();
    currentHistoryDetail = { kap: kap, date: date, hasData: false };
    $("#btn-download-detail").prop("disabled", true);
    $("#history-detail-title").text("Detail Order: " + date + " — KAP " + kap);
    $("#history-detail-body").html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');
    $("#modal-history-detail").modal("show");

    $.ajax({
        url: '<?= base_url("getAocHistoryDetail") ?>',
        type: 'GET',
        data: { kap: kap, date: date },
        dataType: 'json',
        success: function (res) {
            var data = res.data || [];
            if (data.length === 0) {
                $("#history-detail-body").html('<p class="text-center text-muted py-3">Tidak ada data</p>');
                return;
            }
            currentHistoryDetail.hasData = true;
            $("#btn-download-detail").prop("disabled", false);
            var html = '<table class="table table-bordered table-sm mb-0" style="font-size:9.5pt;">';
            html += '<thead class="thead-light"><tr><th>Model</th><th class="text-right">Tentative</th><th class="text-right">Firm</th><th class="text-right">Total</th></tr></thead><tbody>';
            var gtTent = 0, gtFirm = 0;
            for (var i = 0; i < data.length; i++) {
                var r = data[i];
                var tot = parseInt(r.total_tentative) + parseInt(r.total_firm);
                gtTent += parseInt(r.total_tentative);
                gtFirm += parseInt(r.total_firm);
                html += '<tr><td class="font-weight-bold">' + (r.model || '-') + '</td>';
                html += '<td class="text-right">' + (r.total_tentative > 0 ? '<span class="text-warning font-weight-bold">' + r.total_tentative + '</span>' : '-') + '</td>';
                html += '<td class="text-right">' + (r.total_firm > 0 ? '<span class="text-success font-weight-bold">' + r.total_firm + '</span>' : '-') + '</td>';
                html += '<td class="text-right font-weight-bold">' + tot + '</td></tr>';
            }
            html += '<tr class="table-dark"><td><b>TOTAL</b></td><td class="text-right"><b>' + gtTent + '</b></td><td class="text-right"><b>' + gtFirm + '</b></td><td class="text-right"><b>' + (gtTent + gtFirm) + '</b></td></tr>';
            html += '</tbody></table>';
            $("#history-detail-body").html(html);
        }
    });
}

function downloadHistoryDetailExcel() {
    if (!currentHistoryDetail.hasData || !currentHistoryDetail.date) {
        Swal.fire("Perhatian", "Tidak ada data untuk diunduh", "warning");
        return;
    }
    var url = '<?= base_url("downloadAocHistoryDetail") ?>'
            + '?kap='  + encodeURIComponent(currentHistoryDetail.kap)
            + '&date=' + encodeURIComponent(currentHistoryDetail.date);
    window.location.href = url;
}

// ─── Events ───────────────────────────────────────────────────────────────────
$("#filter-kap").on("change",  function () { loadAocData(); loadHistory(); });
$(".model-cb"  ).on("change",  function () { applyModelFilter(); });
$("#btn-check-all"  ).on("click", function () { $(".model-cb").prop("checked", true);  applyModelFilter(); });
$("#btn-uncheck-all").on("click", function () { $(".model-cb").prop("checked", false); applyModelFilter(); });

// ─── Init ─────────────────────────────────────────────────────────────────────
loadAocData();
loadHistory();
</script>
