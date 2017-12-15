<?php
namespace Sellastica\EntityGenerator\Generator;

use Nette\Utils\Strings;
use Sellastica\PhpGenerator\PhpClassRenderer;

class DibiMapperRenderer implements IRenderer
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
		return Strings::before($this->entityReflection->getNamespaceName(), '\\', -1) . '\Mapping';
	}

	/**
	 * @return string
	 */
	public function getClassName(): string
	{
		return $this->entityReflection->getShortName() . 'DibiMapper';
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$renderer = (new PhpClassRenderer($this->getClassName(), ['DibiMapper']))
			->phpBeginning()
			->namespace($this->getNamespace())
			->import('Sellastica\Entity\Mapping\DibiMapper')
			->import($this->entityReflection->getName());

		//internal app method
		if ($this->tableName) {
			$method = $renderer->createMethod('getTableName', 'protected')
				->return('string')
				->addBody(sprintf("return '%s';", $this->tableName));
			$method->createParameter('databaseName')
				->defaultValue(null);
			$method->createAnnotation()
				->param('databaseName', 'bool')
				->return('string');
		}

		$renderer->annotation()->see($this->entityReflection->getShortName());
		return $renderer->render();
	}
}