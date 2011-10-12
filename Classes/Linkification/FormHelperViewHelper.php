<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Linkification;

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

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class FormHelperViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param string $action
	 * @param string $controller
	 * @param string $subpackage
	 * @param string $package
	 */
	public function render($action = NULL, $controller = NULL, $subpackage = NULL, $package = NULL) {
		// Enrichment should also work with "NEW" action. For that, we need to analyze the data type of the target action of the form.
		if ($controller === NULL) {
			$controller = $this->controllerContext->getRequest()->getControllerName();
		}
		if ($package === NULL && $subpackage === NULL) {
			$subpackage = $this->controllerContext->getRequest()->getControllerSubpackageKey();
		}
		if ($package === NULL) {
			$package = $this->controllerContext->getRequest()->getControllerPackageKey();
		}

		$this->viewHelperVariableContainer->add('SandstormMedia\Semantic\Linkification\FormHelperViewHelper', 'action', $action);
		$this->viewHelperVariableContainer->add('SandstormMedia\Semantic\Linkification\FormHelperViewHelper', 'controller', $controller);
		$this->viewHelperVariableContainer->add('SandstormMedia\Semantic\Linkification\FormHelperViewHelper', 'subpackage', $subpackage);
		$this->viewHelperVariableContainer->add('SandstormMedia\Semantic\Linkification\FormHelperViewHelper', 'package', $package);

		return '';
	}
}
?>