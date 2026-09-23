<?php

namespace WBCR\Clearfy\Pages;

/**
 * The Step_Congratulation class represents the final step in the plugin's setup wizard.
 * It provides a congratulatory message to the user after completing the setup process
 * and offers information about upgrading to the Pro version for additional features.
 *
 * This class extends the Step_Custom class to utilize predefined methods for rendering
 * the setup wizard's steps and managing navigation between steps.
 *
 * Properties:
 * - $prev_id (protected): The identifier of the previous step in the setup wizard.
 * - $id (protected): The identifier of the current step in the setup wizard.
 *
 * Methods:
 * - get_title(): Returns the title of the step as displayed in the setup wizard.
 * - html(): Outputs HTML content for displaying the congratulatory message, feature comparison
 *           table, and a link to upgrade to the Pro version.
 * - continue_step(bool $skip = false): Handles navigation to the next step or a fallback
 *           redirect to the "Quick Start" page if no next step is defined.
 *
 * @author  Alex Kovalev <alex.kovalevv@gmail.com> <Telegram:@alex_kovalevv>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 * /
 */
class Step_Congratulation extends \WBCR\Factory_Templates_134\Pages\Step_Custom
{

    protected $prev_id = 'step6';
    protected $id = 'step7';

    //protected $next_id = 'step2';

    /**
     * Retrieves the title text.
     *
     * @return string The localized title string.
     */
    public function get_title(): string
    {
        return __("Finish", "clearfy");
    }

    /**
     * Renders the HTML content for the plugin setup wizard completion message and Pro version features comparison table.
     *
     * This method generates a message indicating the completion of the plugin setup process, provides a detailed comparison of Free and Pro features,
     * and includes a call-to-action button for upgrading to the Pro version.
     *
     * @return void Outputs the generated HTML directly to the page.
     * @throws \Exception
     */
    public function html(): void
    {
        $pricing_page_url = $this->plugin->get_support()->get_pricing_url(true, 'setup_wizard');
        ?>
        <div class="w-factory-templates-134-setup__inner-wrap">
            <h3><?php echo __("Congratulations, the plugin configuration is complete!", "clearfy"); ?></h3>
            <p style="text-align: left;">
                <?php _e('You have successfully completed the basic plugin setup! You can go to the general plugin settings to enable other options that we did not offer you.', 'clearfy'); ?>
            </p>
            <hr>
            <div>
                <p style="text-align: left;">
                    <?php _e("However, you can still improve your site's Google Page Speed score by simply purchasing the Pro version of our plugin.", "clearfy") ?>
                </p>
                <table style="width: 100%">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Free</th>
                        <th>PRO</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><a href="https://wordpress.org/plugins/cyrlitera/" target="_blank">
                                Transliteration of links and file names</a></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>Optimize Yoast Seo</td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>Post tools</td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>Admin bar managers</td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td><a href="https://wordpress.org/plugins/disable-admin-notices/" target="_blank">Disable admin
                                notices</a></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>Disable widgets</td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>
                            <a href="https://wordpress.org/plugins/comments-plus/" target="_blank">Disable comments</a>
                        </td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td><a href="https://wordpress.org/plugins/gonzales/" target="_blank">Assets Manager</a></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>Minify and combine (JS, CSS)</td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>Html minify</td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td><a href="https://robinoptimizer.com/" target="_blank">Image optimizer</a></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>Hide login page</td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td><a href="https://clearfy.pro/hide-my-wp/" target="_blank">Hide My Wp PRO</a></td>
                        <td class="wclearfy-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td><a href="https://clearfy.pro/assets-manager/" target="_blank">Assets Manager PRO</a></td>
                        <td class="wclearfy-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>Multisite control</td>
                        <td class="wclearfy-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>Update manager PRO</td>
                        <td class="wclearfy-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    <tr>
                        <td>SEO friendly images PRO</td>
                        <td class="wclearfy-setup__color--red"><span class="dashicons dashicons-minus"></span></td>
                        <td class="wclearfy-setup__color--green"><span class="dashicons dashicons-yes"></span></td>
                    </tr>
                    </tbody>
                </table>
                <p>
                    <a href="<?php echo esc_url($pricing_page_url); ?>" class="wclearfy-setup__install-component-button"
                       target="_blank"><?php _e('Go Pro', 'clearfy') ?></a>
                </p>
            </div>
        </div>
        <?php $this->render_button(); ?>
        <?php
    }

    /**
     * Handles the continuation to the next step in a multi-step process.
     *
     * This method determines the next step in the process and redirects the user to the appropriate URL.
     * If there are no further steps available, it redirects to a default Quick Start page.
     *
     * @param bool $skip Indicates whether the current step should be skipped. Defaults to false.
     * @return void Redirects the user to the URL of the next step or the Quick Start page.
     * @throws \Exception
     */
    protected function continue_step($skip = false): void
    {
        $next_id = $this->get_next_id();
        if (!$next_id) {
            wp_safe_redirect($this->plugin->getPluginPageUrl('quick_start'));
            die();
        }
        wp_safe_redirect($this->page->getActionUrl($next_id));
        die();
    }
}