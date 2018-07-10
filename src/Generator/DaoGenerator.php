<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\Reflection\ReflectionClass;

class DaoGenerator implements IGenerator
{
	/** @var ReflectionClass */
	private $entityReflection;
	/** @var string */
	private $className;


	/**
	 * @param string $entityClass
	 */
	public function __construct(string $entityClass)
	{
		$this->entityReflection = new ReflectionClass($entityClass);
	}

	public function generate(): void
	{
		$renderer = new DaoRenderer($this->entityReflection);
		$this->className = $renderer->getNamespace() . '\\' . $renderer->getClassName();
		$this->save($renderer->render());
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
		if (!$prefix) {
			$prefix = \Nette\Utils\Strings::firstLower($this->entityReflection->getShortName());
		}

		return "\t{$prefix}Dao: {$this->getClassName()}(@{$prefix}Mapper, @{$prefix}Factory)";
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		$path = dirname($this->entityReflection->getFileName(), 2) . '/Mapping';
		$fileName = basename($this->entityReflection->getFileName(), '.php') . 'Dao.php';
		return $path . '/' . $fileName;
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