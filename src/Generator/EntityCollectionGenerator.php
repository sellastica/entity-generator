<?php
namespace Sellastica\EntityGenerator\Generator;

class EntityCollectionGenerator implements IGenerator
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
		$renderer = new EntityCollectionRenderer($this->entityReflection);
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
	 * @return bool
	 */
	public function shouldGenerate(): bool
	{
		return !file_exists($this->getFileName());
	}

	/**
	 * @return string
	 */
	public function getCollectionClass(): string
	{
		return $this->entityReflection->getName() . 'Collection';
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		return str_replace('.php', 'Collection.php', $this->entityReflection->getFileName());
	}

	/**
	 * @param string $data
	 */
	private function save(string $data)
	{
		file_put_contents($this->getFileName(), $data);
		//folder and file permissions
		@chmod(dirname($this->getFileName()), 0775);
		@chmod($this->getFileName(), 0664);
	}
}