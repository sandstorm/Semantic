<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\Resolver;

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
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 2 or later
 * @scope singleton
 */
class DbpediaResolver {
	public function resolve($query) {
		$data = file_get_contents('http://lookup.dbpedia.org/api/search.asmx/PrefixSearch?QueryClass=Person&MaxHits=5&QueryString=' . urlencode($query));
		$results = new \SimpleXMLElement($data);

		$processedResults = array();
		foreach ($results as $result) {
			$processedResults[] = array(
				'label' => (string)$result->Label,
				'uri' => (string)$result->URI,
				'description' => (string)$result->Description
			);
		}
		return $processedResults;
	}
}
?>