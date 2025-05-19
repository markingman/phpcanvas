<?php

namespace PHPCanvas\Test\App\View\Templates;

use function PHPCanvas\Test\App\View\Partials\htmlspecialchars;

class DefaultTemplate extends AbstractTemplate
{
	public static function __invoke(string $title,string $content): string
	{
		static::ob_start();

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?= htmlspecialchars($title) ?></title>
		<link href="<?= static::path_css('main.css') ?>" rel="" type="stylesheet">
	</head>
	<body><?= $content ?></body>
</html>
<?php

		return static::ob_get_clean();
	}
}
