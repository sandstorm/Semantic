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

namespace SandstormMedia\Semantic\Rdf\View;




use \SandstormMedia\Semantic\Domain\Model\Triple;
/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ShowNtView extends \TYPO3\FLOW3\MVC\View\AbstractView {

	public function render() {
		if ($this->controllerContext->getRequest()->hasArgument('asText')) {
			$this->controllerContext->getResponse()->setHeader('Content-Type', 'text/plain;charset=utf-8');
		} else {
			$this->controllerContext->getResponse()->setHeader('Content-Type', 'text/rdf+n3;charset=utf-8');
		}

		$graph = $this->variables['graph'];
		return $graph->toNt();
	}
}
?>