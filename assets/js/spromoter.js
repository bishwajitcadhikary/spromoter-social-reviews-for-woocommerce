/*
document.addEventListener('DOMContentLoaded', function () {
    // Create & load widget js
    let widget = document.createElement('script');
    widget.type = 'text/javascript';
    widget.async = true;
    widget.src = `//staticwp.spromoter.com/${spromoter_settings.app_id}/widget.js`;
    document.body.appendChild(widget);

    // Review Lightbox
    lightbox.option({
        'showImageNumberLabel': false,
        'positionFromTop': 70
    })
});*/

// Load Assets

let scripts = {
    "js": [
        "https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js",
        "https://unpkg.com/filepond@^4/dist/filepond.js",
        "https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js",
        "https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js",
    ],
    "css": [
        "https://unpkg.com/filepond/dist/filepond.css",
    ]
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        let script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

function loadStyle(href) {
    return new Promise((resolve, reject) => {
        let link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.onload = resolve;
        link.onerror = reject;
        document.head.appendChild(link);
    });
}

// Load scripts
let scriptPromises = scripts.js.map(loadScript);

// Load styles
let stylePromises = scripts.css.map(loadStyle);

// Wait for all scripts and styles to be loaded
Promise.all([...scriptPromises, ...stylePromises])
    .then(() => {

        function sendRequest(endpoint, method = 'POST', body = null, headers = {}) {
            let baseURL = spromoter_settings.dev_mode ? '//api.spromoter.test/v1/' : 'https://api.spromoter.com/v1/';
            let appId = spromoter_settings.app_id;

            headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-App-ID': appId,
                ...headers
            }

            if (method === 'GET') {
                endpoint += '?' + new URLSearchParams(body).toString();
            } else if ((body instanceof FormData)) {
                delete headers['Content-Type'];
            } else {
                body = JSON.stringify(body);
            }

            return fetch(baseURL + endpoint, {
                method,
                body,
                headers
            }).then(function (response) {
                if (!response.ok) {
                    return Promise.reject(response);
                }

                return response.json();
            })
        }

        // Load product ratings
        let products = document.querySelectorAll('.spromoter-product-star-rating');
        if (products.length > 0) {
            let productIds = [];
            products.forEach(product => {
                // if product id is not exist then skip
                if (!product.dataset.productId) {
                    return;
                }

                productIds.push(product.dataset.productId);

                // Remove previous and next element sibling
                let previousElementSibling = product.previousElementSibling;
                let nextElementSibling = product.nextElementSibling;

                if (previousElementSibling) {
                    previousElementSibling.remove();
                }

                if (nextElementSibling) {
                    nextElementSibling.remove();
                }
            });

            let loadProductRatings = sendRequest('ratings', 'POST', {
                products: productIds
            });

            loadProductRatings.then(function (response) {
                for (let i = 0; i < products.length; i++) {
                    let productId = products[i].dataset.productId;
                    let product = response.data[productId];
                    let productRating = product?.rating;
                    let productTotalReviews = product?.count;

                    if (product) {
                        let stars = '';
                        for (let i = 0; i < 5; i++) {
                            if (productRating % 1 !== 0 && i === Math.floor(productRating)) {
                                stars += '<i class="bi bi-star-half"></i>';
                            } else if (i < productRating) {
                                stars += '<i class="bi bi-star-fill"></i>';
                            } else {
                                stars += '<i class="bi bi-star"></i>';
                            }
                        }

                        stars += `<span class="spromoter-product-star-rating-count">(${productTotalReviews})</span>`;

                        products[i].innerHTML = stars;
                    } else {
                        let stars = '';
                        for (let i = 0; i < 5; i++) {
                            stars += '<i class="bi bi-star"></i>';
                        }

                        stars += `<span class="spromoter-product-star-rating-count">(0)</span>`;

                        products[i].innerHTML = stars;
                    }
                }
            });
        }

        // Create review form
        let reviewFormWrapper = document.getElementById('spromoter-reviews-form');

        if (!reviewFormWrapper) {
            console.error("Please add woocommerce_product_tabs or woocommerce_after_single_product hook in your theme's file.");

            return false;
        }

        reviewFormWrapper.innerHTML = `
            <form class='spromoter-review-form' enctype="multipart/form-data">
                <div class='spromoter-rating-wrap'>
                    <div class='spromoter-rating-text'>How was your experience with this product?</div>
                    <div class='spromoter-rating'>
                        <input type='radio' name='spromoter_form_rating' id='spromoter_form_rating_one' value='1' checked />
                        <label for='spromoter_form_rating_one'><i class='bi bi-star-fill'></i></label>
                        <input type='radio' name='spromoter_form_rating' id='spromoter_form_rating_two' value='2' />
                        <label for='spromoter_form_rating_two'><i class='bi bi-star-fill'></i></label>
                        <input type='radio' name='spromoter_form_rating' id='spromoter_form_rating_three' value='3' />
                        <label for='spromoter_form_rating_three'><i class='bi bi-star-fill'></i></label>
                        <input type='radio' name='spromoter_form_rating' id='spromoter_form_rating_four' value='4' />
                        <label for='spromoter_form_rating_four'><i class='bi bi-star-fill'></i></label>
                        <input type='radio' name='spromoter_form_rating' id='spromoter_form_rating_five' value='5' />
                        <label for='spromoter_form_rating_five'><i class='bi bi-star-fill'></i></label>
                    </div>
                </div>
                <input type='text' name='spromoter_form_title' id='spromoter_form_title' class='spromoter-form-input' placeholder='Title' maxlength='255' required>
                <textarea name='spromoter_form_comment' id='spromoter_form_comment' class='spromoter-form-input' placeholder='Comment' maxlength='500' required></textarea>
                <!--<div class='spromoter-form-file'>
                    <label for='spromoter_form_files'><i class="bi bi-upload"></i> Upload files <span>(jpg,jpeg,png,mp4)</span></label>
                    <input name='spromoter_form_files' class='spromoter-form-file-input' type='file' id='spromoter_form_files' accept='.jpg,.jpeg,.png,.mp4' multiple>
                    <div id='spromoter_files_preview' class='spromoter-file-preview-container'></div>
                </div>-->
                <input type='text' id='spromoter_form_name' name='spromoter_form_name' class='spromoter-form-input' placeholder='Name' maxlength='255' required>
                <input type='email' id='spromoter_form_email' name='spromoter_form_email' class='spromoter-form-input' placeholder='Email' maxlength='255' required>
                <input type="file" name="filepond" id="filepond">
                <button type='submit' id='spromoter_submit_button' class='spromoter-button'>Submit</button>
            </form>`;

        // Initialize FilePond
        FilePond.registerPlugin(
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType,
        )
        let filepond = document.getElementById('filepond');
        const pond = FilePond.create(filepond, {
            allowMultiple: true,
            allowReorder: true,
            allowProcess: false,
            maxFileSize: '20MB',
            maxFiles: 20,
            acceptedFileTypes: ['image/png', 'image/jpg', 'image/jpeg', 'video/mp4', 'video/avi'],
            credits: false,
        });

        // Create review filter
        const spromoterReviewFilter = document.getElementById('spromoterReviewFilter');
        spromoterReviewFilter.innerHTML = `
            <h5 class="spromoter-review-filter-title">Search Reviews</h5>
            <form class="spromoter-filter-form" id="" method="POST">
                <div class="mb-3">
                    <label for="spromoter-filter-search" class="spromoter-form-label mb-2">Search</label>
                    <input type="search" class="spromoter-form-input" id="spromoter-filter-search" placeholder="Search Reviews"/>
                </div>
                <div class="mb-3">
                    <label for="spromoter-filter-rating" class="spromoter-form-label mb-2">Ratings</label>
                    <select id="spromoter-filter-rating" class="spromoter-form-input spromoter-form-select">
                        <option value="0">All</option>
                        <option value="5">5 star</option>
                        <option value="4">4 star</option>
                        <option value="3">3 star</option>
                        <option value="2">2 star</option>
                        <option value="1">1 star</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="spromoter-filter-order-by" class="spromoter-form-label mb-2">Date Published</label>
                    <select id="spromoter-filter-order-by" class="spromoter-form-input spromoter-form-select">
                        <option value="latest">Recently</option>
                        <option value="oldest">Older</option>
                    </select>
                </div>
            </form>`;

        // Open review form after click the 'write review button'
        document.getElementById('spromoter-write-review-button').addEventListener('click', function () {
            let spromoterReviewForm = document.querySelector('.spromoter-review-form');
            if (spromoterReviewForm.style.display === "none" || spromoterReviewForm.style.display === "") {
                spromoterReviewForm.style.display = "block";
            } else {
                spromoterReviewForm.style.display = "none";
            }
        });

        // Scroll down to review section
        const productReviewBox = document.querySelector('div.spromoter-product-review-box');

        if (productReviewBox) {
            productReviewBox.addEventListener('click', function () {
                const spromoterWidgetTabLink = document.querySelector('li.spromoter_main_widget_tab > a');

                if (spromoterWidgetTabLink) {
                    spromoterWidgetTabLink.click();

                    const reviewContainerSection = document.getElementById('spromoterReviewContainer');

                    if (reviewContainerSection) {
                        reviewContainerSection.scrollIntoView({behavior: 'smooth'});
                    }
                } else {
                    const reviewContainerSection = document.getElementById('spromoterReviewContainer');

                    if (reviewContainerSection) {
                        reviewContainerSection.scrollIntoView({behavior: 'smooth'});
                    }
                }
            });
        }

        let spromoterContainer = document.querySelector('.spromoter-container');
        if (!spromoterContainer) {
            console.error('Unable to load spromoter')
            return false;
        }

        let productId = spromoterContainer.dataset.spromoterProductId;
        let productTitle = spromoterContainer.dataset.spromoterProductTitle;
        let productImageUrl = spromoterContainer.dataset.spromoterProductImageUrl;
        let productUrl = spromoterContainer.dataset.spromoterProductUrl;
        let productDescription = spromoterContainer.dataset.spromoterProductDescription;
        let productLang = spromoterContainer.dataset.spromoterProductLang;
        let productSpecs = spromoterContainer.dataset.spromoterProductSpecs;

        // Review form submit
        let reviewForm = document.querySelector('.spromoter-review-form');
        reviewForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            let formData = new FormData(reviewForm);
            let formPostData = new FormData();
            formPostData.append('rating', formData.get('spromoter_form_rating'));
            formPostData.append('title', formData.get('spromoter_form_title'));
            formPostData.append('comment', formData.get('spromoter_form_comment'));
            formPostData.append('name', formData.get('spromoter_form_name'));
            formPostData.append('email', formData.get('spromoter_form_email'));
            for (let i = 0; i < pond.getFiles().length; i++) {
                formPostData.append('files[]', pond.getFile(i).file);
            }
            formPostData.append('product_id', productId);
            formPostData.append('product_title', productTitle);
            formPostData.append('product_image_url', productImageUrl);
            formPostData.append('product_url', productUrl);
            formPostData.append('product_description', productDescription);
            formPostData.append('product_lang', productLang);
            formPostData.append('product_specs', productSpecs);
            formPostData.append('collect_from', 'widget');
            formPostData.append('source', 'woocommerce');

            // Disable submit button
            let submitButton = document.getElementById("spromoter_submit_button");
            submitButton.disabled = true;
            submitButton.innerText = 'Submitting...';

            // Submit Review
            let submitReview = sendRequest('reviews/create', 'POST', formPostData);
            submitReview.then(function (data) {
                let review = createReviewData(data.data);

                pond.removeFiles();

                // If spromoter-no-review class exist then remove it
                let noReview = document.querySelector('.spromoter-no-review');
                if (noReview) {
                    noReview.remove();
                }

                // Prepend new review
                appendReview(review, true);

                // Reset form
                reviewForm.reset();

                // Hide form
                reviewForm.style.display = 'none';

                // Show success message
                let reviewButton = document.getElementById('spromoter-write-review-button');
                reviewButton.style.display = 'none';

                function reviewButtonHide() {
                    reviewButton.style.display = 'block';
                }

                setTimeout(reviewButtonHide, 7000);

                // Show success message
                let messageShowContainer = document.querySelector('.spromoter-total-review-show-wrap');
                let successMessage = document.createElement('div');
                successMessage.classList.add('spromoter-success-message');
                successMessage.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    <div class="success-message">Thank you for your review!</div>`;

                // Append success message
                messageShowContainer.appendChild(successMessage);

                function messageHide() {
                    successMessage.style.display = 'none';
                }

                setTimeout(messageHide, 7000);
            }).finally(function () {
                // Enable submit button
                submitButton.disabled = false;
                submitButton.innerText = 'Submit';
            });
        });

        // Load Reviews
        let page = 1;
        let loadReviews = sendRequest('reviews', 'POST', {
            product_id: productId,
            page: page,
            specs: productSpecs
        });

        loadReviews.then(function (response) {
            let reviews = response.data?.reviews.map(function (review) {
                return createReviewData(review);
            })

            createReviewContainers(reviews);
            createLoadMoreButton(response.data?.has_more);

            if (!!spromoter_settings.bottom_line) {
                createBottomLine(response.data?.average_rating ?? 0, response.data?.total_reviews ?? 0)
            }
        })

        // Filter Reviews
        let searchFilter = document.getElementById('spromoter-filter-search');
        let ratingFilter = document.getElementById('spromoter-filter-rating');
        let orderByFilter = document.getElementById('spromoter-filter-order-by');

        function filterReviews() {
            let data = {
                search: searchFilter.value,
                rating: ratingFilter.value,
                order_by: orderByFilter.value,
                product_id: productId,
            }

            let loadReviews = sendRequest('reviews', 'POST', data);

            loadReviews.then(function (response) {
                let reviews = response.data?.reviews.map(function (review) {
                    return createReviewData(review);
                })

                createReviewContainers(reviews, true);
                createLoadMoreButton(response.data?.has_more);
                page = 1;
            })
        }

        // Review filter
        searchFilter.addEventListener('input', filterReviews);
        ratingFilter.addEventListener('change', filterReviews);
        orderByFilter.addEventListener('change', filterReviews);

        function createLoadMoreButton(has_more) {
            let loadMoreBtn = document.getElementById('spromoter-load-more-btn');

            if (has_more) {
                if (loadMoreBtn) {
                    loadMoreBtn.style.display = "block";
                } else {
                    let spromoterActions = document.getElementById('spromoterActions');
                    let newLoadMoreBtn = document.createElement('button');
                    newLoadMoreBtn.classList.add('spromoter-load-more-button');
                    newLoadMoreBtn.id = 'spromoter-load-more-btn';
                    newLoadMoreBtn.type = 'button';
                    newLoadMoreBtn.innerText = 'Load more';
                    spromoterActions.append(newLoadMoreBtn);

                    newLoadMoreBtn.addEventListener('click', function () {
                        // Disable load more button
                        newLoadMoreBtn.disabled = true;
                        newLoadMoreBtn.innerText = 'Loading...';

                        let data = {
                            search: searchFilter.value,
                            rating: ratingFilter.value,
                            order_by: orderByFilter.value,
                            product_id: productId,
                            page: ++page
                        }

                        let loadReviews = sendRequest('reviews', 'POST', data);

                        loadReviews.then(function (response) {
                            let reviews = response.data?.reviews.map(function (review) {
                                return createReviewData(review);
                            })

                            createReviewContainers(reviews);
                            createLoadMoreButton(response.data?.has_more);
                        }).finally(function () {
                            // Enable load more button
                            newLoadMoreBtn.disabled = false;
                            newLoadMoreBtn.innerText = 'Load more';
                        });
                    })
                }
            } else {
                if (loadMoreBtn) {
                    loadMoreBtn.style.display = "none";
                }
            }
        }

        // Submit new review
        function createReviewContainers(reviewData, empty = false) {
            if (empty) {
                document.getElementById('spromoterReviews').innerHTML = '';
            }

            if (reviewData.length === 0) {
                document.getElementById('spromoterReviews').innerHTML = '<div class="spromoter-no-review">No reviews found.</div>';
                return;
            }

            reviewData.forEach((item, index) => {
                appendReview(item);
            });
        }

        function appendReview(item, isPrepend = false) {
            const spromoterReviews = document.getElementById('spromoterReviews');
            const reviewContainer = document.createElement('div');
            reviewContainer.classList.add('spromoter-single-review');
            const verified = `<span class="spromoter-verified"><i class="bi bi-patch-check-fill spromoter-verified-icon"></i> Verified</span>`;
            let attachments = '';

            if (item.attachments?.length > 0) {
                attachments += `<div class="spromoter-comment-media">`;
                for (let i = 0; i < item.attachments.length; i++) {
                    if (item.attachments[i].type.startsWith('image/')) {
                        attachments += `<a class="spromoter-single-media" href="${item.attachments[i].url}" data-lightbox="spromoter-gallery">
                            <img src="${item.attachments[i].url}" alt="image" />
                            <span class="totalNumberOfMedia">${item.attachments.length - 4}<span>+</span></span>
                        </a>`;
                    } else if (item.attachments[i].type.startsWith('video/')) {
                        attachments += `<a class="spromoter-single-media" href="${item.attachments[i].url}" data-lightbox="spromoter-gallery">
                            <video controls>
                                <source src="${item.attachments[i].url}" type="${item.attachments[i].type}">
                            </video>
                            <span class="totalNumberOfMedia">${item.attachments.length - 4}<span>+</span></span>
                        </a>`;
                    }
                }
                attachments += `</div>`;
            }

            reviewContainer.innerHTML = `
                <div class="spromoter-comment-avatar">
                    <img src="${item.avatar}" alt="${item.name}">
                </div>
                <div class="spromoter-comment-info">
                    <div class="spromoter-name-ratings">
                        <div>
                            <div class="spromoter-name">
                                ${item.name}
                            </div>
                            <div>
                                ${item.is_verified ? verified : ''}
                            </div>
                            <div class="spromoter-date">${item.date}</div>
                        </div>
                        <div class="spromoter-ratings">${item.ratings}</div>
                    </div>
                    <div class="spromoter-comments">${item.comment}</div>
                    ${attachments}

                </div>`;

            if (isPrepend) {
                spromoterReviews.prepend(reviewContainer);
            } else {
                spromoterReviews.appendChild(reviewContainer);
            }
        }

        function createReviewData(review) {
            let ratings = '';

            for (let i = 0; i < 5; i++) {
                if (i < review.rating) {
                    ratings += '<i class="bi bi-star-fill"></i>';
                } else {
                    ratings += '<i class="bi bi-star"></i>';
                }
            }

            return {
                id: review.id,
                date: review.created_at,
                avatar: review.avatar,
                name: review.name,
                ratings: ratings,
                comment: review.comment,
                attachments: review.attachments,
                is_verified: review.is_verified
            }
        }

        function createBottomLine(rating, totalReviews) {
            let bottomLine = document.querySelector('.spromoter-product-review-box');

            if (!bottomLine) {
                return;
            }

            // Add rating
            let stars = '';
            for (let i = 0; i < 5; i++) {
                if (rating % 1 !== 0 && i === Math.floor(rating)) {
                    stars += '<i class="bi bi-star-half"></i>';
                } else if (i < rating) {
                    stars += '<i class="bi bi-star-fill"></i>';
                } else {
                    stars += '<i class="bi bi-star"></i>';
                }
            }

            let bottomLineStars = document.createElement('div');
            bottomLineStars.classList.add('spromoter-product-review-box-rating');
            bottomLineStars.innerHTML = stars;
            bottomLine.appendChild(bottomLineStars);

            // Review summary
            const reviewAverage = document.getElementById('spromotertotalReviewsStars');
            reviewAverage.innerHTML = stars;

            document.getElementById('spromotertotalReviewsAverage').innerHTML = rating;

            // Showing total reviews in reviews section
            document.getElementById('spromotertotalReviews').innerHTML = totalReviews == 0 ? '(No review)' : totalReviews == 1 ? '(' + totalReviews + ' Review)' : '(' + totalReviews + ' Reviews)';

            // Add write review button
            let writeReviewButton = document.createElement('div');
            writeReviewButton.classList.add('spromoter-write-review');

            // Showing total reviews in product section
            writeReviewButton.innerText = totalReviews == 0 ? 'Write A Review' : totalReviews == 1 ? totalReviews + ' Review' : totalReviews + ' Reviews';

            //writeReviewButton.href = '#spromoterReviewContainer';
            bottomLine.appendChild(writeReviewButton);
        }

        // Review file upload

        const spromoterfileInput = document.getElementById('spromoter_form_files');

        if (spromoterfileInput) {
            const spromoter_files_preview = document.getElementById('spromoter_files_preview');

            spromoterfileInput.addEventListener('change', function () {
                const files = spromoterfileInput.files;

                let hasInvalidFile = false;

                for (const file of files) {
                    if (!isValidFileType(file.type)) {
                        spromoter_files_preview.innerText('Invalid file types! Choose image or video files.');
                        hasInvalidFile = true;
                        break;
                    }
                }

                if (!hasInvalidFile) {
                    for (const file of files) {
                        const reader = new FileReader();

                        reader.onload = function (e) {
                            const fileContainer = document.createElement('div');
                            fileContainer.classList.add('spromoter-single-file-uplaod');

                            if (file.type.startsWith('image/')) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.alt = file.name;
                                fileContainer.appendChild(img);
                            } else if (file.type.startsWith('video/')) {
                                const video = document.createElement('video');
                                video.src = e.target.result;
                                video.alt = file.name;
                                video.controls = true;
                                fileContainer.appendChild(video);
                            }

                            const fileRemoveBtn = document.createElement('button');
                            fileRemoveBtn.classList.add('spromoter-file-remove-btn');
                            fileRemoveBtn.innerHTML = '<i class="bi bi-x"></i>';
                            fileRemoveBtn.addEventListener('click', function () {
                                fileContainer.remove();
                            });

                            fileContainer.appendChild(fileRemoveBtn);
                            spromoter_files_preview.appendChild(fileContainer);
                        }
                        reader.readAsDataURL(file);
                    }
                } else {
                    spromoterfileInput.value = '';
                }
            });

            function isValidFileType(fileType) {
                return fileType.startsWith('image/') || fileType.startsWith('video/');
            }
        }
    })
    .catch(error => {
        console.error("Error loading scripts or styles:", error);
    });
