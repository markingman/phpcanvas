<?php // $Id: register.Links.php 720 2017-09-07 12:56:50Z dev $

return function ($c) {
	return new PHPCanvas\Routing\Links($c['Router'], $c['Config']->SITE_URL, $c['Config']->SITE_PATH);
};
