<?php
// Add custom Theme Functions here
// Xoa block moi
add_filter('use_block_editor_for_post', '__return_false');

// Admin css
add_action('admin_head', 'my_custom');
function my_custom()
{
    echo '<style>
    li#wp-admin-bar-wp-logo, div#sidebar-container.wpseo_content_cell, div.ui.red.message, div.notice.notice-info, div.update-nag.bsf-update-nag, .notice.notice-error, div.villatheme-dashboard.updated, #setting-error-tgmpa {display:none;}
  </style>';
}

// Custom logo
function my_login_logo_one()
{
?>
    <style type="text/css">
        body.login div#login h1 a {
            background-image: url(https://longanweb.com/wp-content/uploads/2020/03/logo.png);
            background-size: 260px;
            padding-bottom: 10px;
            width: 100%;
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'my_login_logo_one');


/* Code xoa slug san-pham */
function devvn_remove_slug($post_link, $post)
{
    if (!in_array(get_post_type($post), array('product')) || 'publish' != $post->post_status) {
        return $post_link;
    }
    if ('product' == $post->post_type) {
        $post_link = str_replace('/san-pham/', '/', $post_link); //Thay cua-hang bằng slug hiện tại của bạn
    } else {
        $post_link = str_replace('/' . $post->post_type . '/', '/', $post_link);
    }
    return $post_link;
}
add_filter('post_type_link', 'devvn_remove_slug', 10, 2);
/*Sửa lỗi 404 sau khi đã remove slug product hoặc cua-hang*/
function devvn_woo_product_rewrite_rules($flash = false)
{
    global $wp_post_types, $wpdb;
    $siteLink = esc_url(home_url('/'));
    foreach ($wp_post_types as $type => $custom_post) {
        if ($type == 'product') {
            if ($custom_post->_builtin == false) {
                $querystr = "SELECT {$wpdb->posts}.post_name, {$wpdb->posts}.ID
                            FROM {$wpdb->posts} 
                            WHERE {$wpdb->posts}.post_status = 'publish' 
                            AND {$wpdb->posts}.post_type = '{$type}'";
                $posts = $wpdb->get_results($querystr, OBJECT);
                foreach ($posts as $post) {
                    $current_slug = get_permalink($post->ID);
                    $base_product = str_replace($siteLink, '', $current_slug);
                    add_rewrite_rule(
                        $base_product . '?$',
                        "index.php?{$custom_post->query_var}={$post->post_name}",

                        'top'
                    );
                    add_rewrite_rule($base_product . 'comment-page-([0-9]{1,})/?$', 'index.php?' . $custom_post->query_var . '=' . $post->post_name . '&cpage=$matches[1]', 'top');
                    add_rewrite_rule($base_product . '(?:feed/)?(feed|rdf|rss|rss2|atom)/?$', 'index.php?' .

                        $custom_post->query_var . '=' . $post->post_name . '&feed=$matches[1]', 'top');
                }
            }
        }
    }
    if ($flash == true)
        flush_rewrite_rules(false);
}
add_action('init', 'devvn_woo_product_rewrite_rules');
/*Fix lỗi khi tạo sản phẩm mới bị 404*/
function devvn_woo_new_product_post_save($post_id)
{
    global $wp_post_types;
    $post_type = get_post_type($post_id);
    foreach ($wp_post_types as $type => $custom_post) {
        if ($custom_post->_builtin == false && $type == $post_type) {
            devvn_woo_product_rewrite_rules(true);
        }
    }
}
add_action('wp_insert_post', 'devvn_woo_new_product_post_save');

/* Thay product-category bằng slug hiện tại của bạn. Mặc định là product-category */
add_filter('term_link', 'devvn_product_cat_permalink', 10, 3);
function devvn_product_cat_permalink($url, $term, $taxonomy)
{
    switch ($taxonomy):
        case 'product_cat':
            $taxonomy_slug = 'product-category'; //Thay bằng slug hiện tại của bạn. Mặc định là product-category
            if (strpos($url, $taxonomy_slug) === FALSE) break;
            $url = str_replace('/' . $taxonomy_slug, '', $url);
            break;
    endswitch;
    return $url;
}
// Add our custom product cat rewrite rules
function devvn_product_category_rewrite_rules($flash = false)
{
    $terms = get_terms(array(
        'taxonomy' => 'product_cat',
        'post_type' => 'product',
        'hide_empty' => false,
    ));
    if ($terms && !is_wp_error($terms)) {
        $siteurl = esc_url(home_url('/'));
        foreach ($terms as $term) {
            $term_slug = $term->slug;
            $baseterm = str_replace($siteurl, '', get_term_link($term->term_id, 'product_cat'));
            add_rewrite_rule($baseterm . '?$', 'index.php?product_cat=' . $term_slug, 'top');
            add_rewrite_rule($baseterm . 'page/([0-9]{1,})/?$', 'index.php?product_cat=' . $term_slug . '&paged=$matches[1]', 'top');
            add_rewrite_rule($baseterm . '(?:feed/)?(feed|rdf|rss|rss2|atom)/?$', 'index.php?product_cat=' . $term_slug . '&feed=$matches[1]', 'top');
        }
    }
    if ($flash == true)
        flush_rewrite_rules(false);
}
add_action('init', 'devvn_product_category_rewrite_rules');

/*Sửa lỗi khi tạo mới taxomony bị 404*/
add_action('create_term', 'devvn_new_product_cat_edit_success', 10, 2);
function devvn_new_product_cat_edit_success($term_id, $taxonomy)
{
    devvn_product_category_rewrite_rules(true);
}

/*
 * Tùy chỉnh hiển thị thông tin chuyển khoản trong woocommerce
 */
add_filter('woocommerce_bacs_accounts', '__return_false');

add_action('woocommerce_email_before_order_table', 'devvn_email_instructions', 10, 3);
function devvn_email_instructions($order, $sent_to_admin, $plain_text = false)
{

    if (!$sent_to_admin && 'bacs' === $order->get_payment_method() && $order->has_status('on-hold')) {
        devvn_bank_details($order->get_id());
    }
}

add_action('woocommerce_thankyou_bacs', 'devvn_thankyou_page');
function devvn_thankyou_page($order_id)
{
    devvn_bank_details($order_id);
}

function devvn_bank_details($order_id = '')
{
    $bacs_accounts = get_option('woocommerce_bacs_accounts');
    if (!empty($bacs_accounts)) {
        ob_start();
        echo '<table style=" border: 1px solid #ddd; border-collapse: collapse; width: 100%; ">';
    ?>
        <tr>
            <td colspan="2" style="border: 1px solid #eaeaea;padding: 6px 10px;"><strong>Thông tin chuyển khoản</strong></td>
        </tr>
        <?php
        foreach ($bacs_accounts as $bacs_account) {
            $bacs_account = (object) $bacs_account;
            $account_name = $bacs_account->account_name;
            $bank_name = $bacs_account->bank_name;
            $stk = $bacs_account->account_number;
            $icon = $bacs_account->iban;
        ?>
            <tr>
                <td style="width: 200px;border: 1px solid #eaeaea;padding: 6px 10px;"><?php if ($icon) : ?><img src="<?php echo $icon; ?>" alt="" /><?php endif; ?></td>
                <td style="border: 1px solid #eaeaea;padding: 6px 10px;">
                    <strong>STK:</strong> <?php echo $stk; ?><br>
                    <strong>Chủ tài khoản:</strong> <?php echo $account_name; ?><br>
                    <strong>Chi Nhánh:</strong> <?php echo $bank_name; ?>
                </td>
            </tr>
    <?php
        }
        echo '</table>';
        echo ob_get_clean();;
    }
}

/*
* Add quick buy button go to checkout after click

add_action('woocommerce_after_add_to_cart_button','devvn_quickbuy_after_addtocart_button');
function devvn_quickbuy_after_addtocart_button(){
    global $product;
    ?>
    <style>
        .devvn-quickbuy button.single_add_to_cart_button.loading:after {
            display: none;
        }
        .devvn-quickbuy button.single_add_to_cart_button.button.alt.loading {
            color: #fff;
            pointer-events: none !important;
        }
        .devvn-quickbuy button.buy_now_button {
            position: relative;
            color: rgba(255,255,255,0.05);
			background-color: #4B7CD9!important;
        }
        .devvn-quickbuy button.buy_now_button:after {
            animation: spin 500ms infinite linear;
            border: 2px solid #fff;
            border-radius: 32px;
            border-right-color: transparent !important;
            border-top-color: transparent !important;
            content: "";
            display: block;
            height: 16px;
            top: 50%;
            margin-top: -8px;
            left: 50%;
            margin-left: -8px;
            position: absolute;
            width: 16px;
        }
    </style>
    <button type="button" class="button buy_now_button">
        <?php _e('Mua ngay', 'devvn'); ?>
    </button>
    <input type="hidden" name="is_buy_now" class="is_buy_now" value="0" autocomplete="off"/>
    <script>
        jQuery(document).ready(function(){
            jQuery('body').on('click', '.buy_now_button', function(e){
                e.preventDefault();
                var thisParent = jQuery(this).parents('form.cart');
                if(jQuery('.single_add_to_cart_button', thisParent).hasClass('disabled')) {
                    jQuery('.single_add_to_cart_button', thisParent).trigger('click');
                    return false;
                }
                thisParent.addClass('devvn-quickbuy');
                jQuery('.is_buy_now', thisParent).val('1');
                jQuery('.single_add_to_cart_button', thisParent).trigger('click');
            });
        });
    </script>
    <?php
}
add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout');
function redirect_to_checkout($redirect_url) {
    if (isset($_REQUEST['is_buy_now']) && $_REQUEST['is_buy_now']) {
        $redirect_url = wc_get_checkout_url(); //or wc_get_cart_url()
    }
    return $redirect_url;
}
*/
// Them text Số lượng trong Order review
add_filter('woocommerce_checkout_cart_item_quantity', 'customizing_checkout_item_quantity', 10, 3);
function customizing_checkout_item_quantity($quantity_html, $cart_item, $cart_item_key)
{
    $quantity_html = ' <br>
            <span class="product-quantity">' . __('<strong>Số lượng:</strong>') . ' <strong>' . $cart_item['quantity'] . '</strong></span>';
    return $quantity_html;
}
function tutsplus_list_attributes($product)
{
    global $product;
    $product->get_attributes();
}
add_action('woocommerce_product_meta_end', 'tutsplus_list_attributes');

// Them link san pham vao email thong bao
add_filter('woocommerce_order_item_name', 'display_product_title_as_link', 10, 2);
function display_product_title_as_link($item_name, $item)
{

    $_product = wc_get_product($item['variation_id'] ? $item['variation_id'] : $item['product_id']);

    $link = get_permalink($_product->get_id());

    return '<a href="' . $link . '"  rel="nofollow">' . $item_name . '</a>';
}

// Hien thi ten Danh muc san pham trong Quan tri don hang
function action_woocommerce_admin_order_item_headers()
{ ?>
    <th class="item sortable" colspan="2" data-sort="string-ins"><?php _e('Item category', 'woocommerce'); ?></th>
<?php
};


// define the woocommerce_admin_order_item_values callback
function action_woocommerce_admin_order_item_values($_product, $item, $item_id)
{ ?>
    <td class="name" colspan="2">
        <?php
        $termsp = get_the_terms($_product->get_id(), 'product_cat');
        if (!empty($termsp)) {
            foreach ($termsp as $term) {
                $_categoryid = $term->term_id;
                if ($term = get_term_by('id', $_categoryid, 'product_cat')) {
                    echo $term->name . ', ';
                }
            }
        } ?>
    </td>
<?php
};

// add the action
add_action('woocommerce_admin_order_item_values', 'action_woocommerce_admin_order_item_values', 10, 3);
add_action('woocommerce_admin_order_item_headers', 'action_woocommerce_admin_order_item_headers', 10, 0);

//xoa bot field

add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');
function custom_override_checkout_fields($fields)
{
    unset($fields['billing']['billing_postcode']);

    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_country']);


    return $fields;
}

/*an noi dung khi dang nhap*/
add_shortcode('userview', 'vnkings_check_user_login');
function vnkings_check_user_login($atts, $content = null)
{
    if (is_user_logged_in()) {
        return '<p>' . $content . '</p>';
    } else {
        return "Bạn cần đăng nhập để đăng bán!";
    }
}
add_shortcode('nouserview', 'user_login');
function user_login($atts, $content = null)
{
    if (is_user_logged_in()) {
        return "";
    } else {
        return '<p>' . $content . '</p>';
    }
}

/*xoa bot field dia chi*/
add_filter('woocommerce_default_address_fields', 'misha_remove_fields');

function misha_remove_fields($fields)
{

    unset($fields['country']);

    unset($fields['state']);
    unset($fields['city']);
    unset($fields['postcode']);

    return $fields;
}
/*Xu*/
add_filter('woocommerce_currencies', 'add_my_currency');

function add_my_currency($currencies)
{
    $currencies['Xu'] = __(' Xu', 'woocommerce');
    return $currencies;
}
add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol', 10, 2);

function add_my_currency_symbol($currency_symbol, $currency)
{
    switch ($currency) {
        case 'Xu':
            $currency_symbol = ' Xu';
            break;
            //Change a currency symbol
    }
    return $currency_symbol;
}
/*Mora*/
add_filter('woocommerce_currencies', 'add_my_currency_2');

function add_my_currency_2($currencies)
{
    $currencies['Mora'] = __(' Mora', 'woocommerce');
    return $currencies;
}
add_filter('woocommerce_currency_symbol', 'add_my_currency_symbol_2', 10, 2);

function add_my_currency_symbol_2($currency_symbol, $currency)
{
    switch ($currency) {
        case 'Mora':
            $currency_symbol = ' Mora';
            break;
            //Change a currency symbol
    }
    return $currency_symbol;
}

/*noi dung aan khi chua dnhap*/

add_shortcode('userview', 'check_user_login');
function check_user_login($atts, $content = null)
{
    if (is_user_logged_in()) {
        return '<p>' . $content . '</p>';
    } else {
        return "Bạn chưa <a href='/tai-khoan/'>đăng nhập!</a>";
    }
}
/*tat danh gia*/

add_filter('woocommerce_product_tabs', 'woo_remove_product_tabs', 98);
function woo_remove_product_tabs($tabs)
{
    unset($tabs['reviews']); // Bỏ tab đánh giá
    unset($tabs['additional_information']); // Bỏ tab thông tin bổ xung
    return $tabs;
}

/* make child style working */
function my_theme_enqueue_styles()
{
    $parent_style = 'twentyseventeen-style';
    $child_style = 'twentyseventeen-child-style';
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style($child_style, get_stylesheet_uri());
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');



/*---------------------------------*/
function wallet_transactions_top_users_shortcode()
{
    $users = get_users(); // Get a list of all users
    $user_amounts = array(); // Array to hold the total amounts for each user
    $content = '';
    $count = 0; // Counter for the number of users displayed
    foreach ($users as $user) {
        // Get the user ID and username
        $user_id = $user->ID;
        $username = $user->user_login;

        // Get the wallet credit transactions for the user
        $transactions = get_wallet_transactions(array(
            'user_id' => $user_id,
            'type' => 'credit',
        ));

        // Calculate the total amount
        $total_amount = 0;
        foreach ($transactions as $transaction) {
            $total_amount += $transaction->amount;
        }

        // Add the user ID and total amount to the user_amounts array
        if (!empty($transactions)) {
            $user_amounts[$user_id] = $total_amount;
        }
    }

    // Sort the user_amounts array in descending order
    arsort($user_amounts);

    // Add the panel-heading
    $content .= '<div class="panel-heading">Top nạp thẻ</div>';

    // Display the total amount and username for each user in the sorted array
    foreach ($user_amounts as $user_id => $total_amount) {
        $username = get_userdata($user_id)->user_login;
        $content .= '<div class="woo-wallet-transactions-summary">';
        $content .= '<p>' . '<strong>' . esc_html($username) . '</strong></p>';
        $content .= '<h4>' . wc_price(apply_filters('woo_wallet_amount', $total_amount, $user_id), woo_wallet_wc_price_args($user_id)) . '</h4>';
        $content .= '</div>';
        $count++;

        if ($count >= 8) { // Limit the number of users displayed to 5
            break;
        }
    }

    // If no transactions were found for any user, display a message
    if (empty($content)) {
        $content = esc_html__('No transactions found for any user', 'woo-wallet');
    }

    return $content;
}
add_shortcode('wallet_credit_transactions_top_users', 'wallet_transactions_top_users_shortcode');


// Add custom fields to single product page and shop loop
add_action('woocommerce_single_product_summary', 'shoptimizer_custom_product_fields', 7);
add_action('woocommerce_shop_loop_item_title', 'shoptimizer_custom_product_fields', 15);

function shoptimizer_custom_product_fields()
{
    // Get custom field values for the current product
    $cap_ar = get_field('cap_ar');
    $khu_vuc = get_field('khu_vuc');
    $tuong_5 = get_field('tuong_5');
    $tuong = get_field('tuong');
    $vu_khi = get_field('vu-khi');

    // Display custom fields with labels (if they have values)
    if ($cap_ar) {
        echo '<div class="pro-box-info"><span class="font-semi">Cấp AR:</span> ' . esc_html($cap_ar) . '</div>';
    }

    if ($khu_vuc) {
        echo '<div class="pro-box-info"><span class="font-semi">Khu vực:</span> ' . esc_html($khu_vuc) . '</div>';
    }

    if ($tuong_5) {
        echo '<div class="pro-box-info"><span class="font-semi">Tướng 5*:</span> ' . esc_html($tuong_5) . '</div>';
    }

    if ($tuong) {
        $gallery = $tuong;
        if (is_array($gallery) && count($gallery) > 0) {
            echo '<div class="pro-box-info">';
            echo '<span class="font-semi">Tướng:</span> ';
            foreach ($gallery as $image) {
                echo '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '">';
            }
            echo '</div>';
        }
    }

    if ($vu_khi) {
        $gallery = $vu_khi;
        if (is_array($gallery) && count($gallery) > 0) {
            echo '<div class="pro-box-info">';
            echo '<span class="font-semi">Vũ khí:</span> ';
            foreach ($gallery as $image) {
                echo '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '">';
            }
            echo '</div>';
        }
    }
}



// display user email
function display_user_name_and_email( $atts ) {
    $user = wp_get_current_user();
    $output = '';
    if ( $user->ID ) {
        $output .= $user->user_email;
    }
    return $output;
}
add_shortcode( 'user_info', 'display_user_name_and_email' );

