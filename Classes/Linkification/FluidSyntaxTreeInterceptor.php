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

namespace SandstormMedia\Semantic\Linkification;




use TYPO3\FLOW3\Annotations as FLOW3;

/**
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
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
			$node->getUninitializedViewHelper() instanceof \TYPO3\Fluid\ViewHelpers\FormViewHelper
				&& $interceptorPosition === self::INTERCEPT_OPENING_VIEWHELPER) {

			$argumentsReflection = new \ReflectionProperty($node, 'arguments');
			$argumentsReflection->setAccessible(TRUE);
			$arguments = $argumentsReflection->getValue($node);

			$formObjectArguments = array();
			if (isset($arguments['action'])) {
				$formObjectArguments['action'] = $arguments['action'];
			}

			if (isset($arguments['controller'])) {
				$formObjectArguments['controller'] = $arguments['controller'];
			}

			if (isset($arguments['package'])) {
				$formObjectArguments['package'] = $arguments['package'];
			}

			if (isset($arguments['subpackage'])) {
				$formObjectArguments['subpackage'] = $arguments['subpackage'];
			}

			$newNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode(
				new \SandstormMedia\Semantic\Linkification\FormHelperViewHelper(),
				$formObjectArguments
			);

			// Add the interception VH as first child to the form VH
			$node->addChildNode($newNode);

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
				$newNode = new \TYPO3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode(
					new \SandstormMedia\Semantic\Linkification\LinkificationEditorViewHelper(),
					array('property' => $arguments['property'])
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