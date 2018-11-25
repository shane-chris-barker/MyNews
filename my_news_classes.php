<?php
/**
 * Carry out tasks related to the calling of the Bing API.
 *
 * Class contains all functions required to carry out the search on the Bing api.
 *
 * @since 1.0.0
 */
class My_News_Api_Caller
{
    /**
     * Perform a GET request on the bing api with the given params.
     *
     * Function returns a response from the Bing api.
     *
     * @since 1.0.0
     * @param string $selectedNews The selected news category to query the api with
     * @param string $selectedLanguage The selected language to query the api with
     * @param string $apiKey The api key to send to the api
     * @return object An object created from json_decode that contains the api response.
     */
    function get_news($selectedNews, $selectedLanguage, $apiKey)
    {
        $url          = 'https://api.cognitive.microsoft.com/bing/v7.0/news/search/';
        $endpoint     = $this->build_query_string
        (
            $apiKey,
            $selectedNews,
            $selectedLanguage
         );
        $queryString  = $url.$endpoint;
        $response     = wp_remote_get($queryString);
        $response     = json_decode($response['body']);
        return $response;
    }

    /**
     * Build a the query string from the GET request based on passed in params.
     *
     * Function returns a query string for use with a GET request.
     *
     * @since 1.0.0
     * @param string $apiKey The api key to send to the api
     * @param string $selectedNews The selected news category to query the api with
     * @param string $selectedLanguage The selected language to query the api with
     * @return string A query string for a GET request
     */
    private function build_query_string($apiKey, $selectedNews, $selectedLanguage)
    {
        $queryString = null;
        $queryString .= '?q='.esc_html($selectedNews).'&mkt='.esc_html($selectedLanguage).'&subscription-key='.esc_html($apiKey);
        return $queryString;
    }
}
/**************************************************************************/
/**************************************************************************/
/**
 * Carry out tasks related to the lopading of assets.
 *
 * Class carries out functions relating to the CSS and Js of the plugin, including bootstrap.
 *
 * @since 1.0.0
 */
class My_News_Asset_Helper
{
    /**
     * Activate the plugins assets.
     *
     * Fire off any CSS and JS that the plugin uses.
     *
     * @since 1.0.0
     * @return bool The operation succeeded
     */
    function set_css_and_js()
    {
        $boostrapCss    = plugins_url('assets/bootstrap.min.css',  __FILE__);
        $boostrapJs     = plugins_url('assets/bootstrap.min.js',  __FILE__);
        wp_register_style('bootstrap_four_css', $boostrapCss );
        wp_enqueue_style('my_news_css');
        wp_enqueue_style('bootstrap_four_css');
        wp_enqueue_script($js);


        // add bootstraps meta
        $bootstrapMeta = '<meta name="viewport" content="width=device-width,';
        $bootstrapMeta .= 'initial-scale=1, shrink-to-fit=no">';
        add_action
        (
            'wp_head',
            $bootstrapMeta
        );

        return true;
    }
}

/**************************************************************************/
/**************************************************************************/
/**
 * Carry out HTML related tasks.
 *
 * Class builds html elements and views and returns as html strings.
 *
 * @since 1.0.0
 */
class My_News_Html_Helper
{
    /**
     * Builds alerts for displaying in the admin screen
     *
     * Function builds either error or success alerts based on the params
     *
     * @since 1.0.0
     * @param string $alertText The text to be shown on the alert.
     * @param string $alertType The type of alert to be shown.
     * @return string A string of HTML that shows an alert when echoed.
     */
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
        $html .= '<h3>'.esc_html($alertText).'</h3>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Build the settings form screen.
     *
     * function uses given parameters to build the input form for the admin screen.
     *
     * @since 1.0.0
     * @param $selectedNews The currently selected news for the news category select.
     * @param $nonce The nonce to use
     * @param string $selectedLanguage The selected Language for the language select.
     * @param string $apiKey The stored api key for showing in the text box.
     * @param array $availableLanguages The available language options for the langage select.
     * @param array $availableNews The available langnewsuage options for the news select.
     * @return string A HTML string which shows the settings form when echoed.
     */
    function build_settings_form
    (
        $nonce,
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
        $html .= 'Click <a href="'.esc_url('https://azure.microsoft.com/en-us/free/').'" target=_"blank">here</a> for more information</p>';
        $html .= '<p>Once you have an api key, will need to enter this into the form below</p>';
        $html .= '<p><strong>Please Note: </strong>Monitoring billing and usage with the Bing News search service is the responsibility ';
        $html .= 'of the user, not this plugin';
        $html .= '<form method="post" action="" id="my_news_form" class="col-12">';
        $html .= $this->build_input_element('hidden', 'settings_post', 'settings_post', 'settings_post' );
        $html .= $this->build_input_element('hidden', '_wpnonce', '_wpnonce', $nonce);
        $html .= '<div class="form-group">';
        $html .= $this->build_label_element('news', 'Please select a news category from the drop down menu');
        $html .= $this->build_select_element($selectedNews, 'news', $availableNews );
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= $this->build_label_element('languages', 'Please select the language you would like to view your news in');
        $html .= $this->build_select_element($selectedLanguage, 'languages', $availableLanguages );
        $html .= '</div>';
        $html .= '<div class="form-group">';
        $html .= $this->build_label_element('api_key', 'Bing News Search API Key');
        $html .= $this->build_input_element('password', 'api_key', 'api_key', $apiKey);
        $html .= $this->build_button('Get News', null, 'btn btn-primary mt-2');
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Build the results html.
     *
     * function uses given parameters to build the news results for the admin screen.
     *
     * @since 1.0.0
     * @param $selectedNews The currently selected news for the news category select.
     * @param string $selectedLanguage The selected Language for the language select.
     * @param object $resultsData The result of a call from Api_Caller::get_news
     * @return string A HTML string which shows the search results when echoed.
     */
    function build_results_html($selectedNews, $resultsData)
    {
        // if the call was successful and we actually have some results to show..
        if (isset($resultsData->value) && empty($resultsData->value) === false) {
            // loop the result and build html alerts to show the various aspects of the articles
            $html .= '<div class="col-6 mt-2">';
            $html .= '<h3 class="col-12 text-center">Results In The '.esc_html(ucfirst($selectedNews)).' News Category</h3>';
            $html .= '<hr>';
            $html .= '<ul class="list-unstyled mt-2">';
            foreach ($resultsData->value as $article) {

                // if we dont have an image for the article, we'll use our placeholder so it doesn't break.
                $articleImage = plugins_url('assets/my_news_missing_image.png',  __FILE__);
                if (isset($article->image)) {
                    $articleImage = $article->image->thumbnail->contentUrl;
                }

                // format the date
                $publishedDate = new \DateTime($article->datePublished);
                $html .= '<li class="media">';
                $html .= '<div class="col-2">';
                $html .= '<img src="'.esc_url($articleImage).'"  class="img-thumbnail" />';
                $html .= '</div>';
                $html .= '<div class="media-body">';
                $html .= '<h5 class="mt-0 mb-1">'.esc_html($article->name).'</h5>';
                $html .= '<p>'.esc_html($article->description).'</p>';
                $html .= '<div class="row">';
                $html .= '<span class="badge badge-pill badge-primary">Published by: ';
                $html .= esc_html($article->provider[0]->name).'</span>';
                $html .= '<span class="ml-1 badge badge-pill badge-primary">';
                $html .= 'Published on: '.esc_html($publishedDate->format('d-m-Y G:i:s')).'</span>';
                $html .= '</div>';
                $html .= '<div class="row">';
                $html .= '<a class="btn btn-primary mt-1" target="_blank" ';
                $html .= 'href="'.esc_url($article->url).'">Read More (Will open publishing website)</a>';
                $html .= '</div>';

                $html .= '</div>';
                $html .= '</li>';
                $html .= '<hr>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        } else {
            $html .= '<div class="alert alert-danger text-center mt-5" role="alert">';
            $html .= '<h3>No Results Found - Please adjust your search</h3>';
            $html .= '</div>';
        }
        return $html;
    }

    /**
     * Build a select element.
     *
     * function uses given parameters to build a html string select element.
     *
     * @since 1.0.0
     * @param string $selectedOption The default option.
     * @param string $selectName The name for the select element.
     * @param array $options The options that should available in the select.
     * @return string A HTML string which shows the select element when echoed.
     */
    function build_select_element($selectedOption, $selectName, $options = [] )
    {
        $html = '<select id="'.esc_html($selectName).'" name="'.esc_html($selectName).'" class="form-control">';
        foreach($options as $key => $value) {
            if ($selectedOption !== false) {
                if ($selectedOption === $key) {
                    $selected = 'selected';
                } else {
                    $selected = null;
                }
            }
            $html .= '<option value="'.esc_html($key).'" '.esc_html($selected).'>'.esc_html($value).'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Build a label element.
     *
     * function uses given parameters to build a html string label element.
     *
     * @since 1.0.0
     * @param string $for The "for" option.
     * @param string $text The desired text for the label
     * @return string A HTML string which shows the label element when echoed.
     */
    private function build_label_element($for, $text)
    {
        return $html = '<label for="'.$for.'">'.esc_html($text).'</label>';
    }

    /**
     * Build an input html element.
     *
     * function uses given parameters to build a html input element.
     *
     * @since 1.0.0
     * @param string $type The type of input to create.
     * @param string $id The desired id for the input.
     * @param string $name The desired name for the input.
     * @param string|null $currentValue The value for the input - nullable
     * @return string A HTML string which shows the input element when echoed.
     */
    private function build_input_element($type, $id, $name, $currentValue = null)
    {
        $html = '<input class="form-control" type="'.esc_html($type).'" id="'.esc_html($id).'"';
        if ($currentValue !== null && $currentValue !== false) {
             $html .= ' value="'.esc_html($currentValue).'" ';
         }
         return $html .= ' name="'.esc_html($name).'"/>';
     }

     /**
      * Build an input html element as a button.
      *
      * function uses given parameters to build a html input element.
      *
      * @since 1.0.0
      * @param string $text The text to show on the button.
      * @param string|null $id The desired id for the button - nullable.
      * @param string|null $class The desired class for the input - nullable.
      * @return string A HTML string which shows the button input element when echoed.
      */
     private function build_button($text, $id = null, $class = null)
     {
         return $html = '<input type="submit" id="'.esc_html($id).'" class="'.esc_html($class).'" value="'.esc_html($text).'" \>';
     }
}
