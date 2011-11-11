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

namespace SandstormMedia\Semantic\Rdf\TripleStore;




use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
abstract class AbstractConnector implements StoreConnectorInterface {

	/**
	 * @var \SandstormMedia\Semantic\Core\RdfGenerator
	 * @FLOW3\Inject
	 */
	protected $rdfGenerator;

	public function addOrUpdateObject($object) {
		$graph = $this->rdfGenerator->buildGraphForObject($object);

		$uri = $this->rdfGenerator->getResourceUriForObject($object);
		$this->addOrUpdateGraph($uri, $graph->toNt());
	}

	public function removeObject($object) {
		$uri = $this->rdfGenerator->getResourceUriForObject($object);
		$this->removeGraph($uri);
	}

	abstract public function addOrUpdateGraph($graphUri, $dataAsTurtle);
	abstract public function removeGraph($graphUri);
}
?>