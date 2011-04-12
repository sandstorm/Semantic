<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic;

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
 */
class RdfaViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	protected $tagName = 'flow3:rdfa';

	protected $settings;

	/**
	 * @var \F3\Semantic\Domain\Service\ResourceUriService
	 * @inject
	 */
	protected $resourceUriService;

	/**
	 * @param array $settings 
	 */
	public function injectSettings($settings) {
		$this->settings = $settings;
	}

	/**
	 * @param string $propertyPath
	 * @return string
	 */
	public function render($propertyPath) {
		var_dump($propertyPath);
		$propertyPathParts = explode('.', $propertyPath);

		$propertyName = array_pop($propertyPathParts);
		$objectPath = implode('.', $propertyPathParts);

		$object = \F3\FLOW3\Reflection\ObjectAccess::getPropertyPath($this->templateVariableContainer, $objectPath);
		$rdfSubject = $this->resourceUriService->buildResourceUri($object, $this->controllerContext->getUriBuilder());
		
		$rdfPredicate = NULL;
		$rdfSchema = isset($this->settings['PropertyMapping'][get_class($object)]) ? $this->settings['PropertyMapping'][get_class($object)] : array();
		if (isset($rdfSchema['properties'][$propertyName])) {
			$rdfPredicate = $rdfSchema['properties'][$propertyName];
		}

		$innerContent = $this->renderChildren();

		if ($rdfPredicate !== NULL && !is_object($innerContent)) { // TODO: hack to prevent conversion of f.e. DateTime
			$this->tag->setContent($innerContent);
			$this->tag->addAttribute('subject', $rdfSubject);
			$this->tag->addAttribute('predicate', $rdfPredicate);
			// TODO: handle the case that f.e. DateTime is shown, with "content"

			return $this->tag->render();
		} else {
			return $innerContent;
		}
	}
}
?>