<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\PhpGenerator\PhpClassRenderer;

class EntityCollectionRenderer implements IRenderer
{
	/** @var \ReflectionClass */
	private $entityReflection;


	/**
	 * @param \ReflectionClass $entityReflection
	 */
	public function __construct(\ReflectionClass $entityReflection)
	{
		$this->entityReflection = $entityReflection;
	}

	/**
	 * @return string
	 */
	public function getNamespace(): string
	{
		return $this->entityReflection->getNamespaceName();
	}

	/**
	 * @return string
	 */
	public function getClassName(): string
	{
		return $this->entityReflection->getShortName() . 'Collection';
	}

	/**
	 * @return string
	 */
	public function render(): string
	{
		$renderer = (new PhpClassRenderer($this->getClassName(), ['EntityCollection']))
			->phpBeginning()
			->namespace($this->getNamespace());

		if ($this->entityReflection->getNamespaceName() !== 'Core\Domain\Model') {
			$renderer->import('Sellastica\Entity\Entity\EntityCollection');
		}

		$renderer->annotation()
			->property('$items', $this->entityReflection->getShortName() . '[]')
			->method('add($entity)', $this->getClassName())
			->method('remove($key)', $this->getClassName())
			->method('getEntity(int $entityId, $default = null)', $this->entityReflection->getShortName() . '|mixed')
			->method('getBy(string $property, $value, $default = null)', $this->entityReflection->getShortName() . '|mixed');

		return $renderer->render();
	}
}