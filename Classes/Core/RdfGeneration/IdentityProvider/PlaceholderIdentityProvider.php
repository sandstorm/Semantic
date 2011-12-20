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
use TYPO3\FLOW3\Reflection\ObjectAccess;
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
		$pm = $this->persistenceManager;

		$uri = preg_replace_callback('#{(.*?)}#', function ($match) use ($domainObject, $pm) {
			$propertyPath = $match[1];
			$accessorObject = array(
				'object' => $domainObject
			);

			$value = ObjectAccess::getPropertyPath($accessorObject, $propertyPath);
			if (is_object($value) && $pm->getIdentifierByObject($value)) {
				$value = $pm->getIdentifierByObject($value);
			}
			return $value;
		}, $uri);

		return new \SandstormMedia\Semantic\Core\Rdf\Concept\NamedNode($uri);
	}
}
?>