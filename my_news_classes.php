<?php
class Api_Caller
{
  function get_news($selectedNews, $apiKey, $dateFrom, $dateTo = null)
  {
    $url          = 'https://newsapi.org/v2';
    $endpoint     = $this->build_query_string($apiKey, $selectedNews, $dateFrom, isset($dateTo) ? $dateTo : null);
    $queryString  = $url.$endpoint;
    $response     = wp_remote_get($queryString);
    $response     = json_decode($response['body']);

    return $response;
  }

  function build_query_string($apiKey, $selectedNews, $dateFrom, $dateTo = null)
  {
    $queryString = null;
    $queryString .= '/top-headlines?category='.$selectedNews.'&from='.$dateFrom.'&sortBy=popularity&apiKey='.$apiKey;
    return $queryString;

  }
}
/**************************************************************************/
/**************************************************************************/
class My_News_Asset_Helper
{
  function set_css_and_js()
  {
    $siteUrl = get_site_url().'/wp-content/plugins/my_news/assets/my_news_style.css';
    $boostrapCss = 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css';
    $boostrapJs = [];
    $boostrapJs[] = 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js';
    $boostrapJs[] = 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js';

    wp_register_style('my_news_css', $siteUrl );
    wp_register_style('bootstrap_four_css', $boostrapCss );
    wp_enqueue_style('my_news_css');
    wp_enqueue_style('bootstrap_four_css');

    foreach ($boostrapJs as $js) {
      wp_enqueue_script($js);
    }

    add_action('wp_head', 'my_news_set_bootstrap_meta');
  }

  function my_news_set_bootstrap_meta()
  {
    return $bootstrapMeta = '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
  }
}

/**************************************************************************/
/**************************************************************************/
class My_News_Html_Helper
{

  function build_alert($alertText, $alertType)
  {
    switch ($alertType) {
      case('error') :
        $html = '<div class="alert alert-danger container mt-5" role="alert">';
        break;
      case('success') :
        $html = '<div class="alert alert-success container mt-5" role="alert">';
        break;
    }
    $html .= '<p>'.$alertText.'</p>';
    $html .= '</div>';
    return $html;
  }

  function build_settings_form($selectedNews, $apiKey, $availableNews = [])
  {
    $html .= '<div class="col-4 mt-2">';
    $html .= '<h3 class="my_news_mt_10">My News - Settings</h3>';
    $html .= '<p><strong>Please Note:</strong> News articles are pulled from <a href="https://newsapi.org/" target="_blank"> https://newsapi.org/</a></p>';
    $html .= '<p>You will need to register there for an API key and enter into the form below</p>';
    $html .= '<form method="post" action="" id="my_news_form" class="col-12">';
    $html .= '<input type="hidden" value="settings_post" id="settings_post" name="settings_post">';
    $html .= '<div class="form-group">';
    $html .= '<label for="news">Please select a news category from the drop down menu</label>';
    $html .= '<select id="news" name="news" class="form-control">';

    foreach($availableNews as $key => $value) {
      // if we found a selected team during the above post checks
      if ($selectedNews !== false) {
        if ($selectedNews === $key) {
          $selected = 'selected';
        } else {
          $selected = null;
        }
      }
      $html .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
    }

    $html .= '</select>';
    $html .= '</div>';

    $html .= '<div class="form-group">';
    $html .= '<label for="api_key" class="my_news_form_element">newsAPI.org API Key</label>';
    $html .= '<input class="form-control" type="text" id="api_key" name="api_key"';

    if ($apiKey !== false) {
      $html .='value="'.$apiKey.'"';
    }

    $html .= '\>';$html .= $apiKeyField;
    $html .= '</div>';
    $html .= '<input type="submit" class="btn btn-primary" value="Save Settings"\>';
    $html .= '</form>';
    $html .= '</div>';

    return $html;
  }

  function build_results_html($selectedNews, object $resultsData)
  {
    // if the call was successful and we actually have some results to show..
    if ($resultsData->status === 'ok' && $resultsData->totalResults > 0) {
      // loop the result and build html alerts to show the various aspects of the articles
      $html .= '<div class="col-6 mt-2">';
      $html .= '<h3 class="col-12 text-center">Results In The '.ucfirst($selectedNews).' News Category</h3>';
      $html .= '<hr>';
      $html .= '<ul class="list-unstyled mt-2">';


      foreach ($resultsData->articles as $article) {

        if (empty($article->description)) {
          // if we don't even have a description, we can't continue with this $article
          continue;
        }
        $html .= '<li class="media">';

        $html .= '<div class="col-4">';

        // check if the article has an image url and if not use a placeholder image
        $mediaImage = $article->urlToImage;
        if(empty($mediaImage)) {
          $mediaImage =  get_site_url().'/wp-content/plugins/my_news/assets/my_news_missing_image.png';
        }
        $html .= '<img src="'.$mediaImage.'"  class="img-thumbnail" />';
        $html .= '</div>';
        $html .= '<div class="media-body">';

        $html .= '<h5 class="mt-0 mb-1">'.$article->title.'</h5>';
        $html .= '<p>'.$article->description.'</p>';
        $html .= '<a class="btn btn-primary" target="_blank" href="'.$article->url.'">Read More (Opens source website)</a>';
        $html .= '</div>';
        $html .= '</li>';
        $html .= '<hr>';
      }

      $html .= '</ul>';
      $html .= '</div>';
    } else {
      $html .= '<div class="alert alert-warning">';
      $html .= '<h3>No Results Found - Please adjust your search</h3>';
      $html .= '</div>';
    }
    return $html;
  }
}
