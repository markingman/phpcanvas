<?php

return function (PHPCanvas\ContainerInterface $c) {
	return new PHPCanvas\Mail\Mail($c['Log'], $c['Config']->MAIL_SENDER, $c['Config']->MAIL_ON, !empty($c['Config']->MAIL_REGX) ? $c['Config']->MAIL_REGX : '');
};
