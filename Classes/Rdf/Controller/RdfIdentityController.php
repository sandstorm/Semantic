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

use \SandstormMedia\Semantic\Domain\Model\Triple;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class RdfIdentityController extends \TYPO3\FLOW3\Mvc\Controller\ActionController {

	/**
	 * Default action of the backend controller.
	 *
	 * @param string $dataType
	 * @param string $identifier
	 * @return string
	 * @FLOW3\SkipCsrfProtection
	 */
	public function showAction($dataType, $identifier) {
		$this->redirect('show', 'rdfdata', 'SandstormMedia.Semantic', array('dataType' => $dataType, 'identifier' => $identifier), NULL, 303, 'nt');
	}
}
?>