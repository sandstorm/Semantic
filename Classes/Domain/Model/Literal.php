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
class Literal extends Resource {

	/**
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * @var UriReference
	 */
	protected $type = NULL;

	/**
	 * @param string $value
	 */
	public function __construct($value) {
		if ($value instanceof \DateTime) {
			$this->value = $value->format(\DateTime::W3C);
			$this->type = new UriReference('http://www.w3.org/2001/XMLSchema#dateTime');
		} else {
			$this->value = (string)$value;
		}
	}

	/**
	 * @return string
	 * @api
	 */
	public function getValue() {
		return $this->value;
	}

	public function getType() {
		return $this->type;
	}

	public function asN3() {
		$value = str_replace(array("\r\n", "\n"), '\n', $this->value);
		$output = '"' . $value . '"';

		if ($this->type !== NULL) {
			$output .= '^^' . $this->type;
		}
		return $output;
	}
}
?>