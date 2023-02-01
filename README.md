# zibll-medal
### 使用前可以自行修改func.php里面的勋章分辨率等参数！
### 因为没写后台所以添加勋章请手动在数据库添加，后台有默认的勋章数据按照格式添加就行
### 还请把默认的勋章图片链接更换为自己的链接，不要直接使用，谢谢！
### 演示
<img decoding="async" src="https://img.xhacgn.com/images/2023/02/01/-2022-06-19-192642.png" width="50%">
<img decoding="async" src="https://img.xhacgn.com/images/2023/02/01/-2022-06-19-192531.png" width="50%">
<img decoding="async" src="https://img.xhacgn.com/images/2023/02/01/-2022-06-19-192558.png" width="50%">
- 1.把func.php上传到主题跟目录
- 2.导入xz.sql到数据库
# 设置勋章中心
- 可以把mx-medal.php直接上传到根目录直接访问文件，也可以添加到zibll\pages文件夹在wordpress后台添加页面模板！
# 引用在主题显示
- 1.在作者个人主页显示：zibll\inc\functions\zib-author.php的第157行function zib_author_content()函数里面添加代码
 ```php
    //改动开始
        $uid = $author_id;
        $xz = mx_query_xz($uid);
        if ($xz != 'null'){
            $xzhtml = mx_xz_html($uid);
            echo $xzhtml . $html;
        }else {
            echo $html;
        }
 ```

- ###列如
```php
    /**
 * @description: 作者页主内容外框架
 * @param {*}
 * @return {*}
 */
function zib_author_content()
{
    global $wp_query;
    $curauth   = $wp_query->get_queried_object();
    $author_id = $curauth->ID;

    do_action('zib_author_main_content');
    $post_count = zib_get_user_post_count($author_id, 'publish');

    $tabs_array['post'] = array(
        'title'         => '文章<count class="opacity8 ml3">' . $post_count . '</count>',
        'content_class' => '',
        'route'         => true,
        'loader'        => zib_get_author_tab_loader('post'),
    );
    $tabs_array['favorite'] = array(
        'title'         => '收藏<count class="opacity8 ml3">' . get_user_favorite_post_count($author_id) . '</count>',
        'content_class' => '',
        'route'         => true,
        'loader'        => zib_get_author_tab_loader('post'),
    );

    if (!_pz('close_comments')) {
        $comment_count         = get_user_comment_count($author_id);
        $tabs_array['comment'] = array(
            'title'         => '评论<count class="opacity8 ml3">' . $comment_count . '</count>',
            'content_class' => '',
            'route'         => true,
            'loader'        => zib_get_author_tab_loader('comment'),
        );
    }

    $tabs_array = apply_filters('author_main_tabs_array', $tabs_array, $author_id);

    $tabs_array['follow'] = array(
        'title'         => '粉丝<count class="opacity8 ml3">' . _cut_count(get_user_meta($author_id, 'followed-user-count', true)) . '</count>',
        'content_class' => 'text-center',
        'route'         => true,
        'loader'        => zib_get_author_tab_loader('follow'),
    );

    $tab_nav     = zib_get_main_tab_nav('nav', $tabs_array, 'author', false);
    $tab_content = zib_get_main_tab_nav('content', $tabs_array, 'author', false);
    if ($tab_nav && $tab_content) {
        $html = '<div class="author-tab zib-widget">';
        $html .= '<div class="affix-header-sm" offset-top="6">';
        $html .= $tab_nav;
        $html .= '</div>';
        $html .= $tab_content;
        $html .= '</div>';
        //改动开始
        $uid = $author_id;
        $xz = mx_query_xz($uid);
        if ($xz != 'null'){
            $xzhtml = mx_xz_html($uid);
            echo $xzhtml . $html;
        }else {
            echo $html;
        }
    }
}
```
# 在文章页作者卡片显示：在zibll\inc\functions\zib-author.php的第628行function zib_get_user_card_box函数添加代码
```php
    //改动开始
    $uid = get_post_field ('post_author', get_the_ID());
    $xz = mx_query_xz($uid);
    if ($xz != 'null'){
        $xzhtml = mx_xz_html($uid);
        echo $html . $xzhtml;
    }else{
        echo $html;
    }
```
### 列如
```php
//新版的用户小工具
function zib_get_user_card_box($args = array(), $echo = false)
{
    $defaults = array(
        'user_id'            => 0,
        'class'              => '',
        'show_posts'         => true,
        'show_checkin'       => false,
        'show_img_bg'        => false,
        'show_button'        => true,
        'button_1'           => 'post',
        'button_1_text'      => '发布文章',
        'button_1_class'     => 'jb-pink',
        'button_2'           => 'home',
        'button_2_text'      => '用户中心',
        'button_2_class'     => 'jb-blue',
        'show_payvip_button' => false,
        'limit'              => 6,
        'orderby'            => 'views',
        'post_type'          => 'post',
        'post_style'         => 'card',
    );

    $args = wp_parse_args((array) $args, $defaults);

    if (!$args['user_id']) {
        return;
    }

    $user_id   = $args['user_id'];
    $cuid      = get_current_user_id();
    $avatar    = zib_get_avatar_box($user_id, 'avatar-img avatar-lg');
    $name      = zib_get_user_name("id=$user_id&class=flex1 flex ac&follow=true");
    $tag_metas = zib_get_user_badges($user_id);
    $btns      = '';
    $post      = '';
    $checkin   = '';

    if (!$cuid || $cuid != $user_id) {
        $args['show_button'] = false;
    }
    if ($args['show_posts'] && $args['limit'] > 0) {
        $post = zib_get_user_card_post($args);
    }
    if ($args['show_checkin'] && get_current_user_id() == $user_id) {
        $checkin = zib_get_user_checkin_btn('img-badge jb-yellow', '<i class="fa fa-calendar-check-o ml3 mr6"></i>签到', '<i class="ml3 mr6 fa fa-calendar-check-o"></i>已签到');
    }

    if ($args['show_button']) {
        $btns .= zib_get_new_add_btns([$args['button_1']], 'but pw-1em mr6 ' . $args['button_1_class'], $args['button_1_text']);
        if ('home' === $args['button_2']) {
            $btns .= zib_get_user_home_link($user_id, 'but pw-1em ml6 ' . $args['button_2_class'], $args['button_2_text']);
        } else {
            $btns .= zib_get_user_center_link('but pw-1em ml6 ' . $args['button_2_class'], $args['button_2_text']);
        }
        $btns = '<div class="user-btns mt20">' . $btns . '</div>';
    }

    $cover = $args['show_img_bg'] ? '<div class="user-cover graphic" style="padding-bottom: 50%;">' . get_user_cover_img($user_id) . '</div>' : '';

    $html = '<div class="user-card zib-widget ' . $args['class'] . '">' . $cover . '
        <div class="card-content mt10 relative">
            <div class="user-content">
                ' . $checkin . '
                <div class="user-avatar">' . $avatar . '</div>
                <div class="user-info mt20 mb10">
                    <div class="user-name flex jc">' . $name . '</div>
                    <div class="author-tag mt10 mini-scrollbar">' . $tag_metas . '</div>
                    <div class="user-desc mt10 muted-2-color em09">' . get_user_desc($user_id) . '</div>
                    ' . $btns . '
                </div>
            </div>
            ' . $post . '
        </div>
    </div>';

    if (!$echo) {
        return $html;
    }
    //改动开始
    $uid = get_post_field ('post_author', get_the_ID());
    $xz = mx_query_xz($uid);
    if ($xz != 'null'){
        $xzhtml = mx_xz_html($uid);
        echo $html . $xzhtml;
    }else{
        echo $html;
    }
}
```

