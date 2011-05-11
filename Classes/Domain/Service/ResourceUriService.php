<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Domain\Service;

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

/**
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 2 or later
 * @scope singleton
 */
class ResourceUriService {
	/**
	 * @var \F3\FLOW3\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	public function buildResourceUri($domainObject, \F3\FLOW3\MVC\Web\Routing\UriBuilder $uriBuilder) {
		$uri = $uriBuilder
				->reset()
				->setCreateAbsoluteUri(TRUE)
				->uriFor('show', array(
					'dataType' => str_replace('\\', '_', get_class($domainObject)),
					'identifier' => $this->persistenceManager->getIdentifierByObject($domainObject)),
				'RdfIdentity', 'Semantic'); // TODO: we need some kind of Identity service later.

		return new \F3\Semantic\Domain\Model\UriReference($uri);
	}
}
?>