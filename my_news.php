<?php
/*
Plugin Name:  My News
Plugin URI:  http://shanechrisbarker.co.uk/wordpress/plugins/my-news
Description:  Wordpress plugin for displaying a subjects latest news in the wordpress admin area
Version:      0.0.1
Author:       Shane Christopher Barker
Author URI:  http://shanechrisbarker.co.uk
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
add_action( 'admin_menu', 'my_news' );
include('my_news_classes.php');

function my_news ()
{
  add_plugins_page('My News - Settings',  'My News','manage_options', 'my_news_settings', 'my_news_settings');
}

function my_news_settings()
{
  if (false === current_user_can('manage_options')) {
    wp_die(__('You do not have permission to change these settings. '));
  }

  $news         = null;
  $selectedNews = null;
  $assetHelper  = new My_News_Asset_Helper();
  $htmlBuilder  = new My_News_Html_Helper();
  $assetHelper->set_css_and_js();

  // the news options that will be available for selection
  $availableNews = [
    ''              => 'Please Select',
    'sports'        => 'Sports',
    'business'      => 'Business',
    'general'       => 'General',
    'health'        => 'Health',
    'science'       => 'Science',
    'technology'    => 'Technology'
  ];

  ksort($availableNews);
  $selectedNews = get_option('mn_selected_news');
  $apiKey       = get_option('mn_api_key');

  if (($apiKey === false || $apiKey === '') && ($_POST['api_key'] === '' || null === $_POST['api_key'])) {
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
        // making sure the apiKey is alphanumric and doesn't have anything nasty
        // it may be empty if the key is being deleted so allow an empty string
        if (true === ctype_alnum($_POST['api_key']) || $_POST['api_key'] === '') {
          // if we didn't have an api key in the first place, or we do but it's
          // not the one that is being posted so we'll need to update the
          // record regardless.
          if ($apiKey === false || $apiKey !== $_POST['api_key']) {
            update_option('mn_api_key', $_POST['api_key']);
            $apiKey = $_POST['api_key'];
            echo $htmlBuilder->build_alert
            (
              'The Api key was updated succesfully',
              $alertType = 'success'
            );
          }
        } else {
          echo $htmlBuilder->build_alert
          (
            'The Api key is invalid - please enter a valid API key',
            $alertType = 'error'
          );
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
  $formHtml = $htmlBuilder->build_settings_form($selectedNews, $apiKey, $availableNews);
  echo $formHtml;

  // if we have an api key and selected news, we are ready to roll!
  if ($selectedNews !== false && $apiKey !== false && $apiKey !== '') {
    $apiCaller    = new Api_Caller();
    $newsDate     = date('Y-m-d', strtotime('today'));
    $resultsData  = $apiCaller->get_news($selectedNews, $apiKey, $newsDate);
    $resultsHtml  = $htmlBuilder->build_results_html($selectedNews, $resultsData);
    echo $resultsHtml;
  }

  echo '</div>';
}
