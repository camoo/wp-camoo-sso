<?php

declare(strict_types=1);

namespace WP_CAMOO\SSO\Controller;

use WP_CAMOO\SSO\Bootstrap;
use WP_CAMOO\SSO\Gateways\Option;

defined('ABSPATH') or die('You are not allowed to call this script directly!');

class AdminController
{
    private Option $option;

    private string $option_name = Option::MAIN_SETTING_KEY;

    public function __construct(?Option $option = null)
    {
        $this->option = $option ?? new Option();
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public function initialize()
    {
        add_action('admin_init', [new self(), 'admin_init']);
        add_action('admin_menu', [new self(), 'add_page']);
    }

    public function admin_init()
    {
        register_setting(Option::MAIN_SETTING_KEY, $this->option_name, [$this, 'validate']);
    }

    public function add_page()
    {
        //if(current_user_can('camoo_sso')) {
        add_options_page(
            __('Single Sign On', Bootstrap::DOMAIN_TEXT),
            __('Single Sign On', Bootstrap::DOMAIN_TEXT),
            'manage_options',
            Option::MAIN_SETTING_KEY,
            [
                $this,
                'options_do_page',
            ]
        );
    }

    public function admin_head()
    {

        // Jquery Accordion
        wp_enqueue_style('camoo-sso-jquery-ui');
        wp_enqueue_script('jquery-ui-accordion');

        wp_enqueue_style('camoo-sso-admin');
        wp_enqueue_script('camoo-sso-admin');
    }

    public function options_do_page()
    {
        $options = $this->option->get();
        $this->admin_head(); ?>
        <div class="wrap">
            <h2>Single Sign On Configuration</h2>
            <p>
                When activated, this plugin adds a Single Sign On button to the login screen.
                <br/>
                <strong>NOTE:</strong> If you want to add a
                custom link anywhere in your theme simply link to
                <strong><?php  esc_attr_e(site_url('?auth=sso')); ?></strong>
                if the user is not logged in.
            </p>
            <br />
            <div>
                <h3 id="camoo-sso-configuration">Camoo.Hosting SSO Settings</h3>
                <div>
                    <form method="post" action="options.php">
                        <?php settings_fields(Option::MAIN_SETTING_KEY); ?>
                        <table class="form-table">



                            <tr valign="top">
                                <th scope="row">Client Identifier</th>
                                <td>
                                    <input type="text" name="<?php echo $this->option_name ?>[client_id]"
                                           value="<?php echo $options['client_id'] ?? ''; ?>"/>
                                </td>
                            </tr>


                            <tr valign="top">
                                <th scope="row">Append clientId to JWT Server</th>
                                <td>
                                    <input type="checkbox" name="<?php echo $this->option_name ?>[append_client_id]"
                                           value="1" <?php echo !empty($options['show_sso_button_login_page']) && $options['append_client_id'] == 1 ? 'checked="checked"' : ''; ?> />
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">Sync roles with external system</th>
                                <td>
                                    <input type="checkbox" name="<?php echo $this->option_name ?>[sync_roles]"
                                           value="1" <?php echo !empty($options['show_sso_button_login_page']) && $options['sync_roles'] == 1 ? 'checked="checked"' : ''; ?> />
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">Show SSO button on login page</th>
                                <td>
                                    <input type="checkbox" name="<?php echo $this->option_name ?>[show_sso_button_login_page]"
                                           value="1" <?php echo !empty($options['show_sso_button_login_page']) && $options['show_sso_button_login_page'] == 1 ? 'checked="checked"' : ''; ?> />
                                </td>
                            </tr>

                        </table>

                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>"/>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div>
        <?php
    }

    public function validate(array $input): array
    {
        $input['append_client_id'] = isset($input['append_client_id']) ? $input['append_client_id'] : 0;
        $input['sync_roles'] = isset($input['sync_roles']) ? $input['sync_roles'] : 0;
        $input['show_sso_button_login_page'] = isset($input['show_sso_button_login_page']) ? $input['show_sso_button_login_page'] : 0;

        return $input;
    }
}
