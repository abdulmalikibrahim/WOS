
<?php
//UNTUK DOWNLOAD EXCEL WOS MASTER SERVICE PART
if(!empty($this->input->get("download"))){
	header ("Content-type: application/vnd-ms-excel");
	header("Content-Disposition: attachment;filename = Master Service Part.xls");
}
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="<?= base_url("assets/images/favicon.png") ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= base_url("assets/plugins/font-awesome-6/css/all.min.css") ?>"/>
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="<?= base_url("assets/css/bootstrap/css/bootstrap.min.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("assets/plugins/sweetalert2/dist/sweetalert2.min.css") ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url("assets/css/mycss.css") ?>">
	<script src="<?= base_url("assets/plugins/sweetalert2/dist/sweetalert2.all.js") ?>"></script>
	<script type="text/javascript" src="<?= base_url("assets/js/jquery/jquery.min.js") ?>"></script>
    <title>WOS System</title>
    <style>
        html {
            height: 100%;
        }
        body {
            min-height: 100%;
        }
        .swal2-icon-content{
            min-width:128px !important;
            height:128px !important;
            padding-bottom:37px !important;
        }
        .swal2-html-container{
            margin:5px 15px 15px 15px !important;
        }
        .swal2-title{
            margin-top:0.5rem !important;
        }
    </style>
</head>
