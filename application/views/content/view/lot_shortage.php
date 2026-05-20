<div class="container-fluid">

    <div class="row d-flex justify-content-center">
        <div class="col-xl-4 mt-4">
			<div class="d-sm-flex align-items-center justify-content-between mb-4">
				<h1 class="h4 m-0 text-white font-weight-bold" style="font-size:2.5rem;"><?=$title?></h1>
				<span class="badge badge-danger p-2 shadow-sm">
					Total <?= count($list_kurang) ?> Lot Bermasalah
				</span>
			</div>
			<div class="text-right">
				<a href="<?= base_url("docking_wos_dummy"); ?>" class="btn btn-sm btn-danger mb-3">Kembali</a>
			</div>
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-danger text-white">
                                <tr>
                                    <th class="py-3 px-4 border-0">LOT CODE</th>
                                    <th class="text-center py-3 border-0">DATA TABUNGAN</th>
                                    <th class="text-center py-3 border-0">DATA PIS KAP</th>
                                    <th class="text-center py-3 border-0">STATUS KURANG</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($list_kurang)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i><br>
                                            Mantap Bos! Semua data Tabungan sudah sesuai atau melebihi PIS KAP.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($list_kurang as $l): ?>
                                    <tr>
                                        <td class="px-4 align-middle">
                                            <span class="h6 font-weight-bold text-dark mb-0"><?= $l['lot_code'] ?></span>
                                        </td>
										<td class="text-center align-middle text-danger font-weight-bold"><?= $l['tabungan'] ?></td>
                                        <td class="text-center align-middle"><?= $l['pis_kap'] ?></td>
                                        <td class="text-center align-middle">
                                            <div class="badge badge-danger-soft text-danger px-3 py-2" style="background: #fff5f5; border: 1px solid #feb2b2; border-radius: 10px;">
                                                <i class="fas fa-arrow-down mr-1"></i> Kurang <strong><?= $l['kurang'] ?></strong>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
