<?php
namespace Sellastica\EntityGenerator\Generator;

use Nette\Utils\Strings;
use Sellastica\PhpGenerator\PhpClassRenderer;

class RepositoryRenderer implements IRenderer
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
	public function getNamespace(): string
	{
		return Strings::before($this->entityReflection->getNamespaceName(), '\\', -1) . '\Mapping';
	}

	/**
	 * @return string
	 */
	public function getClassName(): string
	{
		return $this->entityReflection->getShortName() . 'Repository';
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$interface = 'I' . $this->entityReflection->getShortName() . 'Repository';

		$renderer = (new PhpClassRenderer($this->getClassName(), ['Repository'], [$interface]))
			->phpBeginning()
			->namespace($this->getNamespace())
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