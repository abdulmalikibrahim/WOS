<!DOCTYPE html>
<html lang="en">
<?php 
$this->load->view("layout/head");
if($this->params1 != ""){
    $overflow = 'scrollbar';
}else{
    $overflow = 'scrollbar';
}
?>
<body class="bg-info" style="overflow-y:<?=$overflow?>; overflow-x:hidden; min-height:100%">
    <?php $this->load->view("content/".$content) ?>
</body>
</html>
<?php $this->load->view("layout/footer") ?>
