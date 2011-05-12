<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\View\RdfData;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3".                      *
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

use \F3\Semantic\Domain\Model\Triple;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 2 or later
 */
class ShowNt extends \F3\FLOW3\MVC\View\AbstractView {

	public function render() {
		if ($this->controllerContext->getRequest()->hasArgument('asText')) {
			$this->controllerContext->getResponse()->setHeader('Content-Type', 'text/plain;charset=utf-8');
		} else {
			$this->controllerContext->getResponse()->setHeader('Content-Type', 'text/rdf+n3;charset=utf-8');
		}

		$graph = $this->variables['graph'];
		$output = '';

		foreach ($graph as $triple) {
			$output .= (string)$triple;
		}
		return $output;
	}
}
?>