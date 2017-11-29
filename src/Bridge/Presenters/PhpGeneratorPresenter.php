<?php
namespace App\UI\Presenters;

use Nette\Application\UI;
use Sellastica\Core\Environment;
use Sellastica\EntityGenerator\Generator\Generator;

class PhpGeneratorPresenter extends UI\Presenter
{
	/** @var Generator @inject */
	public $generator;
	/** @var Environment @inject */
	public $environment;


	/**
	 * It is neccessary to generate builders in the startup method before
	 * any entity is loaded - its builder could be missing
	 */
	public function startup()
	{
		if ($this->environment->isDebugMode()
			&& $this->action === 'builders') {
			$result = $this->generator->generateAllBuildersAndModifiers();
			f($result[0], sprintf('%s builders generated', sizeof($result[0])));
			g($result[1], sprintf('%s modifiers generated', sizeof($result[1])));
		}

		parent::startup();
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentForm(): UI\Form
	{
		$form = new UI\Form();
		$form->addProtection();
		$form->addText('entity_class', 'Entity class with namespace')
			->setDefaultValue('\\\\');
		$form->addText('table_name', 'Database table name (blank for standard)')
			->setNullable();
		$form->addSubmit('submit', 'Submit');
		$form->onSuccess[] = [$this, 'processForm'];

		return $form;
	}

	/**
	 * @param UI\Form $form
	 * @param $values
	 * @throws \Exception
	 */
	public function processForm(UI\Form $form, $values)
	{
		if (!$this->environment->isDebugMode()) {
			return;
		}

		$entityClass = str_replace('/', '\\', $values->entity_class);
		$dump = $this->generator
			->mappers(true)
			->repositories(true)
			->repositoryInterfaces(true)
			->repositoryProxies(true)
			->daos(true)
			->builders(true)
			->modifiers(true)
			->entityFactories(true)
			->collections(true)
			->generate($entityClass, $values->table_name);

		g($dump, 'Created classes');
	}
}
