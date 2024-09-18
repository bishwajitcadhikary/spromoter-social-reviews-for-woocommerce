<?php
namespace KinDigi\SPromoter\Frontend;

class Widget
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
    public static function instance(): ?Widget
    {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        $this->settings = spromoter_settings();
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

    /**
     * Register widgets
     *
     * @return void
     * @since 1.0.0
     */
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

    /**
     * Remove native review system
     *
     * @param $open
     * @param $post_id
     * @return false|mixed
     * @since 1.0.0
     */
    public function remove_native_review_system($open, $post_id )
    {
        if ( get_post_type( $post_id ) == 'product' ) {
            return false;
        }

        return $open;
    }

    /**
     * Show main widget in tab
     *
     * @param $tabs
     * @return mixed
     * @since 1.0.0
     */
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

    /**
     * Show main widget in footer
     *
     * @return void
     * @since 1.0.0
     */
    public function show_main_widget_in_footer()
    {
        global $product;
        if ($product->get_reviews_allowed()) {
            $this->render_main_widget_in_tab();
        }
    }

    /**
     * Render main widget in tab
     *
     * @return void
     * @since 1.0.0
     */
    public function render_main_widget_in_tab()
    {
        global $product;

        $product_data = spromoter_product_data($product);

        echo "<div 
			class='spromoter-container' id='spromoterReviewContainer'
			data-spromoter-app-id='" .esc_html($product_data['app_id']). "'
			data-spromoter-product-id='" .esc_html($product_data['id']). "'
			data-spromoter-product-title='" .esc_html($product_data['title']). "'
			data-spromoter-product-image-url='" .esc_html($product_data['image-url']). "'
			data-spromoter-product-url='" .esc_html($product_data['url']). "'
			data-spromoter-product-description='" .esc_html($product_data['description']). "'
			data-spromoter-product-lang='" .esc_html($product_data['lang']). "'
			data-spromoter-product-shop-domain='" .esc_html($product_data['shop_domain']). "'
			data-spromoter-product-app-id='" .esc_html($product_data['app_id']). "'
			data-spromoter-product-specs='" .esc_html(wp_json_encode($product_data['specs'])). "'
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

    /**
     * Show bottom line widget
     *
     * @return void
     * @since 1.0.0
     */
    public function show_bottom_line_widget()
    {
        global $product;
        if ( $product->get_reviews_allowed() ) {
            $product_data = spromoter_product_data($product);

            echo "<div class='spromoter-product-review-box' 
			data-product-id='".esc_html($product_data['id'])."'
			data-url='".esc_html($product_data['url'])."' 
			data-lang='".esc_html($product_data['lang'])."'></div>";
        }
    }

    /**
     * Enqueue scripts
     *
     * @return void
     * @since 1.0.0
     */
	public function enqueue_scripts()
	{
		wp_enqueue_style('spromoter-filepond', constant('SPROMOTER_PLUGIN_URL') . '/assets/css/filepond.min.css', [], constant('SPROMOTER_PLUGIN_VERSION'));
		wp_enqueue_style('spromoter-main', constant('SPROMOTER_PLUGIN_URL') . '/assets/css/spromoter.css', [], constant('SPROMOTER_PLUGIN_VERSION'));

		wp_enqueue_script('spromoter-lightbox', constant('SPROMOTER_PLUGIN_URL') . '/assets/js/lightbox.min.js', [], constant('SPROMOTER_PLUGIN_VERSION'), true);
		wp_enqueue_script('spromoter-filepond', constant('SPROMOTER_PLUGIN_URL') . '/assets/js/filepond.min.js', [], constant('SPROMOTER_PLUGIN_VERSION'), true);
		wp_enqueue_script('spromoter-filepond-validate-size', constant('SPROMOTER_PLUGIN_URL') . '/assets/js/filepond-plugin-file-validate-size.min.js', [], constant('SPROMOTER_PLUGIN_VERSION'), true);
		wp_enqueue_script('spromoter-filepond-validate-type', constant('SPROMOTER_PLUGIN_URL') . '/assets/js/filepond-plugin-file-validate-type.min.js', [], constant('SPROMOTER_PLUGIN_VERSION'), true);
		wp_enqueue_script('spromoter-main', constant('SPROMOTER_PLUGIN_URL') . '/assets/js/spromoter.js', ['jquery'], constant('SPROMOTER_PLUGIN_VERSION'), true);

		wp_localize_script('spromoter-main', 'spromoter_settings', array(
			'app_id' => $this->settings['app_id'],
			'bottom_line' => $this->settings['show_bottom_line_widget'],
			'api_url' => constant('SPROMOTER_API_URL'),
		));
	}
}

Widget::instance();