<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Domain\Model\Rdf\Environment;

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
class DefaultProfile extends Profile {

	protected $settings;

	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}
	public function initializeObject() {
		foreach ($this->settings['namespaces'] as $prefix => $iri) {
			$this->prefixes->set($prefix, $iri);
		}

		$this->prefixes->set('owl', 'http://www.w3.org/2002/07/owl#');
		$this->prefixes->set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
		$this->prefixes->set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
		$this->prefixes->set('rdfa', 'http://www.w3.org/ns/rdfa#');
		$this->prefixes->set('xhv', 'http://www.w3.org/1999/xhtml/vocab#');
		$this->prefixes->set('xml', 'http://www.w3.org/XML/1998/namespace');
		$this->prefixes->set('xsd', 'http://www.w3.org/2001/XMLSchema#');
	}
}
?>