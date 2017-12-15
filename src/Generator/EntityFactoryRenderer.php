<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\PhpGenerator\PhpClassRenderer;
use Sellastica\PhpGenerator\PhpMethodParameterRenderer;
use Sellastica\Reflection\ReflectionClass;

class EntityFactoryRenderer implements IRenderer
{
	/** @var ReflectionClass */
	private $entityReflection;


	/**
	 * @param ReflectionClass $entityReflection
	 */
	public function __construct(
		ReflectionClass $entityReflection
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
		return $this->entityReflection->getShortName() . 'Factory';
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$renderer = (new PhpClassRenderer($this->getClassName(), ['EntityFactory']))
			->phpBeginning()
			->namespace($this->getNamespace())
			->import('Sellastica\Entity\IBuilder')
			->import('Sellastica\Entity\Entity\IEntity')
			->import('Sellastica\Entity\Entity\EntityFactory');

		$renderer
			->annotation()
			->method('build(IBuilder $builder, bool $initialize = true, int $assignedId = null)', $this->entityReflection->getShortName())
			->see($this->entityReflection->getShortName());

		//doInitialize method
		$renderer
			->createMethod('doInitialize')
			->addParameter(PhpMethodParameterRenderer::fromName('entity')->type('IEntity'))
			->createAnnotation()
			->param('entity', 'IEntity|' . $this->entityReflection->getShortName());

		//getEntityClass method
		$renderer
			->createMethod('getEntityClass')
			->return('string')
			->addBody(sprintf('return %s::class;', $this->entityReflection->getShortName()))
			->createAnnotation()
			->return('string');

		return $renderer->render();
	}
}