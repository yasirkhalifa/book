<?php
/**
 * Plugin Name: Book
 * Description: Book.
 * Author: Yasir Khalifa
 * Author URI: https://myasark.wordpress.com/
 * Version: 1.0
 * License: GPLv2
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
class yasarBook {
    function __construct() {        
      //  add_action('admin_enqueue_scripts', array($this, 'load_admin_style'));
        add_action( 'wp_enqueue_scripts',  array($this, 'load_admin_style'));
        add_action('init', array($this, 'codex_book_init'));
        add_action('add_meta_boxes', array($this, 'book_info'));
        add_action('save_post', array($this, 'book_meta_box_save'));
        add_filter('manage_edit-book_columns', array($this, 'my_edit_book_columns'));
        add_action('manage_book_posts_custom_column', array($this, 'my_manage_book_columns'), 10, 2);
        add_action('restrict_manage_posts', array($this, 'tsm_filter_post_type_by_taxonomy'));
        add_filter('parse_query', array($this, 'tsm_convert_id_to_term_in_query'));
        add_action('restrict_manage_posts', array($this, 'tsm_filter_post_type_by_taxonomy1'));
        add_filter('parse_query', array($this, 'tsm_convert_id_to_term_in_query1'));
        add_action('wp_ajax_nopriv_forBook_Topfilter',array($this,'forBook_Topfilter'));
        add_action('wp_ajax_forBook_Topfilter',array($this,'forBook_Topfilter'));
        add_shortcode('booksearch', array($this,'booksearch_list'));
    }
    function load_admin_style() {
        wp_register_style('bookrangcss', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css', array(), false,  'all');
      wp_enqueue_style('bookrangcss');
     // wp_register_script('bQuerymin', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', null, null, true);
    //    wp_enqueue_script('bQuerymin');
        wp_register_script('bjQueryui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', null, null, true);
       wp_enqueue_script('bjQueryui');
        wp_register_script('bookcustom', plugins_url('/assets/js/bookcustom.js', __FILE__), null, null, true);
        wp_enqueue_script('bookcustom');
        wp_localize_script( 'ajax-script', 'ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

    }
    function codex_book_init() {
        $labels = array('name' => _x('Books', 'post type general name'), 'singular_name' => _x('Book', 'post type singular name'), 'menu_name' => _x('Books', 'admin menu'), 'name_admin_bar' => _x('Book', 'add new on admin bar'), 'add_new' => _x('Add New', 'book'), 'add_new_item' => __('Add New Book'), 'new_item' => __('New Book'), 'edit_item' => __('Edit Book'), 'view_item' => __('View Book'), 'all_items' => __('All Books'), 'search_items' => __('Search Books'), 'parent_item_colon' => __('Parent Books:'), 'not_found' => __('No books found.'), 'not_found_in_trash' => __('No books found in Trash.'));
        $args = array('labels' => $labels, 'description' => __('Description.'), 'public' => true, 'publicly_queryable' => true, 'show_ui' => true, 'show_in_menu' => true, 'query_var' => true, 'rewrite' => array('slug' => 'book'), 'capability_type' => 'post', 'has_archive' => true, 'hierarchical' => false, 'menu_position' => 5, 'menu_icon' => 'dashicons-book', 'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'));
        register_post_type('book', $args);
        register_taxonomy("book_cat", array("book"), array("hierarchical" => true, "label" => "Book Categories", "singular_label" => "Book Category", "rewrite" => true));
        register_taxonomy("book_publication", array("book"), array("hierarchical" => true, "label" => "Book Publications", "singular_label" => "Book Publication", "rewrite" => true));
        flush_rewrite_rules();
    }
    function book_info() {
        add_meta_box('mybook-meta-box-id', 'Book Info', array($this,'book_info_data'), 'book', 'normal', 'high');
    }
    function book_info_data($post) {
        wp_nonce_field('my_book_box_nonce', 'bookmeta_box_nonce');?>
    <label for="subtitle">Sub title</label><input type="text" name="subtitle" value="<?php echo get_post_meta($post->ID, 'subtitle', true); ?>" />
    <label for="author">Author</label><input type="text" name="author" value="<?php echo get_post_meta($post->ID, 'author', true); ?>" />
    <label for="price">Price</label><input type="text" name="price" value="<?php echo get_post_meta($post->ID, 'price', true); ?>" />
    <?php
    }
    function book_meta_box_save($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['bookmeta_box_nonce']) || !wp_verify_nonce($_POST['bookmeta_box_nonce'], 'my_book_box_nonce')) return;
        update_post_meta($post_id, 'subtitle', $_POST['subtitle']);
        update_post_meta($post_id, 'author', $_POST['author']);
        update_post_meta($post_id, 'price', $_POST['price']);
    }
    function my_edit_book_columns($columns) {
        $columns = array('cb' => '<input type="checkbox" />', 'bimage' => "Book Image", 'title' => "Book", 'bprice' => "Price", 'bauthor' => "Author", 'publicer' => "Publicer", 'bcate' => "Category", 'date' => "Date",);
        return $columns;
    }
    function my_manage_book_columns($column, $post_id) {
        global $post;
        switch ($column) {
            case "bimage":
                $post_thumbnail_img = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'thumbnail');
                echo '<img src="' . $post_thumbnail_img[0] . '"  style="max-width:50px;"/>';
            break;
            case "bprice":
                echo get_post_meta($post->ID, 'price', true);
            break;
            case "bauthor":
                echo get_post_meta($post->ID, 'author', true);
            break;
            case "publicer":
                echo get_the_term_list($post->ID, 'book_publication', '', ',', '');
            break;
            case 'bcate':
                echo get_the_term_list($post->ID, 'book_cat', '', ',', '');
            break;
            case "date":
            break;
        }
    }
    function tsm_filter_post_type_by_taxonomy() {
        global $typenow;
        $post_type = 'book';
        $taxonomy = 'book_cat';
        if ($typenow == $post_type) {
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array('show_option_all' => __("Show All {$info_taxonomy->label}"), 'taxonomy' => $taxonomy, 'name' => $taxonomy, 'orderby' => 'name', 'selected' => $selected, 'show_count' => true, 'hide_empty' => true,));
        };
    }
    function tsm_convert_id_to_term_in_query($query) {
        global $pagenow;
        $post_type = 'book';
        $taxonomy = 'book_cat';
        $q_vars = & $query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }
    function tsm_filter_post_type_by_taxonomy1() {
        global $typenow;
        $post_type = 'book';
        $taxonomy = 'book_publication';
        if ($typenow == $post_type) {
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array('show_option_all' => __("Show All {$info_taxonomy->label}"), 'taxonomy' => $taxonomy, 'name' => $taxonomy, 'orderby' => 'name', 'selected' => $selected, 'show_count' => true, 'hide_empty' => true,));
        };
    }
    function tsm_convert_id_to_term_in_query1($query) {
        global $pagenow;
        $post_type = 'book';
        $taxonomy = 'book_publication';
        $q_vars = & $query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }
    function forBook_Topfilter() {
        $bTitle = $_REQUEST['bTitle'];
        $bAuthor = $_REQUEST['bAuthor'];
        $bookCate = $_REQUEST['bookCate'];
        $bookPubli = $_REQUEST['bookPubli'];
        $min_price = $_REQUEST['min_price'];
        $max_price = $_REQUEST['max_price'];
        $bookSE = array($bookCate, $bookPubli);
        if (!empty($bAuthor)) {
            $parmauthor[] = array('key' => 'author', 'value' => $bAuthor, 'compare' => 'LIKE');
            $parmBrand[] = $parmauthor;
        }
        if (!empty($min_price)) {
            $parmprice[] = array('key' => 'price', 'value' => array($min_price, $max_price), 'compare' => 'BETWEEN', 'type' => 'NUMERIC');
            $parmBrand[] = $parmprice;
        }
        $args = array('post_type' => 'book', 'posts_per_page' => - 1, 's' => $bTitle, 'tax_query' => array('relation' => 'AND', array('taxonomy' => 'book_cat', 'field' => 'term_id', 'terms' => $bookCate,), array('taxonomy' => 'book_publication', 'field' => 'term_id', 'terms' => $bookPubli,)), 'meta_query' => array('relation' => 'OR', $parmBrand),);
        $loop = new WP_Query($args);
        while ($loop->have_posts()):
            $loop->the_post();
            the_title();
            echo "<br/>";
        endwhile;
        wp_reset_postdata();
        die();
    }
    
    function booksearch_list() { ?>
    <label>Book Title</label><input type="text" name="btitle" id="btitle" />
    <label>Book Author</label><input type="text" name="bauthor" id="bauthor" />
    <label>Book Category</label><select name="bookcate" class="form-control">
						<option>Please Select</option>
						<?php
							 $terms = get_terms(book_cat);
                             foreach ( $terms as $term) { ?>
							<option class="glass-insulations" value="<?php echo $term->term_id;?>"  >
								<?php echo $term->name;?>
							</option>
						<?php } ?>
					</select>
      <label>Book Publisher</label>
                <select name="bookpubli" class="form-control">
						<option>Please Select</option>
						<?php
							 $pterms = get_terms(book_publication);
                             foreach ( $pterms as $pterm) { ?>
							<option class="glass-insulations" value="<?php echo $pterm->term_id;?>"  >
								<?php echo $pterm->name;?>
							</option>
						<?php } ?>
					</select>
          <label>Price Range</label>
               <div id="slider-range" class="price-filter-range" name="rangeInput"></div>
                <div style="margin:30px auto">
                    <input type="number" min=1 max="9900" oninput="validity.valid||(value='1');" id="min_price" class="price-range-field" value="" />
                    <input type="number" min=1 max="10000" oninput="validity.valid||(value='10000');" id="max_price" class="price-range-field" value="" />
                </div>
                <button class="bookser">Search</button>
                <div class="findbook"></div>
                
	<?php return ;
     }
}
$yasarBook = new yasarBook();


/*
add_action( 'restrict_manage_posts', 'my_restrict_manage_posts' );
function my_restrict_manage_posts() {

    // only display these taxonomy filters on desired custom post_type listings
    global $typenow;
    if ($typenow == 'product') {

        // create an array of taxonomy slugs you want to filter by - if you want to retrieve all taxonomies, could use get_taxonomies() to build the list
        $filters = array('product-category');

        foreach ($filters as $tax_slug) {
            // retrieve the taxonomy object
            $tax_obj = get_taxonomy($tax_slug);
            $tax_name = $tax_obj->labels->name;

            // output html for taxonomy dropdown filter
            echo "<select name='".strtolower($tax_slug)."' id='".strtolower($tax_slug)."' class='postform'>";
            echo "<option value=''>Show All $tax_name</option>";
            generate_taxonomy_options($tax_slug,0,0,(isset($_GET[strtolower($tax_slug)])? $_GET[strtolower($tax_slug)] : null));
            echo "</select>";
        }
    }
}

function generate_taxonomy_options($tax_slug, $parent = '', $level = 0,$selected = null) {
    $args = array('show_empty' => 1);
    if(!is_null($parent)) {
        $args = array('parent' => $parent);
    }
    $terms = get_terms($tax_slug,$args);
    $tab='';
    for($i=0;$i<$level;$i++){
        $tab.='--';
    }
    foreach ($terms as $term) {
        // output each select option line, check against the last $_GET to show the current option selected
        echo '<option value='. $term->slug, $selected == $term->slug ? ' selected="selected"' : '','>' .$tab. $term->name .' (' . $term->count .')</option>';
        generate_taxonomy_options($tax_slug, $term->term_id, $level+1,$selected);
    }

}
*/
