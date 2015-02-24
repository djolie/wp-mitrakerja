<?php
/* 
 * Untuk menampilkan daftar mitra dalam bentuk tabel
 * 
 */
function frontendlistMitraJS(){
    ?>
    <script type="text/javascript">
    var ajaxurl = '<?php bloginfo('siteurl');?>/wp-admin/admin-ajax.php';
    function getListLembagaMitraTable(_paging){
        //alert('URL = '+ajaxurl);
        jQuery.ajax({
                type: "POST",url: ajaxurl,
                data: {action: "getListLembagaMitraTable", paging:_paging, _ajax_nonce: "<?php echo wp_create_nonce('getListLembagaMitraTable'); ?>" },
                beforeSend: function() {jQuery("#loading").show();},
                complete: function() { jQuery("#loading").hide();},
                success: function(data){
                    jQuery("#listLembagaMitraContainer").html(data);
                }
        });
    }
    </script>
    <?php
}
function filterMitra($content){
    $filtered = '[mitraFordTable/]';
    $replacement = '<div id="listLembagaMitraContainer">'.listLembagaMitraTable(0).'</div>';
    $content=str_ireplace($filtered,$replacement,$content);
    return $content;
}


function listLembagaMitraTable($paging=0){
    global $wpdb;
    $view='<div id="loading">Memuat . . .</div>';
    $maxview=5;
    $limit_front=0;
    if($paging>0){
        $page = $paging+1;
        $limit_front = ($page*$maxview)-$maxview;
    }
    $jumlahlembaga = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."lembagamitra"));
    if($jumlahlembaga>0){
        $jml_page = bcdiv($jumlahlembaga,$maxview,0)+1;
        $dataLembaga = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."lembagamitra ORDER BY namaLengkap ASC LIMIT $limit_front,$maxview");

        $page = $paging + 1;
        $prev = $page-2;
        $next = $page;
        $last = $jml_page - 1;

        /*
         *#####################   PAGINATION   ####################
         */

        $pagination='<div id="listLembagaPagination">';
        $pagination.='<a href="#listLembagaPagination" onClick="getListLembagaMitraTable(0)">&laquo;</a>';
        if($page>1){
           $pagination.='<a href="#listLembagaPagination" onClick="getListLembagaMitraTable('.$prev.')">&lsaquo;</a>';
        }else{
           $pagination.='&nbsp;&lsaquo;&nbsp;';
        }
        for($i = $paging;$i<=($paging+$maxview);$i++){
            if($i<$jml_page){
                $p=$i+1;
                if($i==($page-1)) $pagination.='&nbsp;'.$p.'&nbsp;';
                else $pagination.='<a href="#listLembagaPagination" onClick="getListLembagaMitraTable('.$i.')">'.$p.'</a>';
            }
        }
        if($page<$jml_page){
            $pagination.='<a href="#listLembagaPagination" onClick="getListLembagaMitraTable('.$next.')">&rsaquo;</a>';

        }else{
           $pagination.='&nbsp;>&nbsp;';
        }
        $pagination.='<a href="#listLembagaPagination" onClick="getListLembagaMitraTable('.$last.')">&raquo;</a>';
        $pagination.='</div>';
        /*
         *#####################   END PAGINATION   ####################
         */

        $view.='<table class="member">
            <tr>
                <th>No</th>
                <th>Lembaga</th>
                <th>Alamat</th>
                <th>No.Telp</th>
                <th>Email</th>
                <th>Narahubung</th>
                <th>Website</th>
            </tr>';
        $no = ($paging*$maxview)+1;
        foreach($dataLembaga AS $lembaga){
            $style='';
            if($no%2==0)$style='style="background-color:#F9F8EE;"';
            $view.='<tr '.$style.'>';
            $view.='<td>'.$no.'</td>';
            $view.='<td><a href="'.get_bloginfo('siteurl').'?page_id='.$lembaga->pageId.'">'.$lembaga->namaLengkap.'('.$lembaga->namaPendek.')</a></td>';
            $view.='<td>'.$lembaga->alamat.'</td>';
            $view.='<td>';
                $telps = explode(',',$lembaga->telp);
                $view.='<ul>';
                foreach($telps AS $k=>$v){
                    $view.='<li>'.$v.'</li>';
                }
                $view.='</ul>';
            $view.='</td>';
            $view.='<td>';
                $emails = explode(',',$lembaga->email);
                $view.='<ul>';
                foreach($emails AS $k=>$v){
                    $view.='<li>'.str_ireplace('@','<i>[at]</i>', $v).'</li>';
                }
                $view.='</ul>';
            $view.='</td>';
            $view.='<td>'.$lembaga->narahubung.'</td>';
            $view.='<td><a rel="nofollow" target="_blank" href="'.$lembaga->website.'">'.$lembaga->website.'</a></td>';
            $view.='</tr>';
            $no++;
        }

        $view.='</table>';
        $view.= $pagination;
    }else{
        $view='Data lembaga mitra tidak ada';
    }
    return $view;

}

function getListLembagaMitraTable(){
    global $wpdb;
    check_ajax_referer("getListLembagaMitraTable");
    if(isset($_POST['action'])){
        $paging = intval($_POST['paging']);
        //echo '<div id="listLembagaMitraContainer">'.listLembagaMitraTable($paging).'</div>';
        echo listLembagaMitraTable($paging);
    }
    die();
}
add_filter('the_content','filterMitra');
add_action('wp_head','frontendlistMitraJS');
add_action('wp_ajax_nopriv_getListLembagaMitraTable', 'getListLembagaMitraTable' );
add_action('wp_ajax_getListLembagaMitraTable', 'getListLembagaMitraTable' );

?>
