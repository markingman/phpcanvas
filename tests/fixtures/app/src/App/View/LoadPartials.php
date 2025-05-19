<?php declare(strict_types=1);

namespace PHPCanvas\Test\App\View;

use Closure;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

/** Example namespace function loader - consider remote code execution issues in production */
class LoadPartials
{
	public function __construct(
		protected string $dir,
		protected string $path,
		protected string $hash = '',
		protected int $maxDepth = 10,
	) {
	}

	public function __invoke(bool $rebuild = false): void
	{
		if (!$rebuild and file_exists($this->path)) {
			$this->load();

			return;
		}

		$partials = '';

		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir, FilesystemIterator::SKIP_DOTS));
		$it->setMaxDepth($this->maxDepth);

		foreach ($it as $fi) {
			if ($fi instanceof SplFileInfo and $fi->isFile() and $fi->getExtension() === 'php') {
				if (($partial = file_get_contents($fi->getPathname())) === false) {
					throw new RuntimeException("Failed to read file: " . $fi->getPathname());
				}
				$partial = substr($partial, 5);// presumes single <?php prefix
				$partials .= '// ' . $fi->getPathname() . PHP_EOL . $partial . PHP_EOL . PHP_EOL;
			}
		}

		if (file_put_contents($this->path, '<?php' . PHP_EOL . $partials) === false) {
			throw new RuntimeException("Failed to write temporary file: $this->path");
		}

		$this->load();
	}

	private function load(): void
	{
		if ($this->hash and $this->hash !== hash_file('sha256', $this->path)) {
			throw new RuntimeException("Hash mismatch: failed to include $this->path");
		}

		if (!Closure::bind(function ($__file__): mixed {
			return include $__file__;
		}, null, null)($this->path)) {
			throw new RuntimeException("Failed to include existing temporary file: $this->path");
		}
	}
}
