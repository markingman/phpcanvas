<?php
// $t = 1000000;
// $ms = [];
// foreach (range(1, $t) as $i) {
// 	$length = rand(4, 32);
//     $s = substr(strtr(base64_encode(random_bytes($length)), '+/', '-_'), 0, $length);
// // 	echo crc32($s), PHP_EOL;exit;
// 	$m = microtime(true);
// 	$a = crc32($s);
// 	$ms[] = $m - microtime(true);
// }
// 
// var_export(number_format(array_sum($ms)/$t, 10));
// exit;
// 
// class A {
// 	function __construct(string $a = null)
// 	{
// 
// 	}
// 	function foo(string $a)
// 	{
// 
// 	}
// }
// 
// class B extends A {
// 	function __construct(string $a = null, int $b = null)
// 	{
// 
// 	}
// 	function foo(string $a)
// 	{
// 
// 	}
// }
// 
// $B = new B;
// 
// 
// exit;
const METHODS = [
	'GET' => 1,//1
	'HEAD' => 2,//2
	'POST' => 4,//4
	'PUT' => 8,//5
	'DELETE' => 16,//7
	'CONNECT' => 32,
	'OPTIONS' => 128,
	'TRACE' => 256,
	'PATCH' => 512,
];
$sum = 0;
foreach (METHODS as $method => $test) {
	$sum += $test;
}
echo "Sum: $sum", PHP_EOL;
$has = METHODS['GET'] + METHODS['POST']/* + METHODS['PATCH']*/;

var_export($has);
echo PHP_EOL;

foreach (METHODS as $method => $test) {
	echo "Has $method: ", var_export($test & $has, true), PHP_EOL;
}

echo PHP_EOL;

