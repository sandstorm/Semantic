<?php
declare(ENCODING = 'utf-8');
namespace SandstormMedia\Semantic\Linkification;

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
class FluidSyntaxTreeInterceptor implements \TYPO3\Fluid\Core\Parser\InterceptorInterface {

	protected $formObjectArguments = array();
	/**
	 *
	 * @param \TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node
	 * @param integer $interceptorPosition One of the INTERCEPT_* constants for the current interception point
	 * @return \TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface
	 */
	public function process(\TYPO3\Fluid\Core\Parser\SyntaxTree\NodeInterface $node, $interceptorPosition, \TYPO3\Fluid\Core\Parser\ParsingState $parsingState) {

		if ($node instanceof \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode &&
			$node->getUninitializedViewHelper() instanceof \TYPO3\Fluid\ViewHelpers\FormViewHelper) {

			$argumentsReflection = new \ReflectionProperty($node, 'arguments');
			$argumentsReflection->setAccessible(TRUE);
			$arguments = $argumentsReflection->getValue($node);

			if (isset($arguments['action'])) {
				$this->formObjectArguments['action'] = $arguments['action'];
			}

			if (isset($arguments['controller'])) {
				$this->formObjectArguments['controller'] = $arguments['controller'];
			}

			if (isset($arguments['package'])) {
				$this->formObjectArguments['package'] = $arguments['package'];
			}

			if (isset($arguments['subpackage'])) {
				$this->formObjectArguments['subpackage'] = $arguments['subpackage'];
			}

			return $node;
		}


		if ($interceptorPosition === self::INTERCEPT_CLOSING_VIEWHELPER
				&& $node instanceof \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode
				&&($node->getUninitializedViewHelper() instanceof \TYPO3\Fluid\ViewHelpers\Form\TextfieldViewHelper
					|| $node->getUninitializedViewHelper() instanceof \TYPO3\Fluid\ViewHelpers\Form\TextareaViewHelper
					)) {
			$argumentsReflection = new \ReflectionProperty($node, 'arguments');
			$argumentsReflection->setAccessible(TRUE);
			$arguments = $argumentsReflection->getValue($node);
			if (isset($arguments['property'])) {
				$this->formObjectArguments['property'] = $arguments['property'];
				$newNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode(
					new \SandstormMedia\Semantic\Linkification\LinkificationEditorViewHelper(),
					$this->formObjectArguments
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
			\TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_OPENING_VIEWHELPER,
			\TYPO3\Fluid\Core\Parser\InterceptorInterface::INTERCEPT_CLOSING_VIEWHELPER
		);
	}
}
?>