<?php
namespace Sellastica\EntityGenerator\Generator;

use Sellastica\PhpGenerator\PhpClassRenderer;

class EntityCollectionRenderer
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
	private function getClassShortName(): string
	{
		return $this->entityReflection->getShortName() . 'Collection';
	}

	/**
	 * @return string
	 */
	public function render()
	{
		$renderer = (new PhpClassRenderer($this->getClassShortName(), ['EntityCollection']))
			->phpBeginning()
			->namespace($this->entityReflection->getNamespaceName());

		if ($this->entityReflection->getNamespaceName() !== 'Core\Domain\Model') {
			$renderer->import('Sellastica\Entity\Entity\EntityCollection');
		}

		$renderer->annotation()
			->property('$items', $this->entityReflection->getShortName() . '[]')
			->method('add($entity)', $this->getClassShortName())
			->method('remove($key)', $this->getClassShortName())
			->method('getEntity(int $entityId, $default = null)', $this->entityReflection->getShortName() . '|mixed')
			->method('getBy(string $property, $value, $default = null)', $this->entityReflection->getShortName() . '|mixed');

		return $renderer->render();
	}
}