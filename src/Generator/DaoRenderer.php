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
		$builder = '\\' . $this->entityReflection->getName() . 'Builder';
		$renderer = (new PhpClassRenderer($this->getClassName(), ['\\' . $this->getExtendingClass()]))
			->phpBeginning()
			->namespace($this->getNamespace());

		$renderer
			->annotation()
			->see('\\' . $this->entityReflection->getName())
			->property('$mapper', $this->getMapperClass());

		//getBuilder method
		$properties = [];
		foreach ($this->entityReflection->filterProperties('required') as $property) {
			$properties[] = '$data->' . $property->getName();
		}

		$renderer
			->createMethod('getBuilder', 'protected')
			->return('\\' . \Sellastica\Entity\IBuilder::class)
			->addParameter(PhpMethodParameterRenderer::fromName('data'))
			->addParameter(PhpMethodParameterRenderer::fromName('first')->defaultValue(null))
			->addParameter(PhpMethodParameterRenderer::fromName('second')->defaultValue(null))
			->addBody(sprintf('return %s::create(%s)', $builder, implode(', ', $properties)))
			->addBody("\t" . '->hydrate($data);')
			->createAnnotation()
			->inheritDoc();

		//mongo completeEntity method
		if ($this->isMongoDescendant()) {
			$entityParamName = lcfirst($this->entityReflection->getShortName());
			$renderer
				->createMethod('completeEntity', 'protected')
				->return('void')
				->addParameter(PhpMethodParameterRenderer::fromName($entityParamName)->type('\\' . \Sellastica\Entity\Entity\IEntity::class))
				->addParameter(PhpMethodParameterRenderer::fromName('data')->type('\\' . \Sellastica\MongoDB\Model\BSONDocument::class))
				->createAnnotation()
				->param($entityParamName, '\\' . \Sellastica\Entity\Entity\IEntity::class . '|\\' . $this->entityReflection->getName())
				->param('data', '\\' . \Sellastica\MongoDB\Model\BSONDocument::class);
		}

		//getEmptyCollection method
		$collection = '\\' . $this->entityReflection->getName() . 'Collection';
		$renderer
			->createMethod('getEmptyCollection')
			->return('\\' . \Sellastica\Entity\Entity\EntityCollection::class)
			->addBody("return new $collection;")
			->createAnnotation()
			->return($collection);

		return $renderer->render();
	}

	/**
	 * @return bool
	 */
	private function isMongoDescendant(): bool
	{
		return in_array(\Sellastica\MongoDB\Entity\IMongoObject::class, $this->entityReflection->getInterfaceNames());
	}

	/**
	 * @return string
	 */
	private function getExtendingClass(): string
	{
		return $this->isMongoDescendant()
			? \Sellastica\MongoDB\Mapping\MongoDao::class
			: \Sellastica\Entity\Mapping\Dao::class;
	}

	/**
	 * @return string
	 */
	private function getMapperClass(): string
	{
		return $this->isMongoDescendant()
			? $this->entityReflection->getShortName() . 'Mapper'
			: $this->entityReflection->getShortName() . 'DibiMapper';
	}
}