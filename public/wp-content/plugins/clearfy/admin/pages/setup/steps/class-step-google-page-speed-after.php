<?php

namespace WBCR\Clearfy\Pages;

/**
 * Class Step_Google_Page_Speed_After
 *
 * This class represents a step in the Clearfy plugin setup process that evaluates
 * a website's performance using Google Page Speed Insights. It provides a visual
 * representation of the site's performance for desktop and mobile and displays
 * key metrics such as First Contentful Paint, Speed Index, and Time to Interactive.
 *
 * Inherits methods and properties from the Step_Custom class within the
 * WBCR\Factory_Templates_134 namespace.
 *
 * Properties:
 * - $prev_id: Identifier for the previous step in the setup process.
 * - $id: Identifier for the current step in the setup process.
 * - $next_id: Identifier for the next step in the setup process.
 *
 * Methods:
 * - get_title(): Returns the title of the current step as a string.
 * - html(): Outputs the HTML and JavaScript for rendering the Google Page Speed
 *   Insights report and embeds the Clearfy plugin optimization information.
 *
 * @author  Alex Kovalev <alex.kovalevv@gmail.com> <Telegram:@alex_kovalevv>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Google_Page_Speed_After extends \WBCR\Factory_Templates_134\Pages\Step_Custom
{

    protected $prev_id = 'step5';
    protected $id = 'step6';
    protected $next_id = 'step7';

    /**
     * Retrieves the title.
     *
     * @return string The title of the site.
     */
    public function get_title(): string
    {
        return __("Site test #2", 'clearfy');
    }

    /**
     * Outputs the HTML content for displaying Google Page Speed statistics and analyses.
     *
     * The method integrates with the Google PageSpeed Insights API to analyze a website and display
     * the results, including scores for desktop and mobile versions, and key performance metrics such
     * as Speed Index and Time to Interactive. It also provides a link to view the complete results
     * on the official Google PageSpeed Insights platform.
     *
     * Additionally, this method manages the rendering of UI components for displaying scores,
     * statistics, and preloader icons.
     *
     * @return void
     */
    public function html(): void
    {
        ?>
        <script>
            jQuery(document).ready(function ($) {
                wclearfy_fetch_google_pagespeed_audit("<?php echo wp_create_nonce('fetch_google_page_speed_audit') ?>", true);
            });
        </script>
        <div class="w-factory-templates-134-setup__inner-wrap">
            <h3><?php _e('Google Page Speed', 'clearfy'); ?></h3>
            <p style="text-align: left;">
                <?php _e('We analyzed your site on the Google Page Speed service. You can see the test results below. Our plugin is to improve the score of your site on Google Page Speed. Memorize the results to make a comparison after optimization by the Clearfy plugin.', 'clearfy'); ?>
            </p>
            <div class="wclearfy-gogle-page-speed-audit__errors">
                <?php _e('Memorize the results to make a comparison after optimization by the Clearfy plugin.', 'clearfy'); ?>
            </div>
            <div class="wclearfy-gogle-page-speed-audit__preloader"></div>
            <div class="wclearfy-gogle-page-speed-audit" style="display: none;">
                <div class="wclearfy-score">
                    <!-- Desktop -->
                    <div class="wclearfy-desktop-score">
                        <h3><?php _e('Desktop score', 'clearfy'); ?></h3>
                        <div class="wclearfy-desktop-score__circle-wrap">
                            <div id="wclearfy-desktop-score__circle" class="wclearfy-score-circle"></div>
                        </div>
                    </div>

                    <!-- Mobile -->
                    <div class="wclearfy-mobile-score">
                        <h3><?php _e('Mobile score', 'clearfy'); ?></h3>
                        <div class="wclearfy-mobile-score__circle-wrap">
                            <div id="wclearfy-mobile-score__circle" class="wclearfy-score-circle"></div>
                        </div>
                    </div>
                </div>


                <!-- Statistics -->
                <div class="wclearfy-statistic">
                    <div class="wclearfy-statistic__line">
                        <span><?php _e('First Contentful Paint', 'clearfy'); ?></span>
                        <div class="wclearfy-statistic__results">
                            <span id="wclearfy-statistic__desktop-first-contentful-paint">??&nbsp;s</span>&nbsp;/&nbsp;<span
                                    id="wclearfy-statistic__mobile-first-contentful-paint">??&nbsp;s</span>
                        </div>
                    </div>
                    <div class="wclearfy-statistic__line">
                        <span><?php _e('Speed Index', 'clearfy'); ?></span>
                        <div class="wclearfy-statistic__results">
                            <span id="wclearfy-statistic__desktop-speed-index">??&nbsp;s</span>&nbsp;/&nbsp;<span
                                    id="wclearfy-statistic__mobile-speed-index">??&nbsp;s</span>
                        </div>
                    </div>
                    <div class="wclearfy-statistic__line">
                        <span><?php _e('Time to Interactive', 'clearfy'); ?></span>
                        <div class="wclearfy-statistic__results">
                            <span id="wclearfy-statistic__desktop-interactive">??&nbsp;s</span>&nbsp;/&nbsp;<span
                                    id="wclearfy-statistic__mobile-interactive">??&nbsp;s</span>
                        </div>
                    </div>

                    <?php
                    $site_url = get_home_url();
                    $google_page_speed_call = "https://developers.google.com/speed/pagespeed/insights/?url=" . esc_url($site_url);
                    ?>

                    <div style="margin-top: 5px;font-size:12px;">
                        <a href="<?php echo esc_url($google_page_speed_call); ?>" target="_blank"
                           style="outline: 0;text-decoration: none;"><?php _e('View complete results', 'clearfy'); ?></a> <?php _e('on Google PageSpeed Insights.', 'clearfy'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php $this->render_button(); ?>
        <?php
    }
}