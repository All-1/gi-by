<?php
// Exit if accessed directly
if( !defined('ABSPATH') ) {
    exit;
}

/**
 * Class WCL_Setup
 *
 * Handles the initialization and execution of the setup wizard.
 * This class extends the Setup page template to provide a multi-step setup
 * experience, including various configuration options and enhancements
 * for the plugin.
 *
 * @author  Alex Kovalev <alex.kovalevv@gmail.com> <Telegram:@alex_kovalevv>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class WCL_Setup extends WBCR\Factory_Templates_134\Pages\Setup {

    /**
     * Constructor method for initializing the setup wizard.
     *
     * @param \Wbcr_Factory480_Plugin $plugin An instance of the plugin class that provides necessary plugin functionality and settings.
     *
     * @return void
     * @throws Exception
     */
    public function __construct(\Wbcr_Factory480_Plugin $plugin)
    {
        // Call parent constructor.
        parent::__construct($plugin);

        // Path to the directory containing step classes.
        $path = WCL_PLUGIN_DIR . '/admin/pages/setup/steps';

        // Registering the steps of the setup wizard.

        # Step 1: Default step
        $this->register_step($path . '/class-step-default.php', '\WBCR\Clearfy\Pages\Step_Default');

        # Step 2: Google Page Speed (Before)
        $this->register_step($path . '/class-step-google-page-speed-before.php', '\WBCR\Clearfy\Pages\Step_Google_Page_Speed_Before');

        # Step 3: Plugins step
        $this->register_step($path . '/class-step-plugins.php', '\WBCR\Clearfy\Pages\Step_Plugins');

        # Step 4: Speed Optimization Settings
        $this->register_step($path . '/class-step-setting-speed-optimize.php', '\WBCR\Clearfy\Pages\Step_Setting_Speed_Optimize');

        # Step 5: SEO Optimization Settings
        $this->register_step($path . '/class-step-setting-seo-optimize.php', '\WBCR\Clearfy\Pages\Step_Setting_Seo');

        # Step 6: Image Optimization
        $this->register_step($path . '/class-step-optimize-images.php', '\WBCR\Clearfy\Pages\Step_Optimize_Images');

        # Step 7: Google Page Speed (After)
        $this->register_step($path . '/class-step-google-page-speed-after.php', '\WBCR\Clearfy\Pages\Step_Google_Page_Speed_After');

        # Step 8: Congratulations step
        $this->register_step($path . '/class-step-congratulation.php', '\WBCR\Clearfy\Pages\Step_Congratulation');
    }

    /**
     * Enqueues required assets (js and css) for the setup wizard.
     *
     * This loads additional scripts and styles specific to the setup wizard page.
     *
     * @param object $scripts The object to enqueue scripts.
     * @param object $styles  The object to enqueue styles.
     *
     * @return void
     * @since 1.0.0
     * @see   FactoryPages480_AdminPage
     */
    public function assets($scripts, $styles)
    {
        // Call parent method to enqueue default assets.
        parent::assets($scripts, $styles);

        // Add custom JavaScript files.
        $this->scripts->add(WCL_PLUGIN_URL . '/admin/assets/js/circular-progress.js');
        $this->scripts->add(WCL_PLUGIN_URL . '/admin/assets/js/setup.js');

        // Add custom CSS files.
        $this->styles->add(WCL_PLUGIN_URL . '/admin/assets/css/setup/page-setup.css');
    }
}
