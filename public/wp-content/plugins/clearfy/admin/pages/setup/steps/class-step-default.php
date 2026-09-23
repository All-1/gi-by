<?php

namespace WBCR\Clearfy\Pages;

/**
 * Represents the default onboarding step in the Clearfy setup wizard.
 *
 * This class defines the basic attributes and methods used for displaying the
 * initial step of the setup wizard, including the title and HTML content.
 *
 * @author  Alex Kovalev <alex.kovalevv@gmail.com> <Telegram:@alex_kovalevv>
 * @copyright (c) 23.07.2020, Webcraftic
 * @version 1.0
 */
class Step_Default extends \WBCR\Factory_Templates_134\Pages\Step_Custom
{

    protected $id = 'step0';
    protected $next_id = 'step1';

    /**
     * Retrieves the title text.
     *
     * @return string The translated title text.
     */
    public function get_title(): string
    {
        return __("Welcome", 'clearfy');
    }

    /**
     * Renders the HTML structure for the setup wizard onboarding interface.
     *
     * @return void
     * @throws \Exception
     */
    public function html(): void
    {
        ?>
        <div class="w-factory-templates-134-setup__inner-wrap">
            <div class="w-factory-templates-134-setup-step__new_onboarding-wrapper">
                <p class="w-factory-templates-134-setup-step__new_onboarding-welcome">Welcome to</p>
                <h1 class="w-factory-templates-134-logo">
                    <img src="<?php echo WCL_PLUGIN_URL ?>/admin/assets/img/clearfylogo-768x300.png" alt="Clearfy">
                </h1>
                <p><?php _e('Optimize your site even faster using the setup wizard!', 'clearfy') ?></p>
            </div>

        </div>
        <?php $this->render_button(true, false, __('Yes, I want to try the wizard'), 'center'); ?>
        <?php
    }
}