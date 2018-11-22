<?php
/*
Plugin Name:  My News
Plugin URI:  http://shanechrisbarker.co.uk/wordpress/plugins/my-news
Description: Plugin for querying the Bing news api and viewing the results.
Version:      1.0.0
Author:      shanechrisbarker
Author URI:  http://shanechrisbarker.co.uk
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
add_action( 'admin_menu', 'my_news' );

/*
* Contains all the classes needed for the function to run,
* with the exception of the my_news_helper.
*/
include('my_news_classes.php');
/*
* Contains the my_news_helper class.
*/
include('my_news_helper.php');

/**
 * Add the plugin page.
 *
 * functionn adds the plugin settings options the menu and fires the main my_news_settings function.
 *
 * @since 1.0.0
 * @return bool Success
 */
function my_news ()
{
    add_plugins_page(
        'My News - Settings',
        'My News','manage_options',
        'my_news_settings',
        'my_news_settings'
    );

    return true;
}

/**
 * The main function - index action
 *
 * Function is loaded when the my news settings page is accessed from the admin menu.
 * Fires off all events needed for the admin area to function and for results to be
 * search for and saved.
 *
 * @since 1.0.0
 * @return bool All is well...
 */
function my_news_settings()
{
    if (false === current_user_can('manage_options')) {
        wp_die(__('You do not have permission to change these settings. '));
    }

    $nonce          = wp_create_nonce( 'my_news_nonce' );
    $news           = null;
    $selectedNews   = null;
    $assetHelper    = new My_News_Asset_Helper();
    $htmlBuilder    = new My_News_Html_Helper();
    $newsHelper     = new My_News_Helper();
    $assetHelper->set_css_and_js();

    // the news and language options that will be available for selection
    $availableNews      = $newsHelper->get_available_options('news');
    $availableLanguages = $newsHelper->get_available_options('languages');

    // get the protocols we'll allow when outputting html.
    $allowedProtocols   = $newsHelper->get_allowed_protocols();

    // get any previously set values - $variable = false if not already stored in db.
    $selectedNews       = get_option('mn_selected_news');
    $apiKey             = get_option('mn_api_key');
    $selectedLanguage   = get_option('mn_selected_news_language');

    // if we don't have a selected language, default to US
    if ($selectedLanguage === false) {
        $selectedLanguage = 'es-us';
    }

    if (empty($_POST) === false) {
        // we are posting so we need to check the nonce
        $nonce = $_POST['_wpnonce'];
        if (wp_verify_nonce($nonce, 'my_news_nonce') === false ) {
            wp_die(__('You do not have permission to carry out this action. '));
        }
    }

    // if we don't have a valid api key and we aren't posting one, show a warning.
    if (($apiKey === false || $apiKey === '')
    && ($_POST['api_key'] === '' || null === $_POST['api_key'])) {
        echo $htmlBuilder->build_alert
        (
            '<strong>Please Note</strong>: You do not have api key saved - no results will be displayed until an api key is submitted',
            $alertType = 'error'
        );
    }

      // check if the form was posted and it contains data that we expect.
    if (isset($_POST['settings_post']) && $_POST['settings_post'] === 'settings_post') {
        $news = $_POST['news'];
        if (false === array_key_exists($news, $availableNews)) {
            // the select element html has been played with - get outta here.
            echo $htmlBuilder->build_alert
            (
                'Something went wrong - please select a news category and try again',
                $alertType = 'error'
            );
        } else {
            // here we are checking if an $apiKey has been posted along with the form.
            if (isset($_POST['api_key'])) {
                $postedKey = $_POST['api_key'];
                // Check the api key is purely alphanumeric
                $apiKeyIsSafe = ctype_alnum($postedKey);
                if ($apiKeyIsSafe === true) {
                    if ($apiKey !== $postedKey) {
                        // api key and stored api key are not the same - save option.
                        update_option('mn_api_key', $postedKey);
                        $apiKey = $postedKey;

                        echo $htmlBuilder->build_alert
                        (
                            'The Api key was updated succesfully',
                            $alertType = 'success'
                        );
                    }
                } else {

                    echo $htmlBuilder->build_alert
                    (
                        'The Api key contained invalid characters. Please check and try again',
                        $alertType = 'error'
                    );
                    $apiKey = false;
                }
            }
        }
        if(isset($_POST['languages'])) {
            $postedLanguage = $_POST['languages'];
            // if we have a language being posted, Make sure it's a value we expect
            if (array_key_exists($postedLanguage, $availableLanguages)) {
                // value exists but if it is the current value then no point in updating
                if ($selectedLanguage !== $postedLanguage) {
                    // the languages have a '-' in their values - explode on the - and then
                    // check values to ensure what we have left is alphanumeric and safe.
                    $postedLanguageParts = explode('-', $postedLanguage);
                    if (ctype_alnum($postedLanguageParts[0]) && ctype_alnum($postedLanguageParts[1])) {
                        // the value is safe
                        update_option('mn_selected_news_language', $postedLanguage);
                        $selectedLanguage = $postedLanguage;
                    } else {
                        // the posted language wasn't a value that we expected - revert to default
                        $selectedLanguage = 'es-us';
                    }
                }
            }
        }
    }

    // if these are identical, nothing has changed so no need to update for the sake of it.
    if ($selectedNews !== $news && isset($news)) {
        // update the selected news article in the db
        update_option('mn_selected_news', $news);
        $selectedNews = $news;
        echo $htmlBuilder->build_alert
        (
            'Selected News Category updated succesfully',
            $alertType = 'success'
        );
    }

    echo '<div class="row">';
    // build the settings form
    $formHtml = $htmlBuilder->build_settings_form
    (
        $nonce,
        $selectedNews,
        $selectedLanguage,
        $apiKey,
        $availableLanguages,
        $availableNews
    );
    $allowedFormHtml    = $newsHelper->get_allowed_form_html();
    $formHtml           = wp_kses($formHtml, $allowedFormHtml, $allowedProtocols);

    echo $formHtml;

    // if we have an api key and selected news, we are ready to roll!
    if ($selectedNews !== false && $apiKey !== false && $apiKey !== '') {
        $apiCaller    = new Api_Caller();
        $resultsData  = $apiCaller->get_news($selectedNews, $selectedLanguage, $apiKey);
        if(isset($resultsData->statusCode) && $resultsData->statusCode === 401) {
            // usually this means your api key is invalid.
            echo '<div class="col-5 mt-2">';
            echo $htmlBuilder->build_alert
            (
                $resultsData->message,
                $alertType = 'error'
            );
            echo '</div>';
        } else {
            // the call was good - build the results html
            $resultsHtml = $htmlBuilder->build_results_html($selectedNews, $resultsData);
            // make sure the html elements are what we expect
            $allowedHtml        = $newsHelper->get_allowed_results_html();
            $resultsHtml        = wp_kses($resultsHtml, $allowedHtml, $allowedProtocols);
            echo $resultsHtml;
            echo '</div>';
        }
    }

    return true;
}
