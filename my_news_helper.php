<?php
class My_News_Helper
{
    function get_available_options($type)
    {
        switch($type) {
            case('news') :
                $options = [
                    ''              => 'Please Select',
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

   function set_defaults()
   {

   }


 }
