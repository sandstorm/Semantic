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

namespace SandstormMedia\Semantic\Core\RdfGeneration\IdentityProvider;



use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * NO API!!!
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class PlaceholderIdentityProvider implements IdentityProviderInterface {
	/**
	 * @var \TYPO3\FLOW3\Persistence\PersistenceManagerInterface
	 * @FLOW3\Inject
	 */
	protected $persistenceManager;

	public function buildResourceUri($domainObject, $schema) {
		$uri = $schema['rdfUriPattern'];
		$uri = str_replace('{identifier}', $this->persistenceManager->getIdentifierByObject($domainObject), $uri);

		return new \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode($uri);
	}
}
?>