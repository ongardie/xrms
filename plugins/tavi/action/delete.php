<?PHP
require('parse/html.php');
require('lib/captcha.php');
require(TemplateDir . '/edit.php');

function action_delete() {
    
    global $page, $pagestore, $ParseEngine, $version, $UseCaptcha;
    global $http_site_root;
    global $xrms_db_server,$xrms_db_username,$xrms_db_password,$xrms_db_dbname;



    $pg = $pagestore->page($page);
    $pg->read();
  
    # Lista dos documentos
    
    $WKDB = new WikiDB(0,$xrms_db_server,$xrms_db_username,$xrms_db_password,$xrms_db_dbname);

    $sql = "delete from tavi_pages where title='".$page."'";
    $rs = $WKDB->query($sql);
    header("Location: $http_site_root/plugins/tavi/index.php");
}
?>