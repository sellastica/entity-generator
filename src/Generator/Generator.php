<?php
namespace Sellastica\EntityGenerator\Generator;

use Nette;
use Sellastica\Entity\Entity\EntityFactory;

class Generator
{
	/** @var bool */
	private $mappers = false;
	/** @var bool */
	private $daos = false;
	/** @var bool */
	private $repositories = false;
	/** @var bool */
	private $repositoryProxies = false;
	/** @var bool */
	private $repositoryInterfaces = false;
	/** @var bool */
	private $entityFactories = false;
	/** @var bool */
	private $builders = false;
	/** @var bool */
	private $modifiers = false;
	/** @var bool */
	private $collections = false;
	/** @var Nette\DI\Container */
	private $container;


	/**
	 * @param Nette\DI\Container $container
	 */
	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param bool $mappers
	 * @return Generator
	 */
	public function mappers(bool $mappers): Generator
	{
		$this->mappers = $mappers;
		return $this;
	}

	/**
	 * @param bool $daos
	 * @return Generator
	 */
	public function daos(bool $daos): Generator
	{
		$this->daos = $daos;
		return $this;
	}

	/**
	 * @param bool $repositories
	 * @return Generator
	 */
	public function repositories(bool $repositories): Generator
	{
		$this->repositories = $repositories;
		return $this;
	}

	/**
	 * @param bool $repositoryProxies
	 * @return Generator
	 */
	public function repositoryProxies(bool $repositoryProxies): Generator
	{
		$this->repositoryProxies = $repositoryProxies;
		return $this;
	}

	/**
	 * @param bool $repositoryInterfaces
	 * @return Generator
	 */
	public function repositoryInterfaces(bool $repositoryInterfaces): Generator
	{
		$this->repositoryInterfaces = $repositoryInterfaces;
		return $this;
	}

	/**
	 * @param bool $entityFactories
	 * @return Generator
	 */
	public function entityFactories(bool $entityFactories): Generator
	{
		$this->entityFactories = $entityFactories;
		return $this;
	}

	/**
	 * @param bool $builders
	 * @return Generator
	 */
	public function builders(bool $builders): Generator
	{
		$this->builders = $builders;
		return $this;
	}

	/**
	 * @param bool $modifiers
	 * @return Generator
	 */
	public function modifiers(bool $modifiers): Generator
	{
		$this->modifiers = $modifiers;
		return $this;
	}

	/**
	 * @param bool $collections
	 * @return Generator
	 */
	public function collections(bool $collections): Generator
	{
		$this->collections = $collections;
		return $this;
	}

	/**
	 * @param string $entityClass
	 * @param string|null $tableName
	 * @return array
	 * @throws \Exception
	 */
	public function generate(string $entityClass, string $tableName = null)
	{
		if (!class_exists($entityClass)) {
			throw new \Exception(sprintf('Class %s does not exist', $entityClass));
		}

		$dump = [];
		//$classShortName = (new \Sellastica\Reflection\ReflectionClass($entityClass))->getShortName();
		//$classShortNameLower = strtolower($classShortName);

		//repository interface
		if ($this->repositoryInterfaces) {
			$generator = new IRepositoryGenerator($entityClass);
			$generator->generate();
		}

		//repository
		if ($this->repositories) {
			$generator = new RepositoryGenerator($entityClass);
			$generator->generate();
			$dump[] = $generator->getNeonDefinition();
		}

		//repository proxy
		if ($this->repositoryProxies) {
			$generator = new RepositoryProxyGenerator($entityClass);
			$generator->generate();
			$dump[] = $generator->getNeonDefinition();
		}

		//entity factory
		if ($this->entityFactories) {
			$generator = new EntityFactoryGenerator($entityClass);
			$generator->generate();
			$dump[] = $generator->getNeonDefinition();
		}

		//dao
		if ($this->daos) {
			$generator = new DaoGenerator($entityClass);
			$generator->generate();
			$dump[] = $generator->getNeonDefinition();
		}

		//dibi mapper
		if ($this->mappers) {
			$generator = new MapperGenerator($entityClass, $tableName);
			$generator->generate();
			$dump[] = $generator->getNeonDefinition();
		}

		//builder
		if ($this->builders) {
			$generator = new BuilderGenerator($entityClass);
			if ($generator->shouldGenerate()) {
				$generator->generate();
			}
		}

		//modifier
		if ($this->modifiers) {
			$generator = new ModifierGenerator($entityClass);
			if ($generator->shouldGenerate()) {
				$generator->generate();
			}
		}

		//collection
		if ($this->collections) {
			$generator = new EntityCollectionGenerator($entityClass);
			$generator->generate();
		}

		return implode("\n", $dump);
	}

	/**
	 * @param string $class
	 * @param bool $lower
	 * @return string
	 */
	private function getClassShortName(string $class, bool $lower = true): string
	{
		$shortName = (new \Sellastica\Reflection\ReflectionClass($class))->getShortName();
		return $lower ? Nette\Utils\Strings::firstLower($shortName) : $shortName;
	}

	/**
	 * @return array
	 */
	public function generateAllBuildersAndModifiers(): array
	{
		$entityFactories = $this->container->findByType(EntityFactory::class);
		$builders = [];
		$modifiers = [];
		foreach ($entityFactories as $entityFactoryName) {
			/** @var \Sellastica\Entity\Entity\EntityFactory $entityFactory */
			$entityFactory = $this->container->getService($entityFactoryName);
			//builders
			$generator = new BuilderGenerator($entityFactory->getEntityClass());
			if ($generator->shouldGenerate()) {
				$generator->generate();
				$builders[] = $generator->getBuilderClass();
			}

			//modifiers
			$generator = new ModifierGenerator($entityFactory->getEntityClass());
			if ($generator->shouldGenerate()) {
				$generator->generate();
				$modifiers[] = $generator->getModifierClass();
			}
		}

		return [$builders, $modifiers];
	}
}