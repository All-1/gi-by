<?php

namespace WBCR\Clearfy\Pages;

/**
 * Step_Plugins Class
 *
 * Represents a step in the setup process for suggesting and installing additional plugins
 * and components to optimize a WordPress site.
 *
 * This class extends the Step_Custom class from the Factory Templates framework and
 * is primarily used to present a user interface for recommending specific plugins to
 * enhance site performance. It displays plugins along with their respective optimization
 * scores and optional PRO scores, and provides buttons for installation.
 *
 * @author  Alex Kovalev <alex.kovalevv@gmail.com> <Telegram:@alex_kovalevv>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Plugins extends \WBCR\Factory_Templates_134\Pages\Step_Custom
{

    protected $prev_id = 'step1';
    protected $id = 'step2';
    protected $next_id = 'step3';

    /**
     * Retrieves the title for the setup plugins section.
     *
     * @return string The localized title text.
     */
    public function get_title(): string
    {
        return __("Setup Plugins", "clearfy");
    }

    /**
     * Renders the HTML content for displaying and installing recommended plugins and components.
     *
     * This method displays a structured interface that lists plugins and components
     * along with their associated benefits. It provides installation buttons to include
     * these plugins and components for optimizing the site.
     *
     * @return void
     * @throws \Exception
     */
    public function html(): void
    {
        $install_robin_plugin_btn = $this->plugin->get_install_component_button('wordpress', 'robin-image-optimizer/robin-image-optimizer.php');
        $install_wp_super_cache_btn = $this->plugin->get_install_component_button('wordpress', 'wp-super-cache/wp-cache.php');
        $install_assets_manager_component_btn = $this->plugin->get_install_component_button('internal', 'assets_manager');
        $install_minify_and_combine_component_btn = $this->plugin->get_install_component_button('internal', 'minify_and_combine');
        ?>
        <div class="w-factory-templates-134-setup__inner-wrap">
            <h3><?php _e('Installing plugins', 'clearfy') ?></h3>
            <p style="text-align: left;"><?php _e('We analyzed your site and decided that in order to get the maximum result in
				optimizing your site, you will need to install additional plugins.', 'clearfy') ?></p>
            <table class="form-table">
                <thead>
                <tr>
                    <th><?php _e('Plugin', 'clearfy') ?></th>
                    <th style="width:50px"><?php _e('Score', 'clearfy') ?></th>
                    <th style="width:200px"><?php _e('Score with PRO', 'clearfy') ?></th>
                    <th></th>
                </tr>
                </thead>
                <tr>
                    <td>Robin image optimizer</td>
                    <td style="color:grey">+10</td>
                    <td style="color:green">+15</td>
                    <td>
                        <?php $install_robin_plugin_btn->render_link(); ?>
                    </td>
                </tr>
                <tr>
                    <td>Assets manager component</td>
                    <td style="color:grey">+5</td>
                    <td style="color:green">+10</td>
                    <td><?php $install_assets_manager_component_btn->render_link(); ?></td>
                </tr>
                <!--<tr>
					<td>WP Super Cache</td>
					<td style="color:grey">+8</td>
					<td></td>
					<td><?php /*$install_wp_super_cache_btn->renderLink(); */ ?></td>
				</tr>-->
                <tr>
                    <td>Minify and Combine component</td>
                    <td style="color:grey">+10</td>
                    <td style="color:green">+15</td>
                    <td><?php $install_minify_and_combine_component_btn->render_link(); ?></td>
                </tr>
            </table>
        </div>
        <?php $this->render_button(); ?>
        <?php
    }
}