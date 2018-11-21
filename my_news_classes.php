<?php
class Api_Caller
{
  function get_news($selectedNews, $selectedLanguage, $apiKey, $dateFrom, $dateTo = null)
  {
    $url          = 'https://api.cognitive.microsoft.com/bing/v7.0/news/search/';
    $endpoint     = $this->build_query_string($apiKey, $selectedNews, $selectedLanguage, $dateFrom, isset($dateTo) ? $dateTo : null);
    $queryString  = $url.$endpoint;
    $response     = wp_remote_get($queryString);
    $response     = json_decode($response['body']);
    return $response;
  }

  function build_query_string($apiKey, $selectedNews, $selectedLanguage, $dateFrom, $dateTo = null)
  {

    $queryString = null;
    $queryString .= '?q='.$selectedNews.'&mkt='.$selectedLanguage.'&subscription-key='.$apiKey;
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
                $html = '<div class="alert alert-danger text-center container mt-5" role="alert">';
                break;
            case('success') :
                $html = '<div class="alert alert-success text-center container mt-5" role="alert">';
                break;
        }
        $html .= '<h3>'.$alertText.'</h3>';
        $html .= '</div>';
        return $html;
    }

    function build_settings_form
    (
        $selectedNews,
        $selectedLanguage,
        $apiKey,
        $availableLanguages = [],
        $availableNews = []
    )
    {
        $html .= '<div class="col-4 mt-2">';
        $html .= '<h3 class="my_news_mt_10">My News</h3>';
        $html .= '<p><strong>Please Note:</strong> News articles are pulled from';
        $html .= ' Bing and you will need an api key from Microsoft for this plugin to work.';
        $html .= '<br><br><strong>Microsoft offer a free tier service which allows 3000 searches per month at no charge.</strong> - ';
        $html .= 'Click <a href="https://azure.microsoft.com/en-us/free/" target=_"blank">here</a> for more information</p>';
        $html .= '<p>Once you have an api key, will need to enter this into the form below</p>';
        $html .= '<p><strong>Please Note: </strong>Monitoring billing and usage with the Bing News search service is the responsibility ';
        $html .= 'of the user, not this plugin';
        $html .= '<form method="post" action="" id="my_news_form" class="col-12">';
        $html .= $this->build_input_element('hidden', 'settings_post', 'settings_post', 'settings_post' );
        $html .= '<div class="form-group">';
        $html .= $this->build_label_element('news', 'Please select a news category from the drop down menu');
        $html .= $this->build_select_element($selectedNews, 'news', $availableNews );
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= $this->build_label_element('languages', 'Please select the language you would like to view your news in');
        $html .= $this->build_select_element($selectedLanguage, 'languages', $availableLanguages );
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= $this->build_label_element('api_key', 'newsAPI.org API Key');
        $html .= $this->build_input_element('text', 'api_key', 'api_key', $apiKey);
        $html .= $this->build_button('submit', 'Get News', null, 'btn btn-primary mt-2');
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    function build_results_html($selectedNews, $resultsData)
    {
        // if the call was successful and we actually have some results to show..
        if (isset($resultsData->value) && empty($resultsData->value) === false) {
            // loop the result and build html alerts to show the various aspects of the articles
            $html .= '<div class="col-6 mt-2">';
            $html .= '<h3 class="col-12 text-center">Results In The '.ucfirst($selectedNews).' News Category</h3>';
            $html .= '<hr>';
            $html .= '<ul class="list-unstyled mt-2">';
            foreach ($resultsData->value as $article) {

                // if we dont have an image for the article, we'll use our placeholder so it doesn't break.
                $articleImage = get_site_url().'/wp-content/plugins/my_news/assets/my_news_missing_image.png';
                if (isset($article->image)) {
                    $articleImage = $article->image->thumbnail->contentUrl;
                }

                // format the date

                $publishedDate = new \DateTime($article->datePublished);
                $html .= '<li class="media">';
                $html .= '<div class="col-2">';
                $html .= '<img src="'.$articleImage.'"  class="img-thumbnail" />';
                $html .= '</div>';
                $html .= '<div class="media-body">';
                $html .= '<h5 class="mt-0 mb-1">'.$article->name.'</h5>';
                $html .= '<p>'.$article->description.'</p>';
                $html .= '<div class="row">';
                $html .= '<span class="badge badge-pill badge-primary">Published by: '.$article->provider[0]->name.'</span>';
                $html .= '<span class="ml-1 badge badge-pill badge-primary">Published on: '.$publishedDate->format('d-m-Y G:i:s').'</span>';

                $html .= '</div>';
                $html .= '<div class="row">';

                $html .= '<a class="btn btn-primary mt-1" target="_blank" href="'.$article->url.'">Read More (Opens source website)</a>';
                $html .= '</div>';

                $html .= '</div>';
                $html .= '</li>';
                $html .= '<hr>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        } else {
            if (isset($resultsData->value) && empty($resultsData->value)) {
                $html .= '<div class="alert alert-info text-center mt-5" role="alert">';
                $html .= '<h3>Click Get News To Begin</h3>';
            } else {
                $html .= '<div class="alert alert-danger text-center mt-5" role="alert">';
                $html .= '<h3>No Results Found - Please adjust your search</h3>';
            }
            $html .= '</div>';
        }
        return $html;
    }

    function build_select_element($selectedOption, $selectName, $options = [] )
    {
        $html = '<select id="'.$selectName.'" name="'.$selectName.'" class="form-control">';
        foreach($options as $key => $value) {
            if ($selectedOption !== false) {
                if ($selectedOption === $key) {
                    $selected = 'selected';
                } else {
                    $selected = null;
                }
            }
            $html .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    function build_label_element($for, $text)
    {
        return $html = '<label for="'.$for.'">'.$text.'</label>';
    }

    function build_input_element($type, $id, $name, $currentValue = null)
    {
         $html = '<input class="form-control" type="'.$type.'" id="'.$id.'" name="'.$name.'"';

         if ($currentValue !== null && $currentValue !== false) {
             $html .= 'value="'.$currentValue.'"';
         }

         return $html .= '/>';
     }

     function build_button($type, $text, $id = null, $class = null)
     {
         return $html = '<input type="'.$type.'" id="'.$id.'" class="'.$class.'" value="'.$text.'"\>';
     }
}
