<?php

/**
 * CityDrug Global Helper Functions
 */

if (!function_exists('url_without')) {
    /**
     * Return current URL with specified query params removed.
     */
    function url_without(array $keys): string
    {
        $params = request()->query();
        foreach ($keys as $key) {
            unset($params[$key]);
        }
        $query = http_build_query($params);
        return request()->url() . ($query ? '?' . $query : '');
    }
}

if (!function_exists('url_with')) {
    /**
     * Return current URL with specified query params merged/added.
     */
    function url_with(array $merge): string
    {
        $params = array_merge(request()->query(), $merge);
        // Remove null values
        $params = array_filter($params, fn($v) => $v !== null);
        $query  = http_build_query($params);
        return request()->url() . ($query ? '?' . $query : '');
    }
}
