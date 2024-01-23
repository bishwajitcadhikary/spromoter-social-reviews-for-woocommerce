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

                    <form id="spromoterRegisterForm" method="POST">
                        <?php wp_nonce_field( 'spromoter_register_form' ); ?>

                        <div class="mb-3">
                            <label for="first_name" class="spromoter-form-label mb-2">First Name</label>
                            <input
                                    type="text"
                                    class="spromoter-form-input"
                                    id="first_name"
                                    name="first_name"
                                    placeholder="Enter your first name"
                                    autofocus
                                    required
                                    value=""
                            />
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="spromoter-form-label mb-2">Last Name</label>
                            <input
                                    type="text"
                                    class="spromoter-form-input"
                                    id="last_name"
                                    name="last_name"
                                    placeholder="Enter your last name"
                                    required
                                    value=""
                            />
                        </div>

                        <div class="mb-3">
                            <label for="email" class="spromoter-form-label mb-2">Email</label>
                            <input type="text" class="spromoter-form-input" id="email" name="email"
                                   placeholder="Enter your email" value="" required/>
                        </div>

                        <div class="form-password-toggle mb-3">
                            <label class="spromoter-form-label mb-2" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input
                                        type="password"
                                        id="password"
                                        class="spromoter-form-input"
                                        name="password"
                                        placeholder=""
                                        aria-describedby="password"
                                        required
                                        min="8"
                                />
                            </div>
                        </div>

                        <div class="form-password-toggle mb-4">
                            <label class="spromoter-form-label mb-2" for="password_confirmation">Confirm
                                Password</label>
                            <div class="input-group input-group-merge">
                                <input
                                        type="password"
                                        id="password_confirmation"
                                        class="spromoter-form-input"
                                        name="password_confirmation"
                                        placeholder=""
                                        aria-describedby="password_confirmation"
                                        required
                                        min="8"
                                />
                            </div>
                        </div>

                        <button class="spromoter-button mb-3 w-100">Sign up</button>
                    </form>

                    <p class="text-center">
                        <span>Already have an account?</span>
                        <a class="spromoter-button-link" href="<?php echo admin_url('admin.php?page=spromoter&view=login'); ?>">
                            Configure Here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>


