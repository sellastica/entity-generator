<?php
namespace Sellastica\EntityGenerator\Generator;

class IRepositoryGenerator
{
	/** @var \ReflectionClass */
	private $entityReflection;

	/**
	 * @param string $entityClass
	 */
	public function __construct(string $entityClass)
	{
		$this->entityReflection = new \ReflectionClass($entityClass);
	}

	public function generate()
	{
		$data = (new IRepositoryRenderer($this->entityReflection))->render();
		$this->save($data);
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		$file = basename($this->entityReflection->getFileName());
		$file = 'I' . str_replace('.php', 'Repository.php', $file);
		return dirname($this->entityReflection->getFileName()) . '/' . $file;
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