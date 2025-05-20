

<?php
//KAP 1
$array_suffix = $this->model->gds("plan_wos","suffix,plan,batch","plan IS NOT NULL AND suffix !=''","result");
$total_plan = $this->model->gds("plan_wos","SUM(plan) as total_plan","plan IS NOT NULL AND suffix !='' GROUP BY suffix","row");
$total_suffix = count($array_suffix);
$suffix_final = '';
foreach ($array_suffix as $array_suffix) {
    $suffix_final .= "'".$array_suffix->suffix."-".$array_suffix->batch."':".$array_suffix->plan.", ";
}

//KAP 2
$array_suffix_kap2 = $this->model->gds("plan_wos_kap2","suffix,plan,batch","plan IS NOT NULL AND suffix !=''","result");
$total_plan_kap2 = $this->model->gds("plan_wos_kap2","SUM(plan) as total_plan","plan IS NOT NULL AND suffix !='' GROUP BY suffix","row");
$total_suffix_kap2 = count($array_suffix_kap2);
$suffix_final_kap2 = '';
foreach ($array_suffix_kap2 as $array_suffix_kap2) {
    $suffix_final_kap2 .= "'".$array_suffix_kap2->suffix."-".$array_suffix_kap2->batch."':".$array_suffix_kap2->plan.", ";
}
?>
<script>
    $(document).ready(function() {
        load_data();
		load_data_kap2();
    });

    function load_data() {
        $.ajax({
            url:"<?=base_url("load_tabungan")?>",
            beforeSend:function() {
                $("#data_tabungan").html('<tr><td align="center" colspan="25" class="align-middle"><i>Sedang Memuat...</i></td></tr>');
            },
            success:function(r) {
                $("#data_tabungan").html(r);
            },
            error:function(xhr,status,error) {
                voice("gagal.mp3");
                swal.fire("Error",xhr.responseText,"error");
            }
        })
    }

	function load_data_kap2() {
		$.ajax({
			url:"<?=base_url("load_tabungan?t=kap2")?>",
			beforeSend:function() {
				$("#data_tabungan_kap2").html('<tr><td align="center" colspan="25" class="align-middle"><i>Sedang Memuat...</i></td></tr>');
			},
			success:function(r) {
				$("#data_tabungan_kap2").html(r);
			},
			error:function(xhr,status,error) {
				voice("gagal.mp3");
				swal.fire("Error",xhr.responseText,"error");
			}
		})
	}
    function docking(data = null) {
		if(data){
			tipe = data.dataset.tipe;
		}else{
			tipe = "kap1";
		}
		if(tipe == "kap1"){
			url_truncate = "<?=base_url("docking_truncate")?>";
			array_suffix = {<?= $suffix_final ?>};
			var keys = Object.keys(array_suffix);
			var last_suffix = keys[keys.length - 1];
			var total_plan = <?=!empty($total_plan->total_plan) ? ($total_plan->total_plan*1) : 0?>;
			var total_suffix = <?=!empty($total_suffix) ? $total_suffix : 0?>;
		}else{
			url_truncate = "<?=base_url("docking_truncate?t=kap2")?>";
			array_suffix = {<?= $suffix_final_kap2 ?>};
			var keys = Object.keys(array_suffix);
			var last_suffix = keys[keys.length - 1];
			var total_plan = <?= !empty($total_plan_kap2) ? ($total_plan_kap2->total_plan*1) : "0"?>;
			var total_suffix = <?=$total_suffix_kap2?>;
		}
        loading_docking("Sedang Proses Docking...","Sedang Proses Docking...");
        $.ajax({
            url:url_truncate,
            beforeSend:function() {
                $("#docking").html("Sedang Docking...");
            },
            success:function(r) {
                if(r == "sukses"){
                    snd = 0;
                    for (var key in array_suffix) {
                        process_docking(key,array_suffix[key],last_suffix,total_plan,total_suffix,tipe);
                        // console.log(key,array_suffix[key],last_suffix,total_plan,total_suffix,tipe);
                    }
                }else{
                    swal.fire("Error","Terjadi kesalahan di sistem","error");
                }
            },
            error:function(xhr,status,error) {
                swal.fire({icon:"error",title:"Maaf3...",html:xhr.responseText});
            }
        });
    }
    function process_docking(suffix,plan,last_suffix,total_plan,total_suffix,tipe) {
		if(tipe == "kap1"){
			url_docking = "<?=base_url("docking")?>";
		}else{
			url_docking = "<?=base_url("docking_kap2")?>";
		}
        var split_suffix = suffix.split("-");
        var new_suffix = split_suffix[0];
        var batch = split_suffix[1];
        $.ajax({
            type:"get",
            url:url_docking,
            data:{
                suffix:new_suffix,
                plan:plan,
                batch:batch,
            },
            dataType:"JSON",
            success:function(r) {
                d = JSON.parse(JSON.stringify(r));
                let actual = d.actual;
                let total_docking = d.total_docking;
                let balance_total_docking = parseInt(total_docking)-parseInt(total_plan);
                let percentage_docking = parseInt(total_docking)/parseInt(total_plan)*100;
                $("#swal2-html-container").html('Sistem sedang melakukan proses docking suffix <b>'+suffix+'</b><br><div class="progress mt-2" style="height:25px;"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="'+percentage_docking+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percentage_docking+'%; height:100%;"><h5 class="m-0">'+d.total_docking+'/'+total_plan+'</h5></div></div><center><h5 class="mb-0 mt-1" id="hitung_mundur"></h5></center>');
                console.log(suffix+" Plan : "+plan);
                console.log("Suffix not docking : "+d.suffix_not_docking+" ("+d.snd+")"+" Total not docking : "+d.tnd+" & Actual : "+actual+" & Total docking : "+total_docking);
                console.log(total_suffix, d.total_suffix_docking, 'here');
                if(total_suffix == d.total_suffix_docking){
                    voice("sukses.mp3");
                    swal.fire({
                        iconHtml: '<img src="<?=base_url('assets/images/happy.png')?>" width="100%">',
                        customClass: {
                            icon: 'border-0'
                        },
                        title:"Docking Selesai...",
                        html:'Sistem berhasil docking <b>'+total_docking+ '</b> data, sesuai planning<br>Silahkan klik Heijunka Twotone di bawah ini<br><a href="<?=base_url("heijunka_wos?proses=yes")?>" class="btn btn-info mt-4">Heijunka KAP 1</a><a href="<?=base_url("heijunka_wos_kap2?proses=yes")?>" class="btn btn-info mt-4 ml-2">Heijunka KAP 2</a><a href="javascript:void(0)" onclick="swal_close()" class="btn btn-secondary mt-4 ml-2">Tetap Disini</a>',
                        showConfirmButton: false,
                    });
                    $("#docking").html("Docking");
                }else{
                    if(suffix == last_suffix){
                        var waktu = 10;
                        const myInterval = setInterval(myTimer,1000);
                        function myTimer() {
                            waktu--;
                            if(waktu <= 0){
                                voice("sukses.mp3");
                                swal.fire({
                                    iconHtml: '<img src="<?=base_url('assets/images/happy.png')?>" width="100%">',
                                    customClass: {
                                        icon: 'border-0'
                                    },
                                    title:"Docking Selesai...",
                                    html:'Sistem berhasil docking <b>'+total_docking+ '</b> data, sesuai planning<br>Silahkan klik Heijunka Twotone di bawah ini<br><a href="<?=base_url("heijunka_wos?proses=yes")?>" class="btn btn-info mt-4">Heijunka KAP 1</a><a href="<?=base_url("heijunka_wos_kap2?proses=yes")?>" class="btn btn-info mt-4 ml-2">Heijunka KAP 2</a><a href="javascript:void(0)" onclick="swal_close()" class="btn btn-secondary mt-4 ml-2">Tetap Disini</a>',
                                    showConfirmButton: false,
                                });
                                clearInterval(myInterval);
                            }else{
                                $("#hitung_mundur").html("Sedang menyiapkan : "+waktu);
                                console.log(waktu);
                            }
                        }
                    }
                }

            },
            error:function(xhr,status,error) {
                voice("gagal.mp3");
                swal.fire("Error",xhr.responseText,"error");
            }
        });
    }


    function loading(title, html) {
        Swal.fire({
            title: title,
            html: html,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading()
                const b = Swal.getHtmlContainer().querySelector('b')
            },
            allowOutsideClick: false
        })
    }

    function loading_docking(title, html) {
        Swal.fire({
            title: title,
            html: html,
            showConfirmButton: false,
            allowOutsideClick: false
        });
    }
    $("#upload-excel").change(function() {
        loading("Sedang Upload...", "");
        fileupload = document.getElementById("upload-excel");
        file = fileupload.files[0];
        $("#customFile").html(file.name);
        $("#form_export").submit();
    });
    $("#upload-excel-kap2").change(function() {
        loading("Sedang Upload...", "");
        fileupload = document.getElementById("upload-excel-kap2");
        file = fileupload.files[0];
        $("#customFile-kap2").html(file.name);
        $("#form_export_kap2").submit();
    });

    <?php
    $p = $this->input->get("p");
    if(!empty($p)){
        if($p == "docking"){
            echo 'docking();';
        }
    }
    ?>
</script>
