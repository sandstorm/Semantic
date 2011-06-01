<?php
declare(ENCODING = 'utf-8');
namespace F3\Semantic\ContinuousTextEnrichment;

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

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope singleton
 */
class ContinuousTextInterceptor implements \F3\Fluid\Core\Parser\InterceptorInterface {

	/**
	 * Is the interceptor enabled right now?
	 * @var boolean
	 */
	protected $interceptorEnabled = TRUE;

	/**
	 *
	 * @param \F3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node
	 * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
	 * @return \F3\Fluid\Core\Parser\SyntaxTree\NodeInterface
	 */
	public function process(\F3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node, $interceptorPosition, \F3\Fluid\Core\Parser\ParsingState $parsingState) {
		if (!$this->interceptorEnabled) {
			return $node;
		}


		if ($node instanceof \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode && $node->getUninitializedViewHelper() instanceof \F3\Fluid\ViewHelpers\Form\TextareaViewHelper) {
			$argumentsReflection = new \ReflectionProperty($node, 'arguments');
			$argumentsReflection->setAccessible(TRUE);
			$arguments = $argumentsReflection->getValue($node);
			if (isset($arguments['property'])) {
				$propertyNode = $arguments['property'];

				$newNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode(
					new ContinuousTextEditorViewHelper(),
					array('property' => $propertyNode)
				);
				$parsingState->getNodeFromStack()->addChildNode($newNode);
			}
		}
		return $node;
	}

	/**
	 * This interceptor wants to hook into object accessor creation, and opening / closing ViewHelpers.
	 *
	 * @return array Array of INTERCEPT_* constants
	 */
	public function getInterceptionPoints() {
		return array(
			\F3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER
		);
	}
}
?>