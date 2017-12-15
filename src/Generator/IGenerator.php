<?php
namespace Sellastica\EntityGenerator\Generator;

interface IGenerator
{
	function generate(): void;

	/**
	 * @return string
	 */
	function getClassName(): string;
}