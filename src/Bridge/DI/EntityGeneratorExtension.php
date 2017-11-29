<?php
namespace Sellastica\EntityGenerator\Bridge\DI;

use Nette;

class EntityGeneratorExtension extends Nette\DI\CompilerExtension
{
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('entityGenerator'))
			->setClass(\Sellastica\EntityGenerator\Generator\Generator::class);
	}
}
