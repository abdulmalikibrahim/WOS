<script src="<?=base_url("assets/js/popper.js/popper.min.js")?>"></script>
<script src="<?=base_url("assets/js/bootstrap/js/bootstrap.min.js")?>"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
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
        })
    }
    $("#upload-excel").change(function() {
        loading("Sedang Upload...","");
        fileupload = document.getElementById("upload-excel");
        file = fileupload.files[0];
        $("#customFile").html(file.name);
        $("#form_export").submit();
    });

    function autofullfillnumber() {
        $(".input-data").each(function (index,element) {
            let dataPlan = $(this).attr("placeholder");
            $(this).val(dataPlan);
            $(this).trigger("keyup")
        })
    }

    function validasi(stock,input,plan,keyval,pdd,tone) {
        bg = 'bg-success text-white';
        id_input = "#input_"+keyval+"_"+pdd+"_"+tone;
        if(input > 0){
            $(id_input).addClass(bg);
        }else{
            $(id_input).removeClass(bg);
        }
        if(input > plan){
            voice("gagal.mp3");
            swal.fire({
                iconHtml: '<img class="rounded-circle" src="<?=base_url("assets/images/emot-sedih.jpg")?>" width="100%">',
                customClass: {
                    icon: 'border-0'
                },
                title:"Maaf...",
                html:"Adjustment melibihi planning, planningnya cuma "+plan,
            });
            $(id_input).removeClass("bg-success");
            $(id_input).val("");
            $(id_input).focus();
            $("#"+keyval+"_"+pdd).removeClass(bg);
        }
        if(input == "0"){
            $(id_input).val("");
        }
        if(input > stock){
            voice("gagal.mp3");
            swal.fire({
                iconHtml: '<img class="rounded-circle" src="<?=base_url("assets/images/emot-sedih.jpg")?>" width="100%">',
                customClass: {
                    icon: 'border-0'
                },
                title:"Maaf...",
                html:"Adjustment melibihi stock, stock cuma ada "+stock,
            });
            $(id_input).val("");
            $(id_input).focus();
            $(id_input).removeClass(bg);
            $("#"+keyval+"_"+pdd).removeClass(bg);
        }
    }

    $(".input-data").on("keyup", function() {
        var suffix = $(this).attr("data-suffix");
        var suffix_active = $("#suffix_active").html();
        var plan = $("#plan_"+suffix).html();
        var sum = 0;
        $(".bg-two-tone").each(function(){
            sum += +$(this).val();
        });
        var sum_single_tone = 0;
        $(".bg-single-tone").each(function(){
            sum_single_tone += +$(this).val();
        });
        var sum_suffix = 0;
        $(".suffix_"+suffix).each(function() {
            sum_suffix += +$(this).val();
        });
        if(sum_suffix > plan){
            if(plan == ""){
                plan = 0;
            }else{
                plan = plan;
            }
            voice("gagal.mp3");
            swal.fire({
                iconHtml: '<img class="rounded-circle" src="<?=base_url("assets/images/emot-sedih.jpg")?>" width="100%">',
                customClass: {
                    icon: 'border-0'
                },
                title:"Maaf...",
                html:"Adjustment melibihi planning, planning cuma "+plan,
            });
            $(this).val("");
            $(this).focus();
            $(this).removeClass("bg-secondary text-white");
            var id_input = $(this).attr("id");
            var id_cell = id_input.replace("input_","");
            $("#"+id_cell).removeClass("bg-secondary");
        }else{
            if(!($(this).val() != "")){
                $("#1tone_adjust_"+suffix).html("");
                $("#actual_"+suffix).html("");
            }
            let adjust_suffix = parseInt($("#plan_"+suffix).html())-parseInt(sum_suffix);
            if(sum_suffix !== 0){
                if(adjust_suffix <= 0){
                    $("#adjust_"+suffix).removeClass("bg-white");
                    $("#adjust_"+suffix).removeClass("bg-danger");
                    $("#adjust_"+suffix).addClass("bg-success");
                    $("#adjust_"+suffix).html("OK");
                    $("#1tone_adjust_"+suffix).html(plan - sum_suffix);
                    // $("#actual_"+suffix).html(parseInt(sum_suffix) + parseInt(plan - sum_suffix));
                    $("#actual_"+suffix).html(parseInt(sum_suffix));
                    // alert(sum_suffix);
                }else{
                    $("#adjust_"+suffix).removeClass("bg-white");
                    $("#adjust_"+suffix).removeClass("bg-success");
                    $("#adjust_"+suffix).addClass("bg-danger");
                    $("#adjust_"+suffix).html("NG");
                    $("#1tone_adjust_"+suffix).html(($("#single_tone_"+suffix).html()*1)+"/"+(plan - sum_suffix));
                    $("#actual_"+suffix).html(sum_suffix);
                }
            }else{
                $("#adjust_"+suffix).removeClass("bg-success");
                $("#adjust_"+suffix).removeClass("bg-danger");
                $("#adjust_"+suffix).addClass("bg-white");
                $("#adjust_"+suffix).html("");
            }
            remain_suffix(suffix);
            total_remain();
        }
        $("#adjust").html(sum);
        $("#total-single-tone").html(sum_single_tone);
    });

    function remain_suffix(suffix) {
		var remain = $("#actual_"+suffix).html() - parseInt($("#plan_"+suffix).html());
        $("#remain-"+suffix).html(remain);
        $("#remain-"+suffix).removeClass("bg-success");
        $("#remain-"+suffix).addClass("bg-danger text-white");
        if(parseInt($("#remain-"+suffix).html()) == "0"){
            $("#remain-"+suffix).removeClass("bg-danger");
            $("#remain-"+suffix).addClass("bg-success text-white");
        }
    }
    function submit_data() {
        $("#submit_adjust").submit();
    }

    function total_remain() {
        var total_remain = 0;
        $(".remain-suffix").each(function(i, obj) {
            value = obj.textContent;
            if(value != ""){
                // console.log(value);
                total_remain += +parseInt(obj.textContent);
            }
        });
        console.log(total_remain);
        if(total_remain >= 0){
            $("#td_button_simpan").html('<a href="javascript:void(0)" class="btn btn-info ml-2" onclick="submit_data()">Simpan</a>');
        }else{
            $("#td_button_simpan").html('');
        }
    }

    $(".suffix").each(function(i, obj) {
        suffix = obj.textContent;
        remain_suffix(suffix);
    });

    var total_single_tone = 0;
    $(".bg-single-tone").each(function(i, obj) {
        if(obj.value){
            total_single_tone += +parseInt(obj.value);
        }
    });
    $("#total-single-tone").html(total_single_tone);
</script>
