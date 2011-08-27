<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\TripleStore;

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
abstract class AbstractConnector implements StoreConnectorInterface {

	/**
	 * @var \SandstormMedia\Semantic\Domain\Service\RdfGenerator
	 * @inject
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