<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\Reflection\ReflectionClass;

class EntityFactoryGenerator
{
	/** @var ReflectionClass */
	private $entityReflection;


	/**
	 * @param string $entityClass
	 */
	public function __construct(string $entityClass)
	{
		$this->entityReflection = new ReflectionClass($entityClass);
	}

	public function generate()
	{
		$data = (new EntityFactoryRenderer($this->entityReflection))->render();
		$this->save($data);
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