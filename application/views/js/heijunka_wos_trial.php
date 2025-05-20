<script type="text/javascript" src="<?= base_url("assets/js/jquery/jquery.min.js") ?>"></script>
<script src="<?= base_url("assets/plugins/sweetalert2/dist/sweetalert2.all.js") ?>"></script>
<script src="<?= base_url("assets/js/bootstrap/js/bootstrap.min.js") ?>"></script>
<script>
    <?php
    if(!empty($this->input->get("proses"))){
        echo "heijunka('color','suffix','sub');";
    }
    ?>
    function formdate_show() {
        $("#formdate").modal("show");
    }

    function swalok(html,ok_btn) {
        Swal.fire({
            title: "Selesai",
            icon:"success",
            showConfirmButton:false,
            html: html,
            allowOutsideClick: false
        });
    }

    function loading(title,html) {
        Swal.fire({
            title: title,
            html: html,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading()
                const b = Swal.getHtmlContainer().querySelector('b')
            },
            allowOutsideClick: false
        });
    }

    function refresh() {
        location.href = '<?=base_url("heijunka_wos_trial")?>';
    }
    
    function voice(url) {
        var source = "<?=str_replace("index.php","",base_url("assets/voice/"))?>"+url;
        var audio = new Audio();
        audio.addEventListener("load",function() {
            audio.play();
        }, true);
        audio.src = source;
        audio.autoplay = true;
    }

    function heijunka(proses,next,next2,action) {
        base = "<?=base_url()?>";
		if(proses == "model"){
			proses = "model_trial";
		}else{
			proses = proses;
		}
        url = base+"heijunka_"+proses;
        $.ajax({
            url:url,
            dataType:"JSON",
            beforeSend:function() {
                loading("Heijunka "+proses,"Sedang proses heijunka "+proses+", mohon tunggu sebentar...");
            },
            success:function(r) {
                d = JSON.parse(JSON.stringify(r));
                if(action != "Refresh"){
                    if(d.status == "sukses"){
                        voice("sukses.mp3");
                        if(proses == "color"){
                            next_proses = "heijunka('suffix','sub','model','Next_Proses')";
                        }else if(proses == "suffix"){
                            next_proses = "heijunka('sub','model','twotone','Next_Proses')";
                        }else if(proses == "sub"){
                            next_proses = "heijunka('model','twotone','both','Next_Proses')";
                        }else if(proses == "model_trial"){
                            next_proses = "heijunka('twotone','both','Selesai','Next_Proses')";
                        }else if(proses == "twotone"){
                            next_proses = "heijunka('both','Selesai','','Next_Proses')";
                        }
                        if(next == "Selesai"){
                            swalok('Heijunka '+proses+' selesai<br>Pilih file excel di bawah ini untuk upload TD-Link<br><br><form action="<?=base_url("import_td_link")?>" method="post" id="import_td_link_msg" enctype="multipart/form-data"><div class="input-group"><div class="custom-file" align="left"><input type="file" class="custom-file-input" onchange="upload_td_link()" name="upload-file" accept=".xls"><label class="custom-file-label" for="customFile" id="customFile">Pilih File Excel TD-Link</label></div></div></form><br><br><a href="<?=base_url("heijunka_wos_trial")?>" class="btn btn-info">Selesai Tanpa Upload TD-Link</a>');
                        }else{
                            swalok("Heijunka "+proses+" selesai<br>Klik di bawah ini untuk heijunka "+next+"<br><br><a href='javascript:void(0)' onclick="+next_proses+" class='btn btn-sm btn-info text-white'>Heijunka "+next+"</a>");
                        }
                    }else if(d.status == 'tidak ada two tone'){
                        swal.fire("Error","Tidak ada data Two Tone","error");
                    }else{
                        swal.fire("Error","Proses Heijunka Gagal","error");
                    }
                }else{
                    swalok("Heijunka "+proses+" selesai<br><br><a href='javascript:void(0)' onclick='reload()' class='btn btn-sm btn-info text-white'>Selesai</a>");
                }
				console.log(r);
            },
            error:function(xhr,status,error) {
                voice("sukses.mp3");
                swal.fire("Error",xhr.responseText,"error");
            }
        })
    }

	function reload() {
		window.location.href = '<?= base_url("heijunka_wos_trial") ?>';
	}

    $("#upload-excel").change(function() {
        loading("Sedang Upload...","Mohon tunggu sebentar");
        $("#import_td_link").submit();
    });

    $("#upload-wos").change(function() {
        loading("Sedang Upload...","Mohon tunggu sebentar");
        $("#import_wos").submit();
    });

    function upload_td_link() {
        $("#import_td_link_msg").submit();
        loading("Sedang Upload...","Mohon tunggu sebentar");
    }
</script>
<script>
    $("#tipe").change(function() {
        tipe = $(this).val();
        if(tipe == "PIS"){
            $("#form-download").attr("action","<?=base_url("dup")?>");
        }else{
            $("#form-download").attr("action","<?=base_url("dhc")?>");
        }
    });

    $("#choose_nik1").hide();
    $("#choose_nik2").hide();

    $(document).keyup(function(e) {
        if(e.key === "Escape"){
            cancel();
        }
    })

    function cancel() {
        $("#choose_nik1").attr("style","position: fixed; bottom: 0; right:0; border-radius:5px; color:#fff; width:270px;");
        $("#choose_nik2").attr("style","position: fixed; bottom: 0; right:0; border-radius:5px; color:#fff; width:270px;");
        $("#choose_nik1").html("");
        $("#choose_nik2").html("");
        $("#number_row").html("");
        $("#tipe_switch").val("1");

        data_id1 = $("#tr_choose_1").attr("data-id");
        data_id2 = $("#tr_choose_2").attr("data-id");

        $("#tr_choose_1").attr("id",data_id1);
        $("#tr_choose_2").attr("id",data_id2);

        $("#"+data_id1).attr("data-id","");
        $("#"+data_id2).attr("data-id","");

        $(".choose").removeClass("choose");
        
        $("#choose_nik1").hide();
        $("#choose_nik2").hide();
    }

    function choose_nik(no,sapnik) {
        if($("#tipe_switch").val() == "1"){
            $("#choose_nik1").hide();
            $("#choose_nik2").hide();

            $("#choose_nik1").show();
            $("#choose_nik1").attr("style","position: fixed; bottom: 0; right:0; border-radius:5px; color:#fff; width:270px;");
            $("#choose_nik2").hide();
            $("#choose_nik2").html("");
            $("#choose_nik1").html(sapnik);
            $("#tipe_switch").val("2");
            $("#number_row").html(no);
            $("#tr_"+no).attr("id","tr_choose_1");
            $("#td_nomor_"+no).attr("id","td_nomor_choose_1");
            $("#tr_choose_1").addClass("choose");
            $("#tr_choose_1").attr("data-id","tr_"+no);
            $("#td_nomor_choose_1").attr("data-id","td_nomor_"+no);
        }else{
            $("#choose_nik2").show();
            $("#choose_nik2").html(sapnik);
            $("#choose_nik1").attr("style","position: fixed; bottom: 40px; right:0; border-radius:5px; color:#fff; width:270px;");
            $("#tr_"+no).attr("id","tr_choose_2");
            $("#td_nomor_"+no).attr("id","td_nomor_choose_2");
            $("#tr_choose_2").addClass("choose");
            $("#tr_choose_2").attr("data-id","tr_"+no);
            $("#td_nomor_choose_2").attr("data-id","td_nomor_"+no);
            $.ajax({
                type:"get",
                url:"<?= base_url("tukar_sapnik"); ?>",
                data:{
                    nik1:$("#choose_nik1").html(),
                    nik2:$("#choose_nik2").html(),
                },
                success:function(r) {
                    if(r != "sukses"){
                        swal.fire({
                            title:"Error",
                            html:r,
                            icon:"error",
                            allowOutsideClick: false,
                        });
                    }else{

                        urutan1 = $("#number_row").html();
                        urutan2 = no;
                        
                        $("#tr_choose_1").removeClass("choose");
                        $("#tr_choose_2").removeClass("choose");

                        $("#td_nomor_choose_1").html(parseInt(urutan2-1));
                        $("#td_nomor_choose_2").html(parseInt(urutan1-1));

                        $("#tr_choose_1").attr("onclick","choose_nik('"+urutan2+"','"+$("#choose_nik1").html()+"')");
                        $("#tr_choose_2").attr("onclick","choose_nik('"+urutan1+"','"+$("#choose_nik2").html()+"')");
                        
                        $("#tr_choose_1").attr("id","tr_"+urutan2);
                        $("#tr_choose_2").attr("id","tr_"+urutan1);

                        $("#td_nomor_choose_1").attr("id","td_nomor_"+urutan2);
                        $("#td_nomor_choose_2").attr("id","td_nomor_"+urutan1);

                        if(urutan1 < urutan2){
                            urutan3 = parseInt(urutan1) + 1;
                            $("tbody tr:nth-child("+urutan2+")").insertBefore("tbody tr:nth-child("+urutan1+")");

                            $("tbody tr:nth-child("+urutan3+")").insertAfter("tbody tr:nth-child("+urutan2+")");
                            
                        }else{
                            urutan3 = parseInt(urutan2) + 1;
                            $("tbody tr:nth-child("+urutan1+")").insertBefore("tbody tr:nth-child("+urutan2+")");

                            $("tbody tr:nth-child("+urutan3+")").insertAfter("tbody tr:nth-child("+urutan1+")");
                        }

                        setTimeout(() => {
                            $("#choose_nik1").hide(800);
                            $("#choose_nik2").hide(800);
                            $("#choose_nik1").html("");
                            $("#choose_nik2").html("");
                        }, 5000);
                        console.log(urutan1+","+urutan2);
                    }
                },
                error:function(a,b,c) {
                    swal.fire(c,a.responseText,b);
                }
            });
            $("#tipe_switch").val("1");
        }
    }
</script>
<script>
    bot_ng = $(".bot-ng").length;
    console.log(bot_ng);
    if(bot_ng > 0){
        $("#card-footer-both").html(bot_ng);
        $("#card-footer-both").addClass("bg-danger");
    }else{
        $("#card-footer-both").html('SUKSES');
        $("#card-footer-both").addClass("bg-success");
    }
</script>
<?php
// if($ng_both > 0){
//     ?>
//     <script>
//         $("#card-footer-both").html('<?=$ng_both?>');
//         $("#card-footer-both").addClass("bg-danger");
//     </script>
//     <?php
// }else{
//     ?>
//     <script>
//         $("#card-footer-both").html('SUKSES');
//         $("#card-footer-both").addClass("bg-success");
//     </script>
//     <?php
// }

if($count_td_link->count > 0){
    ?>
    <script>
        $("#card-footer-td-link").html('<?=$count_td_link->count?>');
        $("#card-footer-td-link").addClass("bg-success");
    </script>
    <?php
}else{
    ?>
    <script>
        $("#card-footer-td-link").html('0');
        $("#card-footer-td-link").addClass("bg-warning");
    </script>
    <?php
}
?>
