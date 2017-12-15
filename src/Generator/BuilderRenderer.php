<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\PhpGenerator\PhpClassRenderer;
use Sellastica\PhpGenerator\PhpMethodParameterRenderer;
use Sellastica\Reflection\ReflectionClass;

class BuilderRenderer implements IRenderer
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
		return $this->entityReflection->getShortName() . 'Builder';
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$requiredProperties = $this->entityReflection->filterProperties('required');
		$optionalProperties = $this->entityReflection->filterProperties('optional');

		$renderer = (new PhpClassRenderer($this->getClassName(), [], ['IBuilder']))
			->phpBeginning()
			->namespace($this->getNamespace())
			->import('Sellastica\Entity\IBuilder')
			->import('Sellastica\Entity\TBuilder');

		$useStatements = $this->entityReflection->getUseStatements(array_merge($requiredProperties, $optionalProperties));
		foreach ($useStatements as $useStatement) {
			$renderer->import($useStatement);
		}

		$renderer->annotation()->see($this->entityReflection->getShortName());
		$renderer->trait('TBuilder');

		//class required properties
		foreach ($requiredProperties as $property) {
			$renderer->createProperty($property->getName())
				->defaultValue($property->getDefaultValue())
				->createAnnotation()
				->var($property->renderTypes());
		}

		//class optional properties
		foreach ($optionalProperties as $property) {
			$renderer->createProperty($property->getName())
				->defaultValue($property->getDefaultValue())
				->createAnnotation()
				->var($property->renderTypes());
		}

		//constructor
		if (sizeof($requiredProperties)) {
			$constructor = $renderer->createConstructor();
			$annotation = $constructor->createAnnotation();
			foreach ($requiredProperties as $property) {
				$constructor->addParameter(PhpMethodParameterRenderer::fromReflectionPropertyMapper($property));
				$constructor->propertyAssignation($property->getName());
				$annotation->param($property->getName(), $property->renderTypes());
			}
		}

		//getters for required properties
		foreach ($requiredProperties as $property) {
			$getter = $renderer->createGetter($property->getName());
			$getter
				->createAnnotation()
				->return($property->renderTypes());
			if (!$property->isNullable()) {
				$getter->return($property->getType());
			}
		}

		if ($arraySize = sizeof($optionalProperties)) {
			foreach ($optionalProperties as $property) {
				//getters
				$getter = $renderer->createGetter($property->getName());
				$getter
					->createAnnotation()
					->return($property->renderTypes());
				if (!$property->isNullable()) {
					$getter->return($property->getType());
				}

				//setters
				$setter = $renderer->createSetter($property->getName(), $property->getName(), true);
				$setter->addParameter(PhpMethodParameterRenderer::fromReflectionPropertyMapper($property));
				$setter
					->createAnnotation()
					->param($property->getName(), $property->renderTypes())
					->return('$this');
			}
		}

		//generate ID method
		$renderer->createMethod('generateId')
			->addBody('return !' . $this->entityReflection->getShortName() . '::isIdGeneratedByStorage();')
			->return('bool')
			->createAnnotation()
			->return('bool');
		//build method
		$renderer->createMethod('build')
			->addBody('return new ' . $this->entityReflection->getShortName() . '(' . '$this' . ');')
			->return($this->entityReflection->getShortName())
			->createAnnotation()
			->return($this->entityReflection->getShortName());
		//static create method
		$method = $renderer->createMethod('create')
			->static()
			->return('self');
		$annotation = $method->createAnnotation()
			->return('self');

		$body = "return new self(";
		$injects = [];
		foreach ($requiredProperties as $property) {
			$annotation->param($property->getName(), $property->renderTypes());
			$method->addParameter(PhpMethodParameterRenderer::fromReflectionPropertyMapper($property));
			$injects[] = '$' . $property->getName();
		}

		$body .= implode(', ', $injects) . ");";
		$method->addBody($body);

		return $renderer->render();
	}
}