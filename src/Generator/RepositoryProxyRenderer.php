<?php
namespace Sellastica\EntityGenerator\Generator;

use Nette\Utils\Strings;
use Sellastica\PhpGenerator\PhpClassRenderer;

class RepositoryProxyRenderer
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
		$entityShortName = $this->entityReflection->getShortName();
		$interface = 'I' . $entityShortName . 'Repository';
		
		$renderer = (new PhpClassRenderer($entityShortName . 'RepositoryProxy', ['RepositoryProxy'], [$interface]))
			->phpBeginning()
			->namespace($namespace)
			->import('Sellastica\Entity\Mapping\RepositoryProxy')
			->import($this->entityReflection->getNamespaceName() . '\\I' . $entityShortName . 'Repository')
			->import($this->entityReflection->getName());

		$renderer
			->annotation()
			->method('getRepository()', $entityShortName . 'Repository')
			->see($entityShortName);

		return $renderer->render();
	}
}