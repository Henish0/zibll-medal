<?php
/**
 * Template name: Zibll-文章归档
 * Description:   A archives page
 */
require 'wp-load.php';
get_header();
$header_style = zib_get_page_header_style();
if (get_current_user_id() == ''){
    echo ('<div class="zib-widget relative inline-div _medal_yes" align="center" style="width: 100%; height: 200%; margin:0 auto;"><h1><b>您没有登录，请先登录在访问本页面！</b></h1></div><br>');
    get_footer();
    exit();
}else{
    if($_GET['action'] == 'buy_xz'){
        if($_GET['js_xz_id']){
            $js_get = buy_xz(get_current_user_id(),$_GET['js_xz_id']);
            echo "<script>alert('" . $js_get . "');location.href='/mx-medal.php';</script>";
    }
    }
    if($_GET['action'] == 'set_xz'){
        $js_get = set_user_xz_zt(get_current_user_id(),$_GET['xz_id'],$_GET['xz_zt']);
        echo "<script>alert('" . $js_get . "');location.href='/mx-medal.php';</script>";
    }
}
?>
    <script>
        function mx_buy_xz_js(xz_id,xz_name){
            if(confirm("确定要兑换" + xz_name + "吗？")){
                window.location = "/mx-medal.php?action=buy_xz&js_xz_id=" + xz_id
            }
        }
        function mx_set_xz(xz_id,xz_zt) {
            if (xz_zt == 'true') {
                window.location = "/mx-medal.php?action=set_xz&xz_id=" + xz_id + "-false&xz_zt=" + xz_zt
            }else{
                window.location = "/mx-medal.php?action=set_xz&xz_id=" + xz_id + "&xz_zt=" + xz_zt
            }
        }
    </script>
    <style>
        .inline-div {
            display: inline-block;
            width:   auto;
            height: auto;
            margin: 10px 10px 10px 10px;
        }
        ._medal_yes::after {
            color: rgb(76, 175, 80);
            position: absolute;
            line-height: 1;
            top: 1rem;
            right: 1rem;
            font-size: 2rem;
            font-family: "Font Awesome 5 Free";
            content: "✔";
            font-weight: bold;
            z-index: 1;
        }
    </style>
    <body>
<?php
echo mx_get_all_xz_html();
echo mx_get_user_xz_html();
?>
    </body>
<?php
function mx_get_all_xz_html()
{
    $all_xz = get_all_xz();
    //$array = get_object_vars($all_xz);
    $html = '';
    $c = ",";
    $u_h = '';
    $user_id = get_current_user_id();
    $html .= '<div class="user-card zib-widget xz" style="width: 100%; height: 100%; margin:0 auto;" align="center"><h2>勋章中心</h2>';
    $user_xz = mx_query_xz($user_id);
    foreach ($all_xz as $val) {
        if ($val) {
            foreach (explode('|',$user_xz) as $xz_val){
                $v = str_replace('-false','',$xz_val);
                if($v == $val->ID){
                    $u_h = '1';
                    $html .= '<div class="zib-widget relative inline-div _medal_yes" style="width:150px;"><a title="' . $val->name . '"><img src="' . $val->img . '" style="margin-right:5px;" alt="" width="100" height="50"></a>
        <div><b>' . $val->name . '</b></div><div>' . $val->info . '</div></div>';
                    break;
                }else{
                    $u_h = '';
                }
            }
            if($u_h == '') {
                $a = "'";
                $html .= '<div id="' . $val->ID . '" onclick="mx_buy_xz_js(' . $a . $val->ID . $a . $c . $a . $val->name . $a . ')" class="zib-widget relative inline-div" style="width:150px;"><a title="' . $val->name . '"><img src="' . $val->img . '" style="margin-right:5px;" alt="" width="100" height="50"></a>
        <div><b>' . $val->name . '</b></div><div>' . $val->info . '</div></div>';
            }
        }
    }
    if ($html) {
        $html .= '</div><br>';
        return $html;
    } else {
        return '数据库中没有勋章！';
    }
}
function mx_get_user_xz_html(){
    $user_id = get_current_user_id();
    $xz = mx_query_xz($user_id);
    $a = "'";
    $c = ",";
    if ($xz){
        //创建勋章html
        $xzhtml = '';
        $xzhtml .= '<div class="user-card zib-widget xz" align="center">
    <div><b><h2>我的勋章</h2></b></div>
    <br>
         <div>';
//按照指定字符分割文本为数组
        foreach (explode('|',$xz) as $val){
            foreach (explode('-',$val) as $v){
                if($v=='false'){
                    $b = '1';
                }else{
                    $b = '';
                }
            }
            if ($b == ''){
                $xz_info = get_xz_info($val);
                if ($val){
                    $xzhtml .= '<div id="' . $xz_info['ID'] . '" onclick="mx_set_xz(' . $a . $xz_info['id'] . $a . $c . $a . 'false' . $a . ')" class="zib-widget relative inline-div" style="width:150px;"><a title="' . $xz_info['name']  . '"><img src="' . $xz_info['img']  . '" style="margin-right:5px;" alt="" width="100" height="50"></a>
        <div><b>' . $xz_info['name']  . '</b></div><div>' . '佩戴中' . '</div></div>';
                }
            }else{
                $xz_info = get_xz_info($val);
                if ($val){
                    $xzhtml .= '<div id="' . $xz_info['ID'] . '" onclick="mx_set_xz(' . $a . $xz_info['id'] . $a . $c . $a . 'true' . $a . ')" class="zib-widget relative inline-div" style="width:150px;"><a title="' . $xz_info['name']  . '"><img src="' . $xz_info['img']  . '" style="margin-right:5px;" alt="" width="100" height="50"></a>
        <div><b>' . $xz_info['name']  . '</b></div><div>' . '未佩戴' . '</div></div>';
                }
            }
        }
        $xzhtml .= '</div></div>';
        return $xzhtml;
    }else{
        return '您还没有勋章';
    }
}
//var_dump(get_all_xz());
get_footer(); ?>
