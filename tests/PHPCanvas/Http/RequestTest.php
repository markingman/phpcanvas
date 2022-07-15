<?php // $Id: CacheTest.php 507 2016-01-15 11:56:13Z dev $

use PHPCanvas\Http\Request;

#var_dump(realpath('../../../src/classes/PHPCanvas/Http/Request.php'));
#exit;
require '../../../src/classes/PHPCanvas/Http/RequestInterface.php';
require '../../../src/classes/PHPCanvas/Http/Request.php';


$url = '/foo/bar';
exit('here');
$get = ['a' => 'A'];
$Request = new Request($url, null, $get);

var_dump($Request->_GET);
$Request->_GET['a'] = 'B';
var_dump($Request->_GET);
var_dump($_GET);

exit('done');