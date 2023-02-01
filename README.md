# zibll-medal
- 1.把func.php上传到主题跟目录
- 2.导入xz.sql到数据库
# 引用在主题显示
1.在zibll\inc\functions\zib-author.php的第157行function zib_author_content()函数里面添加代码
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
