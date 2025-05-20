<?php 
if(empty($this->npk)){
    redirect("logout");
}
?>
<nav class="pcoded-navbar">
    <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">
        <div class="">
            <div class="main-menu-header">
                <img class="img-80" src="<?= base_url("assets/images/logo-hrs.jpg") ?>" alt="User-Profile-Image">
                <div class="text-light mt-3 font-weight-bold">
                    <?=$this->nama?>
                </div>
            </div>
        </div>
        
        <div class="pcoded-navigation-label">Menu</div>
        <ul class="pcoded-item pcoded-left-item">
            <?php
            $menu = $this->model->gds("t_menu","*","id != '' AND for LIKE '%".$this->level."%' ORDER BY sorting ASC","result");
            foreach ($menu as $menu) {
                $url = $menu->url;
                $icon = $menu->icon;
                $menu_text = $menu->name_menu;
                if($url == $this->params1){
                    $active = "active";
                }else{
                    $active = "";
                }
                ?>
                <li class="pcoded-hasmenu <?=$active?>">
                    <a href="<?=$url?>" class="waves-effect waves-dark">
                        <span class="pcoded-micon"><i class="<?=$icon?>"></i></span>
                        <span class=""><?=$menu_text?></span>
                        <span class="pcoded-mcaret"></span>
                    </a>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>
</nav>