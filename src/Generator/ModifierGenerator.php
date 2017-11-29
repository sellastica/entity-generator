<?php
namespace Sellastica\EntityGenerator\Generator;

use Nette;
use Sellastica\Reflection\ReflectionClass;

class ModifierGenerator
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

		$data = (new ModifierRenderer($this->entityReflection))->render();
		$this->save($data);
	}

	/**
	 * @return bool
	 */
	public function shouldGenerate(): bool
	{
		return $this->entityReflection->hasAnnotation('generate-modifier');
	}

	/**
	 * @return string
	 */
	public function getModifierClass(): string
	{
		return $this->entityReflection->getName() . 'Modifier';
	}

	/**
	 * @return string
	 */
	public function getFileName(): string
	{
		return str_replace('.php', 'Modifier.php', $this->entityReflection->getFileName());
	}

	/**
	 * @return string
	 */
	public function getModifierShortName(): string
	{
		return Nette\Utils\Strings::after($this->getModifierClass(), '\\', -1);
	}

	/**
	 * @param string $data
	 */
	private function save(string $data)
	{
		file_put_contents($this->getFileName(), $data);
	}
}