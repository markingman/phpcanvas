<?php

class MyTest {}

$a = [
	'foo' => function(): MyTest {
	return new MyTest;
}
];


$b = $a['foo']();

var_export($b);

