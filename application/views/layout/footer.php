<script src="<?= base_url("assets/js/bootstrap/js/bootstrap.min.js") ?>"></script>
<script>
    function voice(url) {
        var source = "<?=str_replace("index.php","",base_url("assets/voice/"))?>"+url;
        var audio = new Audio();
        audio.addEventListener("load",function() {
            audio.play();
        }, true);
        audio.src = source;
        audio.autoplay = true;
    }
</script>
<?php
if(!empty($this->session->flashdata("swal"))){
    echo $this->session->flashdata("swal");
}

if(!empty($javascript)){
    $this->load->view("js/".$javascript);
}

if(!empty($js)){
    echo '<script src="'.base_url("assets/myjs/".$js).'?t='.time().'"></script>';
}

if(!empty($this->session->flashdata("voice"))){
    echo $this->session->flashdata("voice");
}

if(!empty($this->session->flashdata("swal"))){
    echo $this->session->flashdata("swal");
}

if($this->params1 == ""){
    if($open_first == "Voice"){
        ?>
        <script>
            voice("selamat datang.mp3");
        </script>
        <?php
    }
}
?>
<script>
	function swal_close(){
		swal.close();
	}
</script>
