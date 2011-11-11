<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Rdf\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Semantic".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class DebuggerController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

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