<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<div class="spromoter-container">
    <div class="spromoter-bg-shape">
        <img src="<?php echo esc_url( spromoter_assets_path( 'images/shape.png' ) ); ?>" alt="SPromoter">
    </div>
    <div class="spromoter-wrapper">
        <div class="spromoter-inner">
            <div class="spromoter-settings-card">
                <div class="card-body">

                    <div class="spromoter-settings-messages">
						<?php settings_errors( 'spromoter_messages' ); ?>
                    </div>

                    <div class="spromoter-settings-card-header mb-4">
                        <div>
                            <!-- Logo -->
                            <a href="#" class="spromoter-brand">
                                <img src="<?php echo esc_url( spromoter_assets_path( 'images/logo.png' ) ); ?>"
                                     alt="SPromoter">
                            </a>
                            <h2>
								<?php esc_html_e( 'Configure your settings!', 'spromoter-social-reviews-for-woocommerce' ); ?>
                            </h2>
                        </div>
                        <div>
							<?php if ( spromoter_is_connected() ) { ?>
                                <div class="spromoter-connected">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
                                         class="bi bi-check-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                        <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                    </svg>
									<?php esc_html_e( 'Connected', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </div>
							<?php } else { ?>
                                <div class="spromoter-disconnected">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor"
                                         class="bi bi-x-circle" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                                    </svg>
									<?php esc_html_e( 'Not Connected', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </div>
							<?php } ?>
                        </div>
                    </div>

                    <form id="spromoterSettingsForm" method="POST">
						<?php wp_nonce_field(); ?>
                        <input type="hidden" name="type" value="update">

                        <div class="mb-3">
                            <label for="app_id" class="spromoter-form-label mb-2">
								<?php esc_html_e( 'App ID', 'spromoter-social-reviews-for-woocommerce' ); ?>
                            </label>
                            <input
                                    type="text"
                                    class="spromoter-form-input"
                                    id="app_id"
                                    name="app_id"
                                    placeholder="Enter app id"
                                    autofocus
                                    required
                                    value="<?php echo esc_html( spromoter_settings( 'app_id' ) ) ?>"
                            />
                        </div>

                        <div class="mb-3">
                            <label for="api_key" class="spromoter-form-label mb-2">
								<?php esc_html_e( 'API Key', 'spromoter-social-reviews-for-woocommerce' ); ?>
                            </label>
                            <input
                                    type="text"
                                    class="spromoter-form-input"
                                    id="api_key"
                                    name="api_key"
                                    placeholder="Enter api key"
                                    required
                                    value="<?php echo esc_html( spromoter_settings( 'api_key' ) ) ?>"
                            />
                        </div>

                        <div class="mb-3">
                            <label for="order_status" class="spromoter-form-label mb-2">Order Status</label>
                            <select name="order_status" id="order_status"
                                    class="spromoter-form-input spromoter-form-select">
                                <option value="completed" <?php echo selected( 'completed', esc_html( spromoter_settings( 'order_status' ) ), false ) ?>>
									<?php esc_html_e( 'Completed', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </option>
                                <option value="processing" <?php echo selected( 'processing', esc_html( spromoter_settings( 'order_status' ) ), false ) ?>>
									<?php esc_html_e( 'Processing', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </option>
                                <option value="on-hold" <?php echo selected( 'on-hold', esc_html( spromoter_settings( 'order_status' ) ), false ) ?>>
									<?php esc_html_e( 'On Hold', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </option>
                                <option value="canceled" <?php echo selected( 'canceled', esc_html( spromoter_settings( 'order_status' ) ), false ) ?>>
									<?php esc_html_e( 'Canceled', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </option>
                                <option value="refunded" <?php echo selected( 'refunded', esc_html( spromoter_settings( 'order_status' ) ), false ) ?>>
									<?php esc_html_e( 'Refunded', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </option>
                                <option value="failed" <?php echo selected( 'failed', esc_html( spromoter_settings( 'order_status' ) ), false ) ?>>
									<?php esc_html_e( 'Failed', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="review_show_in"
                                   class="spromoter-form-label mb-2"><?php esc_html_e( 'Review Show In', 'spromoter-social-reviews-for-woocommerce' ); ?></label>
                            <select name="review_show_in" id="review_show_in"
                                    class="spromoter-form-input spromoter-form-select">
                                <option value="tab" <?php echo selected( 'tab', esc_html( spromoter_settings( 'review_show_in' ) ), false ) ?>>
									<?php esc_html_e( 'Tab', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </option>
                                <option value="footer" <?php echo selected( 'footer', esc_html( spromoter_settings( 'review_show_in' ) ), false ) ?>>
									<?php esc_html_e( 'Footer', 'spromoter-social-reviews-for-woocommerce' ); ?>
                                </option>
                            </select>
                        </div>

                        <div class="spromoter-form-check ps-0 mb-3">
                            <input
                                    type="checkbox"
                                    name="disable_native_review_system"
                                    id="disable_native_review_system"
                                    class="spromoter-form-check-input"
                                    value="1"
								<?php echo checked( esc_html( spromoter_settings( 'disable_native_review_system' ) ) ) ?>
                            >
                            <label class="spromoter-form-check-label" for="disable_native_review_system">
								<?php esc_html_e( 'Disable native review system', 'spromoter-social-reviews-for-woocommerce' ); ?>
                            </label>
                        </div>

                        <div class="spromoter-form-check ps-0 mb-4">
                            <input
                                    type="checkbox"
                                    name="show_bottom_line_widget"
                                    id="show_bottom_line_widget"
                                    class="spromoter-form-check-input"
                                    value="1"
								<?php echo checked( esc_html( spromoter_settings( 'show_bottom_line_widget' ) ) ) ?>
                            >
                            <label class="spromoter-form-check-label" for="show_bottom_line_widget">
								<?php esc_html_e( 'Enable button line in product page', 'spromoter-social-reviews-for-woocommerce' ); ?>
                            </label>
                        </div>
                    </form>

                    <div class="spromoter-button-group">
                        <button type="submit" class="spromoter-button" form="spromoterSettingsForm">
							<?php esc_html_e( 'Save Changes', 'spromoter-social-reviews-for-woocommerce' ); ?>
                        </button>
                    </div>

                    <form method="POST" id="spromoterSubmitPastOrderForm">
						<?php wp_nonce_field(); ?>
                        <input type="hidden" name="submit_past_orders" value="true">
                    </form>

                    <div class="spromoter-admin-footer">
                        <button type="submit" class="spromoter-submit-past-order-button"
                                form="spromoterSubmitPastOrderForm">
                            <i class="bi bi-bag-check"></i>
							<?php esc_html_e( 'Submit Previous Orders', 'spromoter-social-reviews-for-woocommerce' ); ?>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
