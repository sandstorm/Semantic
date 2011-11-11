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

namespace SandstormMedia\Semantic\Linkification;




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