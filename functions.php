<?php

if (function_exists('__') === false) {
    function __($text)
    {
        return $text;
    }
}

if (function_exists('defineGuzzleHttpOption') === false) {
    function defineGuzzleHttpOption(array $options = []): array
    {
        $options['timeout'] = 5;
        $options['connect_timeout'] = 5;

        if (array_key_exists('proxy', $options) === false) {
            $proxy  = \App\Utils\Env::get('proxy');
            if ($proxy !== '') {
                $options['proxy'] = $proxy;
            }
        }

        return $options;
    }
}

