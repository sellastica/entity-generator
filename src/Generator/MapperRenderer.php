<?php
namespace Sellastica\EntityGenerator\Generator;

class MapperRenderer implements IRenderer
{
	/** @var \ReflectionClass */
	private $entityReflection;
	/** @var string */
	private $tableName;


	/**
	 * @param \ReflectionClass $entityReflection
	 * @param string $tableName
	 */
	public function __construct(
		\ReflectionClass $entityReflection,
		string $tableName = null
	)
	{
		$this->entityReflection = $entityReflection;
		$this->tableName = $tableName;
	}

	/**
	 * @return string
	 */
	public function getNamespace(): string
	{
		return \Nette\Utils\Strings::before($this->entityReflection->getNamespaceName(), '\\', -1) . '\Mapping';
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$renderer = (new \Sellastica\PhpGenerator\PhpClassRenderer(
			$this->getClassName(), ['\\' . $this->getExtendingClass()])
		)->phpBeginning()
			->namespace($this->getNamespace());

		//internal app method
		if ($this->tableName) {
			$method = $renderer->createMethod('getTableName', 'protected')
				->return('string')
				->addBody(sprintf("return '%s';", $this->tableName));
			$method->createParameter('databaseName')
				->defaultValue(false);
			$method->createAnnotation()
				->param('databaseName', 'bool')
				->return('string');
		}

		$renderer->annotation()->see('\\' . $this->entityReflection->getName());
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
	public function getClassName(): string
	{
		return $this->isMongoDescendant()
			? $this->entityReflection->getShortName() . 'Mapper'
			: $this->entityReflection->getShortName() . 'DibiMapper';
	}

	/**
	 * @return string
	 */
	private function getExtendingClass(): string
	{
		return $this->isMongoDescendant()
			? \Sellastica\MongoDB\Mapping\MongoMapper::class
			: \Sellastica\Entity\Mapping\DibiMapper::class;
	}
}