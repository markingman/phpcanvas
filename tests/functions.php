<?php

if (!function_exists('getallheaders')) {
    function getallheaders(): false|array {
    	return ['X-Example: example'];
    }
}
