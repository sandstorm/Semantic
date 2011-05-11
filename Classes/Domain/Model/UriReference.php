<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Domain\Model;

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
 */
class UriReference extends Resource {

	/**
	 * @var string
	 */
	protected $uri;

	/**
	 * @var string
	 * @transient
	 */
	protected $settings;
	/**
	 * @param string $uri
	 */
	public function __construct($uri) {
		$this->uri = (string)$uri;
	}

	public function injectSettings($settings) {
		$this->settings = $settings;
	}

	public function initializeObject() {
		if ($this->uri{0} === '[' && $this->uri{strlen($this->uri)-1} === ']') {
			$curie = substr($this->uri, 1, -1);
			list($prefix, $suffix) = explode(':', $curie, 2);

			if (!isset($this->settings['namespaces'][$prefix])) {
				throw new \Exception("TODO: Namespace not found");
			}
			$this->uri = $this->settings['namespaces'][$prefix] . $suffix;
		}
	}

	/**
	 * @return string
	 * @api
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 * @return string
	 * @api
	 */
	public function __toString() {
		return $this->getUri();
	}

	public function asN3() {
		return '<' . $this->getUri() . '>';
	}
}
?>