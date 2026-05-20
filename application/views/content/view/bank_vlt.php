<style>
    /* Classic UI Enhancements for Bank VLT */
    .border-left-primary { border-left: .25rem solid #4e73df !important; }
    .border-left-success { border-left: .25rem solid #1cc88a !important; }
    .border-left-warning { border-left: .25rem solid #f6c23e !important; }
    .border-left-info { border-left: .25rem solid #36b9cc !important; }
    
    .text-primary { color: #4e73df !important; }
    .text-success { color: #1cc88a !important; }
    .text-warning { color: #f6c23e !important; }
    .text-info { color: #36b9cc !important; }
    .text-gray-800 { color: #5a5c69 !important; }
    .text-gray-300 { color: #dddfeb !important; }
    
    .card-filter-target {
        transition: all 0.2s;
    }
    .card-filter-target:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    
    .card-active, .model-active {
        background-color: #45efffff !important;
        color: #fff !important;
        border: 2px solid #1976D2 !important;
        box-shadow: 0 0 12px rgba(25, 118, 210, 0.4) !important;
        transform: translateY(-2px);
    }

    /* ─── Status Badge ─── */
    .badge-proses {
        background: #C8E6C9;
        color: #1B5E20;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 600;
    }

    .badge-belum {
        background: #FFF9C4;
        color: #F57F17;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 600;
    }

    /* ─── Table ─── */
    .tableFixHead { overflow: auto; max-height: calc(100vh - 420px); }
    .tableFixHead thead th { position: sticky; top: -1px; z-index: 2; background: #eee; }
    .tableFixHead thead tr.filter-row th { position: sticky; top: 35px; background: #f8f9fa; z-index: 2; padding: 4px 6px; }

    .bvlt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .75rem;
        white-space: nowrap;
    }

    .bvlt-table thead th {
        color: #333;
        font-weight: 700;
        padding: 8px 10px;
        text-align: center;
        cursor: pointer;
        user-select: none;
        border: 1px solid #dee2e6;
    }

    .bvlt-table thead th .sort-icon { opacity: .5; font-size: .65rem; margin-left: 3px; }
    .bvlt-table thead th.asc .sort-icon::before { content: "▲"; opacity: 1; }
    .bvlt-table thead th.desc .sort-icon::before { content: "▼"; opacity: 1; }
    .bvlt-table thead th:not(.asc):not(.desc) .sort-icon::before { content: "⇅"; }

    .bvlt-table thead tr.filter-row th input {
        width: 100%;
        border: 1px solid #ccc;
        border-radius: 3px;
        padding: 3px 6px;
        font-size: .7rem;
        outline: none;
        color: #333;
        min-width: 60px;
        background: #fff;
    }

    .bvlt-table tbody tr:hover { background: #f2f2f2 !important; }
    .bvlt-table tbody tr.row-done { background: #f9fdf9; }
    .bvlt-table td {
        padding: 6px 10px;
        border: 1px solid #dee2e6;
        text-align: center;
        vertical-align: middle;
    }
    .bvlt-table td.td-left { text-align: left; }

    .btn-edit-row {
        background: #1976D2; color: #fff; border: none; border-radius: 3px;
        padding: 3px 8px; font-size: .7rem; cursor: pointer;
    }
    .btn-reason-row {
        background: #f39c12; color: #fff; border: none; border-radius: 3px;
        padding: 3px 8px; font-size: .7rem; cursor: pointer;
    }

    /* ─── Inline Edit ─── */
    tr.editing { background: #ffdddd !important; }
    .inline-input {
        border: 1px solid #ccc; border-radius: 3px; padding: 3px 5px;
        font-size: .7rem; width: 100%; min-width: 55px; outline: none;
    }
    .btn-cancel-row {
        background: #ef5350; color: #fff; border: none; border-radius: 3px;
        padding: 3px 8px; font-size: .7rem; cursor: pointer;
    }

    /* Empty State */
    .empty-state {
        padding: 40px;
        color: #999;
        font-size: 1.1rem;
        text-align: center;
    }
    .empty-state i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 10px;
    }

    /* Spinner loader */
    .bvlt-loader {
        padding: 40px;
        color: #4e73df;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .bvlt-table tbody tr.row-done {
        background-color: #1f9638ff !important;
        font-weight: bold;
        color: #fafafaff;
    }

    .bvlt-table tbody tr.row-done:hover {
        background-color: #24b142ff !important;
        color: #fafafaff;
    }
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    
</style>

<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;">BANK VLT</h1>
    </div>
    <a href="<?= base_url('') ?>" class="btn btn-sm btn-danger" style="position:absolute; top:15px; right:20px;">
        <i class="fas fa-home"></i> Main Menu
    </a>
</div>

<div class="container-fluid mt-3 pl-3 pr-3">
    <!-- SUMMARY CARDS -->
    <div class="row mb-3">
        <div class="col-lg-2">
            <div class="card border-left-primary shadow-sm mb-2 card-filter-target p-0" id="card-sum-all" style="cursor:pointer" onclick="filterBySummary('all', this)">
                <div class="card-body pt-3 pb-3">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">TOTAL VLT</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-total">—</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-database fa-2x text-info"></i></div>
                    </div>
                </div>
            </div>

            <div class="card border-left-success shadow-sm mb-2 card-filter-target p-0" id="card-sum-1" style="cursor:pointer" onclick="filterBySummary('1', this)">
                <div class="card-body pt-3 pb-3">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">ALREADY PROCESS</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-sudah">—</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-success"></i></div>
                    </div>
                </div>
            </div>
            
            <div class="card border-left-warning shadow-sm mb-2 card-filter-target p-0" id="card-sum-0" style="cursor:pointer" onclick="filterBySummary('0', this)">
                <div class="card-body pt-3 pb-3">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">NOT YET PROCESS</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="card-belum">—</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-times-circle fa-2x text-danger"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <!-- MODEL CARDS -->
            <div class="row mb-3">
                <div class="col-12 d-flex flex-wrap" id="model-cards" style="gap: 10px;">
                    <!-- Generated via JS -->
                </div>
            </div>
        </div>
    </div>
    <!-- MAIN PANEL -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table"></i> Data Bank VLT</h6>
            <div>
                <button class="btn btn-sm btn-outline-danger mr-2" id="btn-clear-filter" style="display:none;" onclick="clearActiveFilters()">
                    <i class="fas fa-times"></i> Clear Filter
                </button>
                <button class="btn btn-sm btn-success" onclick="downloadExcel()">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <!-- Filter Bar -->
            <div class="d-flex flex-wrap align-items-end mb-3" style="gap:15px;">
                <div class="d-flex flex-column">
                    <label style="font-size:.75rem; font-weight:bold;" class="mb-1">Month</label>
                    <input type="month" id="f-month" class="form-control form-control-sm" style="max-width:150px;" onchange="loadData()">
                </div>
                <div class="d-flex flex-column">
                    <label style="font-size:.75rem; font-weight:bold;" class="mb-1">Status</label>
                    <select id="f-status" class="form-control form-control-sm" style="max-width:150px;" onchange="loadData()">
                        <option value="">All</option>
                        <option value="0">Not Yet Process</option>
                        <option value="1">Already Process</option>
                    </select>
                </div>
                <button class="btn btn-sm btn-primary" onclick="loadData()"><i class="fas fa-sync"></i> Refresh Data</button>
                
                <div class="ml-auto">
                    <label style="font-size:.75rem; font-weight:bold; visibility:hidden;" class="mb-1">Upload Data</label>
                    <form action="<?= base_url('import_bank_vlt') ?>" method="post" enctype="multipart/form-data" id="form-upload" class="mb-0">
                        <div class="input-group input-group-sm" style="width: 340px;">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="bvlt-file-input" name="upload-file" accept=".xls,.xlsx" required>
                                <label class="custom-file-label" for="bvlt-file-input" id="bvlt-file-label" style="overflow:hidden;">Pilih File Excel</label>
                            </div>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-info ml-2" id="btn-upload">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="tableFixHead" style="border: 1px solid #dee2e6;">
                <table class="table table-bordered table-hover table-sm mb-0 bvlt-table" id="bvlt-table">
                    <thead>
                        <tr id="header-row">
                            <th onclick="sortTable(0)"  data-col="0"  style="min-width:40px">No <span class="sort-icon"></span></th>
                            <th onclick="sortTable(1)"  data-col="1"  style="min-width:80px">WOS Material <span class="sort-icon"></span></th>
                            <th onclick="sortTable(2)"  data-col="2"  style="min-width:200px">WOS Mat. Desc <span class="sort-icon"></span></th>
                            <th onclick="sortTable(3)"  data-col="3"  style="min-width:140px">SAPNIK <span class="sort-icon"></span></th>
                            <th onclick="sortTable(4)"  data-col="4"  style="min-width:100px">SAP Material <span class="sort-icon"></span></th>
                            <th onclick="sortTable(5)"  data-col="5"  style="min-width:80px">Eng. Model <span class="sort-icon"></span></th>
                            <th onclick="sortTable(6)"  data-col="6"  style="min-width:80px">Eng. Prefix <span class="sort-icon"></span></th>
                            <th onclick="sortTable(7)"  data-col="7"  style="min-width:100px">Eng. Number <span class="sort-icon"></span></th>
                            <th onclick="sortTable(8)"  data-col="8"  style="min-width:60px">Plant <span class="sort-icon"></span></th>
                            <th onclick="sortTable(9)"  data-col="9"  style="min-width:140px">Chassis No <span class="sort-icon"></span></th>
                            <th onclick="sortTable(10)" data-col="10" style="min-width:70px">Lot Code <span class="sort-icon"></span></th>
                            <th onclick="sortTable(11)" data-col="11" style="min-width:100px">Lot Number <span class="sort-icon"></span></th>
                            <th onclick="sortTable(12)" data-col="12" style="min-width:90px">Katashiki <span class="sort-icon"></span></th>
                            <th onclick="sortTable(13)" data-col="13" style="min-width:70px">Katas Sfx <span class="sort-icon"></span></th>
                            <th onclick="sortTable(14)" data-col="14" style="min-width:130px">ADM Prod. ID <span class="sort-icon"></span></th>
                            <th onclick="sortTable(15)" data-col="15" style="min-width:130px">TAM Prod. ID <span class="sort-icon"></span></th>
                            <th onclick="sortTable(16)" data-col="16" style="min-width:100px">Plan Del. Date <span class="sort-icon"></span></th>
                            <th onclick="sortTable(17)" data-col="17" style="min-width:100px">Plan Jig Date <span class="sort-icon"></span></th>
                            <th onclick="sortTable(18)" data-col="18" style="min-width:100px">WOS Rel. Date <span class="sort-icon"></span></th>
                            <th onclick="sortTable(19)" data-col="19" style="min-width:120px">SAPWOS-DES <span class="sort-icon"></span></th>
                            <th onclick="sortTable(20)" data-col="20" style="min-width:80px">Location <span class="sort-icon"></span></th>
                            <th onclick="sortTable(21)" data-col="21" style="min-width:80px">Color Code <span class="sort-icon"></span></th>
                            <th onclick="sortTable(22)" data-col="22" style="min-width:50px">ED <span class="sort-icon"></span></th>
                            <th onclick="sortTable(23)" data-col="23" style="min-width:60px">ORDER <span class="sort-icon"></span></th>
                            <th onclick="sortTable(24)" data-col="24" style="min-width:70px">Destination <span class="sort-icon"></span></th>
                            <th onclick="sortTable(25)" data-col="25" style="min-width:70px">Model <span class="sort-icon"></span></th>
                            <th onclick="sortTable(26)" data-col="26" style="min-width:110px">Status <span class="sort-icon"></span></th>
                            <th onclick="sortTable(27)" data-col="27" style="min-width:60px">Plant <span class="sort-icon"></span></th>
                            <th onclick="sortTable(28)" data-col="28" style="min-width:60px">PDD <span class="sort-icon"></span></th>
                            <th style="min-width:60px">Action</th>
                        </tr>
                        <tr class="filter-row" id="filter-row">
                            <th></th>
                            <th><input type="text" placeholder="Cari..." data-col="1" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="2" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="3" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="4" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="5" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="6" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="7" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="8" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="9" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="10" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="11" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="12" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="13" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="14" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="15" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="16" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="17" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="18" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="19" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="20" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="21" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="22" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="23" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="24" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="25" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="26" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="27" oninput="applyColFilter()"></th>
                            <th><input type="text" placeholder="Cari..." data-col="28" oninput="applyColFilter()"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="bvlt-tbody">
                        <tr>
                            <td colspan="30">
                                <div class="bvlt-loader text-center"><i class="fas fa-circle-notch spin"></i> Memuat data...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-2 text-right">
                <span class="data-counter font-weight-bold">Menampilkan <span id="cnt-shown" class="text-primary">0</span> / <span id="cnt-total" class="text-primary">0</span> data</span>
            </div>
        </div>
    </div>
</div>
