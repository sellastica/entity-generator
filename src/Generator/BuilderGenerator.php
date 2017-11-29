<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\Reflection\ReflectionClass;

class BuilderGenerator
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
		if (!$this->shouldGenerate()) {
			throw new \Exception(sprintf('%s should not be generated'));
		}

		$data = (new BuilderRenderer($this->entityReflection))->render();
		$this->save($data);
	}

	/**
	 * @return bool
	 */
	public function shouldGenerate(): bool
	{
		return $this->entityReflection->hasAnnotation('generate-builder');
	}

	/**
	 * @return string
	 */
	public function getBuilderClass(): string
	{
		return $this->entityReflection->getName() . 'Builder';
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		return str_replace('.php', 'Builder.php', $this->entityReflection->getFileName());
	}

	/**
	 * @param string $data
	 */
	private function save(string $data)
	{
		file_put_contents($this->getFileName(), $data);
	}
}