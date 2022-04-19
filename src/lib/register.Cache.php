<?php // $Id: register.Cache.php 720 2017-09-07 12:56:50Z dev $

return function ($c) {
	return new PHPCanvas\Cache\Cache($c['Config']->DIR_CACHE, $c['Config']->PRM_DIR_COPY);
};
