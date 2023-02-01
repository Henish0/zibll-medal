<?php
/*
 *                        _oo0oo_
 *                       o8888888o
 *                       88" . "88
 *                       (| -_- |)
 *                       0\  =  /0
 *                     ___/`---'\___
 *                   .' \\|     |// '.
 *                  / \\|||  :  |||// \
 *                 / _||||| -:- |||||- \
 *                |   | \\\  - /// |   |
 *                | \_|  ''\---/''  |_/ |
 *                \  .-\__  '-'  ___/-. /
 *              ___'. .'  /--.--\  `. .'___
 *           ."" '<  `.___\_<|>_/___.' >' "".
 *          | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *          \  \ `_.   \_ __\ /__ _/   .-` /  /
 *      =====`-.____`.___ \_____/___.-`___.-'=====
 *                        `=---='
 *
 *
 *      ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 *            佛祖保佑       永不宕机     永无BUG
 *
 */
//require dirname(__FILE__) . '/../../../../../wp-load.php';

/**
 * 勋章功能部分
 */

//判断用户有没有勋章有就返回列表
function mx_query_xz($USER_ID){
    global $wpdb;
    $xzsql = "SELECT * FROM `wp_mx_usermeta` WHERE `uid` = " . $USER_ID . " AND `meta_key` LIKE 'xz'";
    $xz_data = $wpdb->get_row($xzsql,ARRAY_A);
    //var_dump ($xz_data);
    if($xz_data){
        return $xz_data['meta_value'];
    }else{
        return 'null';
    }
}
//返回用户勋章html
function mx_xz_html($user_ID){
    $xhtml = '';
    $xz = mx_query_xz($user_ID);
    if ($xz){
        $a = '';
        //创建勋章html
        $xzhtml = '';
        $xzhtml .= '<div class="user-card zib-widget xz" align="center">
    <div><b>勋章统计</b></div>
    <br>
         <div>';
        //按照指定字符分割文本为数组
        foreach (explode('|',$xz) as $val){
            foreach (explode('-',$val) as $v){
                if($v=='false'){
                    $a = '1';
                }else{
                    $a = '';
                }
            }
            if ($a == ''){
                $xz_info = get_xz_info($val);
                if ($val){
                    $xhtml .= '<a title="' . $xz_info['name'] . '"><img src="' . $xz_info['img'] . '" style="margin-right:5px;" alt="" width="100" height="50"></a>';
                }
            }
        }
        if($xhtml != ''){
            $xzhtml .= $xhtml . '</div></div>';
            return $xzhtml;
        }else{
            $xhtml = 'TA隐藏了所有勋章哦QAQ！';
            $xzhtml .= $xhtml . '</div></div>';
            return $xzhtml;
        }
    }else{
        return 'null';
    }
}
//获取勋章ID、名字、图片、类型、介绍、价格
function get_xz_info($xz_id){
    global $wpdb;
    $sql = "SELECT * FROM `wp_mx_xz` WHERE `ID` = " . $xz_id;
    $info = $wpdb->get_row($sql,ARRAY_A);
    if($info){
        $xz_id = $info['ID'];
        $xz_name = $info['name'];
        $xz_img = $info['img'];
        $xz_type = $info['type'];
        $xz_type_key = $info['type_key'];
        $xz_info = $info['info'];
        $xz_buy = $info['buy'];
        return (array("id"=>$xz_id,"name"=>$xz_name,"img"=>$xz_img,"type"=>$xz_type,"type_key"=>$xz_type_key,"info"=>$xz_info,"buy"=>$xz_buy));
    }else{
        return 'null';
    }
}
//购买勋章
function buy_xz($user_id,$xz_id){
    if(mx_query_xz($user_id) == 'null'){
        add_xz_db($user_id);
    }
    //获得用户积分、勋章价格
    $xz_info = get_xz_info($xz_id);
    $user_points = zibpay_get_user_points($user_id);
    $xz_buy = $xz_info['buy'];
    if ($xz_info['type'] == 'buy'){
        //判断用户积分够不够
        if ($xz_buy > $user_points){
            return '抱歉，您的积分不足，无法兑换！';
        }else{
            $xz_data = mx_query_xz($user_id);
            //按照指定字符分割文本为数组 判断用户勋章是否重复
            foreach (explode('|',$xz_data) as $val){
                if ($val == $xz_id){
                    return '抱歉，您已经兑换过这个勋章了！';
                    exit();
                }
            }
            global $wpdb;
            if ($xz_data){
                $meta_value = $xz_data . $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                $order_num = current_time('ymdHis') . mt_rand(10, 99) . mt_rand(10, 99) . mt_rand(100, 999); // 订单号
                $update_points_data = array(
                    'order_num' => $order_num, //订单号
                    'value'     => -$xz_buy, //值 整数为加，负数为减去
                    'type'      => '兑换勋章', //类型说明
                    'desc'      => '兑换了 ' . $xz_info['name'] . ' 消耗了 ' . $xz_buy . ' 积分', //说明
                    'time'      => current_time('Y-m-d H:i'),
                );
                zibpay_update_user_points($user_id,$update_points_data);
                return '兑换成功！';
            }else{
                $meta_value = $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                $order_num = current_time('ymdHis') . mt_rand(10, 99) . mt_rand(10, 99) . mt_rand(100, 999); // 订单号
                $update_points_data = array(
                    'order_num' => $order_num, //订单号
                    'value'     => -$xz_buy, //值 整数为加，负数为减去
                    'type'      => '兑换勋章', //类型说明
                    'desc'      => '兑换了 ' . $xz_info['name'] . ' 消耗了 ' . $xz_buy . ' 积分', //说明
                    'time'      => current_time('Y-m-d H:i'),
                );
                zibpay_update_user_points($user_id,$update_points_data);
                return '兑换成功！';
            }
        }
    }elseif ($xz_info['type'] == 'free'){
        $xz_data = mx_query_xz($user_id);
        //按照指定字符分割文本为数组 判断用户勋章是否重复
        foreach (explode('|',$xz_data) as $val) {
            if ($val == $xz_id) {
                return '抱歉，您已经领取过这个勋章了！';
                exit();
            }
        }
        global $wpdb;
        if ($xz_data) {
            $meta_value = $xz_data . $xz_id . '|';
            $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
            $wpdb->query($sql);
            return '领取成功！';
        } else {
            $meta_value = $xz_id . '|';
            $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
            $wpdb->query($sql);
            return '领取成功！';
        }
    }elseif ($xz_info['type'] == 'false'){
        return '该勋章已关闭兑换！';
    }elseif ($xz_info['type'] == 'post'){
        //投稿数量兑换
        $xz_post = count_user_posts($user_id,'post',true);
        $xz_data = mx_query_xz($user_id);
        //按照指定字符分割文本为数组 判断用户勋章是否重复
        foreach (explode('|',$xz_data) as $val){
            if ($val == $xz_id){
                return '抱歉，您已经兑换过这个勋章了！';
                exit();
            }
        }
        if($xz_post >= $xz_buy){
            global $wpdb;
            if ($xz_data) {
                $meta_value = $xz_data . $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！';
            } else {
                $meta_value = $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！';
            }
        }else{
            return '抱歉，您不满足此勋章兑换条件！';
        }
    }elseif ($xz_info['type'] == 'comm'){
        //评论数量兑换
        $xz_comm = get_comments('count=true&user_id='.$user_id);
        $xz_data = mx_query_xz($user_id);
        //按照指定字符分割文本为数组 判断用户勋章是否重复
        foreach (explode('|',$xz_data) as $val){
            if ($val == $xz_id){
                return '抱歉，您已经兑换过这个勋章了！';
                exit();
            }
        }
        if($xz_comm >= $xz_buy){
            global $wpdb;
            if ($xz_data) {
                $meta_value = $xz_data . $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！！';
            } else {
                $meta_value = $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！！';
            }
        }else{
            return '抱歉，您不满足此勋章兑换条件！';
        }
    }elseif ($xz_info['type'] == 'jf'){
        //积分数量兑换
        $xz_jf = zibpay_get_user_points($user_id);
        $xz_data = mx_query_xz($user_id);
        //按照指定字符分割文本为数组 判断用户勋章是否重复
        foreach (explode('|',$xz_data) as $val){
            if ($val == $xz_id){
                return '抱歉，您已经兑换过这个勋章了！';
                exit();
            }
        }
        if($xz_jf >= $xz_buy){
            global $wpdb;
            if ($xz_data) {
                $meta_value = $xz_data . $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！！！';
            } else {
                $meta_value = $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！！！';
            }
        }else{
            return '抱歉，您不满足此勋章兑换条件！';
        }
    }elseif ($xz_info['type'] == 'like'){
        //点赞数量兑换
        $xz_like = get_user_posts_meta_count($user_id, 'like');
        $xz_data = mx_query_xz($user_id);
        //按照指定字符分割文本为数组 判断用户勋章是否重复
        foreach (explode('|',$xz_data) as $val){
            if ($val == $xz_id){
                return '抱歉，您已经兑换过这个勋章了！';
                exit();
            }
        }
        if($xz_like >= $xz_buy){
            global $wpdb;
            if ($xz_data) {
                $meta_value = $xz_data . $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！！！！';
            } else {
                $meta_value = $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！！！！';
            }
        }else{
            return '抱歉，您不满足此勋章兑换条件！';
        }
    }elseif ($xz_info['type'] == 'foll'){
        //粉丝数量兑换
        $xz_foll = get_user_meta($user_id, 'followed-user-count', true);
        $xz_data = mx_query_xz($user_id);
        //按照指定字符分割文本为数组 判断用户勋章是否重复
        foreach (explode('|',$xz_data) as $val){
            if ($val == $xz_id){
                return '抱歉，您已经兑换过这个勋章了！';
                exit();
            }
        }
        if($xz_foll >= $xz_buy){
            global $wpdb;
            if ($xz_data) {
                $meta_value = $xz_data . $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！！！！！';
            } else {
                $meta_value = $xz_id . '|';
                $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $meta_value . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                $wpdb->query($sql);
                return '感谢您，领取成功！！！！！';
            }
        }else{
            return '抱歉，您不满足此勋章兑换条件！';
        }
    }
}
//列出勋章列表
function get_all_xz(){
    global $wpdb;
    $sql = "SELECT * FROM `wp_mx_xz`";
    $info = $wpdb->get_results($sql);
    if($info) {
        return $info;
    }else{
        return '数据库中没有勋章！';
    }
}
//为用户创建勋章表
function add_xz_db($user_id){
    global $wpdb;
    $sql = "INSERT INTO `wp_mx_usermeta` (`id`, `uid`, `meta_key`, `meta_value`) VALUES (NULL, '". $user_id ."', 'xz', NULL)";
    $wpdb->query($sql);
}
//修改勋章状态
function set_user_xz_zt($user_id,$xz_id,$xz_zt){
    if($xz_id != '') {
        if ($xz_zt != ''){
            global $wpdb;
            $user_xz = '';
            $xzsql = "SELECT * FROM `wp_mx_usermeta` WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
            $xz_data = $wpdb->get_row($xzsql,ARRAY_A);
            //var_dump ($xz_data);
            if($xz_data){
                foreach (explode('|',$xz_data['meta_value']) as $val){
                    if($val != ''){
                        if($xz_id == $val){
                            if($xz_zt == 'false'){
                                    $user_xz .= $val . '-false|';
                            }
                            if($xz_zt == 'true'){
                                $v = str_replace('-false','',$val);
                                $user_xz .= $v . '|';
                            }
                        }else{
                            $user_xz .= $val . '|';
                        }
                    }
                }
                if($user_xz != ''){
                    $sql = "UPDATE `wp_mx_usermeta` SET `meta_value` = '" . $user_xz . "' WHERE `uid` = " . $user_id . " AND `meta_key` LIKE 'xz'";
                    $wpdb->query($sql);
                    return '勋章状态修改成功！';
                }else{
                    return 'BUG!BUG!BUG!BUG!BUG!请联系站长处理！';
                }
            }else{
                return '您不存在此勋章！';
            }
        }else{
            return '勋章状态值为空！！';
        }
    }else{
        return '勋章ID为空！';
    }
}
