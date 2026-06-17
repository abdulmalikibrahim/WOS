<style>
    .cc-table-wrap { overflow-x: auto; }
    .cc-table {
        border-collapse: collapse;
        font-size: 7.5pt;
        white-space: nowrap;
        min-width: 100%;
    }
    .cc-table th, .cc-table td {
        border: 1px solid #c8d6e0;
        padding: 4px 6px;
        text-align: center;
        min-width: 30px;
    }
    .cc-table thead th {
        background: #1976D2;
        color: #fff;
        font-weight: 700;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .cc-table thead th.col-color {
        left: 0;
        z-index: 3;
        min-width: 60px;
    }
    .cc-table tbody td.col-color {
        background: #f0f4ff;
        font-weight: 700;
        color: #1565C0;
        position: sticky;
        left: 0;
        z-index: 1;
    }
    .cc-table tfoot td {
        background: #e8f0fe;
        font-weight: 700;
        color: #0d47a1;
    }
    .cc-table tfoot td.col-color { background: #1565C0; color: #fff; }
    .cc-table td.total-col { background: #fff8e1; font-weight: 700; color: #e65100; }
    .cc-table thead th.total-col { background: #e65100; }
    .cc-table tbody tr:hover td { background: #dbeafe !important; cursor: default; }

    /* diff table */
    .diff-plus  { background: #e8f5e9 !important; color: #1b5e20; }
    .diff-minus { background: #ffebee !important; color: #b71c1c; }
    .diff-zero  { color: #aaa; }

    /* section header */
    .section-title {
        font-size: 11pt; font-weight: 700; color: #1565C0;
        padding: 6px 10px; border-left: 4px solid #1976D2;
        background: #e8f0fe; border-radius: 0 4px 4px 0;
        margin-bottom: 8px;
    }

    /* loading overlay */
    #cc-loading {
        display: none;
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(255,255,255,0.7);
        align-items: center; justify-content: center;
    }
    .cc-spinner {
        width: 50px; height: 50px;
        border: 5px solid #e3eafc;
        border-top-color: #1976D2;
        border-radius: 50%;
        animation: cc-spin .7s linear infinite;
    }
    @keyframes cc-spin { to { transform: rotate(360deg); } }

    /* upload modal */
    #modal-upload-cc .modal-header { background: #1976D2; color: #fff; }
    #modal-upload-cc .modal-header .close { color: #fff; opacity: 1; }
</style>

<!-- Loading Overlay -->
<div id="cc-loading" style="display:none; align-items:center; justify-content:center;">
    <div style="text-align:center;">
        <div class="cc-spinner mx-auto"></div>
        <div class="mt-2 font-weight-bold text-primary">Memuat data...</div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;">Color Control</h1>
    </div>
    <a href="<?=base_url("")?>" class="btn btn-sm btn-danger" style="position:absolute; top:15px; right:20px;">
        <i class="fas fa-home"></i> Main Menu
    </a>
</div>

<div class="card card-body mt-3">

    <!-- Filter Bar -->
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:10px; border-bottom:1px solid #e9ecef; padding-bottom:12px;">
        <div>
            <label class="mb-1 d-block" style="font-size:7.5pt;font-weight:700;color:#6c757d;text-transform:uppercase;">Periode</label>
            <input type="month" id="cc-period" class="form-control form-control-sm" style="width:160px;">
        </div>
        <div>
            <label class="mb-1 d-block" style="font-size:7.5pt;font-weight:700;color:#6c757d;text-transform:uppercase;">Model</label>
            <select id="cc-model" class="form-control form-control-sm" style="width:160px;">
                <option value="">-- All Model --</option>
            </select>
        </div>
        <div style="padding-top:18px;">
            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-upload-cc">
                <i class="fas fa-upload mr-1"></i>Upload Plan
            </button>
        </div>
        <div style="padding-top:18px;">
            <a href="<?=base_url('download_template_color_control')?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-excel mr-1"></i>Download Template
            </a>
        </div>
        <div style="padding-top:18px;">
            <button class="btn btn-sm btn-outline-danger" onclick="clearPeriod()">
                <i class="fas fa-trash mr-1"></i>Clear Data
            </button>
        </div>
    </div>

    <!-- PLAN TABLE -->
    <div class="section-title mb-2"><i class="fas fa-calendar-alt mr-2"></i>Plan</div>
    <div class="cc-table-wrap mb-4" style="max-height:340px; overflow-y:auto;">
        <table class="cc-table" id="tbl-plan">
            <thead><tr id="plan-head"></tr></thead>
            <tbody id="plan-body"></tbody>
            <tfoot><tr id="plan-foot"></tr></tfoot>
        </table>
    </div>

    <!-- ACTUAL TABLE -->
    <div class="section-title mb-2"><i class="fas fa-check-circle mr-2"></i>Actual</div>
    <div class="cc-table-wrap mb-4" style="max-height:340px; overflow-y:auto;">
        <table class="cc-table" id="tbl-actual">
            <thead><tr id="actual-head"></tr></thead>
            <tbody id="actual-body"></tbody>
            <tfoot><tr id="actual-foot"></tr></tfoot>
        </table>
    </div>

    <!-- DIFF TABLE -->
    <div class="section-title mb-2"><i class="fas fa-balance-scale mr-2"></i>Difference (Plan - Actual)</div>
    <div class="cc-table-wrap mb-2" style="max-height:340px; overflow-y:auto;">
        <table class="cc-table" id="tbl-diff">
            <thead><tr id="diff-head"></tr></thead>
            <tbody id="diff-body"></tbody>
            <tfoot><tr id="diff-foot"></tr></tfoot>
        </table>
    </div>

</div>

<!-- Modal Upload -->
<div class="modal fade" id="modal-upload-cc" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-upload mr-1"></i>Upload Plan Color Control</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <div class="d-flex align-items-start flex-wrap" style="gap:14px;">
                        <div>
                            <label class="font-weight-bold small">Periode (Bulan &amp; Tahun) <span class="text-danger">*</span></label>
                            <input type="month" id="up-period" class="form-control form-control-sm" style="width:160px;">
                        </div>
                        <div>
                            <label class="font-weight-bold small">Model <span class="text-danger">*</span></label>
                            <select id="up-model" class="form-control form-control-sm" style="width:160px;">
                                <option value="">-- Pilih Model --</option>
                            </select>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">Data lama untuk periode &amp; model yang sama akan ditimpa.</small>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">File Excel <span class="text-danger">*</span></label>
                    <div class="custom-file" style="max-width:340px;">
                        <input type="file" class="custom-file-input" id="cc-file-input" accept=".xls,.xlsx">
                        <label class="custom-file-label" id="cc-file-label" for="cc-file-input">Pilih file Excel...</label>
                    </div>
                    <small class="text-muted d-block mt-1">Format: Kolom A = Color, Kolom B-AF = tanggal 1–31.</small>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-success" onclick="doUpload()">
                    <i class="fas fa-upload mr-1"></i>Upload
                </button>
            </div>
        </div>
    </div>
</div>
