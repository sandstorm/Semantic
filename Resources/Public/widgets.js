// Foo bar
// =======
(function($){

	// Generic Helpers
	// ---------------
	//
	// * Taken from https://github.com/jollytoad/jquery.plugins/blob/master/src/jquery.defer.js
	//
	$.defer = function(delay, timerDataName, callback) {
		var timer;

		if ( !callback ) {
			callback = timerDataName;
			timerDataName = undefined;
		}

		// Return the callback proxy
		return function() {
			// Save the vars for the real callback
			var that = this, args = arguments;

			// Reset the delay
			clearTimeout(timerDataName ? $.data(this, timerDataName) : timer);

			// Delay the real callback
			timer = setTimeout(function() {
				callback.apply(that, args);
			}, delay);

			if ( timerDataName ) {
				$.data(this, timerDataName, timer);
			}
		};
	};

	// Self-programmed

	// Calculate a diff between two strings
	// ====================================
	//
	// Return Object:
	//
	// * action: one of "remove", "insert", "modify", if the text length has decreased, increased, stayed the same
	// * position: Number. After which character did the modifications start?
	// * lengthBefore: Number of characters which were modified in the change -- before.
	// * lengthAfter: Number of characters which were modified in the change -- after.
	$.semantic_helper_calculateChanges = function(oldText, newText) {
		var commonPrefix = 0,
		commonSuffix = 0,
		lengthBefore = 0,
		lengthAfter = 0,
		position = 0,
		action = '',
		minimumLength = Math.min(oldText.length, newText.length);

		for (var i=0; i < minimumLength; i++) {
			if (oldText[i] === newText[i]) {
				commonPrefix++;
			} else {
				break;
			}
		}

		for (var i=0; i < minimumLength; i++) {
			if (oldText[oldText.length - i - 1] === newText[newText.length - i - 1]) {
				commonSuffix++;
			} else {
				break;
			}
		}
		if (commonPrefix + commonSuffix >  minimumLength) {
			commonSuffix = minimumLength - commonPrefix;
		}

		// Now, we have commonPrefix and commonSuffix calculated
		if (newText.length < oldText.length) {
			action = 'remove';
		} else if (newText.length > oldText.length) {
			action = 'insert';
		} else {
			action = 'modify';
		}
		position = commonPrefix;
		lengthBefore = oldText.length - commonPrefix - commonSuffix;
		lengthAfter = newText.length - commonPrefix - commonSuffix;

		return {
			commonPrefix: commonPrefix,
			commonSuffix: commonSuffix,
			action: action,
			lengthBefore: lengthBefore,
			lengthAfter: lengthAfter,
			position: position
		}
	};

	// Monitor text changes in a textfield or an input field, and trigger "textChangeWithDiff" events
	$.fn.monitorTextChanges = function() {
		return this.each(function() {
			var $this = $(this),
			lastValue = $this.val();

			var changeCallback = function() {
				var currentValue = $this.val();
				if (currentValue === lastValue) return;

				var changes = $.semantic_helper_calculateChanges(lastValue, currentValue);

				$this.trigger('textChangeWithDiff', changes);

				lastValue = currentValue;
			}
			$this.change(changeCallback);
			$this.keyup(changeCallback);
		});
	};

	/**
	 * =======================
	 * Semantic Lookup popover
	 * =======================
	 * used for linkification of *single entities*
	 */
	$.fn.semanticLookup = function(overrideOptions) {
		var options = {
			linkificationType: null
		};
		options = $.extend(options, {
			/**
			 * Lifecycle method which does further initialization.
			 * In our case, it binds text field changes to the entityTextChange event
			 *
			 * @param {jQuery} $targetElement the current target element which triggered the linkification
			 */
			init: function($targetElement) {
				$targetElement.keyup($.defer(300, function() {
					$targetElement.trigger('semantic-entityTextChange');
				}));
			},

			/**
			 * @param {jQuery} $targetElement the current target element which triggered the linkification
			 * @return {String} entity string which should be linkified, like "Microsoft", "Sebastian Kurf√ºrst", "TYPO3"
			 */
			getEntity: function($targetElement) {
				return $targetElement.val();
			},

			/**
			 * @param {jQuery} $targetElement the current target element which triggered the linkification
			 * @param {String} value the Entity which should be looked up
			 * @param {Function} resultRendererCallback the result rendering function must be triggered as soon as data is available.
			 */
			getLinkificationResults: function($this, value, resultRendererCallback) {
				var params = {
					text: value
				};
				if (options.linkificationType) {
					params.type = options.linkificationType;
				}

				$.get('http://localhost:8080/semantifier/linkify', params, function(results) {
					resultRendererCallback(results);
				});
			},

			/**
			 * @param {jQuery} $targetElement the current target element which triggered the linkification
			 * @return {String} the linked data URI which has been selected, if exists.
			 */
			getLinkedDataUri: function($targetElement) {
				var field = options.findStorageInputField($targetElement);
				return field.val();
			},

			/**
			 * @param {jQuery} $targetElement the current target element which triggered the linkification
			 * @param {String} uri the URI to store
			 */
			storeLinkedDataUri: function(targetElement, uri) {
				options.findStorageInputField(targetElement).val(uri);
			},

			learnNewLinkedDataUri: function($targetElement, uri) {
				var params = {
					uri: uri,
					text: $targetElement.val(),
					type: options.linkificationType
				};

				// TODO: covnert to POST again
				$.post('http://localhost:8080/semantifier/learnEntity', params);
			},

			/**
			 * Helper. Does *not* belong to public API.
			 *
			 * @param {jQuery} $targetElement the current target element which triggered the linkification
			 */
			findStorageInputField: function($targetElement) {
				var $inputField = $targetElement.next();

				var nameOfOriginalInputField = $targetElement.attr('name');
				$inputField.attr('name', nameOfOriginalInputField.substring(0, nameOfOriginalInputField.length - 1) + '_metadata]');

				return $inputField;
			}
		});

		$.extend(options, overrideOptions);

		return this.each(function() {
			var $this = $(this),
			lastValue = null,
			$popoverContent = $('<div class="sm-semantic linkification"><div class="linkification-results">Loading...</div><input type="text" placeholder="Insert custom Linked Data URI" /><button>Save!</button></div>');

			// Configuring the popover
			$this.popover({
				header: $('<div>Linkify</div>'),
				content: $popoverContent,
				closeEvent: function() {
				},
				openEvent: function() {
					if (lastValue === null) {
						// We opened the popup for the first time, and no data has been fetched yet. Thus we need to trigger an "entity text change" event
						$this.trigger('semantic-entityTextChange');
						// .. and we initialize the "custom data URI" save button
						$popoverContent.find('button').click(function() {
							options.storeLinkedDataUri($this, $popoverContent.find('input').val());
							options.learnNewLinkedDataUri($this, $popoverContent.find('input').val());
							// Trigger change event on the linked data URI, such that the UI highlights the currently selected element.
							$this.trigger('semantic-linkedDataUriChange');
						});
					}
				}
			});

			// Helper function which formats the result listing
			var showResults = function(results) {
				var html = '';
				results.forEach(function(result) {
					var description = '';
					if (result.description) {
						description = result.description;
					}

					if (description.length > 120) {
						description = description.substr(0, 120) + '...';
					}

					html += '<div class="linkification-result" title="' + result.id + '">';
					html += '<b>' + result.name + '</b>';
					html += '<div class="description">' + description + '</div>';
					html += '<a href="' + result.id + '" target="_blank">More Information...</a>';
					html += '</div>';
				});

				$popoverContent.find('.linkification-results').html(html);

				if (!$.fn.popover.openedPopup || $.fn.popover.openedPopup.length == 0 || $.fn.popover.openedPopup[0] !== $this[0]) {
					$this.trigger('showPopover');
				}

				// Trigger change event on the linked data URI, such that the UI highlights the currently selected element.
				$this.trigger('semantic-linkedDataUriChange');
			};

			// When the entity text changes, trigger the linkification
			$this.bind('semantic-entityTextChange', function() {
				var value = options.getEntity($this);
				if (value === lastValue) return;

				lastValue = value;
				options.getLinkificationResults($this, value, showResults);
			});

			// Helpers: When clicking a linkification result in the popover, we'll store that in the storage input field
			$('.linkification-result', $popoverContent[0]).live('click', function() {
				options.storeLinkedDataUri($this, $(this).attr('title'));
				$this.trigger('semantic-linkedDataUriChange');
			});

			// When the storage input field changes, we will update the linkification result selection
			$this.bind('semantic-linkedDataUriChange', function() {
				var currentUri = options.getLinkedDataUri($this);
				$('.linkification-result.selected', $popoverContent[0]).removeClass('selected');
				var $selectedLinkedDataElement = $('.linkification-result[title="' + currentUri + '"]', $popoverContent[0]);
				if ($selectedLinkedDataElement.length >= 1) {
					$selectedLinkedDataElement.addClass('selected');
				} else {
					// No selected element found; thus we update the input field
					$popoverContent.find('input').val(currentUri);
				}
			});

			// Custom Initialization
			options.init($this);
		});
	};

	/**
	 * ==========================
	 * Continuous Text Enrichment
	 * ==========================
	 */
	$.fn.continuousTextEnrichment = function() {
		// Helper
		var options = {
			findStorageInputField: function($this) {
				var $inputField = $this.next();

				var nameOfOriginalInputField = $this.attr('name');
				$inputField.attr('name', nameOfOriginalInputField.substring(0, nameOfOriginalInputField.length - 1) + '_continuousTextMetadata]');

				return $inputField;
			}
		};

		return this.each(function() {
			var $this = $(this),
			$storageInputField = options.findStorageInputField($this),
			$enrichmentWidget,
			$enrichmentButton;

			// Insert necessary markup
			$this.after('<div class="sm-semantic enrichmentWidget"></div>');
			$enrichmentWidget = $this.next('.sm-semantic.enrichmentWidget');

			$enrichmentWidget.after('<div class="sm-semantic enrichmentButton"><div class="frontside">Enrich!</div><div class="backside">go back</div></div>');
			$enrichmentButton = $enrichmentWidget.next('.sm-semantic.enrichmentButton');

			// Set up enrichment widget
			var h = $this.height();
			var w = $this.width();
			$enrichmentWidget.height(h);
			$enrichmentWidget.width(w);
			$enrichmentWidget.css('font-size', $this.css('font-size'));
			$enrichmentWidget.css('font-family', $this.css('font-family'));
			$enrichmentWidget.css('margin', $this.css('margin'));
			$enrichmentWidget.css('padding', $this.css('padding'));


			// Helper which can store all annotations which are selected in a hidden field
			var storeAnnotationsInHiddenField = function() {
				var annotations = [];
				$enrichmentWidget.find('.rdf-annotation[about]').each(function() {
					var $annotation = $(this);
					annotations.push({
						uri: $annotation.attr('about'),
						offset: $annotation.attr('data-offset'),
						length: $annotation.attr('data-length')
					});
				});
				$storageInputField.val(JSON.stringify(annotations));
			};

			// Helper which shows the annotations from the server in the enrichment widget.
			var showAnnotationsInEnrichmentWidget = function(results) {
				var text = $this.val();
				var resultingText = '';
				var currentResultIndex = 0;
				var entities = results['entities'];
				var alreadyStoredAnnotations = [];
				if ($storageInputField.val()) {
					try {
						alreadyStoredAnnotations = JSON.parse($storageInputField.val());
					} catch(e) {
						alreadyStoredAnnotations = [];
					}
				}

				var getUri = function(offset) {
					var uri = null;
					if (alreadyStoredAnnotations) {
						$.each(alreadyStoredAnnotations, function(index, annotation) {
							if (parseInt(annotation.offset) == offset) {
								uri = annotation.uri;
							}
						});
					}
					return uri;
				}

				for (var i=0; i<text.length; i++) {
					if (entities[currentResultIndex] && entities[currentResultIndex]['offset'] == i) {
						var uri = getUri(i);
						var aboutAttribute = uri?'about="' + uri + '"' : '';
						resultingText += '<span data-offset="' + i + '" ' + aboutAttribute + ' data-length="' + entities[currentResultIndex]['length'] + '" class="rdf-annotation' + (entities[currentResultIndex]['links'].length == 0 ? ' rdf-annotation-nolinkification' : '') + '" data-linkificationresults="' + JSON.stringify(entities[currentResultIndex]).replace(/"/g, '&quot;') + '">';
						resultingText += text[i];
					} else if (entities[currentResultIndex] && entities[currentResultIndex]['offset'] + entities[currentResultIndex]['length']-1 == i) {
						currentResultIndex++;
						resultingText += text[i];
						resultingText += '</span>';
					} else {
						resultingText += text[i];
					}
					if (text[i] === "\n") {
						resultingText += '<br />';
					}
				}
				$enrichmentWidget.html(resultingText);

				// Configure the "Semantic Lookup" widget
				$enrichmentWidget.find('.rdf-annotation').semanticLookup({
					init: function($targetElement) {
					},
					getEntity: function($targetElement) {
						return $targetElement.html();
					},
					storeLinkedDataUri: function($targetElement, uri) {
						$targetElement.attr('about', uri);
						storeAnnotationsInHiddenField();
					},
					getLinkedDataUri: function($targetElement) {
						return $targetElement.attr('about');
					},
					getLinkificationResults: function($targetElement, value, resultRendererCallback) {
						var results = JSON.parse($targetElement.attr('data-linkificationresults'));
						resultRendererCallback(results.links);
					}
				});
			};

			var currentAnnotationResults = null;
			var currentlyInEnrichmentMode = false;
			// Main entry point: when enrichment button is clicked, we start
			$enrichmentButton.click(function() {
				//$this.hide();
				if (currentlyInEnrichmentMode) {
					currentlyInEnrichmentMode = false;
					$enrichmentButton.parents('.externalReferenceWrapperOuter').first().removeClass('foo');
				} else {
					currentlyInEnrichmentMode = true;

					$enrichmentWidget.html($this.val().replace(/\n/g, '<br />'));

					$enrichmentWidget.css('display', 'inline-block');
					$enrichmentButton.parents('.externalReferenceWrapperOuter').first().addClass('foo');

					showAnnotationsInEnrichmentWidget(currentAnnotationResults);
				}

				//$enrichmentButton.hide();
			});

			// Monitor text changes, and move annotations around if needed
			$this.monitorTextChanges();

			var semantifierCurrentlyRunning = false;
			var diffStack = [];
			var applyDiffStackToCurrentAnnotationResults = function() {
				for (var i=0; i < diffStack.length; i++) {
					var diff = diffStack[i];

					var annotationIndexesToThrowAway = [];
					for (var a=0; a < currentAnnotationResults.entities.length; a++) {
						var annotation = currentAnnotationResults.entities[a];
						// TODO: the following code is almost identical with the code which moves the *already existing* annotations around. can we share it??
						if (annotation.offset < diff.position && annotation.offset + annotation.length < diff.position) {
							// Annotation is fully before the changed area, we do not need to modify it at all.
						} else if (annotation.offset > diff.position + diff.lengthBefore) {
							// Start of annotation is *after* the modified section; so we need to just move it around
							annotation.offset += diff.lengthAfter - diff.lengthBefore;
						} else {
							// Here, annotation is somehow touched by the changed area.
							// Thus, we throw it away, as it needs to be re-annotated.
							annotationIndexesToThrowAway.push(a);
						}
					}

					// Deleting every index which was touched
					for (var index in annotationIndexesToThrowAway) {
						delete currentAnnotationResults.entities[index];
					}
				}
			};
			// Already annotate in the background
			$this.bind('textChangeWithDiff', function(event, diff) {
				if (semantifierCurrentlyRunning) {
					diffStack.push(diff);
					return;
				} else {
					semantifierCurrentlyRunning = true;
					$.post('http://localhost:8080/semantifier/annotate', {
						text: $this.val()
					}, function(results) {
						semantifierCurrentlyRunning = false;
						currentAnnotationResults = results;
						applyDiffStackToCurrentAnnotationResults();
						diffStack = [];
					});

				}
			});

			// Adjust already saved annotations
			$this.bind('textChangeWithDiff', function(event, diff) {
				var oldAnnotations = [];
				if ($storageInputField.val()) {
					try {
						oldAnnotations = JSON.parse($storageInputField.val());
					} catch(e) {
						oldAnnotations = [];
					}
				}
				var newAnnotations = [];
				$.each(oldAnnotations, function(index, annotation) {
					annotation.offset = parseInt(annotation.offset);
					annotation.length = parseInt(annotation.length);

					if (annotation.offset < diff.position && annotation.offset + annotation.length < diff.position) {
						// Annotation is fully before the changed area, we do not need to modify it at all.
						newAnnotations.push(annotation);
						return;
					}
					if (annotation.offset > diff.position + diff.lengthBefore) {
						// Start of annotation is *after* the modified section; so we need to just move it around
						annotation.offset += diff.lengthAfter - diff.lengthBefore;
						newAnnotations.push(annotation);
						return;
					}

					// Here, annotation is somehow touched by the changed area.
					// Thus, we throw it away, as it needs to be re-annotated.
				});
				$storageInputField.val(JSON.stringify(newAnnotations));
			});
		});
	};

	/**
	 * =====================
	 * Embedding in Document
	 * =====================
	 */
	$(document).ready(function() {
		$('.sm-semantic.externalReference').each(function() {
			var $this = $(this);
			$this.prev().semanticLookup({
				linkificationType: $this.data('rdf-linkification-type')
			});
		});

		$('.sm-semantic.continuousText').prev().each(function() {
			var $this = $(this);

			var $container = $(this);
			$container = $container.add($this.next('.sm-semantic.continuousText'));

			var $wrapper = $('<div class="sm-semantic externalReferenceWrapper"></div>');
			$container.wrapAll($wrapper);

			$this.continuousTextEnrichment();
		});

		$('.sm-semantic.externalReferenceWrapper').wrap($('<div class="sm-semantic externalReferenceWrapperOuter"></div>'));

	});

})(jQuery);