<?php

namespace WovoSoft\SPromoter\Admin;

use Exception;

class Export
{
    const ENCLOSURE = '"';
    const DELIMITER = ',';

    /**
     * @param $file
     * @return string|null
     */
    public function download_reviews($file): ?string
    {
        $file_absolute_path = plugin_dir_path(__FILE__) . DIRECTORY_SEPARATOR . $file;
        try {
            if (file_exists($file_absolute_path)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . ($file));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_absolute_path));
                ob_clean();
                flush();
                readfile($file_absolute_path);
                //delete the file after it was downloaded.
                wp_delete_file($file_absolute_path);
                return null;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $e->getMessage();
        }

        return null;
    }

    /**
     * Exports all reviews to a csv file.
     *
     * @return array
     * @since 1.0.0
     */
    public function export_reviews(): array
    {
        $fp = null;
        try {
            $fileName = 'review_export_' . gmdate("Ymd_His") . '.csv';
            $fp = fopen(plugin_dir_path(__FILE__) . '/' . $fileName, 'w');
            $this->writeHeadRow($fp);

            # Load all reviews with their votes
            $allReviews = $this->get_all_reviews();

            foreach ($allReviews as $fullReview) {
                $this->write_review($fullReview, $fp);
            }
            fclose($fp);
            return array($fileName, null);
        } catch (Exception $e) {
            //delete the file if it was created.
            if (isset($fp)) {
                fclose($fp);
                wp_delete_file(plugin_dir_path(__FILE__) . '/' . $fileName);
            }

            error_log($e->getMessage());
            return array(null, $e->getMessage());
        }
    }

    /**
     * Write the head row to the csv file.
     *
     * @param $path
     * @since 1.0.0
     */
    protected function writeHeadRow($path)
    {
        fputcsv($path, $this->get_headers(), self::DELIMITER, self::ENCLOSURE);
    }

    /**
     * Write a review to the csv file.
     *
     * @param array $reviews
     * @param resource $path
     * @since 1.0.0
     */
    protected function write_review(array $reviews, $path)
    {
        fputcsv($path, $reviews, self::DELIMITER, self::ENCLOSURE);
    }

    /**
     * Get the headers for the csv file.
     *
     * @return string[]
     * @since 1.0.0
     */
    protected function get_headers(): array
    {
        return [
            'Review ID',
            'Review Title',
            'Review Body',
            'Review Rating',
            'Review Status',
            'Review Creation Date',
            'Verified Purchase',
            'Reviewer Name',
            'Reviewer Email',
            'Product ID',
            'Product Name',
            'Product Specs',
            'Product URL',
            'Product Image URL',
            'Review Images',
        ];
    }

    /**
     * Get all reviews from the database.
     *
     * @return array
     * @since 1.0.0
     */
    protected function get_all_reviews(): array
    {
        global $wpdb;
        $results = $wpdb->get_results("
                SELECT
                    `" . $wpdb->prefix . "comments`.`comment_ID` AS `review_id`,
                    `" . $wpdb->prefix . "comments`.`comment_approved` AS `review_status`,
                    comment_post_ID AS product_id, 
                    comment_author AS display_name, 
                    comment_date AS date,
                    comment_author_email AS user_email, 
                    comment_content AS review_content, 
                    meta_value AS review_score,
                    post_content AS product_description,
                    post_title AS product_title,
                    user_id,
                    CASE WHEN oi.order_item_id IS NOT NULL THEN 1 ELSE 0 END AS verified_purchase
                FROM `" . $wpdb->prefix . "comments` 
                INNER JOIN `" . $wpdb->prefix . "posts` ON `" . $wpdb->prefix . "posts`.`ID` = `" . $wpdb->prefix . "comments`.`comment_post_ID` 
                INNER JOIN `" . $wpdb->prefix . "commentmeta` ON `" . $wpdb->prefix . "commentmeta`.`comment_id` = `" . $wpdb->prefix . "comments`.`comment_ID`
                LEFT JOIN `" . $wpdb->prefix . "woocommerce_order_items` AS oi ON oi.order_item_id = `" . $wpdb->prefix . "comments`.`comment_post_ID`
                WHERE `post_type` = 'product' AND meta_key='rating'
        ");

        $all_reviews = [];

        foreach ($results as $value) {
            $current_review = [];
            $review_content = $this->clean_content($value->review_content);
            $current_review['Review ID'] = $value->review_id;
            $current_review['Review Title'] = $this->get_first_words($review_content, 5);
            $current_review['Review Body'] = $review_content;
            $current_review['Review Rating'] = $value->review_score;
            $current_review['Review Status'] = $value->review_status ? 'Published' : 'Pending';
            $current_review['Review Creation Date'] = $value->date;
            $current_review['Verified Purchase'] = $value->verified_purchase;
            $current_review['Reviewer Name'] = $this->clean_content($value->display_name);
            $current_review['Reviewer Email'] = $value->user_email;
            $current_review['Product ID'] = $value->product_id;
            $current_review['Product Name'] = $this->clean_content($value->product_title);
            $current_review['Product Specs'] = wp_json_encode(get_product_specs(wc_get_product($value->product_id)));
            $current_review['Product URL'] = get_permalink($value->product_id);
            $current_review['Product Image URL'] = get_product_image_url($value->product_id);
            $current_review['Review Images'] = '';

            $all_reviews[] = $current_review;
        }
        return $all_reviews;
    }

    /**
     * Clean the content of the review.
     *
     * @param $content
     * @return string
     * @since 1.0.0
     */
    private function clean_content($content): string
    {
        $content = preg_replace('/<br(\s*)?\/?>/i', "\n", $content);
        return html_entity_decode(wp_strip_all_tags(strip_shortcodes($content)));
    }

    /**
     * Get the first 5 words of the review.
     *
     * @param string $content
     * @param int $count
     * @return string
     * @since 1.0.0
     */
    private function get_first_words(string $content = '', int $count = 6): string
    {
        $words = str_word_count($content, 1);
        if (count($words) > $count) {
            return join(" ", array_slice($words, 0, $count));
        } else {
            return join(" ", $words);
        }
    }
}