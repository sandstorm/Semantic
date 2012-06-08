<?php
/*                                                                        *
 * This script belongs to the "SandstormMedia.Semantic" package.          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3          *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * © 2011 Sandstorm Media UG (haftungsbeschränkt)                         *
 *        http://sandstorm-media.de                                       */

namespace SandstormMedia\Semantic\Rdf\Controller;




use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class DebuggerController extends \TYPO3\FLOW3\Mvc\Controller\ActionController {

	/**
	 * @var SandstormMedia\Semantic\Core\Schema\ClassSchemaResolver
	 * @FLOW3\Inject
	 */
	protected $classSchemaResolver;

	/**
	 * @return string
	 * @FLOW3\SkipCsrfProtection
	 */
	public function indexAction() {
		$output = array();
		foreach ($this->classSchemaResolver->getClassNamesWhichHaveASchema() as $className) {
			$propertySchema = array();
			foreach ($this->classSchemaResolver->getPropertyNames($className) as $propertyName) {
				$propertySchema[$propertyName] = $this->classSchemaResolver->getPropertySchema($className, $propertyName);
			}
			$output[$className] = array(
				'schema' => $this->classSchemaResolver->getClassSchema($className),
				'properties' => $propertySchema
			);
		}
		$this->view->assign('schema', $output);
	}
}
?>