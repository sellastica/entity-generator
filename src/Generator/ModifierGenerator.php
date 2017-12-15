<?php
namespace Sellastica\EntityGenerator\Generator;

use Nette;
use Sellastica\Reflection\ReflectionClass;

class ModifierGenerator implements IGenerator
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

		$renderer = new ModifierRenderer($this->entityReflection);
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