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
class Triple {

	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var string
	 */
	protected $predicate;

	/**
	 * @var string
	 */
	protected $object;


	public function __construct($subject, $predicate, $object) {
		$this->subject = $subject;
		$this->predicate = $predicate;
		$this->object = $object;
	}
	public function getSubject() {
		return $this->subject;
	}


	public function getPredicate() {
		return $this->predicate;
	}

	public function getObject() {
		return $this->object;
	}

}
?>