<?php
/*
 * @Author: Qinver
 * @Url: zibll.com
 * @Date: 2020-12-17 21:45:28
 * @LastEditTime: 2022-04-12 17:01:20
 */
require 'wp-load.php';
    if (get_current_user_id()){
    echo buy_xz(get_current_user_id(),2);
    }else{
    echo '您没有登录！';
    }
