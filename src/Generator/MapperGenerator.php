<?php
namespace Sellastica\EntityGenerator\Generator;

class MapperGenerator implements IGenerator
{
	/** @var \ReflectionClass */
	private $entityReflection;
	/** @var string */
	private $tableName;
	/** @var string */
	private $className;


	/**
	 * @param string $entityClass
	 * @param string $tableName
	 */
	public function __construct(string $entityClass, string $tableName = null)
	{
		$this->entityReflection = new \ReflectionClass($entityClass);
		$this->tableName = $tableName;
	}

	public function generate(): void
	{
		$renderer = new MapperRenderer(
			$this->entityReflection,
			$this->tableName
		);
		$this->className = $renderer->getNamespace() . '\\' . $renderer->getClassName();
		$this->save($renderer->render());
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		$path = dirname($this->entityReflection->getFileName(), 2) . '/Mapping';
		$suffix = in_array(\Sellastica\MongoDB\Entity\IMongoObject::class, $this->entityReflection->getInterfaceNames())
			? 'Mapper'
			: 'DibiMapper';
		$fileName = basename($this->entityReflection->getFileName(), '.php') . $suffix . '.php';
		return $path . '/' . $fileName;
	}

	/**
	 * @return string
	 */
	public function getClassName(): string
	{
		return $this->className;
	}

	/**
	 * @param string|null $prefix
	 * @return string
	 */
	public function getNeonDefinition(string $prefix = null): string
	{
		return "\t"
			. ($prefix ?? \Nette\Utils\Strings::firstLower($this->entityReflection->getShortName()))
			. 'Mapper: '
			. $this->getClassName();

	}

	/**
	 * @param string $data
	 */
	private function save(string $data)
	{
		if (!is_dir(dirname($this->getFileName()))) {
			mkdir(dirname($this->getFileName()), 0775, true);
		}

		file_put_contents($this->getFileName(), $data);
		//folder and file permissions
		@chmod(dirname($this->getFileName()), 0775);
		@chmod($this->getFileName(), 0664);
	}
}