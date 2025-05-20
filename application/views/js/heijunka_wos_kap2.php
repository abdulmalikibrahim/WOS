<script type="text/javascript" src="<?= base_url("assets/js/jquery/jquery.min.js") ?>"></script>
<script src="<?= base_url("assets/plugins/sweetalert2/dist/sweetalert2.all.js") ?>"></script>
<script src="<?= base_url("assets/js/bootstrap/js/bootstrap.min.js") ?>"></script>
<?php
if(!empty($this->session->flashdata("swal"))){
	echo $this->session->flashdata("swal");
}
?>
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
        location.href = '<?=base_url("heijunka_wos_kap2")?>';
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
        url = base+"heijunka_"+proses+"_kap2";
        console.log(url);
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
                            next_proses = "heijunka('model','Selesai','','Next_Proses')";
                        }else if(proses == "model"){
                            next_proses = "heijunka('twotone','both','Selesai','Next_Proses')";
                        }else if(proses == "twotone"){
                            next_proses = "heijunka('both','Selesai','','Next_Proses')";
                        }
                        if(next == "Selesai"){
                            swalok('Heijunka '+proses+' selesai<br><a href="<?=base_url("heijunka_wos_kap2")?>" class="btn btn-info">Selesai</a>');
                        }else{
                            swalok("Heijunka "+proses+" selesai<br>Klik di bawah ini untuk heijunka "+next+"<br><br><a href='javascript:void(0)' onclick="+next_proses+" class='btn btn-sm btn-info text-white'>Heijunka "+next+"</a>");
                        }
                    }else if(d.status == 'tidak ada two tone'){
                        swal.fire("Error","Tidak ada data Two Tone","error");
                    }else if(d.status == "mentok"){
                        swal.fire("Warning",d.res,"warning");
                    }else{
                        swal.fire("Error","Proses Heijunka Gagal","error");
                    }
                }else{
                    if(d.status == "sukses"){
                        swalok("Heijunka "+proses+" selesai<br><br><a href='javascript:void(0)' onclick='reload()' class='btn btn-sm btn-info text-white'>Selesai</a>");
                    }else if(d.status == 'tidak ada two tone'){
                        swal.fire("Error","Tidak ada data Two Tone","error");
                    }else if(d.status == "mentok"){
                        swal.fire("Warning",d.res,"warning");
                    }else{
                        swal.fire("Error","Proses Heijunka Gagal","error");
                    }
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
		window.location.href = '<?= base_url("heijunka_wos_kap2") ?>';
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
            $("#form-download").attr("action","<?=base_url("dup_kap2")?>");
        }else{
            $("#form-download").attr("action","<?=base_url("dhc_kap2")?>");
        }
    });

    $("#dummy").change(function() {
        dummy = $(this).val();
        if(dummy == "YES"){
            $("#form-download").attr("target","_blank");
        }else{
            $("#form-download").attr("target","");
        }
    });

    $("#btn-save").click(function() {
        dummy = $("#dummy").val();
        if(dummy == "YES"){
            no = 5;
            const intervalID = setInterval(() => {
                if(no == 5){
                    $(this).attr("disabled",true);
                }
                $(this).html("Reload Page "+no+"s");
                no--;
                if(no <= 0){
                    clearInterval(intervalID);
                    // location.reload()
                    window.location.href = '<?= base_url("heijunka_wos_kap2?start_vin="); ?>' + $("#start_vin").val()
                    // console.log("Reload");
                }
            }, 1000);
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
        $.ajax({
            type:"get",
            url:"<?= base_url("set_session"); ?>",
            data:{
                nik:sapnik,
                type_switch:$("#tipe_switch").val(),
            },
            success:function(r) {
                console.log(r);
            },
            error:function(a,b,c) {
                console.log(a.responseText);
            }
        });
        if($("#tipe_switch").val() == "1"){
            $("#choose_nik1").hide();
            $("#choose_nik2").hide();

            $("#choose_nik1").show();
            $("#choose_nik1").attr("style","position: fixed; bottom: 0; right:0; border-radius:5px; color:#fff; width:270px;");
            $("#choose_nik2").hide();
            $("#choose_nik2").html("");
            
            $("#choose_nik1").html(sapnik);
            if($("#choose_nik1").html() != ""){
                $("#tipe_switch").val("2");
                $("#number_row").html(no);
                $("#tr_"+no).attr("id","tr_choose_1");
                $("#td_nomor_"+no).attr("id","td_nomor_choose_1");
                $("#tr_choose_1").addClass("choose");
                $("#tr_choose_1").attr("data-id","tr_"+no);
                $("#td_nomor_choose_1").attr("data-id","td_nomor_"+no);
            }
            console.log($("#choose_nik1").html(),$("#choose_nik2").html(),$("#tipe_switch").val());
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
                success:function(r) {
                    console.log(r,$("#choose_nik1").html(),$("#choose_nik2").html());
                    if(r != "sukses"){
                        swal.fire({
                            title:"Error",
                            html:r,
                            icon:"error",
                            allowOutsideClick: false,
                        }).then((result) => {
                            if(result.isConfirmed){
                                // window.reload();
                            }
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
                            urutan3 = parseInt(urutan1) + 2;
                            $("tbody tr:nth-child("+urutan2+")").insertBefore("tbody tr:nth-child("+urutan1+")");

                            $("tbody tr:nth-child("+urutan3+")").insertAfter("tbody tr:nth-child("+urutan2+")");
                            
                        }else{
                            urutan3 = parseInt(urutan2) + 1;
                            $("tbody tr:nth-child("+urutan1+")").insertBefore("tbody tr:nth-child("+urutan2+")");

                            $("tbody tr:nth-child("+urutan3+")").insertAfter("tbody tr:nth-child("+urutan1+")");
                        }

                        // setTimeout(() => {
                        //     $("#choose_nik1").html("");
                        //     $("#choose_nik2").html("");
                        // }, 5000);
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
    if(bot_ng > 0){
        $("#card-footer-both").html(bot_ng);
        $("#card-footer-both").addClass("bg-danger");
    }else{
        $("#card-footer-both").html('SUKSES');
        $("#card-footer-both").addClass("bg-success");
    }

	ng_model = $(".ng-model").length;
	if(ng_model > 0){
		$("#ng-model").addClass("bg-danger text-light");
	}else{
		$("#ng-model").removeClass("bg-danger text-light");
	}
</script>
<?php
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
