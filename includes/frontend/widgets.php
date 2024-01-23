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
        $this->register_widgets();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function register_widgets()
    {
        if ($this->settings['review_show_in'] == 'tab') {
            add_action('woocommerce_product_tabs', [$this, 'show_main_widget_in_tab']);
        } elseif ($this->settings['review_show_in'] == 'footer') {
            add_action('woocommerce_after_single_product', [$this, 'show_main_widget_in_footer']);
        }

        if ($this->settings['show_bottom_line_widget']) {
            add_action('woocommerce_single_product_summary', [$this, 'show_bottom_line_widget'], 15);
        }
    }

    public function show_main_widget_in_tab($tabs)
    {
        global $product;
        if ($product->get_reviews_allowed()) {
            $tabs['spromoter_main_widget'] = [
                'title' => esc_html__('Reviews', 'spromoter-reviews'),
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
        wp_enqueue_script('spromoter-lightbox-scripts', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js');
        wp_enqueue_style('spromoter', SP_PLUGIN_URL . '/assets/css/spromoter.css', [], SP_PLUGIN_VERSION);
        wp_enqueue_script('spromoter', SP_PLUGIN_URL . '/assets/js/spromoter.js', [], SP_PLUGIN_VERSION, true);

        wp_localize_script('spromoter', 'spromoterSettings', array(
            'app_id' => $this->settings['app_id'],
            'bottom_line' => $this->settings['show_bottom_line_widget'],
        ));
    }
}

Widgets::instance();