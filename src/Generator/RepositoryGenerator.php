<?php
namespace Sellastica\EntityGenerator\Generator;

class RepositoryGenerator
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
		$data = (new RepositoryRenderer(
			$this->entityReflection
		))->render();
		$this->save($data);
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		$path = dirname($this->entityReflection->getFileName(), 2) . '/Mapping';
		$fileName = basename($this->entityReflection->getFileName(), '.php') . 'Repository.php';
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