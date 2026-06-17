<style>
    /* ── Cards ───────────────────────────────────────────────── */
    .sc-card {
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s;
        background: #fff;
        box-shadow: 0 2px 8px rgba(25,118,210,.13);
    }
    .sc-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(25,118,210,.22);
    }
    .sc-card .card-header {
        background: #1976D2;
        color: #fff;
        border-radius: 10px 10px 0 0 !important;
        padding: 10px 14px 8px;
    }
    .sc-card .card-code { font-size: 1.5rem; font-weight: 800; letter-spacing: 1px; }
    .sc-card .card-name { font-size: 8pt; opacity: .85; }
    .sc-card .sc-stat { font-size: 8pt; padding: 3px 0; }
    .sc-stat .sc-lbl { color: #6c757d; font-weight: 600; text-transform: uppercase; }
    .sc-stat .sc-val { font-weight: 700; color: #212529; }
    .sc-stat .sc-val.avail-ok   { color: #2e7d32; }
    .sc-stat .sc-val.avail-warn { color: #e65100; }
    .sc-stat .sc-val.avail-neg  { color: #b71c1c; }

    /* ── Detail Table ────────────────────────────────────────── */
    .sc-tbl-wrap { overflow-x: auto; }
    .sc-tbl {
        border-collapse: collapse;
        font-size: 10pt;
        white-space: nowrap;
        min-width: 100%;
    }
    .sc-tbl th, .sc-tbl td {
        border: 1px solid #c8d6e0;
        padding: 6px 12px;
        text-align: center;
    }
    .sc-tbl thead th {
        background: #1976D2;
        color: #fff;
        font-weight: 700;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .sc-tbl thead th.col-kts {
        text-align: left;
        min-width: 130px;
        left: 0;
        z-index: 3;
    }
    .sc-tbl tbody td.col-kts {
        text-align: left;
        background: #f5f8ff;
        position: sticky;
        left: 0;
        z-index: 1;
    }
    .sc-tbl tbody tr:hover td { background: #dbeafe !important; cursor: default; }
    .sc-tbl tr.row-subtotal td {
        background: #e3f2fd;
        font-weight: 700;
        color: #0d47a1;
    }
    .sc-tbl tr.row-subtotal td.col-kts {
        background: #1565C0;
        color: #fff;
    }
    .sc-tbl tr.row-total td {
        background: #1976D2;
        font-weight: 700;
        color: #fff;
    }
    .sc-tbl td.avail-ok   { background: #e8f5e9 !important; color: #1b5e20; }
    .sc-tbl td.avail-warn { background: #fff8e1 !important; color: #e65100; }
    .sc-tbl td.avail-neg  { background: #ffebee !important; color: #b71c1c; }
    .sc-tbl input.add-mdp-input {
        width: 72px;
        text-align: center;
        border: 1px solid #90caf9;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 10pt;
        background: #f0f9ff;
    }
    .sc-tbl input.add-mdp-input:focus { outline: none; border-color: #1976D2; background: #fff; }
    .sc-tbl input.add-mdp-input.saving { opacity: .5; }

    /* ── Loading overlay ─────────────────────────────────────── */
    #sc-loading {
        display: none;
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(255,255,255,.72);
        align-items: center; justify-content: center;
    }
    .sc-spinner {
        width: 50px; height: 50px;
        border: 5px solid #e3eafc;
        border-top-color: #1976D2;
        border-radius: 50%;
        animation: sc-spin .7s linear infinite;
    }
    @keyframes sc-spin { to { transform: rotate(360deg); } }

    /* ── Modal ───────────────────────────────────────────────── */
    #modal-sc-detail .modal-header { background: #1976D2; color: #fff; }
    #modal-sc-detail .modal-header .close { color: #fff; opacity: 1; }
    #modal-upload-sc .modal-header { background: #1976D2; color: #fff; }
    #modal-upload-sc .modal-header .close { color: #fff; opacity: 1; }

    /* ── Empty state ─────────────────────────────────────────── */
    #sc-empty { display:none; text-align:center; padding: 40px 0; color: #9e9e9e; font-size: 11pt; }
</style>

<!-- Loading Overlay -->
<div id="sc-loading" style="display:none; align-items:center; justify-content:center;">
    <div style="text-align:center;">
        <div class="sc-spinner mx-auto"></div>
        <div class="mt-2 font-weight-bold text-primary">Memuat data...</div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12" align="center">
        <h1 class="m-0 text-white" style="font-size:3rem;">Suffix Control</h1>
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
            <input type="month" id="sc-period" class="form-control form-control-sm" style="width:160px;">
        </div>
        <div style="padding-top:18px;">
            <button class="btn btn-sm btn-primary" onclick="loadData()">
                <i class="fas fa-sync mr-1"></i>Tampilkan
            </button>
        </div>
        <div style="padding-top:18px;">
            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-upload-sc">
                <i class="fas fa-upload mr-1"></i>Upload Plan
            </button>
        </div>
        <div style="padding-top:18px;">
            <a href="<?=base_url('download_template_suffix_control')?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-excel mr-1"></i>Download Template
            </a>
        </div>
        <div style="padding-top:18px;">
            <button class="btn btn-sm btn-outline-danger" onclick="clearPeriod()">
                <i class="fas fa-trash mr-1"></i>Hapus Data Periode
            </button>
        </div>
    </div>

    <!-- Cards Grid -->
    <div id="sc-cards" class="row" style="gap:0;"></div>
    <div id="sc-empty">
        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
        Tidak ada data untuk periode ini. Silakan upload terlebih dahulu.
    </div>

</div>

<!-- Modal Detail -->
<div class="modal fade" id="modal-sc-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="max-width:96vw;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold" id="sc-detail-title">
                    <i class="fas fa-table mr-1"></i>Detail Suffix Control
                </h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-2">
                <div class="sc-tbl-wrap" style="max-height:70vh; overflow-y:auto;">
                    <table class="sc-tbl" id="sc-detail-table">
                        <thead>
                            <tr>
                                <th class="col-kts">Katashiki</th>
                                <th>Sfx</th>
                                <th>Plan MDP</th>
                                <th>Add. MDP</th>
                                <th>Total Plan</th>
                                <th>Act. Receive</th>
                                <th>Remain</th>
                                <th>Act. WOS</th>
                                <th>Available</th>
                            </tr>
                        </thead>
                        <tbody id="sc-detail-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="modal-upload-sc" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-upload mr-1"></i>Upload Plan Suffix Control</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="font-weight-bold small">Periode (Bulan &amp; Tahun) <span class="text-danger">*</span></label>
                    <input type="month" id="sc-up-period" class="form-control form-control-sm" style="width:180px;">
                    <small class="text-muted">Data lama untuk periode yang sama akan ditimpa.</small>
                </div>
                <div class="form-group mb-2">
                    <label class="font-weight-bold small">File Excel <span class="text-danger">*</span></label>
                    <div class="custom-file" style="max-width:340px;">
                        <input type="file" class="custom-file-input" id="sc-file-input" accept=".xls,.xlsx">
                        <label class="custom-file-label" id="sc-file-label" for="sc-file-input">Pilih file Excel...</label>
                    </div>
                    <small class="text-muted d-block mt-1">Format: Kol A=Katashiki, B=Suffix, C=Model Name, D=Model Code, E=Plan MDP.</small>
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
