<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'wos';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['login'] = 'wos';
$route['plan_wos'] = 'wos/plan_wos';
$route['tabungan'] = 'wos/tabungan';
$route['adjust_twotone'] = 'wos/adjust_twotone';
$route['download_docking'] = 'wos/download_docking';
$route['heijunka_wos'] = 'wos/heijunka_wos';
$route['heijunka_wos_kap2'] = 'wos/heijunka_wos_kap2';
$route['heijunka_wos_print'] = 'wos/heijunka_wos_print';
$route['hard_copy_std'] = 'wos/hard_copy_std';
$route['dup'] = 'wos/download_u_pis';
$route['dhc'] = 'wos/download_hardcopy';
$route['dup_kap2'] = 'wos/download_u_pis_kap2';
$route['dhc_kap2'] = 'wos/download_hardcopy_kap2';
$route['upload_vlt'] = 'wos/upload_vlt';
$route['show_nik'] = 'wos/show_nik';
$route['pkb_service_part'] = 'wos/pkb_service_part';
$route['create_pkb'] = 'wos/create_pkb';

//PLAN WOS
$route['import_plan_wos'] = 'Plan_WOS/import_plan_wos';
$route['clear_plan_wos'] = 'Plan_WOS/clear_plan_wos';

//TABUNGAN
$route['import_tabungan'] = 'Tabungan/import_tabungan_vlt';
$route['check_pdd'] = 'Tabungan/check_pdd';
$route['save_adjust'] = 'Tabungan/save_adjust';
$route['docking'] = 'Tabungan/docking';
$route['docking_kap2'] = 'Tabungan/docking_kap2';
$route['docking_truncate'] = 'Tabungan/docking_truncate';
$route['clear_tabungan'] = 'Tabungan/clear_tabungan';
$route['heijunka_tone'] = 'Tabungan/heijunka_tone';
$route['load_tabungan'] = 'Tabungan/load_tabungan';

//VLT
$route['import_vlt'] = 'Upload_VLT/import_vlt';
$route['download_txt'] = 'Upload_VLT/download_txt';
$route['clear_vlt'] = 'Upload_VLT/clear_vlt';
$route['load_vlt'] = 'Upload_VLT/load_vlt';

//HEIJUNKA
$route['master_service_part'] = 'wos/master_service_part';
$route['download_master_service_part'] = 'wos/download_master_service_part';
$route['download_pro_number'] = 'wos/download_pro_number';
$route['edit_master_sp/(:any)'] = 'wos/edit_master_sp/$1';
$route['create_wos'] = 'wos/create_wos';
$route['create_wos_sp_download'] = 'wos/create_wos_sp_download';
$route['save_edit_master_sp/(:any)'] = 'C_Heijunka_WOS/save_edit_master_sp/$1';
$route['heijunka_check_batch'] = 'C_Heijunka_WOS/heijunka_check_batch';
$route['heijunka_color'] = 'C_Heijunka_WOS/heijunka_color';
$route['heijunka_suffix'] = 'C_Heijunka_WOS/heijunka_suffix';
$route['heijunka_sub'] = 'C_Heijunka_WOS/heijunka_sub';
$route['heijunka_model'] = 'C_Heijunka_WOS/heijunka_model';
$route['heijunka_both'] = 'C_Heijunka_WOS/heijunka_both';
$route['heijunka_transmisi'] = 'C_Heijunka_WOS/heijunka_transmisi';

$route['heijunka_check_batch_kap2'] = 'C_Heijunka_WOS_KAP2/heijunka_check_batch';
$route['heijunka_color_kap2'] = 'C_Heijunka_WOS_KAP2/heijunka_color';
$route['heijunka_suffix_kap2'] = 'C_Heijunka_WOS_KAP2/heijunka_suffix';
$route['heijunka_sub_kap2'] = 'C_Heijunka_WOS_KAP2/heijunka_sub';
$route['heijunka_model_kap2'] = 'C_Heijunka_WOS_KAP2/heijunka_model';
$route['heijunka_both_kap2'] = 'C_Heijunka_WOS_KAP2/heijunka_both';
$route['heijunka_transmisi_kap2'] = 'C_Heijunka_WOS_KAP2/heijunka_transmisi';
$route['dummy_process_kap2'] = 'C_Heijunka_WOS_KAP2/dummy_process_kap';
$route['process_tabungan_dummy_kap2'] = 'C_Heijunka_WOS_KAP2/process_tabungan_dummy_kap2';

$route['heijunka_model_trial'] = 'C_Heijunka_WOS/heijunka_model_trial';
$route['urutkan_nomor'] = 'C_Heijunka_WOS/urutkan_nomor';
$route['import_td_link'] = 'C_Heijunka_WOS/import_td_link';
$route['import_wos'] = 'C_Heijunka_WOS/import_wos';
$route['import_woskap2'] = 'C_Heijunka_WOS_KAP2/import_wos';
$route['import_wos_backup_print'] = 'C_Heijunka_WOS/import_wos_backup_print';
$route['print_card'] = 'C_Heijunka_WOS/print_card';
$route['heijunka_twotone'] = 'C_Heijunka_WOS/heijunka_twotone';
$route['import_master_sp'] = 'C_Heijunka_WOS/import_master_sp';
$route['clear_master_sp'] = 'C_Heijunka_WOS/clear_master_sp';
$route['import_sp'] = 'C_Heijunka_WOS/import_sp';
$route['clear_sp'] = 'C_Heijunka_WOS/clear_sp';
$route['pro_number'] = 'wos/pro_number';
$route['import_pro_number'] = 'C_Heijunka_WOS/import_pro_number';
$route['clear_pro_number'] = 'C_Heijunka_WOS/clear_pro_number';

//HARD COPY
$route["ihcs"] = 'Hardcopy_std/input_hard_copy_std';
$route["delete_hcstd"] = 'Hardcopy_std/delete_hcstd';
$route["download_hc"] = 'Download/download_hc';

$route["tukar_sapnik"] = 'C_Heijunka_WOS/tukar_sapnik';
$route["set_session"] = "C_Heijunka_WOS/set_session";
$route["err404"] = "WOS/err404";

$route['docking_wos_dummy'] = 'WOS_Dummy';
$route['import_pis_kap2'] = 'WOS_Dummy/import_pis_kap2';
$route['load_pis_kap2'] = 'WOS_Dummy/load_pis_kap2';
$route['docking_dummy'] = 'WOS_Dummy/docking_dummy';

$route['filtering_color/(:any)'] = 'C_Heijunka_WOS/filtering_color/$1';
$route['wos_duplicate_checking'] = 'C_Heijunka_WOS/wos_duplicate_checking';
$route['getDataVINChecking/(:num)'] = 'C_Heijunka_WOS/getDataVINChecking/$1';
$route['upload_wos_duplicate_checking'] = 'C_Heijunka_WOS/upload_wos_duplicate_checking';
$route['checkUpload'] = 'C_Heijunka_WOS/checkUpload';
$route['downloadDataDuplicate'] = 'C_Heijunka_WOS/downloadDataDuplicate';
$route['clearDataDouble'] = 'C_Heijunka_WOS/clearDataDouble';
$route['search_vin'] = 'C_Heijunka_WOS/search_vin';
$route['downloadVIN'] = 'C_Heijunka_WOS/downloadVIN';

$route['delete_unit_batam'] = 'Tabungan/delete_unit_batam';