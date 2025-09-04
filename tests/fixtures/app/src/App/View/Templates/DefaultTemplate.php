<?php

namespace PHPCanvas\Test\App\View\Templates;

use function PHPCanvas\Test\App\View\Partials\htmlspecialchars;

class DefaultTemplate extends AbstractTemplate
{
	public function __construct(
		protected string $title,
		protected string $content,
	) {
	}

	public function __invoke(): string
	{
		static::ob_start();

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?= htmlspecialchars($this->title) ?></title>
		<link href="<?//= static::path_css('main.css') ?>" rel="" type="stylesheet">
	</head>
	<body>
		<main>
			<?= $this->content ?>
		</main>
	</body>
</html>
<?php

		return static::ob_get_clean();
	}
}
