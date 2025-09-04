<?php

namespace PHPCanvas\Test\App\View\Layouts;

// use function PHPCanvas\Test\App\View\Partials\htmlentities;
use function PHPCanvas\Test\App\View\Partials\htmlentities;

class Block /*implements StringView*/
{
	public function __construct(
		protected string $title
	){
	}

	public function __invoke(): string
	{
		ob_start();
?>
<div class="example-block">
	<?= htmlentities($this->title) ?>
</div>
<?php
		return ob_get_clean();
	}
}
