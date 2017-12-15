<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\Reflection\ReflectionClass;

class BuilderGenerator implements IGenerator
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
		if (!$this->shouldGenerate()) {
			throw new \Exception(sprintf('%s should not be generated'));
		}

		$renderer = new BuilderRenderer($this->entityReflection);
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