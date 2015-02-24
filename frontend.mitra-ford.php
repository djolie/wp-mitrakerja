<?php

//$mapHelp = 'Peta di atas menyajikan beberapa Lembaga yang bermitra dengan Kemitraan.
//            Untuk melihat detail lembaga mitra, cukup klick pada icon <img src="'.MITRAFORD_URL.'/images/marker-icon.png"/>
//            Untuk kemudahan pemilihan lembaga yang ditampilkan, Anda bisa menggunakan panel Navigasi yang terletak disisi kanan peta. Untuk menampilkan Navigasi, cukup klick tombol Show/Hide Navigation,
//            Anda bisa melihat profil dan peta kerja Lembaga.';
$mapHelp = 'Peta ini menyajikan informasi sebaran organisasi yang bermitra dengan Kemitraan di wakili oleh icon <img src="'.MITRAFORD_URL.'/images/marker-icon.png"/> Beberapa mitra berada di satu wilayah yang sama dan tidak terlihat jelas, untuk melihat lebih jelas silakan zoom in pada wilayah yang di tuju atau menggunakan panel zoom in - zoom out <img src="'.MITRAFORD_URL.'/images/zoom.png"/>. Untuk melihat sekilas informasi lembaga mitra, cukup klick pada icon FGP. Anda juga bisa menggunakan panel Navigasi yang terletak di sisi kanan peta untuk melihat profil dan peta kerja lembaga. Untuk menampilkan navigasi, cukup klick tombol Show/Hide Navigation.';

/*#################### Front End ############################*/
function frontendMitraFordCSS(){
    echo '<link rel="stylesheet" href="'.MITRAFORD_URL.'/css/frontend.style.css"/>';    
}
function frontendMitraFordJS(){
    echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
    //echo '<script type="text/javascript" src="'.MITRAFORD_URL.'/js/InfoBubble.js"></script>';
    //echo '<script type="text/javascript" src="'.MITRAFORD_URL.'/js/InfoBox.js"></script>';
    echo '<script type="text/javascript" src="'.MITRAFORD_URL.'/js/jquery-1.5.1.js"></script>';
    
}
function frontendOnloadJS(){
    global $wpdb;

?>
        <script type="text/javascript">
        jQuery.noConflict();
        jQuery(document).ready(function() {
            /*jQuery('#WRSS_Slideshow').cycle({
                        fx:     'scrollHorz',
                        timeout: 0,
                        next:   '#WRSSnext',
                        prev:   '#WRSSprev',
                        after: onAfterSlideWRSS
              });*/
            <?php
            $dataLembaga = $wpdb->get_results("SELECT idLembaga FROM ".$wpdb->prefix."lembagamitra ORDER BY idLembaga");
            foreach($dataLembaga AS $lembaga){
                ?>
                        jQuery("#lembagatabs-<?php echo $lembaga->idLembaga;?>").tabs();
                <?php
            }
            ?>
        });
        /*function onAfterSlideWRSS(curr, next, opts) {
            var index = opts.currSlide;
            //alert('Index : '+index);
            jQuery('#WRSSprev')[index == 0 ? 'hide' : 'show']();
            jQuery('#WRSSnext')[index == opts.slideCount - 1 ? 'hide' : 'show']();
        }*/
        </script>
<?php
}

function frontMapFusion(){
    global $wpdb,$mapHelp;
?>
    <script type="text/javascript">
    var ajaxurl = '<?=site_url()?>/wp-admin/admin-ajax.php';
    var markerimage = 'http://desamembangun.or.id/wp-content/plugins/mitra-ford.0.2/images/marker-icon.png';
    var markers = [];
    var infoWindow = [];
    var map;
    function frontMapFusion(){
        //if(center == '')center = '-3.411598,114.84519999999998';
        var latlng = new google.maps.LatLng(-7.180564054181425,108.17621937197259);// lat long -> Tasik        
        var markerlatlng = [];
        var myOptions = {
          zoom: 9,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP,
          scrollwheel: false
        }
        map = new google.maps.Map(document.getElementById("frontMapFusion"), myOptions);
        google.maps.event.addListener(map, 'click', function() {
            hidePetaWilayahKerja();

        });
        jQuery.ajax({
                type: "POST",url: ajaxurl,
                data: {action: "getDataLembagaMitra", _ajax_nonce: "<?php echo wp_create_nonce('getDataLembagaMitra'); ?>" },
                beforeSend: function() {jQuery("#loadingMapData").show();},
                complete: function() { jQuery("#loadingMapData").hide();},
                dataType: "json",
                success: function(data){
		    //alert(data);
                    jQuery.each(data, function(key, val) {
                        var i = val.idLembaga;                        

                        alamatLatLong = val.alamatLatLong;
                        alamatLatLong = alamatLatLong.toString();
                        alamatLatLong = alamatLatLong.split(',');
                        markerlatlng[i] = new google.maps.LatLng(parseFloat(alamatLatLong[0]),parseFloat(alamatLatLong[1]));                        

                        markers[i] = new google.maps.Marker({
                            map: map,
                            draggable : true,
                            position: markerlatlng[i],
                            title: val.namaLembaga,
                            icon:markerimage
                        });
                        contentString = val.profilLembaga;
                        infoWindow[i] = new google.maps.InfoWindow({
                            content: contentString,
                            pixelOffset: new google.maps.Size(0,15)                           
                            
                        });
                        var latitude = parseFloat(alamatLatLong[0]);
                        var longitude = parseFloat(alamatLatLong[1]);
                        google.maps.event.addListener(markers[i], 'click', function() {                            
                            openLembagaInfo(i);
                        });
                        

                    });                  

                }
        });
    }
    window.onload = function(){
        frontMapFusion();

          jQuery("#mapNavigationPanel").toggle(function(){                    
                    jQuery("#mapNavigation").removeClass("disableNavigation");
                    jQuery("#mapNavigation").addClass("enableNavigation");
                    jQuery("#frontMapOperation").show();
                },function(){
                    jQuery("#mapNavigation").removeClass("enableNavigation");
                    jQuery("#mapNavigation").addClass("disableNavigation");
                    jQuery("#frontMapOperation").hide();
          });
         
    }
    function menuProfilClick(){
        jQuery("#programContent").hide();
        jQuery("#wilayahContent").hide();
        jQuery("#menuProgram").removeClass('selected');
        jQuery("#menuWilayah").removeClass('selected');

        jQuery("#profilContent").show();
        jQuery("#menuProfil").addClass('selected');
    }
    function menuProgramClick(){
        jQuery("#profilContent").hide();
        jQuery("#wilayahContent").hide();
        jQuery("#menuProfil").removeClass('selected');
        jQuery("#menuWilayah").removeClass('selected');

        jQuery("#programContent").show();
        jQuery("#menuProgram").addClass('selected');
    }
    function menuWilayahClick(){
        jQuery("#profilContent").hide();
        jQuery("#programContent").hide();
        jQuery("#menuProfil").removeClass('selected');
        jQuery("#menuProgram").removeClass('selected');

        jQuery("#wilayahContent").show();
        jQuery("#menuWilayah").addClass('selected');
    }
    var closeInfoWindow = function(val,idLembaga){
        infoWindow[idLembaga].close();
    }
    function openLembagaInfo(idLembaga){
        hidePetaWilayahKerja();
        infoWindow.forEach(closeInfoWindow);
        infoWindow[idLembaga].open(map,markers[idLembaga]);          
    }
    
    function openWilayahKerjaLembaga(_idLembaga){
        jQuery.ajax({
                type: "POST",url: ajaxurl,
                data: {action: "getDataWilayahKerja", idLembaga:_idLembaga, _ajax_nonce: "<?php echo wp_create_nonce('getDataWilayahKerja'); ?>" },
                beforeSend: function() {jQuery("#loadingMapData").show();},
                complete: function() { jQuery("#loadingMapData").hide();},
                dataType: "json",
                success: function(data){
                    showPetaWilayahKerja();
                    jQuery.each(data.lembaga, function(key, val) {
                        jQuery("#judulPetaWilayah").html('Wilayah kerja: '+val['namaLengkap'].toString()+'('+val['namaPendek'].toString()+')');
                        if(val['mapFusionEmbedURL']!==''){
                        var frame = '<iframe style="margin:0;padding:0;width:700px;height:310px;" scrolling="no" border"0" src="'+val['mapFusionEmbedURL']+'">Loading Map . . .</iframe>';
                        jQuery("#petaWilayahKerja").html(frame);
                        }else{
                         jQuery("#petaWilayahKerja").html('Maaf, Peta wilayah kerja Lembaga belum tersedia.');
                        }
                    });
                }
                });
    }
    /*################## Map Navigation ####################*/
    function byPartnerClick(){
        jQuery("#listRegionMitra").hide();
        jQuery("#byRegion").removeClass('selected');

        jQuery("#listLembagaMitra").show();
        jQuery("#byPartner").addClass('selected');
    }
    function byRegionClick(){
        jQuery("#listLembagaMitra").hide();
        jQuery("#byPartner").removeClass('selected');

        jQuery("#listRegionMitra").show();
        jQuery("#byRegion").addClass('selected');
    }
    /*################## End Map Navigation ####################*/
    
    function showPetaWilayahKerja(){
        jQuery("#petaWilayahKerjaContainer").show('slow');
        jQuery("#judulPetaWilayah").show('slow');
        jQuery("#closeImage").show('slow');
    }
    function hidePetaWilayahKerja(){
        jQuery("#petaWilayahKerjaContainer").hide('slow');
        jQuery("#judulPetaWilayah").hide('slow');
        jQuery("#closeImage").hide('slow');
    }
    function getListLembagaMitra(_paging){
        jQuery.ajax({
                type: "POST",url: ajaxurl,
                data: {action: "getListLembagaMitra", paging:_paging, _ajax_nonce: "<?php echo wp_create_nonce('getListLembagaMitra'); ?>" },
                beforeSend: function() {jQuery("#loadingMapData").show();},
                complete: function() { jQuery("#loadingMapData").hide();},
                success: function(data){
                    jQuery("#listLembagaMitra").html(data);
                }
        });
    }
    function setMapByRegion(center,zoom){
        newMapLatLong = center.split(',');
        var newMap = new google.maps.LatLng(parseFloat(newMapLatLong[0]),parseFloat(newMapLatLong[1])); 
        map.setCenter(newMap);    
        map.setZoom(zoom);
    }
    </script>
    <span id="loadingMapData">Loading Map Data . . .</span>
    <div id="petaWilayahKerjaContainer">        
        <div id="petaWilayahKerja"></div>
    </div>
    <span id="closeImage"><a href="#" onClick="hidePetaWilayahKerja()"><span style="width:30px;height:30px;">&nbsp;</span></a></span>
    <div id="judulPetaWilayah"></div>
    <table id="mapNavigation" class="disableNavigation">
        <tr>
            <td class="navigationLink"><a id="mapNavigationPanel" href="#"><img src="<?php echo MITRAFORD_URL.'/images/showhide-navigation.png';?>"/></a></td>
            <td class="navigationContent">
            <div id="frontMapOperation">
                <div id="mapNavigationTab">
                <ul>
                <li id="byPartner" onClick="byPartnerClick()" class="selected">Desa</li>
                <li id="byRegion" onClick="byRegionClick()">Wilayah</li>
                </ul>
                </div>
                <div id="listLembagaMitra">
                    <?php echo listLembagaMitra(0);?>
                </div>
                <div id="listRegionMitra">
                    <?php echo listRegionMitra();?>
                </div>
            </div>
            </td>           
            
        </tr>
    </table>
    
    <!--<div id="frontMapOperationBG"></div>-->
    <div id="frontMapFusion"></div>
    <!--
    <div id="mapHelp">
        <p><?php echo $mapHelp;?></p>
    </div>
    -->
<?php
}

function listLembagaMitra($paging=0){
    global $wpdb;
    $view='';
    
    //$view.='<h2>Daftar Lembaga Mitra</h2>';
    $maxview=6;
    $limit_front=0;    
    if($paging>0){
        $page = $paging+1;
        $limit_front = ($page*$maxview)-$maxview;        
    }
    $jumlahlembaga = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."lembagamitra"));
    if($jumlahlembaga>0){
        $jml_page = bcdiv($jumlahlembaga,$maxview,0)+1;
        $dataLembaga = $wpdb->get_results("SELECT idLembaga,namaLengkap,namaPendek,mapFusionEmbedURL FROM ".$wpdb->prefix."lembagamitra ORDER BY namaLengkap ASC LIMIT $limit_front,$maxview");
        //$jmldatalembaga = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."lembagamitra ORDER BY namaLengkap ASC LIMIT $limit_front,$maxview"));
        //$view.='jumlah =  '.$jmldatalembaga;
        $page = $paging + 1;
        $prev = $page-2;
        $next = $page;
        $last = $jml_page - 1;

        /*
         *#####################   PAGINATION   ####################
         */

        $view.='<div id="lembagaPagination">';
        $view.='<a href="#" onClick="getListLembagaMitra(0)"><<</a>';
        if($page>1){
           $view.='<a href="#" onClick="getListLembagaMitra('.$prev.')"><</a>';
        }else{
           $view.='&nbsp;<&nbsp;';
        }
        for($i = $paging;$i<=($paging+$maxview);$i++){
            if($i<$jml_page){
                $p=$i+1;
                if($i==($page-1)) $view.='&nbsp;'.$p.'&nbsp;';
                else $view.='<a href="#" onClick="getListLembagaMitra('.$i.')">'.$p.'</a>';
            }
        }
        if($page<$jml_page){
            $view.='<a href="#" onClick="getListLembagaMitra('.$next.')">></a>';
           
        }else{
           $view.='&nbsp;>&nbsp;';
        }
        $view.='<a href="#" onClick="getListLembagaMitra('.$last.')">>></a>';
        $view.='</div>';
        /*
         *#####################   END PAGINATION   ####################
         */


        $view.='<ul>';
        foreach($dataLembaga AS $lembaga){
            $view.='<li>
                <a href="#" onClick="openLembagaInfo('.$lembaga->idLembaga.')">'.$lembaga->namaLengkap.'</a>';
            /*if(preg_match('/google.com/i',$lembaga->mapFusionEmbedURL)){
                $view.='<a href="#" onClick="openWilayahKerjaLembaga('.$lembaga->idLembaga.')">Peta kerja</a>';
            }*/ 
            $view.='</li>';
        }       

        $view.='</ul>';
    }else{
        $view='Data lembaga mitra tidak ada';
    }    
    return $view;

}

function getListLembagaMitra(){
    global $wpdb;
    check_ajax_referer("getListLembagaMitra");
    if(isset($_POST['action'])){
        $paging = intval($_POST['paging']);
        echo listLembagaMitra($paging);
    }
    die();
}

function getDataLembagaMitra(){
    global $wpdb;
    check_ajax_referer("getDataLembagaMitra");
    if(isset($_POST['action'])){
        $dataLembaga = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lembagamitra ORDER BY idLembaga");
	//print_r($dataLembaga);
echo mysql_error();
	$lembagaMitra = array();
        $i=0;
        foreach ($dataLembaga AS $lembaga){
            $view='<div id="infoLembaga">';
            $view.='<h2>'.$lembaga->namaLengkap.'</h2>';
            $view.='<div id="lembagatabs-'.$lembaga->idLembaga.'">';
            $view.='
            <div id="lembagaMenu">
            <ul>
            <li id="menuProfil" onClick="menuProfilClick()" class="selected">Profil</li>
            <li id="menuProgram" onClick="menuProgramClick()">Produk Unggulan</li>
            <li id="menuWilayah" onClick="menuWilayahClick()">Potensi</li>
            </ul>
            </div>';
            $view.='<div id="infoLembagaContent">';
            $view.='<div id="profilContent">
                    <p>'.$lembaga->deskripsi.'</p>';
                    $view.='<b>Alamat</b><li>'.$lembaga->alamat.'</li>';
                    $view.='<b>Nomor telepon</b>';
                    $telp = explode(',', $lembaga->telp);
                    foreach ($telp AS $key=>$value){
                        $view.='<li>'.$value.'</li>';
                    }
                    $view.='<b>Narahubung</b>';
                    $narahubung = explode(',', $lembaga->narahubung);
                    foreach ($narahubung AS $key=>$value){
                        $view.='<li>'.$value.'</li>';
                    }
                    $view.='<b>Email</b>';
                    $email = explode(',', $lembaga->email);
                    foreach ($email AS $key=>$value){
                        $view.='<li>'.$value.'</li>';
                    }
                    $view.='<b>Website</b><li><a href="'.$lembaga->website.'">'.$lembaga->website.'</a></li>';
                    $view.='<p><br/>Klik <a target="_blank" href="'.get_bloginfo('siteurl').'/?page_id='.$lembaga->pageId.'">di sini</a> untuk melihat profil selengkapnya.</p>';

            $view.='</div>';
            $view.='<div id="programContent"><ul>';
                    $produk_unggulan = explode(',', $lembaga->program);
                    foreach ($produk_unggulan AS $key=>$value){
                        $view.='<li>'.$value.'</li>';
                    }
                    $view.='</ul></div>';
            /*
            $dataWilayah = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wilayahkerja WHERE idLembaga='$lembaga->idLembaga'");
            $view.='<div id="wilayahContent"><ul>';
                    foreach($dataWilayah AS $wilayah){
                        $view.='<li>'.$wilayah->wilayah.'</li>';
                    }
            $view.='</ul>';
             *
             */
            $view.='<div id="wilayahContent">';//$lembaga->mapFusionEmbedURL
            if(preg_match('/google.com/i',$lembaga->mapFusionEmbedURL)){
                $view.='<br/><br/><p> Klik <a href="#" onClick="openWilayahKerjaLembaga('.$lembaga->idLembaga.')">disini</a> untuk melihat Peta Wilayah Kerja Lembaga.</p>';
            }else{
            	$view.='<ul>';
                    $potensi = explode(',', $lembaga->mapFusionEmbedURL);
                    foreach ($potensi AS $key=>$value){
                        $view.='<li>'.$value.'</li>';
                    }
                    $view.='</ul>';                
            }
            
            $view.='</div>';
            $view.='</div>';
            $view.='</div></div>';
            $lembagaMitra[$i]['idLembaga'] = $lembaga->idLembaga;
            $lembagaMitra[$i]['namaLembaga'] = $lembaga->namaLengkap;
            $lembagaMitra[$i]['profilLembaga'] = $view;
            $lembagaMitra[$i]['alamatLatLong'] = $lembaga->alamatLatLong;
            $lembagaMitra[$i]['deskripsi'] = $lembaga->deskripsi;
            $lembagaMitra[$i]['program'] = $lembaga->program;
            $i++;
        }
        echo json_encode($lembagaMitra);
    }else{
        echo json_encode('dataLembaga tidak ditemukan');
    }
    die();
}


/*
 * Fungsi untuk menangani request JSON data sebaran wilayah Lembaga Mitra
 * kemudian untuk membentuk petanya
 */
function getDataWilayahKerja(){
    global $wpdb;
    check_ajax_referer("getDataWilayahKerja");
    if(isset($_POST['action'])){
        $idLembaga = intval($_POST['idLembaga']);
        $dataLembaga = $wpdb->get_results("SELECT idLembaga,namaLengkap,namaPendek,mapFusionEmbedURL FROM ".$wpdb->prefix."lembagamitra WHERE idLembaga='$idLembaga'");
        $data = array();
        $data['lembaga'] = $dataLembaga;
        echo json_encode($data);
    }
    die();
}
function listRegionMitra(){
   global $wpdb;
        $dataRegion = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wilayah ORDER BY id");
        $regionMitra = array();
        $i=0;
        $view='';
        $view.='<ul>';
        foreach ($dataRegion AS $region){
            $view.='<li><a href="#" onClick="setMapByRegion(\''.$region->latlong.'\','.$region->zoom.')">'.$region->wilayah.'</li>';
        }
        $view.='</ul>';
   return $view;
}
add_action('wp_head','frontendMitraFordCSS');
add_action('wp_head','frontendMitraFordJS');
//add_action('wp_head','frontendOnloadJS');
add_action('wp_ajax_nopriv_getDataLembagaMitra', 'getDataLembagaMitra' );
add_action('wp_ajax_getDataLembagaMitra', 'getDataLembagaMitra' );

add_action('wp_ajax_nopriv_getDataWilayahKerja', 'getDataWilayahKerja' );
add_action('wp_ajax_getDataWilayahKerja', 'getDataWilayahKerja' );

add_action('wp_ajax_nopriv_getListLembagaMitra', 'getListLembagaMitra' );
add_action('wp_ajax_getListLembagaMitra', 'getListLembagaMitra' );

?>
