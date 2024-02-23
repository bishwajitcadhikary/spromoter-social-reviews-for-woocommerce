<?php
namespace WovoSoft\SPromoter\Frontend;

class Widgets
{
    /**
     * The single instance of the class
     *
     */
    protected static $_instance = null;

    protected $settings;

    /**
     * Main instance
     *
     */
    public static function instance(): ?Widgets
    {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        $this->settings = settings();
        add_action('template_redirect', [$this, 'register_widgets']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_filter('woocommerce_product_get_rating_html', [$this, 'product_get_rating_html'], 10, 3);
    }

    public function product_get_rating_html(  )
    {
        global $product;

        if ( ! $product->get_reviews_allowed() ) {
            return '';
        }

        return '<div class="spromoter-product-star-rating" data-product-id="'.$product->get_id().'"></div>';
    }

    public function register_widgets()
    {
        if (empty($this->settings['app_id']) || empty($this->settings['api_key'])) {
            return;
        }

        add_filter( 'comments_open', [$this, 'remove_native_review_system'], null, 2 );

        if ($this->settings['review_show_in'] == 'tab') {
            add_action('woocommerce_product_tabs', [$this, 'show_main_widget_in_tab']);
        } elseif ($this->settings['review_show_in'] == 'footer') {
            add_action('woocommerce_after_single_product', [$this, 'show_main_widget_in_footer']);
        }

        if ($this->settings['show_bottom_line_widget']) {
            add_action('woocommerce_single_product_summary', [$this, 'show_bottom_line_widget'], 15);
        }
    }

    public function remove_native_review_system( $open, $post_id )
    {
        if ( get_post_type( $post_id ) == 'product' ) {
            return false;
        }

        return $open;
    }

    public function show_main_widget_in_tab($tabs)
    {
        global $product;
        if ($product->get_reviews_allowed()) {
            $tabs['spromoter_main_widget'] = [
                'title' => esc_html__('Reviews', 'spromoter-social-reviews-for-woocommerce'),
                'priority' => 50,
                'callback' => [$this, 'render_main_widget_in_tab']
            ];
        }

        return $tabs;
    }

    public function show_main_widget_in_footer()
    {
        global $product;
        if ($product->get_reviews_allowed()) {
            $this->render_main_widget_in_tab();
        }
    }

    public function render_main_widget_in_tab()
    {
        global $product;

        $product_data = get_product_data($product);

        echo "<div 
			class='spromoter-container' id='spromoterReviewContainer'
			data-spromoter-app-id='" .$product_data['app_id']. "'
			data-spromoter-product-id='" .$product_data['id']. "'
			data-spromoter-product-title='" .$product_data['title']. "'
			data-spromoter-product-image-url='" .$product_data['image-url']. "'
			data-spromoter-product-url='" .$product_data['url']. "'
			data-spromoter-product-description='" .$product_data['description']. "'
			data-spromoter-product-lang='" .$product_data['lang']. "'
			data-spromoter-product-shop-domain='" .$product_data['shop_domain']. "'
			data-spromoter-product-app-id='" .$product_data['app_id']. "'
			data-spromoter-product-specs='" .json_encode($product_data['specs']). "'
			>
			<div class='spromoter-total-review-show-wrap'>
				<div class='powered-by-spromoter'>Powered by - Spromoter</div>
				<div id='spromotertotalReviewsAverage'></div>
				<div class='spromoter-total-reviews-star'>
					<div id='spromotertotalReviewsStars'></div>
					<span id='spromotertotalReviews'></span>
				</div>
				<button type='button' class='spromoter-button' id='spromoter-write-review-button'>Write A Review</button>
			</div>
			<div id='spromoter-reviews-form'></div>
		  <div id='spromoterReviewFilter'></div>
		  <div id='spromoterReviews'></div>
		  <div id='spromoterActions' class='spromoter-actions'></div>
		</div>";
    }

    public function show_bottom_line_widget()
    {
        global $product;
        if ( $product->get_reviews_allowed() ) {
            $product_data = get_product_data($product);

            echo "<div class='spromoter-product-review-box' 
			data-product-id='".$product_data['id']."'
			data-url='".$product_data['url']."' 
			data-lang='".$product_data['lang']."'></div>";
        }
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('spromoter', SP_PLUGIN_URL . '/assets/css/spromoter.css', [], SP_PLUGIN_VERSION);
        wp_enqueue_script('spromoter', SP_PLUGIN_URL . '/assets/js/spromoter.js', [], SP_PLUGIN_VERSION, true);

        wp_localize_script('spromoter', 'spromoter_settings', array(
            'app_id' => $this->settings['app_id'],
            'bottom_line' => $this->settings['show_bottom_line_widget'],
            'dev_mode' => defined('WP_SPROMOTER_DEV_MODE')
        ));
    }
}

Widgets::instance();