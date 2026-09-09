<?php

namespace PHPCanvas\Test\App\View\Layouts;

use PHPCanvas\Test\App\View\HTMLContext;
use function PHPCanvas\Test\App\View\Partials\htmlentities;

class Block
{
	public function __invoke(string $title): string
	{
		HTMLContext::ob_start();
		?>

		<div class="example-block">
			<?= htmlentities($title) ?>
		</div>

		<?php
		return HTMLContext::ob_get_clean();
	}
}
