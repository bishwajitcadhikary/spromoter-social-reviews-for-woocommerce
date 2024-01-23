<?php
defined('ABSPATH') || exit;
?>
<div class="spromoter-container">
    <div class="spromoter-wrapper">
        <div class="spromoter-inner">
            <div class="spromoter-settings-card">
                <div class="card-body">
                    <!-- Logo -->
                    <a href="https://reviews.spromoter.com" class="spromoter-brand" target="_blank" rel="noopener">
                        <img src="<?php echo assets_path('images/logo.png'); ?>" alt="SPromoter">
                    </a>
                    <h2 class="mb-2">Get started with SPromoter</h2>
                    <p class="mb-4">Make your review management easy!</p>

<!--                    show errors-->


                    <form id="spromoterLoginForm" method="POST">
                        <?php wp_nonce_field( 'spromoter_login_form' ); ?>
                        <div class="mb-3">
                            <label for="app_id" class="spromoter-form-label mb-2">APP ID</label>
                            <input
                                    type="text"
                                    class="spromoter-form-input"
                                    id="app_id"
                                    name="app_id"
                                    placeholder="Enter app id"
                                    autofocus

                                    value=""
                            />

                            <?php settings_errors('app_id', true); ?>
                        </div>
                        <div class="mb-3">
                            <label for="api_key" class="spromoter-form-label mb-2">API Key</label>
                            <input
                                    type="text"
                                    class="spromoter-form-input"
                                    id="api_key"
                                    name="api_key"
                                    placeholder="Enter api key"
                                    value=""
                            />

                            <?php settings_errors('api_key'); ?>
                        </div>

                        <button class="spromoter-button mb-3 w-100">Verify</button>
                    </form>

                    <p class="text-center">
                        <span>New to SPromoter?</span>
                        <a href="<?php echo esc_html(admin_url('admin.php?page=spromoter&view=register')) ?>" class="spromoter-button-link">
                            Register Here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>


