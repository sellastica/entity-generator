<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\PhpGenerator\PhpClassRenderer;

class IRepositoryRenderer
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
		$entityShortName = $this->entityReflection->getShortName();
		$interface = 'I' . $entityShortName . 'Repository';
		$entityCollection = $this->entityReflection->getShortName() . 'Collection';

		$renderer = (new PhpClassRenderer($interface, ['IRepository']))
			->classType('interface')
			->phpBeginning()
			->namespace($this->entityReflection->getNamespaceName())
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