<style>
    .aoc-wrap          { overflow-y: auto; max-height: calc(100vh - 290px); }
    .aoc-wrap thead th { position: sticky; top: -1px; z-index: 1; background: #dee2e6; white-space: nowrap; }
    .aoc-wrap td       { white-space: nowrap; }
    .col-sapnik        { max-width: 150px; overflow: hidden; text-overflow: ellipsis; }
    .row-firm          { background-color: #28a745 !important; color: #fff !important; }
    .row-firm td       { color: #fff !important; }
    .row-tentative     { background-color: #fff3cd !important; }
    .row-temporary     { background-color: #ffe5b4 !important; }
    .row-temporary td  { color: #7a3e00 !important; }
    .row-selected      { outline: 3px solid #fd7e14; outline-offset: -2px; }
    .cb-row            { cursor: pointer; }
    .right-panel       { overflow-y: auto; max-height: calc(100vh - 130px); }
    .card-sm .card-header { padding: 6px 12px; font-size: 10.5pt; }
    .card-sm .card-body   { padding: 10px 12px; }
    .history-table td, .history-table th { padding: 4px 8px; font-size: 9pt; vertical-align: middle; }
</style>

<div class="row justify-content-center mb-2">
    <div class="col-12 text-center">
        <h1 class="m-0 text-white" style="font-size:2.6rem;"><?= $title ?></h1>
    </div>
    <a href="<?= base_url("") ?>" class="btn btn-sm btn-danger" style="height:32px; position:absolute; top:20px; right:20px;">Main Menu</a>
</div>

<!-- Filter bar -->
<div class="card card-body py-2 px-3 mb-3">
    <div class="row align-items-center">
        <div class="col-auto" style="min-width:120px;">
            <label class="text-danger font-weight-bold mb-1" style="font-size:10pt;">Line (KAP)</label>
            <select class="form-control form-control-sm" id="filter-kap">
                <option value="1">KAP 1</option>
                <option value="2">KAP 2</option>
            </select>
        </div>
        <div class="col">
            <label class="text-danger font-weight-bold mb-1" style="font-size:10pt;">Filter Model</label>
            <div id="model-checkboxes" class="d-flex flex-wrap" style="gap:4px 14px;">
                <?php foreach ($modelList as $mdl): ?>
                <div class="form-check form-check-inline m-0">
                    <input class="form-check-input model-cb" type="checkbox" value="<?= htmlspecialchars($mdl) ?>" id="cb-<?= htmlspecialchars($mdl) ?>" checked>
                    <label class="form-check-label font-weight-bold" for="cb-<?= htmlspecialchars($mdl) ?>"><?= htmlspecialchars($mdl) ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-auto d-flex align-items-end pb-1" style="gap:5px;">
            <button class="btn btn-xs btn-outline-secondary px-2 py-1" style="font-size:8.5pt;" id="btn-check-all">Semua</button>
            <button class="btn btn-xs btn-outline-secondary px-2 py-1" style="font-size:8.5pt;" id="btn-uncheck-all">Hapus</button>
        </div>
    </div>
</div>

<!-- Main -->
<div class="row mb-4" style="flex-wrap:nowrap; gap:0;">

    <!-- Table col-6 -->
    <div class="col-lg-6 pr-2">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                <span class="font-weight-bold" id="table-title" style="font-size:10pt;">DATA AOC KAP 1</span>
                <div style="font-size:9pt;">
                    <span class="badge badge-dark px-2" id="total-count">—</span>
                    <span class="ml-1 text-success font-weight-bold" id="selected-count-label"></span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="aoc-wrap">
                    <table class="table table-bordered table-sm mb-0" style="font-size:8.5pt;">
                        <thead>
                            <tr align="center">
                                <th style="width:32px;">No.</th>
                                <th style="width:28px;"><input type="checkbox" id="cb-all" title="Pilih/Hapus semua"></th>
                                <th style="max-width:150px;">SAPNIK</th>
                                <th style="width:52px;">Suffix</th>
                                <th style="width:52px;">Color</th>
                                <th style="width:60px;">Model</th>
                                <th style="width:82px;">PDD</th>
                                <th style="width:82px;">Tentative</th>
                                <th style="width:82px;">Firm</th>
                            </tr>
                        </thead>
                        <tbody id="aoc-table-body">
                            <tr><td colspan="9" class="text-center py-4 text-muted">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right panel col-6 -->
    <div class="col-lg-6 pl-2">
        <div class="right-panel">

            <!-- Auto Select -->
            <div class="card card-sm mb-2">
                <div class="card-header bg-secondary text-white font-weight-bold">Auto Select</div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="font-weight-bold text-danger mb-1" style="font-size:9.5pt;">Jumlah Unit</label>
                            <input type="number" class="form-control form-control-sm" id="input-jumlah" min="1" placeholder="cth: 340">
                        </div>
                        <div class="col-6">
                            <label class="font-weight-bold text-danger mb-1" style="font-size:9.5pt;">Tanggal Order</label>
                            <input type="date" class="form-control form-control-sm" id="input-date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div id="cont-info" class="mb-2 px-2 py-1 rounded" style="background:#f8f9fa; font-size:8.5pt; display:none; line-height:1.7;">
                        Posisi mulai: <b id="info-start"></b> &nbsp;|&nbsp;
                        Tersedia: <b id="info-available"></b>
                        <span id="info-overflow-row" style="display:none;"> &nbsp;|&nbsp;
                            <span class="text-warning font-weight-bold">Overflow WOS -2 hari: <b id="info-overflow"></b></span>
                        </span>
                    </div>

                    <div class="d-flex" style="gap:6px;">
                        <button class="btn btn-sm btn-warning flex-fill font-weight-bold" onclick="autoSelect('tentative')">
                            <i class="fas fa-magic mr-1"></i>Auto Tentative
                        </button>
                        <button class="btn btn-sm btn-success flex-fill font-weight-bold" onclick="autoSelect('firm')">
                            <i class="fas fa-magic mr-1"></i>Auto Firm
                        </button>
                    </div>
                </div>
            </div>

            <!-- Confirm -->
            <div class="card card-sm mb-2">
                <div class="card-header font-weight-bold">Confirm Update</div>
                <div class="card-body">
                    <div id="confirm-info" class="text-center mb-2" style="font-size:10pt; min-height:26px;">
                        <span class="text-muted" style="font-size:9pt;">Belum ada baris terpilih</span>
                    </div>
                    <div class="d-flex" style="gap:6px;">
                        <button class="btn btn-sm btn-warning flex-fill font-weight-bold" id="btn-confirm-tentative" onclick="confirmUpdate('tentative')" disabled>
                            Confirm Tentative
                        </button>
                        <button class="btn btn-sm btn-success flex-fill font-weight-bold" id="btn-confirm-firm" onclick="confirmUpdate('firm')" disabled>
                            Confirm Firm
                        </button>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="card card-sm mb-2">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap align-items-center" style="gap:10px 16px; font-size:8.5pt;">
                        <div class="d-flex align-items-center" style="gap:5px;">
                            <div style="width:14px;height:14px;background:#28a745;border-radius:3px;flex-shrink:0;"></div>Firm
                        </div>
                        <div class="d-flex align-items-center" style="gap:5px;">
                            <div style="width:14px;height:14px;background:#fff3cd;border:1px solid #ffc107;border-radius:3px;flex-shrink:0;"></div>Tentative
                        </div>
                        <div class="d-flex align-items-center" style="gap:5px;">
                            <div style="width:14px;height:14px;background:#ffe5b4;border:1px solid #fd7e14;border-radius:3px;flex-shrink:0;"></div>Temporary
                        </div>
                        <div class="d-flex align-items-center" style="gap:5px;">
                            <div style="width:14px;height:14px;background:#fff;border:2px solid #fd7e14;border-radius:3px;flex-shrink:0;"></div>Terpilih
                        </div>
                    </div>
                </div>
            </div>

            <!-- History -->
            <div class="card card-sm">
                <div class="card-header d-flex justify-content-between align-items-center font-weight-bold">
                    <span>History Order</span>
                    <span class="badge badge-secondary" id="history-kap-label"></span>
                </div>
                <div class="card-body p-0">
                    <div style="overflow-y:auto; max-height:220px;">
                        <table class="table table-bordered table-sm mb-0 history-table">
                            <thead class="thead-light" style="position:sticky;top:-1px;z-index:1;">
                                <tr align="center">
                                    <th>Tanggal</th>
                                    <th>Tentative</th>
                                    <th>Firm</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody id="history-table-body">
                                <tr><td colspan="4" class="text-center text-muted py-2">—</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Summary Modal -->
<div class="modal fade" id="modal-summary" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title font-weight-bold">Summary Update AOC</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="summary-body"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" onclick="loadAocData()">Tutup & Refresh</button>
            </div>
        </div>
    </div>
</div>

<!-- History Detail Modal -->
<div class="modal fade" id="modal-history-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title font-weight-bold" id="history-detail-title">Detail</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" id="history-detail-body">
                <div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-success" id="btn-download-detail" onclick="downloadHistoryDetailExcel()">
                    <i class="fas fa-file-excel mr-1"></i>Download Excel
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
