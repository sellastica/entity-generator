<?php
namespace Sellastica\EntityGenerator\Generator;

use Nette\Utils\Strings;
use Sellastica\PhpGenerator\PhpClassRenderer;

class RepositoryRenderer
{
	/** @var \ReflectionClass */
	private $entityReflection;


	/**
	 * @param \ReflectionClass $entityReflection
	 */
	public function __construct(
		\ReflectionClass $entityReflection
	)
	{
		$this->entityReflection = $entityReflection;
	}

	/**
	 * @return string
	 */
	public function render()
	{
		$namespace = Strings::before($this->entityReflection->getNamespaceName(), '\\', -1) . '\Mapping';
		$interface = 'I' . $this->entityReflection->getShortName() . 'Repository';

		$renderer = (new PhpClassRenderer($this->entityReflection->getShortName() . 'Repository', ['Repository'], [$interface]))
			->phpBeginning()
			->namespace($namespace)
			->import('Sellastica\Entity\Mapping\Repository')
			->import($this->entityReflection->getName())
			->import($this->entityReflection->getNamespaceName() . '\\I' . $this->entityReflection->getShortName() . 'Repository');

		$renderer
			->annotation()
			->property('dao', $this->entityReflection->getShortName() . 'Dao')
			->see($this->entityReflection->getShortName());

		return $renderer->render();
	}
}