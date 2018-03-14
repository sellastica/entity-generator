<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\PhpGenerator\PhpClassRenderer;
use Sellastica\PhpGenerator\PhpMethodParameterRenderer;
use Sellastica\Reflection\ReflectionClass;
use Sellastica\Utils\Strings;

class DaoRenderer implements IRenderer
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
		return Strings::before($this->entityReflection->getNamespaceName(), '\\', -1) . '\Mapping';
	}

	/**
	 * @return string
	 */
	public function getClassName(): string
	{
		return $this->entityReflection->getShortName() . 'Dao';
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$builder = $this->entityReflection->getShortName() . 'Builder';
		$renderer = (new PhpClassRenderer($this->getClassName(), ['Dao']))
			->phpBeginning()
			->namespace($this->getNamespace())
			->import('Sellastica\Entity\IBuilder')
			->import('Sellastica\Entity\Mapping\Dao')
			->import(Strings::removeFromBeginning($this->entityReflection->getName(), '\\'))
			->import(Strings::removeFromBeginning($this->entityReflection->getName(), '\\') . 'Builder')
			->import('Sellastica\Entity\Entity\EntityCollection');

		$renderer
			->annotation()
			->see($this->entityReflection->getShortName())
			->property('$mapper', $this->entityReflection->shortName . 'DibiMapper');

		//getBuilder method
		$properties = [];
		foreach ($this->entityReflection->filterProperties('required') as $property) {
			$properties[] = '$data->' . $property->getName();
		}

		$renderer
			->createMethod('getBuilder', 'protected')
			->return('IBuilder')
			->addParameter(PhpMethodParameterRenderer::fromName('data'))
			->addParameter(PhpMethodParameterRenderer::fromName('first')->defaultValue(null))
			->addParameter(PhpMethodParameterRenderer::fromName('second')->defaultValue(null))
			->addBody(sprintf('return %s::create(%s)', $builder, implode(', ', $properties)))
			->addBody("\t" . '->hydrate($data);')
			->createAnnotation()
			->inheritDoc();

		//getEmptyCollection method
		$collection = $this->entityReflection->getShortName() . 'Collection';
		$renderer->import($this->entityReflection->getNamespaceName() . "\\$collection");
		$renderer
			->createMethod('getEmptyCollection')
			->return('EntityCollection')
			->addBody("return new $collection;")
			->createAnnotation()
			->return("EntityCollection|$collection");

		return $renderer->render();
	}
}