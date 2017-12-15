<?php
namespace Sellastica\EntityGenerator\Generator;

interface IRenderer
{
	/**
	 * @return string
	 */
	function getNamespace(): string;

	/**
	 * @return string
	 */
	function getClassName(): string;

	/**
	 * @return string
	 */
	function render(): string;
}