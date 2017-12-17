<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\EntityGenerator\Generator;

class RepositoryProxyGenerator implements IGenerator
{
	/** @var \ReflectionClass */
	private $entityReflection;
	/** @var string */
	private $className;


	/**
	 * @param string $entityClass
	 */
	public function __construct(string $entityClass)
	{
		$this->entityReflection = new \ReflectionClass($entityClass);
	}

	public function generate(): void
	{
		$renderer = new Generator\RepositoryProxyRenderer($this->entityReflection);
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
	 * @return string
	 */
	public function getNeonDefinition(): string
	{
		$entityShortName = \Nette\Utils\Strings::firstLower($this->entityReflection->getShortName());
		return "\t{$entityShortName}RepositoryProxy: " . $this->getClassName();

	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		$path = dirname($this->entityReflection->getFileName(), 2) . '/Mapping';
		$fileName = basename($this->entityReflection->getFileName(), '.php') . 'RepositoryProxy.php';
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