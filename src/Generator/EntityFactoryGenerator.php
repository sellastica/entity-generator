<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\Reflection\ReflectionClass;

class EntityFactoryGenerator implements IGenerator
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
		$renderer = new EntityFactoryRenderer($this->entityReflection);
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

		return "\t{$prefix}Factory: {$this->getClassName()}";
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		return str_replace('.php', 'Factory.php', $this->entityReflection->getFileName());
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