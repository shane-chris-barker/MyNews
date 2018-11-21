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
                    'es-es' => 'Spain'
                ];
                asort($options);
                break;
        }
        return $options;
    }
 }
