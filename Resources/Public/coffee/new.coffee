$ = window.jQuery
$.defer = (delay, timerDataName, callback) ->

	timer = undefined

	if !callback
		callback = timerDataName
		timerDataName = undefined



	# Return the callback proxy
	return (args...) ->

		# Reset the delay

		clearTimeout(if timerDataName then $.data(this, timerDataName) else timer)

		# Delay the real callback
		timer = setTimeout(=>
			callback.apply(this, args);
		, delay)

		if timerDataName
			$.data(this, timerDataName, timer);



# Self-programmed

# Calculate a diff between two strings
# ====================================
#
# Return Object:
#
# * action: one of "remove", "insert", "modify", if the text length has decreased, increased, stayed the same
# * position: Number. After which character did the modifications start?
# * lengthBefore: Number of characters which were modified in the change -- before.
# * lengthAfter: Number of characters which were modified in the change -- after.
$.semantic_helper_calculateChanges = (oldText, newText) ->
	commonPrefix = 0
	commonSuffix = 0
	lengthBefore = 0
	lengthAfter = 0
	position = 0
	action = ''

	minimumLength = Math.min(oldText.length, newText.length)

	for i in [0..minimumLength]
		if oldText[i] == newText[i]
			commonPrefix++
		else
			break


	for i in [0..minimumLength]
		if oldText[oldText.length - i - 1] == newText[newText.length - i - 1]
			commonSuffix++
		else
			break


	if commonPrefix + commonSuffix >  minimumLength
		commonSuffix = minimumLength - commonPrefix


	# Now, we have commonPrefix and commonSuffix calculated
	if newText.length < oldText.length
		action = 'remove'
	else if newText.length > oldText.length
		action = 'insert'
	else
		action = 'modify'

	position = commonPrefix
	lengthBefore = oldText.length - commonPrefix - commonSuffix
	lengthAfter = newText.length - commonPrefix - commonSuffix

	return {
		commonPrefix
		commonSuffix
		action
		lengthBefore
		lengthAfter
		position
	}

# Monitor text changes in a textfield or an input field, and trigger "textChangeWithDiff" events
$.fn.monitorTextChanges = ->
	return this.each(->
		$this = $(this)
		lastValue = $this.val()

		changeCallback = ->
			currentValue = $this.val()
			if currentValue == lastValue
				return

			changes = $.semantic_helper_calculateChanges(lastValue, currentValue)

			$this.trigger('textChangeWithDiff', changes)

			lastValue = currentValue

		$this.change(changeCallback)
		$this.keyup(changeCallback)
	)

#
# =======================
# Semantic Lookup popover
# =======================
# used for linkification of *single entities*
#
$.fn.semanticLookup = (overrideOptions) ->
	options = {
		linkificationType: null
	};
	options = $.extend(options, {
		#
		# Lifecycle method which does further initialization.
		# In our case, it binds text field changes to the entityTextChange event
		#
		# @param {jQuery} $targetElement the current target element which triggered the linkification
		#
		init: ($targetElement) ->
			$targetElement.keyup($.defer(300, ->
				$targetElement.trigger('semantic-entityTextChange')
			))

		#
		# @param {jQuery} $targetElement the current target element which triggered the linkification
		# @return {String} entity string which should be linkified, like "Microsoft", "Sebastian Kurf√ºrst", "TYPO3"
		#
		getEntity: ($targetElement) ->
			return $targetElement.val()

		#
		# @param {jQuery} $targetElement the current target element which triggered the linkification
		# @param {String} value the Entity which should be looked up
		# @param {Function} resultRendererCallback the result rendering function must be triggered as soon as data is available.
		#
		getLinkificationResults: ($this, value, resultRendererCallback) ->
			params = {
				text: value
			}
			if options.linkificationType
				console.log("SET TYPE")
				params.type = options.linkificationType

			$.get('http://localhost:8080/semantifier/linkify', params, (results) ->
				resultRendererCallback(results)
			)

		#
		# @param {jQuery} $targetElement the current target element which triggered the linkification
		# @return {String} the linked data URI which has been selected, if exists.
		#
		getLinkedDataUri: ($targetElement) ->
			field = options.findStorageInputField($targetElement)
			return field.val()

		#
		# @param {jQuery} $targetElement the current target element which triggered the linkification
		# @param {String} uri the URI to store
		#
		storeLinkedDataUri: (targetElement, uri) ->
			options.findStorageInputField(targetElement).val(uri)

		learnNewLinkedDataUri: ($targetElement, uri) ->
			params = {
				uri,
				text: $targetElement.val(),
				type: options.linkificationType
			};

			$.post('http://localhost:8080/semantifier/learnEntity', params);

		#
		# Helper. Does *not* belong to public API.
		#
		# @param {jQuery} $targetElement the current target element which triggered the linkification
		#
		findStorageInputField: ($targetElement) ->
			$inputField = $targetElement.next();

			nameOfOriginalInputField = $targetElement.attr('name');
			$inputField.attr('name', nameOfOriginalInputField.substring(0, nameOfOriginalInputField.length - 1) + '_metadata]');

			return $inputField;

	})

	$.extend(options, overrideOptions)

	return this.each(->
		$this = $(this);
		lastValue = null;
		$popoverContent = $('<div class="sm-semantic linkification"><div class="linkification-results">Loading...</div><input type="text" placeholder="Insert custom Linked Data URI" /><button>Save!</button></div>');

		# Configuring the popover
		$this.popover({
			header: $('<div>Linkify</div>'),
			content: $popoverContent,
			closeEvent: ->
				# empty fn
			openEvent: ->
				if lastValue == null
					# We opened the popup for the first time, and no data has been fetched yet. Thus we need to trigger an "entity text change" event
					$this.trigger('semantic-entityTextChange');
					# .. and we initialize the "custom data URI" save button
					$popoverContent.find('button').click(->
						options.storeLinkedDataUri($this, $popoverContent.find('input').val());
						options.learnNewLinkedDataUri($this, $popoverContent.find('input').val());
						# Trigger change event on the linked data URI, such that the UI highlights the currently selected element.
						$this.trigger('semantic-linkedDataUriChange');
					);

		})

		# Helper function which formats the result listing
		showResults = (results) ->
			html = '';
			results.forEach((result) ->
				description = ''
				if result.description
					description = result.description;

				if description.length > 120
					description = description.substr(0, 120) + '...';

				html += '<div class="linkification-result" title="' + result.id + '">';
				html += '<b>' + result.name + '</b>';
				html += '<div class="description">' + description + '</div>';
				html += '<a href="' + result.id + '">More Information...</a>';
				html += '</div>';
			);

			$popoverContent.find('.linkification-results').html(html);


			# Trigger change event on the linked data URI, such that the UI highlights the currently selected element.
			$this.trigger('semantic-linkedDataUriChange');


		# When the entity text changes, trigger the linkification
		$this.bind('semantic-entityTextChange', ->
			value = options.getEntity($this)
			if value == lastValue
				return

			lastValue = value
			options.getLinkificationResults($this, value, showResults)
		);

		# Helpers: When clicking a linkification result in the popover, we'll store that in the storage input field
		$('.linkification-result', $popoverContent[0]).live('click', ->
			options.storeLinkedDataUri($this, $(this).attr('title'));
			$this.trigger('semantic-linkedDataUriChange');
		);

		# When the storage input field changes, we will update the linkification result selection
		$this.bind('semantic-linkedDataUriChange', ->
			currentUri = options.getLinkedDataUri($this);
			$('.linkification-result.selected', $popoverContent[0]).removeClass('selected');
			$selectedLinkedDataElement = $('.linkification-result[title="' + currentUri + '"]', $popoverContent[0]);
			if $selectedLinkedDataElement.length >= 1
				$selectedLinkedDataElement.addClass('selected');
			else
				# No selected element found; thus we update the input field
				$popoverContent.find('input').val(currentUri)
		);

		# Custom Initialization
		options.init($this);
	)


# TODO continue with "Continuous text enrichment"??