<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<div class="spromoter-container">
    <div class="spromoter-wrapper">
        <div class="spromoter-inner">
            <div class="spromoter-settings-card">
                <div class="card-body">
                    <!-- Logo -->
                    <a href="https://reviews.spromoter.com" class="spromoter-brand" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url( spromoter_assets_path( 'images/logo.png' ) ); ?>" alt="SPromoter">
                    </a>
                    <h2 class="mb-2">Get started with SPromoter</h2>
                    <p class="mb-4">Make your review management easy!</p>

                    <form id="spromoterLoginForm" method="POST">
						<?php wp_nonce_field(); ?>
                        <div class="mb-3">
                            <label for="app_id" class="spromoter-form-label mb-2">App ID</label>
                            <input
                                    type="text"
                                    class="spromoter-form-input"
                                    id="app_id"
                                    name="app_id"
                                    placeholder="Enter your app id"
                                    autofocus
                                    value="<?php echo esc_attr( spromoter_post_unslash( 'app_id' ) ) ?>"
                                    required
                                    minlength="36"
                            />

							<?php settings_errors( 'app_id', true ); ?>
                        </div>
                        <div class="mb-3">
                            <label for="api_key" class="spromoter-form-label mb-2">API Key</label>
                            <input
                                    type="text"
                                    class="spromoter-form-input"
                                    id="api_key"
                                    name="api_key"
                                    placeholder="Enter your api key"
                                    value="<?php echo esc_attr( spromoter_post_unslash( 'api_key' ) ); ?>"
                                    required
                            />

							<?php settings_errors( 'api_key' ); ?>
                        </div>

                        <button class="spromoter-button mb-3 w-100">Verify</button>
                    </form>

                    <p class="text-center">
                        <span>New to SPromoter?</span>
                        <a href="<?php echo esc_html( admin_url( 'admin.php?page=spromoter&view=register' ) ) ?>"
                           class="spromoter-button-link">
                            Register Here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>


