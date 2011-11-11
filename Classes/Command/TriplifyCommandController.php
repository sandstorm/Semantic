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
namespace SandstormMedia\Semantic\Command;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class TriplifyCommandController extends \TYPO3\FLOW3\MVC\Controller\CommandController {

	/**
	 * @FLOW3\Inject
	 * @var \SandstormMedia\Semantic\Triplify\TriplifyService
	 */
	protected $triplifyService;

	/**
	 * Generate triples from foreign databases
	 *
	 * @param string $serviceIdentifier string The service identifier from the settings
	 * @return string nTriples string
	 */
	public function generateTriplesCommand($serviceIdentifier) {
		$triples = $this->triplifyService->generateTriples($serviceIdentifier);
		return $triples->toNt();
	}
}
?>