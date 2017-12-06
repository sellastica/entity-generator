<?php
namespace Sellastica\EntityGenerator\Generator;

class DibiMapperGenerator
{
	/** @var \ReflectionClass */
	private $entityReflection;
	/** @var string */
	private $tableName;

	/**
	 * @param string $entityClass
	 * @param string $tableName
	 */
	public function __construct(string $entityClass, string $tableName = null)
	{
		$this->entityReflection = new \ReflectionClass($entityClass);
		$this->tableName = $tableName;
	}

	public function generate()
	{
		$data = (new DibiMapperRenderer(
			$this->entityReflection,
			$this->tableName
		))->render();

		$this->save($data);
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		$path = dirname($this->entityReflection->getFileName(), 2) . '/Mapping';
		$fileName = basename($this->entityReflection->getFileName(), '.php') . 'DibiMapper.php';
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