<?php
/**
 * Plugin Name: WP B2B Custom Content
 * Description: 外贸 B2B 官网自定义内容类型与字段（产品 Product / 应用场景 Application / 技术文章 Technical Article）及产品分类、SEO 字段
 * Version: 0.1.0
 * Author: B2B Website
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ========== 1. 注册自定义内容类型 ========== */
add_action( 'init', 'wpb2b_register_post_types' );
function wpb2b_register_post_types() {

	// ---- 产品 Product ----
	register_post_type( 'product', array(
		'labels' => array(
			'name'          => __( 'Products', 'wpb2b' ),
			'singular_name' => __( 'Product', 'wpb2b' ),
			'add_new'       => __( 'Add Product', 'wpb2b' ),
			'add_new_item'  => __( 'Add New Product', 'wpb2b' ),
			'edit_item'     => __( 'Edit Product', 'wpb2b' ),
			'all_items'     => __( 'All Products', 'wpb2b' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-products',
		'menu_position'=> 5,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		'rewrite'      => array( 'slug' => 'products', 'with_front' => false ),
		'show_in_rest' => true,
	) );

	// ---- 应用场景 Application ----
	register_post_type( 'application', array(
		'labels' => array(
			'name'          => __( 'Applications', 'wpb2b' ),
			'singular_name' => __( 'Application', 'wpb2b' ),
			'add_new'       => __( 'Add Application', 'wpb2b' ),
			'add_new_item'  => __( 'Add New Application', 'wpb2b' ),
			'edit_item'     => __( 'Edit Application', 'wpb2b' ),
			'all_items'     => __( 'All Applications', 'wpb2b' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-layout',
		'menu_position'=> 6,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		'rewrite'      => array( 'slug' => 'applications', 'with_front' => false ),
		'show_in_rest' => true,
	) );

	// ---- 技术文章 Technical Article ----
	register_post_type( 'technical_article', array(
		'labels' => array(
			'name'          => __( 'Technical Articles', 'wpb2b' ),
			'singular_name' => __( 'Technical Article', 'wpb2b' ),
			'add_new'       => __( 'Add Technical Article', 'wpb2b' ),
			'add_new_item'  => __( 'Add New Technical Article', 'wpb2b' ),
			'edit_item'     => __( 'Edit Technical Article', 'wpb2b' ),
			'all_items'     => __( 'All Technical Articles', 'wpb2b' ),
		),
		'public'       => true,
		'has_archive'  => true,
		'menu_icon'    => 'dashicons-welcome-learn-more',
		'menu_position'=> 7,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'date' ),
		'rewrite'      => array( 'slug' => 'technical-articles', 'with_front' => false ),
		'show_in_rest' => true,
	) );

	// ---- 产品分类（仅用于产品）----
	register_taxonomy( 'product_category', 'product', array(
		'labels' => array(
			'name'          => __( 'Product Categories', 'wpb2b' ),
			'singular_name' => __( 'Product Category', 'wpb2b' ),
			'all_items'     => __( 'All Product Categories', 'wpb2b' ),
		),
		'hierarchical' => true,
		'public'       => true,
		'rewrite'      => array( 'slug' => 'product-category', 'with_front' => false ),
		'show_in_rest' => true,
	) );
}

/* ========== 2. 产品自定义字段（型号 / 关键词标签） ========== */
add_action( 'add_meta_boxes', 'wpb2b_add_product_meta_boxes' );
function wpb2b_add_product_meta_boxes() {
	add_meta_box(
		'wpb2b_product_fields',
		__( 'Product Details (B2B)', 'wpb2b' ),
		'wpb2b_product_fields_html',
		'product',
		'normal',
		'high'
	);
}

function wpb2b_product_fields_html( $post ) {
	wp_nonce_field( 'wpb2b_product_fields_save', 'wpb2b_product_fields_nonce' );
	$model_no = get_post_meta( $post->ID, '_wpb2b_model_no', true );
	$keywords = get_post_meta( $post->ID, '_wpb2b_keywords', true );
	?>
	<p>
		<label for="wpb2b_model_no"><strong><?php _e( 'Model / Part Number', 'wpb2b' ); ?></strong></label><br>
		<input type="text" id="wpb2b_model_no" name="wpb2b_model_no" value="<?php echo esc_attr( $model_no ); ?>" style="width:100%;" placeholder="e.g. B2B-1000">
		<small><?php _e( 'Shown on product page and used for inquiry association.', 'wpb2b' ); ?></small>
	</p>
	<p>
		<label for="wpb2b_keywords"><strong><?php _e( 'Keyword Tags', 'wpb2b' ); ?></strong></label><br>
		<input type="text" id="wpb2b_keywords" name="wpb2b_keywords" value="<?php echo esc_attr( $keywords ); ?>" style="width:100%;" placeholder="keyword1, keyword2, keyword3">
		<small><?php _e( 'Comma separated. Used for internal search, category & SEO.', 'wpb2b' ); ?></small>
	</p>
	<?php
}

add_action( 'save_post_product', 'wpb2b_save_product_fields' );
function wpb2b_save_product_fields( $post_id ) {
	if ( ! isset( $_POST['wpb2b_product_fields_nonce'] ) || ! wp_verify_nonce( $_POST['wpb2b_product_fields_nonce'], 'wpb2b_product_fields_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = array( '_wpb2b_model_no', '_wpb2b_keywords' );
	foreach ( $fields as $f ) {
		if ( isset( $_POST[ str_replace( '_wpb2b_', 'wpb2b_', $f ) ] ) ) {
			update_post_meta( $post_id, $f, sanitize_text_field( $_POST[ str_replace( '_wpb2b_', 'wpb2b_', $f ) ] ) );
		}
	}
}

/* ========== 3. 通用 SEO 字段（标题 / 描述） ========== */
add_action( 'add_meta_boxes', 'wpb2b_add_seo_meta_box' );
function wpb2b_add_seo_meta_box() {
	$types = array( 'product', 'application', 'technical_article', 'post', 'page' );
	foreach ( $types as $t ) {
		add_meta_box( 'wpb2b_seo_fields', __( 'SEO (B2B)', 'wpb2b' ), 'wpb2b_seo_fields_html', $t, 'normal', 'default' );
	}
}

function wpb2b_seo_fields_html( $post ) {
	wp_nonce_field( 'wpb2b_seo_fields_save', 'wpb2b_seo_fields_nonce' );
	$title = get_post_meta( $post->ID, '_wpb2b_seo_title', true );
	$desc  = get_post_meta( $post->ID, '_wpb2b_seo_desc', true );
	?>
	<p>
		<label for="wpb2b_seo_title"><strong><?php _e( 'SEO Title', 'wpb2b' ); ?></strong></label><br>
		<input type="text" id="wpb2b_seo_title" name="wpb2b_seo_title" value="<?php echo esc_attr( $title ); ?>" style="width:100%;">
	</p>
	<p>
		<label for="wpb2b_seo_desc"><strong><?php _e( 'Meta Description', 'wpb2b' ); ?></strong></label><br>
		<textarea id="wpb2b_seo_desc" name="wpb2b_seo_desc" rows="3" style="width:100%;"><?php echo esc_textarea( $desc ); ?></textarea>
	</p>
	<?php
}

add_action( 'save_post', 'wpb2b_save_seo_fields' );
function wpb2b_save_seo_fields( $post_id ) {
	if ( ! isset( $_POST['wpb2b_seo_fields_nonce'] ) || ! wp_verify_nonce( $_POST['wpb2b_seo_fields_nonce'], 'wpb2b_seo_fields_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['wpb2b_seo_title'] ) ) {
		update_post_meta( $post_id, '_wpb2b_seo_title', sanitize_text_field( $_POST['wpb2b_seo_title'] ) );
	}
	if ( isset( $_POST['wpb2b_seo_desc'] ) ) {
		update_post_meta( $post_id, '_wpb2b_seo_desc', sanitize_textarea_field( $_POST['wpb2b_seo_desc'] ) );
	}
}

/* ========== 4. SEO meta 输出（由 RankMath 接管，已停用） ==========
 * RankMath SEO 插件负责 <title>/<meta description>/OG/Schema 等全部 SEO 输出。
 * 自研字段（_wpb2b_seo_title/_wpb2b_seo_desc）保留作编辑器备用，
 * 不再输出到前端，避免与 RankMath 产生重复标签。
 */

/* ========== 5. 产品详情页：CTA 按钮 + 产品询盘表单（CF7） ========== */
add_filter( 'the_content', 'wpb2b_product_content_inquiry' );
function wpb2b_product_content_inquiry( $content ) {
	if ( is_singular( 'product' ) && in_the_loop() && is_main_query() ) {
		$post   = get_queried_object();
		$model  = $post ? get_post_meta( $post->ID, '_wpb2b_model_no', true ) : '';
		$title  = $post ? $post->post_title : '';
		$form_id = 19; // Product Inquiry (CF7)

		$buttons = '<div class="wpb2b-product-cta" style="margin:28px 0 10px;">'
			. '<a class="wpb2b-btn wpb2b-btn-quote" href="#wpb2b-inquiry" style="display:inline-block;padding:12px 28px;background:#c8372c;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;margin-right:10px;">' . esc_html__( 'Request a Quote', 'wpb2b' ) . '</a>'
			. '<a class="wpb2b-btn wpb2b-btn-email" href="mailto:' . esc_attr( get_option( 'admin_email' ) ) . '?subject=' . rawurlencode( 'Inquiry: ' . $title ) . '" style="display:inline-block;padding:12px 28px;border:1px solid #333;color:#222;text-decoration:none;border-radius:4px;font-weight:600;">' . esc_html__( 'Email Us', 'wpb2b' ) . '</a>'
			. '</div>';

		$form = do_shortcode( '[contact-form-7 id="' . (int) $form_id . '" title="Product Inquiry"]' );

		$related = wpb2b_related_products_html( $post ? $post->ID : 0 );

		$content .= $buttons . '<div id="wpb2b-inquiry" class="wpb2b-inquiry-form">' . $form . '</div>' . $related;
	}
	return $content;
}

/* ========== 6. 首页动态板块短代码 ========== */
/* [wpb2b_products count="4"] 最新产品 / [wpb2b_categories] 产品分类
 * [wpb2b_applications count="3"] 应用场景 / [wpb2b_articles count="3"] 最新文章
 * 无内容时输出占位提示，不产出空块。
 */
add_shortcode( 'wpb2b_products', 'wpb2b_sc_products' );
function wpb2b_sc_products( $atts ) {
	$atts = shortcode_atts( array( 'count' => 4 ), $atts, 'wpb2b_products' );
	$q = new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => (int) $atts['count'],
		'no_found_rows'  => true,
	) );
	if ( ! $q->have_posts() ) {
		return '<p class="wpb2b-empty">' . esc_html__( 'Products coming soon. Please check back later.', 'wpb2b' ) . '</p>';
	}
	$html = '<div class="wpb2b-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:22px;">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$model = get_post_meta( get_the_ID(), '_wpb2b_model_no', true );
		$thumb = get_the_post_thumbnail( get_the_ID(), 'medium', array( 'style' => 'width:100%;height:150px;object-fit:cover;' ) );
		if ( ! $thumb ) {
			$thumb = '<div style="width:100%;height:150px;background:linear-gradient(135deg,#eef2f6,#dde5ec);display:flex;align-items:center;justify-content:center;color:#7a8896;font-size:13px;">' . esc_html__( 'Product image', 'wpb2b' ) . '</div>';
		}
		$html .= '<a href="' . esc_url( get_permalink() ) . '" style="text-decoration:none;color:inherit;border:1px solid #e5e9ee;border-radius:8px;overflow:hidden;display:block;">'
			. $thumb
			. '<div style="padding:14px 16px;"><div style="font-weight:600;font-size:15px;line-height:1.35;">' . esc_html( get_the_title() ) . '</div>'
			. ( $model ? '<div style="font-size:12px;color:#6b7785;margin-top:4px;">Model: ' . esc_html( $model ) . '</div>' : '' )
			. '<div style="font-size:13px;color:#c8372c;margin-top:8px;">' . esc_html__( 'View details →', 'wpb2b' ) . '</div></div></a>';
	}
	wp_reset_postdata();
	return $html . '</div>';
}

add_shortcode( 'wpb2b_categories', 'wpb2b_sc_categories' );
function wpb2b_sc_categories() {
	$terms = get_terms( array( 'taxonomy' => 'product_category', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '<p class="wpb2b-empty">' . esc_html__( 'Product categories coming soon.', 'wpb2b' ) . '</p>';
	}
	$html = '<div class="wpb2b-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;">';
	foreach ( $terms as $term ) {
		$count = $term->count ? sprintf( ' (%d)', $term->count ) : '';
		$html .= '<a href="' . esc_url( get_term_link( $term ) ) . '" style="display:block;padding:26px 20px;background:#f4f7f9;border-radius:8px;text-decoration:none;color:inherit;border:1px solid #e5e9ee;">'
			. '<div style="font-weight:700;font-size:16px;">' . esc_html( $term->name ) . $count . '</div>'
			. '<div style="font-size:13px;color:#c8372c;margin-top:10px;">' . esc_html__( 'View products →', 'wpb2b' ) . '</div></a>';
	}
	return $html . '</div>';
}

add_shortcode( 'wpb2b_applications', 'wpb2b_sc_applications' );
function wpb2b_sc_applications( $atts ) {
	$atts = shortcode_atts( array( 'count' => 3 ), $atts, 'wpb2b_applications' );
	$q = new WP_Query( array(
		'post_type'      => 'application',
		'posts_per_page' => (int) $atts['count'],
		'no_found_rows'  => true,
	) );
	if ( ! $q->have_posts() ) {
		return '<p class="wpb2b-empty">' . esc_html__( 'Application scenarios coming soon.', 'wpb2b' ) . '</p>';
	}
	$html = '<div class="wpb2b-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$html .= '<a href="' . esc_url( get_permalink() ) . '" style="display:block;padding:24px 20px;background:#fbf5f3;border:1px solid #f0ded8;border-radius:8px;text-decoration:none;color:inherit;">'
			. '<div style="font-weight:600;font-size:15px;">' . esc_html( get_the_title() ) . '</div>'
			. '<div style="font-size:13px;color:#6b7785;margin-top:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' . esc_html( wp_trim_words( get_the_excerpt(), 18 ) ) . '</div></a>';
	}
	wp_reset_postdata();
	return $html . '</div>';
}

add_shortcode( 'wpb2b_articles', 'wpb2b_sc_articles' );
function wpb2b_sc_articles( $atts ) {
	$atts = shortcode_atts( array( 'count' => 3 ), $atts, 'wpb2b_articles' );
	$q = new WP_Query( array(
		'post_type'      => 'technical_article',
		'posts_per_page' => (int) $atts['count'],
		'no_found_rows'  => true,
	) );
	if ( ! $q->have_posts() ) {
		return '<p class="wpb2b-empty">' . esc_html__( 'Technical articles coming soon.', 'wpb2b' ) . '</p>';
	}
	$html = '<div class="wpb2b-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$html .= '<a href="' . esc_url( get_permalink() ) . '" style="display:block;padding:22px 20px;border:1px solid #e5e9ee;border-radius:8px;text-decoration:none;color:inherit;">'
			. '<div style="font-weight:600;font-size:15px;">' . esc_html( get_the_title() ) . '</div>'
			. '<div style="font-size:12px;color:#8a96a3;margin-top:6px;">' . esc_html( get_the_date() ) . '</div>'
			. '<div style="font-size:13px;color:#6b7785;margin-top:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' . esc_html( wp_trim_words( get_the_excerpt(), 18 ) ) . '</div></a>';
	}
	wp_reset_postdata();
	return $html . '</div>';
}
/* ========== 7. 语言切换器（hover 下拉 + 点击双模式） ========== */
add_shortcode( 'wpb2b_lang_switcher', 'wpb2b_lang_switcher_sc' );
function wpb2b_lang_switcher_sc() {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '/';
	$lang    = 'EN';
	$current = '/';
	if ( preg_match( '#^/(fr|es)(/|$)#', (string) $path, $m ) ) {
		$lang    = strtoupper( $m[1] );
		$current = '/' . strtolower( $m[1] ) . '/';
	}

	$items = array(
		array( 'English', '/' ),
		array( 'Français', '/fr/' ),
		array( 'Español', '/es/' ),
	);

	$html = '<div class="wpb2b-lang-switcher" style="position:relative;display:inline-block;">'
		. '<span class="wpb2b-lang-current" style="cursor:pointer;display:inline-flex;align-items:center;gap:5px;font-weight:700;font-size:13px;padding:6px 10px;border:1px solid transparent;border-radius:4px;user-select:none;">' . esc_html( $lang ) . ' <span style="font-size:9px;">&#9662;</span></span>'
		. '<div class="wpb2b-lang-menu" style="display:none;position:absolute;right:0;top:100%;background:#fff;box-shadow:0 4px 14px rgba(0,0,0,.14);border:1px solid #e5e9ee;border-radius:6px;min-width:130px;padding:6px 0;z-index:9999;">';
	foreach ( $items as $it ) {
		$active = ( $it[1] === $current );
		$html  .= '<a href="' . esc_url( $it[1] ) . '" style="display:block;padding:9px 16px;text-decoration:none;font-size:13px;'
			. ( $active ? 'background:#f4f7f9;font-weight:700;color:#c8372c;' : 'color:#222;' ) . '">'
			. esc_html( $it[0] ) . ( $active ? ' ✓' : '' ) . '</a>';
	}
	$html .= '</div>'
		. '<style>'
		. '.wpb2b-lang-switcher:hover .wpb2b-lang-menu,.wpb2b-lang-switcher.wpb2b-open .wpb2b-lang-menu{display:block !important;}'
		. '.wpb2b-lang-switcher:hover .wpb2b-lang-current,.wpb2b-lang-switcher.wpb2b-open .wpb2b-lang-current{border-color:#e5e9ee;background:#f7f9fb;}'
		. '.wpb2b-lang-menu a:hover{background:#f4f7f9;}'
		. '</style>'
		. '<script>(function(){'
		. 'var toggle=function(sw){var open=sw.classList.contains("wpb2b-open");document.querySelectorAll(".wpb2b-lang-switcher").forEach(function(s){s.classList.remove("wpb2b-open");});if(!open){sw.classList.add("wpb2b-open");}};'
		. 'document.addEventListener("click",function(e){'
		. 'var hit=null;document.querySelectorAll(".wpb2b-lang-switcher").forEach(function(sw){if(sw.contains(e.target)){hit=sw;}});'
		. 'if(hit){toggle(hit);}else{document.querySelectorAll(".wpb2b-lang-switcher").forEach(function(s){s.classList.remove("wpb2b-open");});}'
		. '});'
		. 'document.addEventListener("keydown",function(e){if(e.key==="Escape"){document.querySelectorAll(".wpb2b-lang-switcher").forEach(function(s){s.classList.remove("wpb2b-open");});}});'
		. '})();</script>'
		. '</div>';

	return $html;
}

/* 相关产品：手动选择优先（_wpb2b_related），否则按分类自动推荐 */
function wpb2b_related_products_html( $post_id ) {
	$manual = get_post_meta( $post_id, '_wpb2b_related', true );
	$ids = array_filter( array_map( 'intval', (array) $manual ) );
	if ( empty( $ids ) ) {
		$terms = get_the_terms( $post_id, 'product_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$q = new WP_Query( array(
				'post_type'      => 'product',
				'posts_per_page' => 4,
				'post__not_in'   => array( $post_id ),
				'tax_query'      => array( array(
					'taxonomy' => 'product_category',
					'field'    => 'term_id',
					'terms'    => wp_list_pluck( $terms, 'term_id' ),
				) ),
				'orderby'        => 'rand',
			) );
			$ids = wp_list_pluck( $q->posts, 'ID' );
		}
	}
	if ( empty( $ids ) ) {
		return '';
	}
	$html = '<div class="wpb2b-related" style="margin-top:40px;"><h3>' . esc_html__( 'Related Products', 'wpb2b' ) . '</h3><div style="display:flex;flex-wrap:wrap;gap:16px;">';
	foreach ( $ids as $rid ) {
		$thumb = get_the_post_thumbnail( $rid, 'thumbnail', array( 'style' => 'width:120px;height:90px;object-fit:cover;' ) );
		$html .= '<div style="width:160px;"><a href="' . esc_url( get_permalink( $rid ) ) . '">' . $thumb . '<span style="display:block;font-size:13px;">' . esc_html( get_the_title( $rid ) ) . '</span></a></div>';
	}
	$html .= '</div></div>';
	return $html;
}

/* ========== 8. 安全加固（B2B 站不需要的功能全部关闭） ========== */
// 关闭 XML-RPC（减少暴力破解/攻击面；本站无 Jetpack/离线发布需求）
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', function ( $headers ) {
	if ( isset( $headers['X-Pingback'] ) ) {
		unset( $headers['X-Pingback'] );
	}
	return $headers;
} );
// 全站关闭评论与 Pingback（B2B 询盘站不需要评论区）
add_filter( 'comments_open', '__return_false' );
add_filter( 'pings_open', '__return_false' );
add_filter( 'comments_array', '__return_empty_array' );

/* ========== 9. 邮件发件人配置（开发环境：发件地址须为合法格式） ========== */
add_filter( 'wp_mail_from', function () {
	return 'no-reply@yourcompany.com'; // 上线时替换为真实域名邮箱
} );
add_filter( 'wp_mail_from_name', function () {
	return 'B2B Website';
} );
