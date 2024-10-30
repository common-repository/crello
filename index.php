<?php

/*
Plugin Name: Crello
Plugin URI: https://crello.com
Description: Adds a Crello button to post editor, which allows you to insert designs made in Crello directly to your posts
Author: Crello
Version: 1.0.5
Text Domain: crello
*/

class Crello
{
    const PLUGIN_NAME = 'crello';

    const OPTION_API_KEY = 'crello-api-key';
    const OPTION_DESIGN_TYPE = 'crello-design-type';

    const DEFAULT_DESIGN_TYPE = 'socialMediaSM';

    public static $availableDesignTypes = array(
        'socialMediaSM' => 'Social Media',
        'facebookSM' => 'Facebook post',
        'instagramSM' => 'Instagram post',
        'twitterSM' => 'Twitter post',
        'tumblrSM' => 'Thumblr post',
        'pinterestSM' => 'Pisterest post',
        'titleBG' => 'Blog title',
        'graphicBG' => 'Blog graphic',
        'imageBG' => 'Blog image',
        'facebookCoverHC' => 'Facebook cover',
        'twitterHC' => 'Twitter header',
        'fbEventCoverHC' => 'Facebook event cover',
        'emailHeaderHC' => 'Email header',
        'youtubeHC' => 'Youtube channel art',
        'twitterSMA' => 'Twitter add',
        'leaderboardSMA' => 'Leaderboard',
        'instagramADSMA' => 'Instagram ad',
        'largeRectangleSMA' => 'Large rectangle',
        'mediumRectangleSMA' => 'Medium rectangle',
        'skyscraperSMA' => ' Wide skyscraper',
        'facebookADSMA' => 'Facebook ad',
        'posterMM' => 'Poster',
        'cardEO' => 'Card',
    );

    /**
     * Adds menu option
     */
    public static function createMenu()
    {
        $topMenuSlug = 'crello-general-settings';
        add_menu_page(
            'Crello settings',
            'Crello settings',
            'administrator',
            $topMenuSlug,
            array(__CLASS__, 'generalSettingsPage')
        );
    }

    /**
     * Includes admin settings page template
     */
    public static function generalSettingsPage()
    {
        $uploadDir = self::getUploadDir();
        require_once(__DIR__ . '/settingsPage.php');
    }

    /**
     * @return null|string|string[]
     */
    protected static function getApiKey()
    {
        return preg_replace('/["\']+/', '', get_option('crello-api-key'));
    }

    public static function getDesignType()
    {
        $value = get_option(self::OPTION_DESIGN_TYPE);
        return $value && isset(self::$availableDesignTypes[$value]) ? $value : self::DEFAULT_DESIGN_TYPE;
    }

    /**
     * Executed in post editing page
     */
    public static function addCrelloMediaButton()
    {
        $apikey = self::getApiKey();
        $designType = self::getDesignType();
        if (!$apikey) {
            return;
        }
        if (!self::getUploadDir()) {
            return;
        }
        echo "
            <div
                data-callback='crelloSocialMediaCallback'
                id='insert-crello-media'
                data-type='$designType'
                data-apikey='$apikey'
                class='design-with-crello'
            ><div class='text'>Create in </div><div class='logo'></div></div>";
    }

    /**
     * Registers plugin settings
     */
    public static function registerSettings()
    {
        register_setting('crello-settings-group', self::OPTION_API_KEY);
        register_setting('crello-settings-group', self::OPTION_DESIGN_TYPE);
    }

    /**
     * Adds css/js
     */
    public static function addCrelloScripts()
    {
        wp_enqueue_script('crello-main', '//static.crello.com/js/frame_v1.min.js', array(), null);
        wp_enqueue_style('crello-button', plugins_url('crello-button.css', __FILE__));
        wp_enqueue_script('crello-button', plugins_url('crello-button.js', __FILE__));
    }

    /**
     * Ajax handler for crello image upload
     */
    public static function uploadImage()
    {
        if (empty($_REQUEST['crelloImageUrl']) || empty($_REQUEST['attachPostId'])) {
            return;
        }
        $dir = self::getUploadDir();
        if (!$dir) {
            echo 'Invalid upload dir permissions';
            wp_die();
        }
        $url = $_REQUEST['crelloImageUrl'];
        $filename = explode('/', $url);
        $filename = end($filename);
        $content = file_get_contents($url);
        $filePath = $dir . $filename;
        file_put_contents($filePath, $content);
        $attachment = array(
            'guid'           => $filePath,
            'post_mime_type' => mime_content_type($filePath),
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename($filename)),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $attachId = wp_insert_attachment($attachment, $filename, $_REQUEST['attachPostId']);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachData = wp_generate_attachment_metadata($attachId, $filePath);
        wp_update_attachment_metadata($attachId, $attachData);

        echo wp_get_attachment_url($attachId);
        wp_die();
    }

    /**
     * Gets writable upload dir path or false
     *
     * @return bool|string
     */
    protected static function getUploadDir()
    {
        $uploadDir = wp_upload_dir();
        $dir = $uploadDir['basedir'] . DIRECTORY_SEPARATOR;
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }
        return $dir;
    }

    /**
     * Redirects to plugin settings page
     *
     * @param string $plugin
     */
    public static function activationRedirect($plugin)
    {
        if ($plugin != self::getPluginName()) {
            return;
        }
        exit(wp_redirect(self::getSettingsLink()));
    }

    /**
     * @return string
     */
    protected static function getSettingsLink()
    {
        return admin_url('admin.php?page=crello-general-settings');
    }

    /**
     * @return string
     */
    protected static function getPluginName()
    {
        return plugin_basename( __FILE__ );
    }

    /**
     * @param $links
     */
    public static function adminPluginSettingsLink($links)
    {
        $apikey = self::getApiKey();
        $name = $apikey ? 'Settings' : '<b style="color:red">Enter api key</b>';
        array_unshift($links, '<a href="' . self::getSettingsLink() . '">' . $name . '</a>');
        return $links;
    }

    public static function init()
    {
        add_action('admin_init', array(__CLASS__, 'registerSettings'));
        add_action('media_buttons', array(__CLASS__, 'addCrelloMediaButton'), 15);
        add_action('wp_enqueue_media', array(__CLASS__, 'addCrelloScripts'));
        add_action('admin_menu', array(__CLASS__, 'createMenu'));
        add_action('wp_ajax_uploadImage', array(__CLASS__, 'uploadImage'));
        add_action('activated_plugin', array(__CLASS__, 'activationRedirect'));
        add_filter('plugin_action_links_' . self::PLUGIN_NAME . '/index.php', array(__CLASS__, 'adminPluginSettingsLink'));
    }
}

Crello::init();