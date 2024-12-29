<?php

if (!function_exists('getallheaders')) {
    /** @return array<string, string>*/
    function getallheaders(): array {
    	return ['X-Example' => 'example'];
    }
}
