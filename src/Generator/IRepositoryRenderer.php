<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\PhpGenerator\PhpClassRenderer;

class IRepositoryRenderer implements IRenderer
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
		return $this->entityReflection->getNamespaceName();
	}

	/**
	 * @return string
	 */
	public function getClassName(): string
	{
		return 'I' . $this->entityReflection->getShortName() . 'Repository';
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$entityShortName = $this->entityReflection->getShortName();
		$entityCollection = $this->entityReflection->getShortName() . 'Collection';

		$renderer = (new PhpClassRenderer($this->getClassName(), ['IRepository']))
			->classType('interface')
			->phpBeginning()
			->namespace($this->getNamespace())
			->import('Sellastica\Entity\Configuration')
			->import('Sellastica\Entity\Mapping\IRepository');

		$renderer
			->annotation()
			->method('find(int $id)', $entityShortName)
			->method('findOneBy(array $filterValues)', $entityShortName)
			->method('findOnePublishableBy(array $filterValues, Configuration $configuration = null)', $entityShortName)
			->method('findAll(Configuration $configuration = null)', $entityShortName . '[]|' . $entityCollection)
			->method('findBy(array $filterValues, Configuration $configuration = null)', $entityShortName . '[]|' . $entityCollection)
			->method('findByIds(array $idsArray, Configuration $configuration = null)', $entityShortName . '[]|' . $entityCollection)
			->method('findPublishable(int $id)', $entityShortName . '[]|' . $entityCollection)
			->method('findAllPublishable(Configuration $configuration = null)', $entityShortName . '[]|' . $entityCollection)
			->method('findPublishableBy(array $filterValues, Configuration $configuration = null)', $entityShortName . '[]|' . $entityCollection)
			->see($entityShortName);

		return $renderer->render();
	}
}