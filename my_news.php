<?php
/*
Plugin Name:  My News
Plugin URI:  http://shanechrisbarker.co.uk/wordpress/plugins/my-news
Description:  Wordpress plugin for displaying a subjects latest news in the wordpress admin area
Version:      1.0.0
Author:       Shane Christopher Barker
Author URI:  http://shanechrisbarker.co.uk
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
add_action( 'admin_menu', 'my_news' );

/*
* Contains all the classes needed for the function to run,
* with the exception of the my_news_helper
*/
include('my_news_classes.php');
/*
* Contains the my_news_helper class
*/
include('my_news_helper.php');

/**
 * Add the plugin page
 *
 * functionn adds the plugin settings options the menu and fires the main my_news_settings function
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
    $news         = null;
    $selectedNews = null;
    $assetHelper  = new My_News_Asset_Helper();
    $htmlBuilder  = new My_News_Html_Helper();
    $newsHelper   = new My_News_Helper();
    $assetHelper->set_css_and_js();

    // the news and language options that will be available for selection
    $availableNews      = $newsHelper->get_available_options('news');
    $availableLanguages = $newsHelper->get_available_options('languages');

    // get any previously set values - $variable = false if not already stored in db.
    $configValues       = get_option('my_news_values');
    $selectedNews       = get_option('mn_selected_news');
    $apiKey             = get_option('mn_api_key');
    $selectedLanguage   = get_option('mn_selected_news_language');

    // if we don't have a selected language, default to US
    if ($selectedLanguage === false) {
        $selectedLanguage = 'es-us';
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
                /**
                * We don't have an api key currently saved or we do but it's
                * not the one that is being posted so we'll need to update the
                * record regardless.
                */
                if ($apiKey === false || $apiKey !== $_POST['api_key']) {
                    update_option('mn_api_key', $_POST['api_key']);
                    $apiKey = $_POST['api_key'];
                    echo $htmlBuilder->build_alert
                    (
                        'The Api key was updated succesfully',
                        $alertType = 'success'
                    );
                }
            }
        }
        if(isset($_POST['languages'])) {
            // if we have a language being posted, Make sure it's a value we expect
            if (array_key_exists($_POST['languages'], $availableLanguages)) {
                // value exists but if it is the current value then no point in updating
                if ($selectedLanguage !== $_POST['languages']) {
                    update_option('mn_selected_news_language', $_POST['languages']);
                }
                $selectedLanguage = $_POST['languages'];
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
        $selectedNews,
        $selectedLanguage,
        $apiKey,
        $availableLanguages,
        $availableNews
    );
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
            $resultsHtml  = $htmlBuilder->build_results_html($selectedNews, $resultsData);
            echo $resultsHtml;
            echo '</div>';
        }
    }

    return true;
}
