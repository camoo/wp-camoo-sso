<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Controller;

use WP_CAMOO\SSO\Gateways\Option;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

final class AdminController
{
    private const INPUT_CHECKED = 'checked="checked"';

    private Option $option;

    private string $optionName = Option::MAIN_SETTING_KEY;

    private static ?self $instance = null;

    public function __construct(?Option $option = null)
    {
        $this->option = $option ?? new Option();
    }

    public static function getInstance(): self
    {

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function initialize(): void
    {
        add_action('admin_init', [$this, 'initAdmin']);
        add_action('admin_menu', [$this, 'addPage']);
    }

    public function initAdmin(): void
    {
        register_setting($this->optionName, $this->optionName, [$this, 'validate']);
    }

    public function addPage(): void
    {
        if (!current_user_can('camoo_sso')) {
            return;
        }
        add_options_page(
            __('Single Sign On', 'camoo-sso'),
            __('Single Sign On', 'camoo-sso'),
            'manage_options',
            Option::MAIN_SETTING_KEY,
            [$this, 'doPageOptions']
        );
    }

    public function adminHead(): void
    {
        wp_enqueue_style('camoo-sso-jquery-ui');
        wp_enqueue_script('jquery-ui-accordion');

        wp_enqueue_style('camoo-sso-admin');
        wp_enqueue_script('camoo-sso-admin');
    }

    public function doPageOptions(): void
    {
        $options = $this->option->get();

        $this->adminHead(); ?>
        <div class="wrap">
            <h2><?php echo __('Single Sign On Configuration', 'camoo-sso') ?></h2>

            <div class="notice notice-' . 'info' . ' is-dismissible"
            ">
            <div style="' . 'padding:12px;' . '">
                <p>
                    When activated, this plugin adds a Single Sign On button to the login screen.
                    <br/><strong>NOTE:</strong> If you wish to add a custom link anywhere in your theme link to
                    <strong><?php esc_attr_e(site_url('?auth=sso')); ?></strong> if the user is not logged in
                </p>
            </div>
        </div>

        <br/>
        <div class="camoo-sso-settings-table">
            <h3 id="camoo-sso-configuration"><?php echo __('Camoo.Hosting SSO Settings', 'camoo-sso') ?></h3>
            <div class="camoo-sso-options-table">
                <form method="post" action="options.php">
                    <?php settings_fields(Option::MAIN_SETTING_KEY); ?>
                    <table class="form-table">

                        <tr class="td-camoo-sso-options">
                            <th scope="row"><?php echo __('Redirect to dashboard after login', 'camoo-sso') ?></th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo esc_html($this->optionName); ?>[redirect_to_dashboard]"
                                           value="1" <?php echo !empty($options['redirect_to_dashboard']) &&
                                    $options['redirect_to_dashboard'] == 1 ? self::INPUT_CHECKED : ''; ?> />
                                </label>
                            </td>
                        </tr>

                        <tr class="td-camoo-sso-options">
                            <th scope="row"><?php echo __('Sync roles with Camoo', 'camoo-sso') ?></th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo esc_html($this->optionName); ?>[sync_roles]"
                                           value="1" <?php echo !empty($options['sync_roles']) &&
                                    $options['sync_roles'] == 1 ? self::INPUT_CHECKED : ''; ?> />
                                </label>
                            </td>
                        </tr>

                        <tr class="td-camoo-sso-options">
                            <th scope="row"><?php echo __('Show SSO button on login page', 'camoo-sso') ?></th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo esc_html($this->optionName); ?>[show_sso_button_login_page]"
                                           value="1" <?php echo !empty($options['show_sso_button_login_page']) &&
                                    $options['show_sso_button_login_page'] == 1 ? self::INPUT_CHECKED : ''; ?> />
                                </label>
                            </td>
                        </tr>
                        <tr class="td-camoo-sso-options">
                            <th scope="row">
                                <?php echo __('Allow login accounts from your main account', 'camoo-sso') ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo esc_html($this->optionName); ?>[allow_login_account]"
                                           value="1" <?php echo !empty($options['allow_login_account']) &&
                                    $options['allow_login_account'] == 1 ? self::INPUT_CHECKED : ''; ?> />
                                </label>
                            </td>
                        </tr>
                        <tr class="td-camoo-sso-options">
                            <th scope="row"><?php echo __('Disable username and password login', 'camoo-sso') ?></th>
                            <td>
                                <label>
                                    <input type="checkbox"
                                           name="<?php echo esc_html($this->optionName); ?>[disable_username_password_login]"
                                           value="1" <?php echo !empty($options['disable_username_password_login']) &&
                                    $options['disable_username_password_login'] == 1 ? self::INPUT_CHECKED : ''; ?> />
                                </label>
                            </td>
                        </tr>

                    </table>

                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>"/>
                    </p>
                </form>
            </div>
        </div>
        <div style="clear:both;"></div>
        <?php
    }

    public function validate(array $input): array
    {
        $validatedInput = [];

        // List of all expected checkbox options
        $checkboxOptions = [
            'sync_roles',
            'show_sso_button_login_page',
            'redirect_to_dashboard',
            'allow_login_account',
            'disable_username_password_login',
        ];

        // Iterate over each checkbox option
        foreach ($checkboxOptions as $option) {
            // Check if the checkbox is set in the input and is equal to '1'
            $validatedInput[$option] = isset($input[$option]) && $input[$option] === '1' ? 1 : 0;
        }

        // Add more validation for other types of input here if necessary

        return $validatedInput;
    }
}
