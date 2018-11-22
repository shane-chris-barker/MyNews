<?php
/**
 * Carry out generic functions related to the plugin.
 *
 * Class does dirty tasks such as returning various config options.
 *
 * @since 1.0.0
 */
class My_News_Helper
{
    /**
     * Get options to build select elements with.
     *
     * Function returns array with $key => $value pairs for
     * populating the select elements on the admin page.
     *
     * @since 1.0.0
     *
     * @param string $type The type of options array to build.
     * @return array $key => $value array of options.
     */
    function get_available_options($type)
    {
        switch($type) {
            case('news') :
                $options = [
                    'sports'        => 'Sports',
                    'business'      => 'Business',
                    'general'       => 'General',
                    'health'        => 'Health',
                    'science'       => 'Science',
                    'technology'    => 'Technology',
                    'entertainment' => 'Entertainment',
                ];

                ksort($options);
                break;
            case('languages') :
                $options = [
                    'es-us' => 'English',
                    'fr-fr' => 'French',
                    'it-it' => 'Italian',
                    'ru-ru' => 'Russian',
                    'pl-pl' => 'Polish',
                    'es-es' => 'Spain',
                    'de-de' => 'German'
                ];
                asort($options);
                break;
        }
        return $options;
    }

    /**
     * Get the allowed html elements for the results list.
     *
     * function return an array of permitted elements and their attributes.
     *
     *
     * @since 1.0.0
     *
     * @return array The allowed html elements.
     */
    function get_allowed_results_html()
    {
        return $allowedResultsHtml = [
            'a' => [
                'href'      => [],
                'target'    => [],
                'id'        => [],
                'class'     => [],
            ],
            'div' => [
                'class' => [],
                'id'    => [],
                'role'  => []
            ],
            'span' => [
                'class' => [],
                'id'    => [],
            ],
            'h5' => [
                'id'    => [],
                'class' => []
            ],
            'img' => [
                'id'    => [],
                'class' => [],
                'src'   => []
            ],
            'br' => [],
            'li' => [
                'class' => []
            ],
            'ul' => [
                'class' => []
            ],
            'hr' => [],
            'h3' => [],
            'p'  => []
        ];
    }

    /**
     * Get The allowed protocols for the results html.
     *
     * Function returns a string of allowed protocols.
     *
     * @since 1.0.0
     *
     * @return string the allowed protocols.
     */
    function get_allowed_protocols()
    {
        return $allowedProtocols = 'http, https';
    }

    /**
     * Get the allowed html elements for the results list.
     *
     * function return an array of permitted elements and their attributes.
     *
     *
     * @since 1.0.0
     *
     * @return array The allowed html elements.
     */
    function get_allowed_form_html()
    {
        return $allowedFormHtml = [
            'div' => [
                'class' => [],
                'id'    => [],
            ],
            'h3' => [
                'class' => []
            ],

            'form' => [
                'class'     => [],
                'id'        => [],
                'action'    => [],
                'method'    => []
            ],
            'input' => [
                'type'      => [],
                'class'     => [],
                'id'        => [],
                'name'      => [],
                'value'     => []
            ],
            'label' => [
                'for'    => []
            ],
            'br' => [],
            'select' => [
                'class' => [],
                'name'  => [],
                'id'    => []
            ],
            'option' => [
                'value'     => [],
                'selected'  => []
            ],
            'hr' => [],
            'p'  => [],
            'a' => [
                'target'    => [],
                'href'      => []
            ]
        ];
    }
 }
