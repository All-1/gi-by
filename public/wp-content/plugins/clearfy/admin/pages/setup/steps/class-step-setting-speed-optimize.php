<?php

namespace WBCR\Clearfy\Pages;

use JetBrains\PhpStorm\NoReturn;

/**
 * Provides optimization settings for improving the speed of a WordPress site.
 *
 * This class extends the functionality of the Step_Form class to create a step-by-step
 * interface for configuring speed optimization options, including JavaScript, CSS, and other optimizations.
 *
 * Class Step_Setting_Speed_Optimize
 * Extends \WBCR\Factory_Templates_134\Pages\Step_Form
 *
 * Properties:
 * - $prev_id: Represents the identifier for the previous step in the form navigation.
 * - $id: The unique identifier for the current step.
 * - $next_id: Represents the identifier for the next step in the form navigation.
 *
 * Methods:
 * - get_title(): Returns the title of the current step.
 * - get_form_description(): Returns the description of the form for the current step, explaining
 *   the benefits of caching to improve performance.
 * - get_form_options(): Returns an array of configurable options related to speed optimization,
 *   which include enabling caching, optimizing JavaScript and CSS code, disabling unnecessary
 *   functionalities like emojis or RSD links. Options may also include corresponding event triggers
 *   to dynamically show/hide related fields or warnings.
 *
 * @author  Alex Kovalev <alex.kovalevv@gmail.com> <Telegram:@alex_kovalevv>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Setting_Speed_Optimize extends \WBCR\Factory_Templates_134\Pages\Step_Form
{

    protected $prev_id = 'step2';
    protected $id = 'step3';
    protected $next_id = 'step4';

    /**
     * Retrieves the title text.
     *
     * @return string The translated title string.
     */
    public function get_title(): string
    {
        return __("Optimize speed", "clearfy");
    }

    /**
     * Retrieves the description of the form.
     *
     * @return string The translated description of the form.
     */
    public function get_form_description(): string
    {
        return __('Caching allows your WordPress site to skip a lot of steps. Instead of going through the whole page generation process every time, your caching plugin makes a copy of the page
				after the first load, and then serves that cached version to every subsequent user.', 'clearfy');
    }

    /**
     * Retrieves a list of form options for configuring plugin settings.
     *
     * @return array An array of form configuration options, including types, names, titles, layouts, hints, default values,
     *               and event handlers for JavaScript and CSS optimization, as well as options for removing unnecessary
     *               WordPress features (e.g., emojis, RSD link, wlwmanifest link, shortlink).
     */
    public function get_form_options(): array
    {
        /*$options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'enable_cache_plugin',
            'title' => __('Enable Caching', 'clearfy') . ' <span class="wbcr-clearfy-recomended-text">(' . __('Recommended', 'clearfy') . ')</span>',
            'layout' => ['hint-type' => 'icon'],
            'hint' => __('Enable simple caching. If you require expert caching, go to the advanced settings of the wp super cache plugin.', 'clearfy'),
            'default' => true
        ];

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'disable_caching_for_logged_visitors',
            'title' => __('Disable caching for logged in visitors', 'clearfy') . ' <span class="wbcr-clearfy-recomended-text">(' . __('Recommended', 'clearfy') . ')</span>',
            'layout' => ['hint-type' => 'icon'],
            'hint' => __('Caching won\'t work for authenticated users.', 'clearfy'),
            'default' => true
        ];*/

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'js_optimize',
            'title' => __('Optimize JavaScript Code?', 'minify-and-combine'),
            'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'grey'],
            //'hint' => __('Optimize JavaScript Code.', 'minify-and-combine'),
            'default' => true,
            'eventsOn' => [
                'show' => '#wbcr-mac-optimize-js-fields,#wbcr-mac-optimization-danger-message-1'
            ],
            'eventsOff' => [
                'hide' => '#wbcr-mac-optimize-js-fields,#wbcr-mac-optimization-danger-message-1'
            ]
        ];

        /*$options[] = array(
            'type' => 'html',
            'html' => array( $this, 'optimizationDangerMessage1' )
        );*/

        $js_options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'js_aggregate',
            'title' => __('Aggregate JS-files?', 'minify-and-combine'),
            'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'grey'],
            'hint' => __('Aggregate all linked JS-files to have them loaded non-render blocking? If this option is off, the individual JS-files will remain in place but will be minified.', 'minify-and-combine'),
            'default' => false,
            'eventsOn' => [
                'show' => '#wbcr-mac-optimization-danger-message-2'
            ],
            'eventsOff' => [
                'hide' => '#wbcr-mac-optimization-danger-message-2'
            ]
        ];

        $js_options[] = [
            'type' => 'html',
            'html' => [$this, 'optimizationDangerMessage2']
        ];

        $options[] = [
            'type' => 'div',
            'id' => 'wbcr-mac-optimize-js-fields',
            'items' => $js_options
        ];

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'css_optimize',
            'title' => __('Optimize CSS Code?', 'minify-and-combine'),
            'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'grey'],
            'hint' => __('If your scripts break because of a JS-error, you might want to try this.', 'minify-and-combine'),
            'default' => true,
            'eventsOn' => [
                'show' => '#wbcr-clr-optimize-css-fields'
            ],
            'eventsOff' => [
                'hide' => '#wbcr-clr-optimize-css-fields'
            ]
        ];

        $css_options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'css_aggregate',
            'title' => __('Aggregate CSS-files?', 'minify-and-combine'),
            'layout' => ['hint-type' => 'icon', 'hint-icon-color' => 'grey'],
            'hint' => __('Aggregate all linked CSS-files? If this option is off, the individual CSS-files will remain in place but will be minified.', 'minify-and-combine'),
            'default' => false,
            'eventsOn' => [
                'show' => '#wbcr-mac-optimization-danger-message-4'
            ],
            'eventsOff' => [
                'hide' => '#wbcr-mac-optimization-danger-message-4'
            ]
        ];

        $css_options[] = [
            'type' => 'html',
            'html' => [$this, 'optimizationDangerMessage4']
        ];

        $options[] = [
            'type' => 'div',
            'id' => 'wbcr-clr-optimize-css-fields',
            'items' => $css_options
        ];

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'disable_emoji',
            'title' => __('Disable Emojis', 'clearfy'),
            'layout' => ['hint-type' => 'icon'],
            'hint' => __('Emojis are fun and all, but if you are aren’t using them they actually load a JavaScript file (wp-emoji-release.min.js) on every page of your website. For a lot of businesses, this is not needed and simply adds load time to your site. So we recommend disabling this.', 'clearfy') . '<br><br><b>Clearfy: </b>' . __('Removes WordPress Emojis JavaScript file (wp-emoji-release.min.js).', 'clearfy'),
            'default' => true
        ];

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'remove_rsd_link',
            'title' => __('Remove RSD Link', 'clearfy'),
            'layout' => ['hint-type' => 'icon'],
            'hint' => __('The above link is used by blog clients. If you edit your site from your browser then you don’t need this. It is also used by some 3rd party applications that utilize XML-RPC requests. In most cases, this is just unnecessary code.', 'clearfy') . '<br><code>link rel="EditURI" type="application/rsd+xml" title="RSD"</code><br><br><b>Clearfy: </b>' . __('Remove RSD (Real Simple Discovery) link tag.', 'clearfy'),
            'default' => true
        ];

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'remove_wlw_link',
            'title' => __('Remove wlwmanifest Link', 'clearfy'),
            'layout' => ['hint-type' => 'icon'],
            'hint' => '<code>link rel="wlwmanifest" type="application/wlwmanifest+xml"</code><br>' . __('This link is actually used by Windows Live Writer. If you don’t know use Windows Live Writer, which we are guessing you don’t, this is just unnecessary code.', 'clearfy') . '<br><br><b>Clearfy: </b>' . __('Remove wlwmanifest (Windows Live Writer) link tag.', 'clearfy'),
            'default' => true
        ];

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'remove_shortlink_link',
            'title' => __('Remove Shortlink', 'clearfy'),
            'layout' => ['hint-type' => 'icon'],
            'hint' => sprintf(__('By default, the following tag shows up in every WordPress install. %s This is used for a shortlink to your pages and posts. However, if you are already using pretty permalinks, such as domain.com/post, then there is no reason to keep this, it is just unnecessary code.', 'clearfy'), '<br><code>link rel="shortlink" href="https://domain.com?p=712"</code><br>') . '<br><br><b>Clearfy: </b>' . __('Remove Shortlink link tag.', 'clearfy'),
            'default' => true
        ];

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'remove_adjacent_posts_link',
            'title' => __('Remove links to previous, next post', 'clearfy'),
            'layout' => ['hint-type' => 'icon'],
            'hint' => __('If you use Wordpress as a CMS, then you can delete these links, they can only come in handy for a blog.', 'clearfy') . '<br><br><b>Clearfy: </b>' . __('Remove the previous and next post links within the wp_head of your wordpress theme.', 'clearfy'),
            'default' => true
        ];

        $options[] = [
            'type' => 'checkbox',
            'way' => 'buttons',
            'name' => 'remove_recent_comments_style',
            'title' => __('Remove .recentcomments styles', 'clearfy'),
            'layout' => ['hint-type' => 'icon'],
            'hint' => __('WP by default for the widget "recent comments" prescribes in the code styles that are almost impossible to change, because to them apply! important.', 'clearfy') . '<br><br><b>Clearfy: </b>' . __('Removes .recentcomments styles from head section.', 'clearfy'),
            'default' => true
        ];

        return apply_filters('wclearfy/setup_wizard/speed_optimize_step/form_options', $options);
    }

    /**
     * Displays a warning message indicating potential risks associated with activating a specific optimization setting.
     *
     * @param int $selector_id The unique identifier for the warning message container. Defaults to 1.
     *
     * @return void Outputs the HTML structure of the warning message directly to the page.
     */
    public function optimizationDangerMessage($selector_id = 1): void
    {
        ?>
        <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="control-group col-sm-8">
                <div id="wbcr-mac-optimization-danger-message-<?= $selector_id ?>" class="wbcr-clearfy-danger-message">
                    <?php _e('<b>This could break things!</b><br>If you notice any errors on your website after having activated this setting, just deactivate it again, and your site will be back to normal.', 'clearfy') ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Triggers a specific optimization danger message corresponding to level 1.
     *
     * @return void
     */
    public function optimizationDangerMessage1(): void
    {
        $this->optimizationDangerMessage(1);
    }

    /**
     * Triggers a specific optimization danger message corresponding to level 2.
     *
     * @return void
     */
    public function optimizationDangerMessage2(): void
    {
        $this->optimizationDangerMessage(2);
    }

    /**
     * Triggers a specific optimization danger message corresponding to level 3.
     *
     * @return void
     */
    public function optimizationDangerMessage3(): void
    {
        $this->optimizationDangerMessage(3);
    }

    /**
     * Triggers a specific optimization danger message corresponding to level 4.
     *
     * @return void
     */
    public function optimizationDangerMessage4(): void
    {
        $this->optimizationDangerMessage(4);
    }

    /**
     * Executes the continuation of the current setup wizard step, with an optional skip parameter.
     *
     * @param bool $skip Determines if the step should be skipped. Default is false.
     * @return void
     */
    protected function continue_step($skip = false): void
    {
        do_action('wclearfy/setup_wizard/speed_optimize_step/continue_step');

        parent::continue_step();
    }

}