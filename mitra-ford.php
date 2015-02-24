<?php
/*
Plugin Name: Lembaga Mitra Ford Foundation version 0.2
Plugin URI: http://infest.or.id
Description: Plugin untuk mengelola daftar mitra Ford Foundation
Author: Khayat
Version: 0.2
Author URI: http://khayat.wordpress.com
*/


define('MITRAFORD_DIR', dirname(plugin_basename(__FILE__)));
define('MITRAFORD_URL', get_option('siteurl').'/wp-content/plugins/' . MITRAFORD_DIR);

/*################## Back End #################################*/
global $mitrafordPlugin;
$mitrafordPlugin = "0.2";
// action function for above hook
function mitraford_add_pages() {
    // Add a new top-level menu (ill-advised):
    add_menu_page(__('Tim Desa Membangun','menu-mitraford'), __('Tim Desa Membangun','menu-mitraford'), 'manage_options', 'manage_mitraford', 'manageMitraFord',MITRAFORD_URL.'/images/mitra-logo.png');

    // Add a submenu to the custom top-level menu:
    add_submenu_page('manage_mitraford', __('Kelola Daftar Desa','menu-mitraford'), __('Kelola daftar desa','menu-test'), 'manage_options', 'manage_mitraford', 'manageMitraFord');

    
}


function manageMitraFord() {
    global $wpdb;
    echo '<div class="icon32" id="icon-options-general"></div>';
    echo '<h2>Tim Desa Membangun</h2>';
    echo '<div style="margin-top:40px;"><input type="button" value="Tambahkan baru" class="button add-new-h2" onclick="tambahMitra(0)"></div>';
    echo '<div id="loading"></div>';
    echo '<div id="mitraResponse">'.viewMitra().'</div>';

}
function viewMitra(){
    global $wpdb;
    $view='';
    $view.='
    <table class="widefat fixed" cellspacing="0">
    <thead>
    <tr class="thead">
     <th width="50">No</th><th>Nama Lembaga</th><th>Alamat</th><th colspan="2" width="100" align="center">Aksi</th>
    </tr>
    </thead>

    <tfoot>
    <tr class="thead">
    <th width="50">No</th><th>Nama Lembaga</th><th>Alamat</th><th colspan="2" width="100" align="center">Aksi</th>
    </tr>
    </tfoot>

    <tbody id="users" class="list:user user-list">';

    $style = '';
    $result = $wpdb->get_results("SELECT idLembaga,namaLengkap,namaPendek,alamat FROM ".$wpdb->prefix."lembagamitra ORDER BY namaLengkap ASC");
    $i=0;
    foreach($result as $result_lembaga){
        $i++;
        if($i%2==0)$style='style="background-color:#FFFFFF"';
        else $style='style="background-color:#F9F9F9"';
        $view.='<tr '.$style.'><td>'.$i.'</td>
              <td><strong>'.$result_lembaga->namaLengkap.'('.$result_lembaga->namaPendek.')</strong></td>
              <td>'.$result_lembaga->alamat.'</td>
              <td><a href="javascript:ubahMitra(\''.$result_lembaga->idLembaga.'\',\'0\');">Ubah</a></td>
              <td><a style="color:red;" href="#" onClick="hapusLembagaMitra(\''.$result_lembaga->idLembaga.'\')">Hapus</a></td></tr>';
    }

    $view.='</tbody>
    </table>';
    return $view;

}
function showMitra(){
    check_ajax_referer( "showMitra" );
    echo viewMitra();
    die();
}
function backendMitraCss(){
    echo '<link rel="stylesheet" href="'.MITRAFORD_URL.'/css/backend.style.css" type="text/css" />';
}
function backendMitraJavascript(){
?>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="<?php echo MITRAFORD_URL;?>/js/mitra.map.js"></script>


    <script type="text/javascript">
    function tambahMitra(save) {
        if(save==0){
            jQuery.ajax({
                type: "POST",url: ajaxurl,data: {save:save,action: "tambahMitra", _ajax_nonce: "<?php echo wp_create_nonce('tambahMitra'); ?>" },
                beforeSend: function() {jQuery("#loading").text("Loading . . .");},
                complete: function() { jQuery("#loading").text("");},
                success: function(html){
                    jQuery("#mitraResponse").html(html);
                    initAlamatLembaga();
                    //initWilayahKerjaLembaga();
                }
             });
         }else{
            var dataform = jQuery("#mitraForm").serialize();
            //alert(dataform);
            jQuery.ajax({
                type: "POST",url: ajaxurl,
                data: "_ajax_nonce=<?php echo wp_create_nonce('tambahMitra'); ?>&"+dataform,
                beforeSend: function() {jQuery("#loading").text("Loading . . .");},
                complete: function() { jQuery("#loading").text("");},
                dataType: "json",
                success: function(json){                    
                    alert(json.message);
                    if(json.status=='success'){
                        showMitra();
                    }

                }
             });
         }
    }
    function ubahMitra(idMitra,save) {
        if(save==0){
            jQuery.ajax({
                type: "POST",url: ajaxurl,data: { idLembaga:idMitra,save:save,action: "ubahMitra", _ajax_nonce: "<?php echo wp_create_nonce('ubahMitra'); ?>" },
                beforeSend: function() {jQuery("#loading").text("Loading . . .");},
                complete: function() { jQuery("#loading").text("");},
                success: function(html){
                    jQuery("#mitraResponse").html(html);
                    //initAlamatLembaga();
                    petaAlamatLembaga('edit');
                    getFusionTableMap();
                }
             });
         }else{
            var dataform = jQuery("#mitraForm").serialize();
            jQuery.ajax({
                type: "POST",url: ajaxurl,
                data: "_ajax_nonce=<?php echo wp_create_nonce('ubahMitra'); ?>&"+dataform,
                beforeSend: function() {jQuery("#loading").text("Loading . . .");},
                complete: function() { jQuery("#loading").text("");},
                dataType: "json",
                success: function(json){
                    alert(json.message);
                    if(json.status=='success'){
                        showMitra();
                    }
                }
             });
         }
    }
    
    function hapusLembagaMitra(idMitra){
          if(confirm('Apakah anda yakin akan menghapus data ini ?')){
            jQuery.ajax({
                type: "POST",url: ajaxurl,data: {idLembaga:idMitra,action: "hapusLembagaMitra", _ajax_nonce: "<?php echo wp_create_nonce('hapusLembagaMitra'); ?>" },
                beforeSend: function() {jQuery("#loading").text("Loading . . .");},
                complete: function() { jQuery("#loading").text("");},
                dataType: "json",
                success: function(response){
                    alert(response.message);
                    if(response.status=='success'){
                        showMitra();
                    }
                }
             });
           }return false;
    }
    function cancelOperation(){
        jQuery("#mitraResponse").html('');
        showMitra();
    }
    function showMitra(){
        jQuery.ajax({
                type: "POST",url: ajaxurl,data: {action: "showMitra", _ajax_nonce: "<?php echo wp_create_nonce('showMitra'); ?>" },
                beforeSend: function() {jQuery("#loading").text("Loading . . .");},
                complete: function() { jQuery("#loading").text("");},
                success: function(html){
                    jQuery("#mitraResponse").html(html);
                }
             });
    }
    </script>
<?php
}

function tambahMitra(){
    global $wpdb;
    check_ajax_referer( "tambahMitra" );
    if(isset($_POST['save'])) {
        $save = intval($_POST['save']);
        $view='';
        if($save==0){
            echo mitraForm($data=array('namaPendek'=>'kosong'), 'add');
        }else{
            $namaLengkap        = $_POST['namaLengkap'];
            $namaPendek         = $_POST['namaPendek'];
            $deskripsi          = $_POST['deskripsi'];
            $telp               = $_POST['telp'];
            $narahubung         = $_POST['narahubung'];
            $email              = $_POST['email'];
            $website            = $_POST['website'];
            $alamatLembaga      = $_POST['alamatLembaga'];
            $alamatLatLong      = $_POST['alamatLatLong'];
            $pageId             = $_POST['page_id'];
            $program            = $_POST['program'];
            //$jumlahWilayahKerja = $_POST['jumlahWilayahKerja'];
            //$wilayah            = $_POST['wilayah'];
            //$centerMap          = $_POST['centerMap'];
            //$zoomMap            = $_POST['zoomMap'];
            $fusionMapURL       = htmlentities($_POST['fusionMap']);
            //$fusionMapURL       = html_entity_decode($fusionMapURL);
            
            $rssUrl             = $_POST['rssUrl'];

            $response = array();

            if($namaLengkap && $namaPendek && $deskripsi && $telp && $narahubung && $email && $alamatLembaga && $alamatLatLong && $program && $fusionMapURL){
                $insert = $wpdb->insert( $wpdb->prefix.'lembagamitra', array( 'namaLengkap' => $namaLengkap, 'namaPendek' => $namaPendek,'deskripsi'=>$deskripsi,'telp'=>$telp,'narahubung'=>$narahubung,'email'=>$email,'website'=>$website,'alamat'=>$alamatLembaga,'alamatLatLong'=>$alamatLatLong,'program'=>$program,'pageId'=>$pageId,'mapFusionEmbedURL'=>$fusionMapURL,'rssUrl'=>$rssUrl), array('%s', '%s', '%s','%s', '%s','%s', '%s','%s', '%s','%s','%d','%s','%s'));
                if($insert){                    
                    $response['status'] = 'success';
                    $response['message'] = 'Data berhasil disimpan !';
                }else{
                    $response['status'] = 'error';
                    $response['message'] = 'Penyimpanan data GAGAL !'.mysql_error();
                }
                echo json_encode($response);
            }else{
                $response['status'] = 'error';
                $response['message'] = 'Mohon lengkapi semua isian Form !';
                echo json_encode($response);
            }            
        }
        die();
    }
}
function mitraForm($data = array(),$action='add'){
    $view='';
    //$data_alamat = array();
    $titikalamat = explode(',', $data['alamatLatLong']);
    $view.='<div id="mitraFormSubmitResponse"></div>';
    $view.= '<form id="mitraForm" name="mitraForm">
                    <table class="mitraformcontainer" cellpadding="0" cellspacing="0">
                    <tr><th class="manage-column column-title" colspan="2">Tambah Mitra</th></tr>
                    <tr>
                      <td class="label">Nama Desa</td>
                      <td>';
                        if($action=='add')$view.='<input type="hidden" name="action" value="tambahMitra">';
                        else if($action=='edit'){
                         $view.='<input type="hidden" name="action" value="ubahMitra">';
                         $view.='<input type="hidden" name="idLembaga" value="'.$data['idLembaga'].'">';
                        }
                        $view.='<input type="hidden" name="save" value="1">
                        <input type="text" name="namaLengkap" value="'.$data['namaLengkap'].'" size="40">
								<input type="hidden" name="namaPendek" value="'.$data['namaPendek'].'" size="40">                        
                        </td>
                    </tr>                   

                    <tr>
                      <td class="label">Profil Singkat</td>
                      <td><textarea name="deskripsi" cols="50" rows="5">'.$data['deskripsi'].'</textarea></td>
                    </tr>
                    <tr>

                    <tr>
                      <td class="label">No.Telp</td>
                      <td>
                        <input type="text" name="telp" value="'.$data['telp'].'" size="40"><br/>
                        <small>Jika nomor telepon lebih dari satu, pisahkan dengan koma (,)</small></td>
                    </tr>

                    <tr>
                      <td class="label">Narahubung</td>
                      <td>
                        <input type="text" name="narahubung" value="'.$data['narahubung'].'" size="40"></td>
                    </tr>

                    <tr>
                      <td class="label">Email</td>
                      <td>
                        <input type="text" name="email" value="'.$data['email'].'" size="40"><br/>
                        <small>Jika alamat email lebih dari satu, pisahkan dengan koma (,)</small></td>
                    </tr>

                    <tr>
                      <td class="label">Alamat website desa</td>
                      <td>
                        <input type="text" name="website" value="'.$data['website'].'" size="40"><br/>
                        <small>Contoh: <i>http://infest.or.id</i></small></td>
                      </td>
                    </tr>

                    <tr>
                      <td class="label">URL RSS website</td>
                      <td>
                        <input type="text" name="rssUrl" value="'.$data['rssUrl'].'" size="50"><br/>
                        <small>Contoh: <i>http://infest.or.id/feed/</i></small></td>
                      </td>
                    </tr>

                    <tr>
                      <td class="label">Alamat kantor desa</td>
                      <td>
                        <textarea name="alamatLembaga" id="alamatLembaga" cols="50" rows="3" >'.$data['alamat'].'</textarea><br/>
                        <div class="alamatContainer">
                        <table >
                            <tr>
                                <td class="mapOption">
                                    <input type="radio" id="byAddress" value="byAddress" name="alamatBy"> <label>Berdasarkan Alamat di atas</label><br/>
                                    <input type="radio" id="byLatLong" value="byLatLong" name="alamatBy"> <label>Berdasarkan Latitude Longitude</label><br/>
                                    <p>
                                      Latitude<br/><input type="text" name="alamatLat" id="alamatLat" value="'.$titikalamat[0].'" onfocus="setByLatLongChecked()"><br/>
                                      Longitude<br/><input type="text" name="alamatLong" id="alamatLong" value="'.$titikalamat[1].'" onfocus="setByLatLongChecked()"><br/>
                                    </p>
                                    <input type="button" value="Get Map" onClick="petaAlamatLembaga(\'new\')"> <br/>
                                    <input type="hidden" name="alamatLatLong" id="alamatLatLong" value="'.$data['alamatLatLong'].'">
                                     <div id="label_titik_alamat"></div>
                                </td>
                                <td>
                                    <div id="gMap">Memuat peta . . . </div>
                                </td>
                            </tr>
                        </table>
                        </div>
                        

                      </td>
                    </tr>

                    <tr>
                      <td class="label">Halaman profil desa</td>
                      <td>';
                       if(isset($data['pageId']))$view.= wp_dropdown_pages(array('echo'=>0,'child_of'=> 114,'selected'=>$data['pageId']));
                       else $view.= wp_dropdown_pages(array('echo'=>0,'child_of'=> 114)).'</td>';
                      //'.wp_dropdown_pages('echo=0&child_of=114').'
                      
                      $view.='</td>
                    </tr>

                    <tr>
                      <td class="label">Produk Unggulan</td>
                      <td><textarea name="program" cols="50" rows="3">'.$data['program'].'</textarea>
							 <br/><small>Jika produk unggulan lebih dari satu, pisahkan dengan koma (,)</small>                      
                      </td>
                    </tr>
                    <tr>

                    <tr>
                      <td class="label">Potensi</td>
                      <td>
                        <textarea name="fusionMap" id="fusionMap" cols="50" style="height:auto;">'.html_entity_decode($data['mapFusionEmbedURL']).'</textarea>
                        <br/><small>Jika potensi desa lebih dari satu, pisahkan dengan koma (,)</small>
                      </td>
                    </tr>                    

                    <tr>
                     <td colspan="2">
                     <input type="button" value="Batal" class="button" onclick="cancelOperation()">&nbsp;&nbsp;';
                     if($action=='add'){
                        $view.='<input type="button" value="Simpan" class="button-primary" onclick="tambahMitra(\'1\')">';
                     }else{
                         $view.='<input type="hidden" name="idLembaga" id="idLembaga" value="'.$data['idLembaga'].'">';
                         $view.='<input type="button" value="Simpan" class="button-primary" onclick="ubahMitra(\'1\')">';
                     }

                     $view.='</td>
                    </tr>
                    </table>
                    </form>';
            return $view;
}
function ubahMitra(){
    global $wpdb;
    check_ajax_referer( "ubahMitra" );
    if(isset($_POST['idLembaga'])) {
        $idLembaga = intval($_POST['idLembaga']);
        $save = intval($_POST['save']);
        $view='';
        if($save==0){
            $dataLembaga = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."lembagamitra WHERE idLembaga=$idLembaga");            
            $data = (array)$dataLembaga;            
            echo mitraForm($data,'edit');
        }else{
            $idLembaga          = $_POST['idLembaga'];
            $namaLengkap        = $_POST['namaLengkap'];
            $namaPendek         = $_POST['namaPendek'];
            $deskripsi          = $_POST['deskripsi'];
            $telp               = $_POST['telp'];
            $narahubung         = $_POST['narahubung'];
            $email              = $_POST['email'];
            $website            = $_POST['website'];
            $alamatLembaga      = $_POST['alamatLembaga'];
            $alamatLatLong      = $_POST['alamatLatLong'];
            $pageId             = $_POST['page_id'];
            $program            = $_POST['program'];
            //$jumlahWilayahKerja = $_POST['jumlahWilayahKerja'];
            //$wilayah            = $_POST['wilayah'];
            //$centerMap          = $_POST['centerMap'];
            //$zoomMap            = $_POST['zoomMap'];
            $fusionMapURL       = htmlentities($_POST['fusionMap']);
            //$fusionMapURL       = html_entity_decode($fusionMapURL);

            $rssUrl             = $_POST['rssUrl'];

            $response = array();

            if($namaLengkap && $namaPendek && $deskripsi && $telp && $narahubung && $email && $alamatLembaga && $alamatLatLong && $program && $fusionMapURL){
                $update = $wpdb->update( $wpdb->prefix.'lembagamitra', array( 'namaLengkap' => $namaLengkap, 'namaPendek' => $namaPendek,'deskripsi'=>$deskripsi,'telp'=>$telp,'narahubung'=>$narahubung,'email'=>$email,'website'=>$website,'alamat'=>$alamatLembaga,'alamatLatLong'=>$alamatLatLong,'program'=>$program,'pageId'=>$pageId,'mapFusionEmbedURL'=>$fusionMapURL,'rssUrl'=>$rssUrl),array('idLembaga'=>$idLembaga), array( '%s','%s', '%s','%s', '%s','%s', '%s','%s', '%s','%s','%d','%s','%s'),array('%d'));
                if($update){                    
                    $response['status'] = 'success';
                    $response['message'] = 'Data berhasil disimpan !';
                }else{
                    $response['status'] = 'error';
                    $response['message'] = 'Penyimpanan data GAGAL !'.mysql_error();
                }
                echo json_encode($response);
            }else{
                $response['status'] = 'error';
                $response['message'] = 'Mohon lengkapi semua isian Form !';
                echo json_encode($response);
            }
        }
        die();
    }
}

function hapusLembagaMitra(){
    global $wpdb;
    check_ajax_referer("hapusLembagaMitra");
    if(isset($_POST['idLembaga'])) {
        $idLembaga = intval($_POST['idLembaga']);
        $delete = $wpdb->query("DELETE FROM ".$wpdb->prefix."lembagamitra WHERE idLembaga='$idLembaga'");
        //$deleteWilayah = $wpdb->query("DELETE FROM ".$wpdb->prefix."wilayahkerja WHERE idLembaga='$idLembaga'");
        $response = array();
        $response['status'] = 'success';
             $response['message'] = 'Data mitra berhasil dihapus !';
        if($delete){
             $response['status'] = 'success';
             $response['message'] = 'Data mitra berhasil dihapus !';
        }else{
            $response['status'] = 'error';
            $response['message'] = 'Penghapusan data mitra GAGAL !';
        }
        echo json_encode($response);
    }
    die();
}

add_action('admin_menu', 'mitraford_add_pages');// Hook for adding admin menus
add_action('admin_head', 'backendMitraCss');
add_action('admin_head', 'backendMitraJavascript');
//register_activation_hook(__FILE__,'mitrafordPluginIinstall');// Hook install table of database
add_action('wp_ajax_ubahMitra', 'ubahMitra' );
add_action('wp_ajax_tambahMitra', 'tambahMitra' );
add_action('wp_ajax_hapusLembagaMitra', 'hapusLembagaMitra' );
add_action('wp_ajax_showMitra', 'showMitra' );

include_once('frontend.mitra-ford.php');
include_once('frontend.listMitra.php');
?>