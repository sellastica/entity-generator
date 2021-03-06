<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\PhpGenerator\PhpClassRenderer;
use Sellastica\PhpGenerator\PhpMethodParameterRenderer;
use Sellastica\Reflection\ReflectionClass;

class ModifierRenderer implements IRenderer
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
		return $this->entityReflection->getShortName() . 'Modifier';
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$renderer = (new PhpClassRenderer($this->getClassName(), [], ['IModifier']))
			->phpBeginning()
			->namespace($this->getNamespace())
			->import('Sellastica\Entity\IModifier')
			->import('Sellastica\Entity\TModifier');

		$properties = $this->entityReflection->filterProperties('modifiable');
		foreach ($this->entityReflection->getUseStatements($properties) as $useStatement) {
			$renderer->import($useStatement);
		}

		$renderer->annotation()->see($this->entityReflection->getShortName());
		$renderer->trait('TModifier');
		$renderer->createProperty('data')
			->defaultValue('[]')
			->createAnnotation()
			->var('array');

		if ($arraySize = sizeof($properties)) {
			foreach ($properties as $property) {
				//setters
				$renderer
					->createMethod($property->getName())
					->return('self')
					->addParameter(PhpMethodParameterRenderer::fromReflectionPropertyMapper($property, !$property->hasAnnotation('no-typehint')))
					->addBody(sprintf('$this->data[\'%s\'] = $%s;', $property->getName(), $property->getName()))
					->addBody('return $this;')
					->createAnnotation()
					->param($property->getName(), !$property->hasAnnotation('no-typehint') ? $property->renderTypes() : null)
					->return('self');
			}
		}

		return $renderer->render();
	}
}