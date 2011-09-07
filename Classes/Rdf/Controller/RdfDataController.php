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

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class RdfDataController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

	protected $defaultViewObjectName = 'SandstormMedia\Semantic\Rdf\View\ShowNtView';

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \SandstormMedia\Semantic\Core\RdfGenerator
	 * @inject
	 */
	protected $rdfGenerator;

	/**
	 * Default action of the backend controller.
	 *
	 * @param string $dataType
	 * @param string $identifier
	 * @return string
	 * @skipCsrfProtection
	 */
	public function showAction($dataType, $identifier) {
		$domainModelObjectName = str_replace('_', '\\', $dataType);

		if (!$this->objectManager->isRegistered($domainModelObjectName)) {
			throw new \Exception("TODO: Data Type not found.");
		}

		$graph = $this->rdfGenerator->buildGraph($domainModelObjectName, $identifier);

		$this->view->assign('graph', $graph);
	}
}
?>