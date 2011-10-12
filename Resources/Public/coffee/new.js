(function() {
  var $;
  var __slice = Array.prototype.slice, __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };
  $ = window.jQuery;
  $.defer = function(delay, timerDataName, callback) {
    var timer;
    timer = void 0;
    if (!callback) {
      callback = timerDataName;
      timerDataName = void 0;
    }
    return function() {
      var args;
      args = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
      clearTimeout(timerDataName ? $.data(this, timerDataName) : timer);
      timer = setTimeout(__bind(function() {
        return callback.apply(this, args);
      }, this), delay);
      if (timerDataName) {
        return $.data(this, timerDataName, timer);
      }
    };
  };
  $.semantic_helper_calculateChanges = function(oldText, newText) {
    var action, commonPrefix, commonSuffix, i, lengthAfter, lengthBefore, minimumLength, position;
    commonPrefix = 0;
    commonSuffix = 0;
    lengthBefore = 0;
    lengthAfter = 0;
    position = 0;
    action = '';
    minimumLength = Math.min(oldText.length, newText.length);
    for (i = 0; 0 <= minimumLength ? i <= minimumLength : i >= minimumLength; 0 <= minimumLength ? i++ : i--) {
      if (oldText[i] === newText[i]) {
        commonPrefix++;
      } else {
        break;
      }
    }
    for (i = 0; 0 <= minimumLength ? i <= minimumLength : i >= minimumLength; 0 <= minimumLength ? i++ : i--) {
      if (oldText[oldText.length - i - 1] === newText[newText.length - i - 1]) {
        commonSuffix++;
      } else {
        break;
      }
    }
    if (commonPrefix + commonSuffix > minimumLength) {
      commonSuffix = minimumLength - commonPrefix;
    }
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
    };
  };
  $.fn.monitorTextChanges = function() {
    return this.each(function() {
      var $this, changeCallback, lastValue;
      $this = $(this);
      lastValue = $this.val();
      changeCallback = function() {
        var changes, currentValue;
        currentValue = $this.val();
        if (currentValue === lastValue) {
          return;
        }
        changes = $.semantic_helper_calculateChanges(lastValue, currentValue);
        $this.trigger('textChangeWithDiff', changes);
        return lastValue = currentValue;
      };
      $this.change(changeCallback);
      return $this.keyup(changeCallback);
    });
  };
  $.fn.semanticLookup = function(overrideOptions) {
    var options;
    options = {
      linkificationType: null
    };
    options = $.extend(options, {
      init: function($targetElement) {
        return $targetElement.keyup($.defer(300, function() {
          return $targetElement.trigger('semantic-entityTextChange');
        }));
      },
      getEntity: function($targetElement) {
        return $targetElement.val();
      },
      getLinkificationResults: function($this, value, resultRendererCallback) {
        var params;
        params = {
          text: value
        };
        if (options.linkificationType) {
          console.log("SET TYPE");
          params.type = options.linkificationType;
        }
        return $.get('http://localhost:8080/semantifier/linkify', params, function(results) {
          return resultRendererCallback(results);
        });
      },
      getLinkedDataUri: function($targetElement) {
        var field;
        field = options.findStorageInputField($targetElement);
        return field.val();
      },
      storeLinkedDataUri: function(targetElement, uri) {
        return options.findStorageInputField(targetElement).val(uri);
      },
      learnNewLinkedDataUri: function($targetElement, uri) {
        var params;
        params = {
          uri: uri,
          text: $targetElement.val(),
          type: options.linkificationType
        };
        return $.post('http://localhost:8080/semantifier/learnEntity', params);
      },
      findStorageInputField: function($targetElement) {
        var $inputField, nameOfOriginalInputField;
        $inputField = $targetElement.next();
        nameOfOriginalInputField = $targetElement.attr('name');
        $inputField.attr('name', nameOfOriginalInputField.substring(0, nameOfOriginalInputField.length - 1) + '_metadata]');
        return $inputField;
      }
    });
    $.extend(options, overrideOptions);
    return this.each(function() {
      var $popoverContent, $this, lastValue, showResults;
      $this = $(this);
      lastValue = null;
      $popoverContent = $('<div class="sm-semantic linkification"><div class="linkification-results">Loading...</div><input type="text" placeholder="Insert custom Linked Data URI" /><button>Save!</button></div>');
      $this.popover({
        header: $('<div>Linkify</div>'),
        content: $popoverContent,
        closeEvent: function() {},
        openEvent: function() {
          if (lastValue === null) {
            $this.trigger('semantic-entityTextChange');
            return $popoverContent.find('button').click(function() {
              options.storeLinkedDataUri($this, $popoverContent.find('input').val());
              options.learnNewLinkedDataUri($this, $popoverContent.find('input').val());
              return $this.trigger('semantic-linkedDataUriChange');
            });
          }
        }
      });
      showResults = function(results) {
        var html;
        html = '';
        results.forEach(function(result) {
          var description;
          description = '';
          if (result.description) {
            description = result.description;
          }
          if (description.length > 120) {
            description = description.substr(0, 120) + '...';
          }
          html += '<div class="linkification-result" title="' + result.id + '">';
          html += '<b>' + result.name + '</b>';
          html += '<div class="description">' + description + '</div>';
          html += '<a href="' + result.id + '">More Information...</a>';
          return html += '</div>';
        });
        $popoverContent.find('.linkification-results').html(html);
        return $this.trigger('semantic-linkedDataUriChange');
      };
      $this.bind('semantic-entityTextChange', function() {
        var value;
        value = options.getEntity($this);
        if (value === lastValue) {
          return;
        }
        lastValue = value;
        return options.getLinkificationResults($this, value, showResults);
      });
      $('.linkification-result', $popoverContent[0]).live('click', function() {
        options.storeLinkedDataUri($this, $(this).attr('title'));
        return $this.trigger('semantic-linkedDataUriChange');
      });
      $this.bind('semantic-linkedDataUriChange', function() {
        var $selectedLinkedDataElement, currentUri;
        currentUri = options.getLinkedDataUri($this);
        $('.linkification-result.selected', $popoverContent[0]).removeClass('selected');
        $selectedLinkedDataElement = $('.linkification-result[title="' + currentUri + '"]', $popoverContent[0]);
        if ($selectedLinkedDataElement.length >= 1) {
          return $selectedLinkedDataElement.addClass('selected');
        } else {
          return $popoverContent.find('input').val(currentUri);
        }
      });
      return options.init($this);
    });
  };
}).call(this);
