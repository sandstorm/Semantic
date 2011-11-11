<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Core\RdfGeneration\IdentityProvider;

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
 * NO API!!!
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class ResourceUriService implements IdentityProviderInterface {
	/**
	 * @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @FLOW3\Inject
	 */
	protected $persistenceManager;

	public function buildResourceUri($domainObject) {
		$uriBuilder = new \TYPO3\FLOW3\MVC\Web\Routing\UriBuilder();
		$uriBuilder->setRequest(new FakeRequestForUriBuilder());

		$uri = $uriBuilder
				->reset()
				->setCreateAbsoluteUri(TRUE)
				->uriFor('show', array(
					'dataType' => str_replace('\\', '_', get_class($domainObject)),
					'identifier' => $this->persistenceManager->getIdentifierByObject($domainObject)),
				'RdfIdentity', 'SandstormMedia.Semantic');

		return new \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode($uri);
	}
}
?>