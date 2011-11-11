<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Core\Rdf\Environment;

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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class DefaultProfile extends Profile {

	protected $settings;

	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}
	public function initializeObject() {
		foreach ($this->settings['prefixes'] as $prefix => $iri) {
			$this->prefixes->set($prefix, $iri);
		}

			// Required prefixes by the standard
		$this->prefixes->set('owl', 'http://www.w3.org/2002/07/owl#');
		$this->prefixes->set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
		$this->prefixes->set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
		$this->prefixes->set('rdfa', 'http://www.w3.org/ns/rdfa#');
		$this->prefixes->set('xhv', 'http://www.w3.org/1999/xhtml/vocab#');
		$this->prefixes->set('xml', 'http://www.w3.org/XML/1998/namespace');
		$this->prefixes->set('xsd', 'http://www.w3.org/2001/XMLSchema#');

			// Non-required prefixes; but we rely on their existence nevertheless
		$this->prefixes->set('void', 'http://rdfs.org/ns/void#');
		$this->prefixes->set('annot', 'http://sandstorm-media.de/ns/2011/annotation#');
		if (isset($this->settings['terms'])) {
			foreach ($this->settings['terms'] as $term => $iri) {
				$this->terms->set($term, $iri);
			}
		}

			// Non-required terms, but we expect them to be there nevertheless
		$this->terms->set('norms', 'http://vocab.org/waiver/terms/norms');

			// licenses
		$this->terms->set('pddl', 'http://www.opendatacommons.org/licenses/pddl/');
		$this->terms->set('odc-by', 'http://www.opendatacommons.org/licenses/by/');
		$this->terms->set('odc-odbl', 'http://www.opendatacommons.org/licenses/odbl/');
		$this->terms->set('cc0', 'http://creativecommons.org/publicdomain/zero/1.0/');

			// community norms
		$this->terms->set('odc-by-sa', 'http://www.opendatacommons.org/norms/odc-by-sa/');
	}
}
?>