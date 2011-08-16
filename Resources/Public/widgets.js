(function($){

	/**
	 * HELPERS
	 */
	// Taken from https://github.com/jollytoad/jquery.plugins/blob/master/src/jquery.defer.js
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

	/**
	 * Semantic Lookup popover
	 */
	$.fn.semanticLookup = function() {
		// Options
		var options = {
			getValueCallback: function(el) {
				return el.val();
			},
			findStorageInputField: function($this) {
				var $inputField = $this.next();

				var nameOfOriginalInputField = $this.attr('name');
				$inputField.attr('name',nameOfOriginalInputField.substring(0, nameOfOriginalInputField.length - 1) + '_metadata]');

				return $inputField;
			}
		};

		return this.each(function() {
			var $this = $(this),
			    lastValue = null,
			    $popoverContent = $('<div class="sm-semantic linkification">Loading...</div>'),
				$storageInputField = options.findStorageInputField($this);

			// Configuring the popover
			$this.popover({
				header: $('<div>Linkify</div>'),
				content: $popoverContent,
				closeEvent: function() {
				},
				openEvent: function() {
					if (lastValue === null) {
						// We opened the popup, for the first time,
						// but no data has been fetched yet. Thus we need to trigger a "change" event
						$this.trigger('keyup');
					}
				}
			});

			// Binding to the target object's keyup event, and fetching the data from there
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
					html += '</div>';
				});
				$popoverContent.html(html);

				// Trigger change event on storage input field, such that the active element (if any) gets displayed.
				$storageInputField.change();
			};

			$this.bind('keyup', $.defer(300, function() {
				var value = options.getValueCallback($(this));
				if (value === lastValue) return;

				lastValue = value;
				$.get('http://localhost:8080/semantifier/linkify', {
					text: value
					// TODO: support for entity type
				}, function(results) {
					showResults(results);
				});
			}));

			// Helpers: When clicking a linkification result in the popover, we'll store that in the storage input field
			$('.linkification-result', $popoverContent[0]).live('click', function() {
				$storageInputField.val($(this).attr('title'));
				$storageInputField.change();
			});

			// When the storage input field changes, we will update the linkification result selection
			$storageInputField.change(function() {
				$('.linkification-result.selected', $popoverContent[0]).removeClass('selected');
				$('.linkification-result[title="' + $storageInputField.val() + '"]', $popoverContent[0]).addClass('selected');
			});


		});
	};

	$(document).ready(function() {
		$('.sm-semantic.externalReference').prev().semanticLookup();
	})
})(jQuery);