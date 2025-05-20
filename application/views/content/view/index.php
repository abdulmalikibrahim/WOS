<style>
    #myList {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease, opacity 0.5s ease;
        opacity: 0;
    }

    #myList.show {
        max-height: 500px; /* sesuaikan dengan isi list */
        opacity: 1;
    }

</style>
<?php
$show = true;
if($show){
    ?>
    <div class="row justify-content-center">
        <div class="col-lg-12" align="center"><h1 class="m-0 text-white" style="font-size:5rem;"><?=$title?></h1></div>
        <div class="col-lg-12 align-items-center text-white">
            <ul style="list-style-type: none; font-size:20pt; font-family:Arial;">
                <li class="font-weight-bold mt-1">MAIN MENU</li>
                <li class="font-weight-bold mt-3 mb-2">1. WOS Vehicle</li>
                <li class="mb-2">
                    <ul style="list-style-type:none; font-size:17pt;">
                        <li class="mt-2 mb-2">
                            <a href="javascript:void(0)" id="create_wos" data-show="true" class="text-white font-weight-bold" style="text-decoration:none">1.1. Create WOS</a>
                            <ul class="sub_create_wos">
                                <li>
                                    <a href="<?=base_url("plan_wos")?>" class="text-white font-weight-bold" style="text-decoration:none">1.1.1. Plan WOS</a>
                                </li>
                                <li>
                                    <a href="<?=base_url("tabungan")?>" class="text-white font-weight-bold" style="text-decoration:none">1.1.2. Tabungan</a>
                                </li>
                                <li>
                                    <a href="<?=base_url("heijunka_wos")?>" class="text-white font-weight-bold" style="text-decoration:none">1.1.3. Heijunka WOS KAP 1</a>
                                </li>
                                <li>
                                    <a href="<?=base_url("heijunka_wos_kap2")?>" class="text-white font-weight-bold" style="text-decoration:none">1.1.4. Heijunka WOS KAP 2</a>
                                </li>
                                <li>
                                    <a href="<?=base_url("docking_wos_dummy")?>" class="text-white font-weight-bold" style="text-decoration:none">1.1.5. Docking WOS Dummy to WOS With VIN KAP2</a>
                                </li>
                                <li>
                                    <a href="<?=base_url("wos_duplicate_checking")?>" class="text-white font-weight-bold" style="text-decoration:none">1.1.6. WOS Duplicate Checking</a>
                                </li>
                            </ul>
                        </li>
                        <li class="mt-2 mb-2">
                            <a href="<?=base_url("upload_vlt")?>" class="text-white font-weight-bold" style="text-decoration:none">1.2. Create Template Upload VLT</a>
                        </li>
                        <li class="mt-2 mb-2">
                            <a href="javascript:void(0)" id="master_setting" data-show="true" class="text-white font-weight-bold" style="text-decoration:none">1.3. Master Setting</a>
                            <ul class="sub_master_setting">
                                <li>
                                    <a href="<?=base_url("hard_copy_std")?>" class="text-white font-weight-bold" style="text-decoration:none">1.3.1. Hard Copy Standard</a>
                                </li>
                                <li>
                                    <a href="<?=base_url("heijunka_wos_print")?>" class="text-white font-weight-bold" style="text-decoration:none">1.3.3. Backup Vehicle Card Print</a>
                                </li>
                                <li>
                                    <a href="<?=base_url("filtering_color/page")?>" class="text-white font-weight-bold" style="text-decoration:none">1.3.3. Filtering Color</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="font-weight-bold mb-2">2. WOS Service Part</li>
                <li class="mb-4">
                    <ul style="list-style-type:none; font-size:17pt;">
                        <li class="mt-2 mb-2">
                            <a href="<?=base_url("master_service_part")?>" class="text-white font-weight-bold" style="text-decoration:none">2.1. Master Service Part</a>
                        </li>
                        <li class="mt-2 mb-2">
                            <a href="<?=base_url("pro_number")?>" class="text-white font-weight-bold" style="text-decoration:none">2.2. PRO Number</a>
                        </li>
                        <li class="mt-2 mb-2">
                            <a href="<?=base_url("create_wos")?>" class="text-white font-weight-bold" style="text-decoration:none">2.3. Create WOS</a>
                        </li>
                        <!-- <li class="mt-2 mb-2">
                            <a href="<?=base_url("pkb_service_part")?>" class="text-white font-weight-bold" style="text-decoration:none">2.4. PKB Service Part</a>
                        </li> -->
                    </ul>
                </li>
            </ol>
        </div>
    </div>
    <img src="<?=base_url("assets/images/bpn-rocky.png")?>" style="width:75%; height:827px; object-fit: cover; object-position: center center; position: fixed; bottom: 0px; right: 0px; z-index: -1;">
    <?php
}else{
    ?>
    Please using new address this <a href="http://10.59.195.219/wos" target="_blank" class="text-light">http://10.59.195.219/wos</a>
    <?php
}
?>
<script type="text/javascript" src="<?= base_url("assets/js/jquery/jquery.min.js") ?>"></script>
<script>
    $(".sub_create_wos").hide(200);
    $(".sub_master_setting").hide(200);

    $("#create_wos").click(function(e) {
        show = e.currentTarget.dataset.show;
        id = e.currentTarget.id;
        if(show == "true"){
            show_sub("sub_"+id);
        }else{
            hide_sub("sub_"+id);
        }
        newshow = show == "true" ? "false" : "true";
        $(this).attr("data-show",newshow);
    });
    
    $("#master_setting").click(function(e) {
        show = e.currentTarget.dataset.show;
        id = e.currentTarget.id;
        if(show == "true"){
            show_sub("sub_"+id);
        }else{
            hide_sub("sub_"+id);
        }
        newshow = show == "true" ? "false" : "true";
        $(this).attr("data-show",newshow);
    });

    function show_sub(classname) {
        $("."+classname).show(200);
    }
    function hide_sub(classname) {
        $("."+classname).hide(200);
    }
</script>