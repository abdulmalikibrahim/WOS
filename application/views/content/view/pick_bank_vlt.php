<link rel="stylesheet" href="<?=base_url("assets/css/dataTables.bootstrap4.min.css")?>">
<style>
    .model-card {
        cursor: pointer; border: 2px solid #dee2e6; border-radius: 6px;
        padding: 6px 14px; text-align: center; min-width: 90px;
        transition: all 0.2s; background: #fff; user-select: none;
    }
    .model-card:hover  { border-color: #20c997; background: #f0fff9; }
    .model-card.active { border-color: #20c997; background: #d0f5ec; }
    .model-card .mc-name { font-weight: bold; font-size: 11pt; color: #007bff; }
    .model-card .mc-count { font-size: 8pt; color: #666; }
    .model-card.all-card .mc-name { color: #555; }

    #btn-gunakan:disabled { opacity: 0.45; cursor: not-allowed; }
    .kap-badge { font-size: 10pt; font-weight: bold; padding: 3px 12px; border-radius: 4px; background: #0069d9; color: #fff; }

    /* Column filter row */
    #pick-table thead tr.filter-row th { padding: 3px 4px; background: #f8f9fa; }
    #pick-table thead tr.filter-row input:not([type=checkbox]) {
        width: 100%; font-size: 7.5pt; padding: 2px 5px;
        border: 1px solid #ced4da; border-radius: 3px;
        box-sizing: border-box; background: #fff;
    }

    /* DataTables controls */
    .dataTables_wrapper .dataTables_filter input { font-size: 9pt; }
    .dataTables_wrapper .dataTables_length select { font-size: 9pt; }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { font-size: 9pt; }

    /* ── Excel-like dropdown filter ── */
    .dt-dropdown { position: relative; display: block; }

    .dt-dd-btn {
        width: 100%; font-size: 7.5pt; padding: 3px 6px;
        border: 1px solid #ced4da; border-radius: 3px;
        background: #fff; cursor: pointer;
        display: flex; align-items: center; justify-content: space-between; gap: 4px;
        white-space: nowrap; line-height: 1.4;
    }
    .dt-dd-btn:hover { border-color: #adb5bd; background: #f8f9fa; }
    .dt-dd-btn.filtered { border-color: #0056b3; background: #cce5ff; color: #004085; font-weight: bold; }

    .dt-dd-panel {
        position: fixed; z-index: 99999; width: 240px;
        background: #fff; border: 1px solid #ced4da; border-radius: 5px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15); padding: 8px;
        font-size: 8.5pt;
    }

    .dt-dd-search { margin-bottom: 6px; }
    .dt-dd-search input { font-size: 8pt !important; width: 100%; }

    .dt-dd-select-all {
        display: flex; align-items: center; gap: 6px;
        padding: 3px 4px 6px; border-bottom: 1px solid #dee2e6; margin-bottom: 4px;
        cursor: pointer; font-weight: bold; user-select: none;
    }
    .dt-dd-select-all input[type=checkbox] { margin: 0; cursor: pointer; }

    .dt-dd-list { max-height: 185px; overflow-y: auto; margin-bottom: 7px; }
    .dt-dd-item {
        display: flex; align-items: center; gap: 7px;
        padding: 3px 4px; border-radius: 3px; cursor: pointer;
        font-size: 8pt; user-select: none;
    }
    .dt-dd-item:hover { background: #eef3ff; }
    .dt-dd-item.dd-hidden { display: none; }
    .dt-dd-item input[type=checkbox] { margin: 0; flex-shrink: 0; cursor: pointer; }
    .dt-dd-item span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .dt-dd-footer {
        display: flex; gap: 5px; justify-content: flex-end;
        border-top: 1px solid #dee2e6; padding-top: 6px;
    }
    .dt-dd-footer .btn { font-size: 7.5pt; padding: 2px 10px; }

    @keyframes spin-pick { to { transform: rotate(360deg); } }
    #pick-table tbody tr { cursor: pointer; }
</style>

<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;"><?=$title?></h1>
    </div>
    <a href="<?=base_url("docking_wos_dummy")?>" class="btn btn-sm btn-danger" style="height:35px; position:absolute; top:15px; right:20px;">
        <i class="fas fa-arrow-left mr-1"></i>Kembali
    </a>
</div>

<div class="card card-body mt-3">

    <!-- Top bar -->
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:8px; border-bottom:1px solid #e9ecef; padding-bottom:12px;">

        <!-- Bulan -->
        <div class="d-flex flex-column">
            <label class="mb-1" style="font-size:7.5pt;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.4px;">Bulan</label>
            <input type="month" id="f-month" class="form-control form-control-sm" style="width:148px;">
        </div>

        <!-- Filter VIN -->
        <div class="d-flex flex-column justify-content-end" style="padding-top:18px;">
            <button class="btn btn-sm btn-outline-primary" onclick="openSapnikModal()" id="btn-sapnik-filter" style="white-space:nowrap;">
                <i class="fas fa-list-ul mr-1"></i>Filter VIN
                <span id="sapnik-badge" class="badge badge-light border ml-1" style="display:none; color:#0056b3;"></span>
            </button>
        </div>

        <!-- Reset -->
        <div class="d-flex flex-column justify-content-end" style="padding-top:18px;">
            <button class="btn btn-sm btn-outline-secondary" onclick="resetFilter()">
                <i class="fas fa-undo mr-1"></i>Reset
            </button>
        </div>

        <!-- KAP badge -->
        <div class="d-flex flex-column justify-content-end" style="padding-top:18px;">
            <span id="kap-badge" class="kap-badge">KAP1</span>
        </div>

        <!-- Kanan: selected + gunakan -->
        <div class="d-flex align-items-center ml-auto" style="padding-top:18px; gap:10px;">
            <span id="selected-info" class="text-muted" style="font-size:9pt;">0 terpilih</span>
            <button id="btn-gunakan" class="btn btn-sm btn-success" onclick="gunakanData()" disabled>
                <i class="fas fa-check mr-1"></i>Gunakan Data Terpilih
            </button>
        </div>

    </div>

    <!-- Modal Filter VIN/SAPNIK -->
    <div class="modal fade" id="modal-sapnik" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title font-weight-bold"><i class="fas fa-filter mr-1 text-primary"></i>Filter VIN / SAPNIK</h6>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body pb-2">
                    <p class="text-muted mb-2" style="font-size:8pt;">Paste VIN/SAPNIK satu per baris atau pisahkan dengan koma.</p>
                    <textarea id="fc-sapnik" rows="8" class="form-control form-control-sm"
                        style="font-size:8.5pt;resize:vertical;font-family:monospace;line-height:1.5;"
                        placeholder="Contoh:&#10;D52BDUM1605260055&#10;D55LDUM1605260001"></textarea>
                    <small id="sapnik-count" class="text-muted mt-1 d-block"></small>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSapnikFilter()">
                        <i class="fas fa-times mr-1"></i>Clear
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="applySapnikModal()">
                        <i class="fas fa-check mr-1"></i>Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Model Cards -->
    <div class="d-flex flex-wrap mb-3" id="model-cards" style="gap:8px;"></div>

    <!-- DataTable -->
    <div class="table-responsive" style="position:relative;">
        <div id="table-loading" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,0.75); z-index:10; display:none; align-items:center; justify-content:center; border-radius:4px;">
            <div style="text-align:center; color:#1976D2;">
                <div style="width:40px;height:40px;border:4px solid #e3eafc;border-top-color:#1976D2;border-radius:50%;animation:spin-pick .7s linear infinite;margin:0 auto 8px;"></div>
                <div style="font-size:9pt;font-weight:600;">Memuat data...</div>
            </div>
        </div>
        <table id="pick-table" class="table table-bordered table-hover table-sm w-100" style="font-size:9pt;">
            <thead class="thead-light">
                <tr>
                    <th style="width:36px;">No.</th>
                    <th style="width:30px;"><input type="checkbox" id="chk-all" title="Pilih Semua" onchange="toggleAll(this)"></th>
                    <th>SAPNIK</th>
                    <th>Katashiki Suffix</th>
                    <th>Plan Delivery Date</th>
                    <th>Color Code</th>
                    <th>Destination</th>
                    <th>Model</th>
                </tr>
                <tr class="filter-row">
                    <th></th>
                    <th></th>
                    <th></th>
                    <th><?php /* suffix */ ?><div class="dt-dropdown"><button class="dt-dd-btn" id="dd-btn-suffix" type="button" onclick="toggleDropdown(event,'suffix')">Semua <i class="fas fa-chevron-down fa-xs"></i></button><div class="dt-dd-panel" id="dd-panel-suffix" style="display:none;"></div></div></th>
                    <th><?php /* pdd    */ ?><div class="dt-dropdown"><button class="dt-dd-btn" id="dd-btn-pdd"    type="button" onclick="toggleDropdown(event,'pdd')"   >Semua <i class="fas fa-chevron-down fa-xs"></i></button><div class="dt-dd-panel" id="dd-panel-pdd"    style="display:none;"></div></div></th>
                    <th><?php /* color  */ ?><div class="dt-dropdown"><button class="dt-dd-btn" id="dd-btn-color"  type="button" onclick="toggleDropdown(event,'color')" >Semua <i class="fas fa-chevron-down fa-xs"></i></button><div class="dt-dd-panel" id="dd-panel-color"  style="display:none;"></div></div></th>
                    <th><?php /* dest   */ ?><div class="dt-dropdown"><button class="dt-dd-btn" id="dd-btn-dest"   type="button" onclick="toggleDropdown(event,'dest')"  >Semua <i class="fas fa-chevron-down fa-xs"></i></button><div class="dt-dd-panel" id="dd-panel-dest"   style="display:none;"></div></div></th>
                    <th><?php /* model  */ ?><div class="dt-dropdown"><button class="dt-dd-btn" id="dd-btn-model"  type="button" onclick="toggleDropdown(event,'model')" >Semua <i class="fas fa-chevron-down fa-xs"></i></button><div class="dt-dd-panel" id="dd-panel-model"  style="display:none;"></div></div></th>
                </tr>
            </thead>
            <tbody id="tbl-body"></tbody>
        </table>
    </div>

</div>

<script src="<?=base_url("assets/js/dataTables.min.js")?>"></script>
<script src="<?=base_url("assets/js/dataTables.bootstrap4.min.js")?>"></script>
