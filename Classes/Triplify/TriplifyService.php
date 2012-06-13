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
namespace SandstormMedia\Semantic\Triplify;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("singleton")
 */
class TriplifyService {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * @param string $serviceIdentifier
	 * @return \SandstormMedia\Semantic\Core\Rdf\Concept\Graph
	 */
	public function generateTriples($serviceIdentifier) {
		if (!isset($this->settings['triplify'][$serviceIdentifier])) {
			throw new Exception\ServiceIdentifierNotFoundException(sprintf('The given service identifier "%s" has not been found.', $serviceIdentifier), 1320992632);
		}
		$rdfDataset = new \SandstormMedia\Semantic\Core\Rdf\Concept\Dataset();

		$triplifyConfiguration = $this->settings['triplify'][$serviceIdentifier];

		$pdoConnection = new \PDO($triplifyConfiguration['pdoConnection'], $triplifyConfiguration['pdoUser'], $triplifyConfiguration['pdoPassword']);

		$driverClassName = $triplifyConfiguration['driver'];
		$driver = new $driverClassName($pdoConnection, $rdfDataset, $triplifyConfiguration['baseUri']);
		$driver->run();

		return $rdfDataset;
	}
}
?>
