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
		var options;
		options = {

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
			 * @return {String} entity string which should be linkified, like "Microsoft", "Sebastian Kurfürst", "TYPO3"
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
				$.get('http://localhost:8080/semantifier/linkify', {
					text: value
					// TODO: support for entity type
				}, function(results) {
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
		};

		$.extend(options, overrideOptions);

		return this.each(function() {
			var $this = $(this),
			    lastValue = null,
			    $popoverContent = $('<div class="sm-semantic linkification">Loading...</div>');

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
					html += '</div>';
				});
				$popoverContent.html(html);

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
				$('.linkification-result[title="' + currentUri + '"]', $popoverContent[0]).addClass('selected');
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

			$enrichmentWidget.after('<div class="sm-semantic enrichmentButton">Enrich!</div>');
			$enrichmentButton = $enrichmentWidget.next('.sm-semantic.enrichmentButton');

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
				var text = $this.html();
				var resultingText = '';
				var currentResultIndex = 0;
				var entities = results['entities'];

				for (var i=0; i<text.length; i++) {
					if (entities[currentResultIndex] && entities[currentResultIndex]['offset'] == i) {
						resultingText += '<span data-offset="' + i + '" data-length="' + entities[currentResultIndex]['length'] + '" class="rdf-annotation' + (entities[currentResultIndex]['links'].length == 0 ? ' rdf-annotation-nolinkification' : '') + '" data-linkificationresults="' + JSON.stringify(entities[currentResultIndex]).replace(/"/g, '&quot;') + '">';
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

			// Main entry point: when enrichment button is clicked, we start
			$enrichmentButton.click(function() {
				var h = $this.height();
				var w = $this.width();

				$this.hide();
				$enrichmentButton.hide();

				// TODO: nl2br for the enrichment HTML
				$enrichmentWidget.html($this.val());
				$enrichmentWidget.height(h);
				$enrichmentWidget.width(w);
				$enrichmentWidget.css('font-size', $this.css('font-size'));
				$enrichmentWidget.css('font-family', $this.css('font-family'));
				$enrichmentWidget.css('margin', $this.css('margin'));
				$enrichmentWidget.css('padding', $this.css('padding'));
				$enrichmentWidget.css('display', 'inline-block');

				showAnnotationsInEnrichmentWidget({"language":"en","entities":[{"entity":"Bill Gates","offset":0,"length":10,"mostLikelyTagName":"http://xmlns.com/foaf/0.1/Person","links":[{"id":"http://dbpedia.org/resource/Bill_Gates","type":"http://dbpedia.org/ontology/Person","name":"Bill Gates","description":"William Henry \"Bill\" Gates III (born October 28, 1955) is an American business magnate, philanthropist, author and chairman of Microsoft, the software company he founded with Paul Allen. He is consistently ranked among the world's wealthiest people and was the wealthiest overall from 1995 to 2009, excluding 2008, when he was ranked third.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Bill_Gates%27_house","type":"","name":"Bill Gates' house","description":"Bill Gates' house is a large earth-sheltered mansion in the side of a hill overlooking Lake Washington in Medina, Washington. The 66,000 sq ft (6,100 m) house is noted for its design and the technology it incorporates. It is nicknamed Xanadu 2.0. In 2009, property taxes were reported to be US $1.063 million on a total assessed value of US$147.5 million.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Bill_Gates%27_flower_fly","type":"http://dbpedia.org/ontology/Insect","name":"Bill Gates' flower fly","description":"Bill Gates' flower fly (Eristalis gatesi) is a flower fly found only in Costa Rican high montane cloud forests and named after Bill Gates. Another fly found in similar habitats was named after Gates' associate Paul Allen, called Paul Allen's flower fly; according to Chris Thompson, the describer of these species, both names were in \"recognition of [their their] great contributions to the science of Dipterology\".","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Bill_Gates_%28frontiersman%29","type":"","name":"Bill Gates (frontiersman)","description":"\"Swiftwater\" Bill Gates (? - 1935) was an American frontiersman and fortune hunter, and a fixture in stories of the Klondike Gold Rush. He made and lost several fortunes, and died in Peru in 1935 pursuing a silver strike. In one famous Klondike story he presented Dawson dance hall girl Gussie Lamore her weight in gold. Gates was married briefly to Grace Lamore in 1898; he later married Bera Beebe, with whom he fathered two sons, Fredrick and Clifford.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/%5BBill_Gates","type":"","name":"[Bill Gates","description":"","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Bill_Gates","type":null,"name":"比尔·盖茨","linkifierName":"sindice"},{"id":"http://dbpedia.org/page/William_H._Gates,_Sr.","type":null,"name":"About: William H. Gates, Sr.","linkifierName":"sindice"},{"id":"http://dblp.l3s.de/d2r/resource/authors/Bill_Gates","type":null,"name":"Bill Gates","linkifierName":"sindice"},{"id":"http://www.slideshare.net/maagila/bill-gates-4383186","type":null,"name":"Bill gates","linkifierName":"sindice"},{"id":"http://www.slideshare.net/mariusalberticus/bill-gates-2561394","type":null,"name":"Bill Gates","linkifierName":"sindice"},{"id":"http://www.slideshare.net/guest612291/bill-gates-515117","type":null,"name":"Bill Gates","linkifierName":"sindice"},{"id":"http://www.slideshare.net/dexternica/bill-gates-1903205","type":null,"name":"Bill Gates","linkifierName":"sindice"},{"id":"http://www.slideshare.net/charlesgshen/bill-gates-2172241","type":null,"name":"Bill Gates","linkifierName":"sindice"},{"id":"http://www.stiridebine.ro/tag/bill-gates","type":null,"name":"\"Bill Gates\"","linkifierName":"sindice"},{"id":"http://www.semanlink.net/tag/bill_gates","type":null,"name":"Bill Gates","linkifierName":"sindice"}]},{"entity":"Founder of Microsoft","offset":12,"length":20,"mostLikelyTagName":"http://dbpedia.org/ontology/Company","links":[{"id":"http://www.thaindian.com/newsportal/world-news/microsoft-founder-criticizes-apple-and-google_100529908.html","type":null,"name":"\"Microsoft founder criticizes Apple and Google\"","linkifierName":"sindice"},{"id":"http://trak.in/Tags/Business/microsoft-founder/","type":null,"name":"\"microsoft founder\"","linkifierName":"sindice"},{"id":"http://in.news.yahoo.com/microsoft-founder-criticizes-apple-google-111306844.html","type":null,"name":"\"Microsoft founder criticizes Apple and Google - Yahoo! News\"","linkifierName":"sindice"},{"id":"http://news.yahoo.com/last-xbox-founder-leaves-microsoft-221446399.html","type":null,"name":"\"Last Xbox Founder Leaves Microsoft - Yahoo! News\"","linkifierName":"sindice"},{"id":"http://www.thaindian.com/newsportal/sci-tech/microsoft-co-founder-sues-11-companies_100419363.html","type":null,"name":"\"Microsoft co-founder sues 11 companies\"","linkifierName":"sindice"},{"id":"http://www.thedailybeast.com/cheats/2011/03/30/microsoft-co-founder-slams-bill-gates.html","type":null,"name":"\"Microsoft Co-Founder Slams Bill Gates - The Daily Beast\"","linkifierName":"sindice"},{"id":"http://www.geekextreme.com/technology-news-archives/Gentoo_founder_joins_Microsoft-4533","type":null,"name":"\"Gentoo founder joins Microsoft\"","linkifierName":"sindice"},{"id":"http://www.mixx.com/stories/43955484/microsoft_co_founder_hits_out_at_gates_wsj_com","type":null,"name":"Microsoft Co-Founder Hits Out at Gates - WSJ.com - Mixx","linkifierName":"sindice"},{"id":"http://www.computerworld.com.au/article/252264/vmware_ousts_founder_hires_ex-microsoft_bigwig","type":null,"name":"\"VMware ousts founder, hires ex-Microsoft bigwig - Computerworld\"","linkifierName":"sindice"},{"id":"http://www.damego.com/microsoft-co-founder-paul-allen-suing-major-web-companies","type":null,"name":"\"Microsoft Co-Founder Paul Allen Suing Major Web Companies | Damego\"","linkifierName":"sindice"}]},{"entity":"He","offset":49,"length":2,"mostLikelyTagName":"http://dbpedia.org/ontology/Company","links":[{"id":"http://dbpedia.org/resource/Heavy_metal_music","type":"http://dbpedia.org/ontology/MusicGenre","name":"Heavy metal music","description":"Heavy metal (often referred to simply as metal) is a genre of rock music that developed in the late 1960s and early 1970s, largely in the United Kingdom and the United States. With roots in blues-rock and psychedelic rock, the bands that created heavy metal developed a thick, massive sound, characterized by highly amplified distortion, extended guitar solos, emphatic beats, and overall loudness. Heavy metal lyrics and performance styles are generally associated with masculinity and machismo.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Helsinki","type":"","name":"Helsinki","description":"Helsinki is the capital and largest city in Finland. It is in the southern part of Finland, in the region of Uusimaa/Nyland, on the shore of the Gulf of Finland, by the Baltic Sea. The population of the city of Helsinki is 584,420, making it the most populous municipality in Finland by a wide margin. Helsinki is located some 400 kilometres (250 mi) east of Stockholm, Sweden, 300 kilometres (190 mi) west of St. Petersburg, Russia and 80 kilometres (50 mi) north of Tallinn, Estonia.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Myocardial_infarction","type":"http://dbpedia.org/ontology/Disease","name":"Myocardial infarction","description":"Myocardial infarction (MI) or acute myocardial infarction (AMI), commonly known as a heart attack, is the interruption of blood supply to part of the heart, causing heart cells to die. This is most commonly due to occlusion (blockage) of a coronary artery following the rupture of a vulnerable atherosclerotic plaque, which is an unstable collection of lipids (fatty acids) and white blood cells in the wall of an artery.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Hectare","type":"","name":"Hectare","description":"The hectare is a unit of area, defined as 10,000 square metres, and primarily used in the measurement of land. In 1795, when the metric system was introduced, the are was defined as being 100 square metres and the hectare was thus 100 ares or 1/100 km.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Hebrew_language","type":"http://dbpedia.org/ontology/Language","name":"Hebrew language","description":"Hebrew (עִבְרִית, Ivrit) is a Semitic language of the Afro-Asiatic language family. Culturally, it is considered the Jewish language. Hebrew in its modern form is spoken by most of the seven million people in Israel while Classical Hebrew has been used for prayer or study in Jewish communities around the world for over two thousand years. It is one of the official languages of Israel, along with Arabic.","linkifierName":"dbpedia"}]},{"entity":"Berlin","offset":75,"length":6,"mostLikelyTagName":"http://dbpedia.org/ontology/Company","links":[{"id":"http://dbpedia.org/resource/Berlin","type":"http://dbpedia.org/ontology/City","name":"Berlin","description":"Berlin is the capital city of Germany and one of sixteen states of Germany. With a population of 3.4 million people, Berlin is Germany's largest city. It is the second most populous city proper and the eighth most populous urban area in the European Union. Located in northeastern Germany, it is the center of the Berlin-Brandenburg Metropolitan Area, comprising 5 million people from over 190 nations.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Berlin_Wall","type":"","name":"Berlin Wall","description":"The Berlin Wall was a barrier constructed by the German Democratic Republic starting August 13, 1961, that completely cut off West Berlin from surrounding East Germany and from East Berlin. The barrier included guard towers placed along large concrete walls, which circumscribed a wide area (later known as the \"death strip\") that contained anti-vehicle trenches, \"fakir beds\" and other defenses.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Berlin_International_Film_Festival","type":"http://dbpedia.org/ontology/Event","name":"Berlin International Film Festival","description":"The Berlin International Film Festival, also called the Berlinale, is one of the world's leading film festivals and most reputable media events. It is held in Berlin, Germany. Founded in 1951, the festival has been celebrated annually in February since 1978. With 274,000 tickets sold and 487,000 admissions it is considered the largest publicly-attended film festival worldwide. Up to 400 films are shown in several sections, representing a comprehensive array of the cinematic world.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Humboldt_University_of_Berlin","type":"http://dbpedia.org/ontology/EducationalInstitution","name":"Humboldt University of Berlin","description":"The Humboldt University of Berlin (German Humboldt-Universität zu Berlin) is Berlin's oldest university, founded in 1810 as the University of Berlin (Universität zu Berlin) by the liberal Prussian educational reformer and linguist Wilhelm von Humboldt, whose university model has strongly influenced other European and Western universities. From 1828 it was known as the Frederick William University (Friedrich-Wilhelms-Universität), later (unofficially) also as the Universität unter den Linden.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Berlin_S-Bahn","type":"","name":"Berlin S-Bahn","description":"The Berlin S-Bahn is a rapid transit system in and around Berlin, the capital city of Germany. It consists of 15 lines and is integrated with the mostly underground U-Bahn to form the backbone of Berlin's rapid transport system. Unlike the U-Bahn, the S-Bahn crosses the Berlin city and state border into the surrounding state of Brandenburg, mostly from the former East Berlin but today also from West Berlin to Potsdam.","linkifierName":"dbpedia"},{"id":"http://www4.wiwiss.fu-berlin.de/eurostat/resource/regions/Berlin","type":null,"name":"Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/PeopleFromBerlin","type":null,"name":"person from Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/UniversitiesInBerlin","type":null,"name":"university in Berlin","linkifierName":"sindice"},{"id":"http://www4.wiwiss.fu-berlin.de/eurostat/data/regions/Berlin","type":null,"name":"RDF Description of Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Air_Berlin","type":null,"name":"柏林航空","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Berlin_Anhalter_Bahnhof","type":null,"name":"Stazione di Berlin Anhalter Bahnhof","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/SongsByIrvingBerlin","type":null,"name":"song by Irving Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Technical_University_of_Berlin","type":null,"name":"Technical University of Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Berlin_G%C3%B6rlitzer_Bahnhof","type":null,"name":"Görlitzer Bahnhof","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Berlin_Sportpalast","type":null,"name":"Berlin Sportpalast","linkifierName":"sindice"}]},{"entity":"TYPO3","offset":101,"length":5,"mostLikelyTagName":"http://xmlns.com/foaf/0.1/Person","links":[{"id":"http://dbpedia.org/resource/TYPO3","type":"http://dbpedia.org/ontology/Software","name":"TYPO3","description":"TYPO3 is a free and open source content management system as well as a Model–view–controller (MVC) Web Application Development framework written in PHP. It is released under the GNU General Public License. It can run on Apache or IIS on top of Linux, Microsoft Windows, OS/2 or Mac OS X.","linkifierName":"dbpedia"},{"id":"http://rdfohloh.wikier.org/project/typo3","type":null,"name":"\"TYPO3\"","linkifierName":"sindice"},{"id":"http://blog.my-workstation.com/category/technik/typo3","type":null,"name":"\"Typo3\"","linkifierName":"sindice"},{"id":"http://www.rptech-world.com/tag/typo3","type":null,"name":"\"typo3\"","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/TYPO3","type":null,"name":"Typo3","linkifierName":"sindice"},{"id":"http://cmsreport.com/category/typo3","type":null,"name":"\"Typo3 | CMS Report\"","linkifierName":"sindice"},{"id":"http://www.lnx-world.de/topics/webtechnology/typo3/","type":null,"name":"\"Typo3\"","linkifierName":"sindice"},{"id":"http://hosting.ber-art.nl/typo3/","type":null,"name":"\"typo3\"","linkifierName":"sindice"},{"id":"http://www.sublogic.org/typo3/","type":null,"name":"Typo3","linkifierName":"sindice"},{"id":"http://herbertvandinther.com/tag/typo3/","type":null,"name":"\"Typo3\"","linkifierName":"sindice"},{"id":"http://dbpedia.org/page/TYPO3","type":null,"name":"About: TYPO3","linkifierName":"sindice"}]},{"entity":"Content Management System","offset":112,"length":25,"mostLikelyTagName":"http://dbpedia.org/ontology/Company","links":[{"id":"http://dbpedia.org/resource/Content_management_system","type":"","name":"Content management system","description":"A content management system (CMS) is the collection of procedures used to manage work flow in a collaborative environment. These procedures can be manual or computer-based. The procedures are designed to: Allow for a large number of people to contribute to and share stored data Control access to data, based on user roles.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Content_management_systems_for_school_websites","type":"","name":"Content management systems for school websites","description":"","linkifierName":"dbpedia"},{"id":"http://semanticweb.org/id/Category-3ASemantic_content_management_system","type":null,"name":"Semantic content management system","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Enterprise_content_management","type":null,"name":"Enterprise-Content-Management-System","linkifierName":"sindice"},{"id":"http://faviki.com/tag/Content_management_system","type":null,"name":"Content management system  | Faviki. Tags that make sense.","linkifierName":"sindice"},{"id":"http://faviki.com/person/Janos.Haits/tag/Content_management_system","type":null,"name":"Janos.Haits: Content management system  | Faviki. Tags that make sense.","linkifierName":"sindice"},{"id":"http://faviki.com/tag/Web_content_management_system","type":null,"name":"Web content management system  | Faviki. Tags that make sense.","linkifierName":"sindice"},{"id":"http://dbpedia.org/page/Content_management_system","type":null,"name":"About: Content management system","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Component_content_management_system","type":null,"name":"Component content management system","linkifierName":"sindice"},{"id":"http://www.slideshare.net/fishtech/vcgenius-content-management-system","type":null,"name":"VCGenius Content Management System","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/PHP_content_management_system","type":null,"name":"PHP content management system","linkifierName":"sindice"},{"id":"http://www.slideshare.net/jaspervangent/content-management-system-3048652","type":null,"name":"Content Management System","linkifierName":"sindice"}]},{"entity":"Kasper Skaarhoj","offset":158,"length":15,"mostLikelyTagName":"http://xmlns.com/foaf/0.1/Person","links":[]},{"entity":"Denmark","offset":184,"length":7,"mostLikelyTagName":"http://dbpedia.org/ontology/Company","links":[{"id":"http://dbpedia.org/resource/Denmark","type":"http://dbpedia.org/ontology/Country","name":"Denmark","description":"Denmark, officially the Kingdom of Denmark together with Greenland and the Faroe Islands, is a Scandinavian country in Northern Europe. It is the southernmost of the Nordic countries, southwest of Sweden and south of Norway, and bordered to the south by Germany. Denmark borders both the Baltic and the North Sea.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Denmark_national_football_team","type":"","name":"Denmark national football team","description":"The Denmark national football team is controlled by the Danish Football Association and has represented the country of Denmark in international football competitions since 1908. The team has been a solidly competitive side in international football since the mid-1980s, with the triumph in the 1992 European Championships (Euro 1992) tournament as its most prominent victory, beating the European champions in the semi-final, and the world champions in the final.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Denmark%E2%80%93Norway","type":"http://dbpedia.org/ontology/Country","name":"Denmark–Norway","description":"Denmark–Norway is the historiographical name for a former political entity consisting of the kingdoms of Denmark and Norway, including the originally Norwegian dependencies of Iceland, Greenland and the Faroe Islands. Following the strife surrounding the break-up of its predecessor, the Kalmar Union, the two kingdoms entered into another personal union in 1536 which lasted until 1814. The corresponding adjective and demonym is Dano-Norwegian.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Kingdom_of_Denmark","type":"http://dbpedia.org/ontology/Country","name":"Kingdom of Denmark","description":"The Kingdom of Denmark, or Danish Realm, is a constitutional monarchy and a community consisting of Denmark proper in northern Europe and two autonomous countries, the Faroe Islands in the North Atlantic and Greenland in North America, with Denmark as the hegemonial part, where the residual judicial, executive and legislative power rests. The relationship of the member states is referred to as Rigsfællesskabet.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Denmark_national_under-21_football_team","type":"","name":"Denmark national under-21 football team","description":"The Denmark national under-21 football team has played since 1976 and is controlled by the Danish Football Association. Before 1976, the age limit was 23 years.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/class/yago/GovernmentMinisterialOfficesOfDenmark","type":null,"name":"office of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/PrimeMinistersOfDenmark","type":null,"name":"minister of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/FootballVenuesInDenmark","type":null,"name":"venue in Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/UnitedStatesAmbassadorsToDenmark","type":null,"name":"ambassador to Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/OlympicFootballersOfDenmark","type":null,"name":"About: footballer of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/OlympicSailorsOfDenmark","type":null,"name":"sailor of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/LGBTPeopleFromDenmark","type":null,"name":"person from Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/FilmFestivalsInDenmark","type":null,"name":"About: festival in Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/OlympicWrestlersOfDenmark","type":null,"name":"wrestler of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/LGBTWritersFromDenmark","type":null,"name":"writer from Denmark","linkifierName":"sindice"}]},{"entity":"Sebastian Kurfuerst","offset":217,"length":19,"mostLikelyTagName":"http://xmlns.com/foaf/0.1/Person","links":[{"id":"http://sebastian.kurfuerst.eu/index.rdf","type":null,"name":"\"Sebastian Kurfürst, Dresden (FOAF Profile)\"","linkifierName":"sindice"}]},{"entity":"CFO","offset":245,"length":3,"mostLikelyTagName":"http://xmlns.com/foaf/0.1/Person","links":[{"id":"http://dbpedia.org/resource/Chief_financial_officer","type":"","name":"Chief financial officer","description":"The chief financial officer (CFO) is a corporate officer primarily responsible for managing the financial risks of the corporation. This officer is also responsible for financial planning and record-keeping, as well as financial reporting to higher management. In some sectors the CFO is also responsible for analysis of data. The title is equivalent to finance director, a common title in the United Kingdom.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFOX-FM","type":"http://dbpedia.org/ontology/Organisation","name":"CFOX-FM","description":"CFOX-FM (identified on air and in print as 99.3 The Fox) is a Canadian radio station in the Greater Vancouver region of British Columbia. It broadcasts at 99.3 MHz on the FM band with an effective radiated power of 75,000 watts from a transmitter on Mount Seymour in the District of North Vancouver. Studios are located in Downtown Vancouver, in the TD Tower. The station is owned by Corus Entertainment. CFOX has an active rock format.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFOS","type":"http://dbpedia.org/ontology/Organisation","name":"CFOS","description":"CFOS is an AM radio station broadcasting from downtown Owen Sound, Ontario, Canada. They play oldies and news (plus an adult standards show, \"Remember When,\" seven nights a week from 9 p.m. -midnight), and are branded as 560 CFOS. 560 CFOS is owned and operated by Bayshore Broadcasting of Owen Sound.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFOM-FM","type":"http://dbpedia.org/ontology/Organisation","name":"CFOM-FM","description":"CFOM-FM is a French-language Canadian radio station located in Quebec City, Quebec. While the station's official city of license is and always has been Lévis, its studios are now in Quebec City, and it identifies itself as a Quebec City station. Owned and operated by Corus Entertainment, it broadcasts on 102.9 MHz using a directional antenna with an average effective radiated power of 16,800 watts and a peak effective radiated power of 32,800 watts.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFOB-FM","type":"http://dbpedia.org/ontology/Organisation","name":"CFOB-FM","description":"CFOB-FM, branded as B93, is a Canadian radio station, broadcasting at 93.1 FM in Fort Frances, Ontario. The station airs an adult contemporary format. The station was launched in 1944 as AM 1340 CKFI, a Dominion Network affiliate owned by local businessman J. G. McLaren. The station moved to AM 800 in 1952, and adopted the CFOB callsign in 1955. The station was acquired by Fawcett Broadcasting, in 1960. Fawcett sold the station to Border Broadcasting in 1966, but reacquired it in 1971.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFO","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://chem2bio2rdf.org/pdb/resource/pdb_ligand/CFO","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://jobs.aol.com/articles/tag/cfo/","type":null,"name":"\"Cfo\"","linkifierName":"sindice"},{"id":"http://whitepapers.technologyevaluation.com/search/for/cfo.html","type":null,"name":"\"CFO\"","linkifierName":"sindice"},{"id":"http://www.keenbonusreview.com/tag/cfo","type":null,"name":"\"Cfo\"","linkifierName":"sindice"},{"id":"http://www.ifrsnewsandviews.com/tag/cfo/","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/word-CFO","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://antyweb.pl/tag/cfo/","type":null,"name":"\"CFO\"","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/wordsense-CFO-noun-1","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://trak.in/Tags/Business/cfo/","type":null,"name":"\"cfo\"","linkifierName":"sindice"}]},{"entity":"Sandstorm Media","offset":252,"length":15,"mostLikelyTagName":"http://dbpedia.org/ontology/Company","links":[{"id":"http://dbpedia.org/resource/Sandstorm_%28Dungeons_%26_Dragons%29","type":null,"name":"Sandstorm (Dungeons & Dragons)","linkifierName":"sindice"},{"id":"http://www.overstock.com/Books-Movies-Music-Games/Sandstorm/835222/product.html","type":null,"name":"\"Sandstorm | Overstock.com\"","linkifierName":"sindice"},{"id":"http://www.overstock.com/Books-Movies-Music-Games/Sandstorm/1148073/product.html","type":null,"name":"\"Sandstorm | Overstock.com\"","linkifierName":"sindice"},{"id":"http://www.overstock.com/Books-Movies-Music-Games/Sandstorm/1555419/product.html","type":null,"name":"\"Sandstorm | Overstock.com\"","linkifierName":"sindice"},{"id":"http://www.lunch.com/Reviews/book/Sandstorm-1613765.html","type":null,"name":"Sandstorm Book Reviews | Lunch.com","linkifierName":"sindice"},{"id":"http://www.overstock.com/Books-Movies-Music-Games/Sandstorm-Paperback/5099550/product.html","type":null,"name":"\"Sandstorm (Paperback) | Overstock.com\"","linkifierName":"sindice"},{"id":"http://www.overstock.com/Books-Movies-Music-Games/Sandstorm-Paperback/5331163/product.html","type":null,"name":"\"Sandstorm (Paperback) | Overstock.com\"","linkifierName":"sindice"},{"id":"http://www.overstock.com/Books-Movies-Music-Games/Mera-Sandstorm/2030818/product.html","type":null,"name":"\"Mera - Sandstorm | Overstock.com\"","linkifierName":"sindice"},{"id":"http://www.thaindian.com/newsportal/business/sandstorm-in-china-hits-over-six-mn-people_100354052.html","type":null,"name":"\"Sandstorm in China hits over six mn people\"","linkifierName":"sindice"},{"id":"http://www.overstock.com/Books-Movies-Music-Games/Sandstorm-Large-Print-Paperback/5331316/product.html","type":null,"name":"\"Sandstorm (Large Print,Paperback) | Overstock.com\"","linkifierName":"sindice"}]},{"entity":"UG","offset":268,"length":2,"mostLikelyTagName":"http://dbpedia.org/ontology/Company","links":[{"id":"http://dbpedia.org/resource/Uganda","type":"http://dbpedia.org/ontology/Country","name":"Uganda","description":"The Republic of Uganda is a landlocked country in East Africa. It is bordered on the east by Kenya, on the north by Sudan, on the west by the Democratic Republic of the Congo, on the southwest by Rwanda, and on the south by Tanzania. The southern part of the country includes a substantial portion of Lake Victoria, which is also bordered by Kenya and Tanzania. Uganda takes its name from the Buganda kingdom, which encompassed a portion of the south of the country including the capital Kampala.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Ugly_Betty","type":"http://dbpedia.org/ontology/TelevisionShow","name":"Ugly Betty","description":"Ugly Betty is an American dramedy television series created by Silvio Horta, which premiered on ABC on September 28, 2006, and ended on April 14, 2010. The series revolves around the character Betty Suarez and is based on the Colombian telenovela Yo soy Betty, la fea, which was created by Fernando Gaitán.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Ugarit","type":"","name":"Ugarit","description":"Ugarit (modern Ras Shamra رأس شمرة, near Latakia, Syria) was an ancient cosmopolitan port city, sited on the Mediterranean coast. Ugarit sent tribute to Egypt and maintained trade and diplomatic connections with Cyprus, documented in the archives recovered from the site and corroborated by Mycenaean and Cypriot pottery found there. The polity was at its height from ca. 1450 BC until 1200 BC.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/UGK","type":"http://dbpedia.org/ontology/Band","name":"UGK","description":"UGK (short for Underground Kingz) was an American hip hop duo from Port Arthur, Texas formed in 1987 by the late Chad \"Pimp C\" Butler . He then joined with Bernard \"Bun B\" Freeman, who became his longtime partner.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/UGM-27_Polaris","type":"","name":"UGM-27 Polaris","description":"The Polaris missile was a two-stage solid-fuel nuclear-armed submarine-launched ballistic missile (SLBM) built during the Cold War by Lockheed Corporation of California for the United States Navy. It was designed to be used as part of the Navy's contribution to the United States arsenal of nuclear weapons, replacing the Regulus cruise missile. Known as a Fleet Ballistic Missile (FBM), the Polaris was first launched from the Cape Canaveral, Florida, missile test base on January 7, 1960.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/UG","type":null,"name":"UG","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/.ug","type":null,"name":".ug","linkifierName":"sindice"},{"id":"http://www.thecrazysquirrel.net/2008/09/08/ug/","type":null,"name":"\"Ug!\"","linkifierName":"sindice"},{"id":"http://ifuwant.cn/tag/ug/","type":null,"name":"UG","linkifierName":"sindice"},{"id":"http://goofyj.wordpress.com/2007/01/05/ug-ug/","type":null,"name":"Ug! Ug! « My Adventures and Antics","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Ug_%28book%29","type":null,"name":"Ug (book)","linkifierName":"sindice"},{"id":"http://www.slideshare.net/dofa03/amp01-ug","type":null,"name":"Amp01 Ug","linkifierName":"sindice"},{"id":"http://wap.in/download/ug-Ft-timbalFt/the-way-i-are/?type=G&lang=EN&id=73376","type":null,"name":"http://wap.in/download/ug-Ft-timbalFt/the-way-i-are/?type=G&lang=EN&id=73376","linkifierName":"sindice"},{"id":"http://twitter.com/foursquareUG","type":null,"name":"Foursquare UG (foursquareUG) on Twitter","linkifierName":"sindice"},{"id":"http://sulochanosho.wordpress.com/2010/03/27/the-best-of-ug/","type":null,"name":"\"The Best of UG « Eccentric UG\"","linkifierName":"sindice"}]},{"entity":"One","offset":272,"length":3,"mostLikelyTagName":"http://dbpedia.org/ontology/Company","links":[{"id":"http://dbpedia.org/resource/One_Day_International","type":"","name":"One Day International","description":"One Day International (ODI) is a form of cricket, in which 50 overs are played per side between two national cricket teams. The Cricket World Cup is played in this format. One Day International matches are also called \"Limited Overs Internationals (LOI)\", because they are limited overs cricket matches between national sides, and if the weather interferes they are not always completed in one day.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/One_Life_to_Live","type":"http://dbpedia.org/ontology/TelevisionShow","name":"One Life to Live","description":"One Life to Live (OLTL) is an American soap opera which, since July 15, 1968, has been broadcast on the ABC television network. Created by Agnes Nixon the series was the first daytime drama to primarily feature racially and socioeconomically diverse characters and consistently emphasize social issues. Actress Erika Slezak has portrayed central heroine Victoria \"Viki\" Lord on One Life to Live since March 1971 and has won a record six Daytime Emmy Awards for the role.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/One_Piece","type":"","name":"One Piece","description":"One Piece started as three one-shot stories entitled Romance Dawn—which would later be used as the title for One Piece's first chapter and volume. The two one-shots featured the character of Luffy, and included elements that would later appear in the main series. The first of these short stories was published in August 1996 in a special issue of Shōnen Jump and later in One Piece Red.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/One_Tree_Hill_%28TV_series%29","type":"http://dbpedia.org/ontology/TelevisionShow","name":"One Tree Hill (TV series)","description":"One Tree Hill is an American teen, young adult television drama created by Mark Schwahn, which premiered on September 23, 2003 on The WB Television Network. After its third season, The WB merged with UPN to form The CW Television Network, and since September 27, 2006 the network is the official broadcaster for the show in the USA. The show is set in fictional town Tree Hill in North Carolina and originally follows the lives of two half-brothers, Lucas Scott and Nathan Scott.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/One-shot_%28comics%29","type":"","name":"One-shot (comics)","description":"In the American comic book industry, the term one-shot is used to denote a pilot comic or a stand-alone story created to last as one issue. These single issues are usually labeled with a \"#1\" despite there being no following issues, and are sometimes subtitled as \"specials\".","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/One_Moment_in_Time","type":null,"name":"One Moment in Time","linkifierName":"sindice"},{"id":"http://www.mpii.de/yago/resource/One_(U2_song)","type":null,"name":"One (Mary J. Blige song)","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/I_Wanna_Be_the_Only_One","type":null,"name":"I Wanna Be the Only One","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Lovely_One","type":null,"name":"Lovely One","linkifierName":"sindice"},{"id":"http://dbpedia.org/ontology/FormulaOneRacer","type":null,"name":"Formula One racer","linkifierName":"sindice"},{"id":"http://sw.opencyc.org/2008/06/10/concept/en/One_of_the_presidents_of_the_United_States","type":null,"name":"One of the presidents of the United States","linkifierName":"sindice"},{"id":"http://dbpedia.org/page/Formula_One_04","type":null,"name":"About: Formula One 04","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Love_One_Another","type":null,"name":"Love One Another","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/No_One_Needs_to_Know","type":null,"name":"No One Needs to Know","linkifierName":"sindice"},{"id":"http://dbpedia.org/page/Formula_One_06","type":null,"name":"About: Formula One 06","linkifierName":"sindice"}]},{"entity":"TYPO3","offset":283,"length":5,"mostLikelyTagName":"http://dbpedia.org/ontology/Company","links":[{"id":"http://dbpedia.org/resource/TYPO3","type":"http://dbpedia.org/ontology/Software","name":"TYPO3","description":"TYPO3 is a free and open source content management system as well as a Model–view–controller (MVC) Web Application Development framework written in PHP. It is released under the GNU General Public License. It can run on Apache or IIS on top of Linux, Microsoft Windows, OS/2 or Mac OS X.","linkifierName":"dbpedia"},{"id":"http://rdfohloh.wikier.org/project/typo3","type":null,"name":"\"TYPO3\"","linkifierName":"sindice"},{"id":"http://blog.my-workstation.com/category/technik/typo3","type":null,"name":"\"Typo3\"","linkifierName":"sindice"},{"id":"http://www.rptech-world.com/tag/typo3","type":null,"name":"\"typo3\"","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/TYPO3","type":null,"name":"Typo3","linkifierName":"sindice"},{"id":"http://cmsreport.com/category/typo3","type":null,"name":"\"Typo3 | CMS Report\"","linkifierName":"sindice"},{"id":"http://www.lnx-world.de/topics/webtechnology/typo3/","type":null,"name":"\"Typo3\"","linkifierName":"sindice"},{"id":"http://hosting.ber-art.nl/typo3/","type":null,"name":"\"typo3\"","linkifierName":"sindice"},{"id":"http://www.sublogic.org/typo3/","type":null,"name":"Typo3","linkifierName":"sindice"},{"id":"http://herbertvandinther.com/tag/typo3/","type":null,"name":"\"Typo3\"","linkifierName":"sindice"},{"id":"http://dbpedia.org/page/TYPO3","type":null,"name":"About: TYPO3","linkifierName":"sindice"}]},{"entity":"Thomas Maroschik","offset":300,"length":16,"mostLikelyTagName":"http://xmlns.com/foaf/0.1/Person","links":[{"id":"http://tmaroschik.dfau.de/index.rdf","type":null,"name":"Thomas Maroschik, DFAU Webentwickler aus Fürth in Bayern, Deutschland (FOAF Profile)","linkifierName":"sindice"},{"id":"http://twitter.com/WrYBiT","type":null,"name":"Jens vs. Hoffmann (WrYBiT) on Twitter","linkifierName":"sindice"},{"id":"http://semantictweet.com/skurfuerst","type":null,"name":"http://semantictweet.com/skurfuerst","linkifierName":"sindice"},{"id":"http://semantictweet.com/derDali","type":null,"name":"http://semantictweet.com/derDali","linkifierName":"sindice"},{"id":"http://semantictweet.com/derDali/friends","type":null,"name":"http://semantictweet.com/derDali/friends","linkifierName":"sindice"}]},{"entity":"Bill Gates","offset":0,"length":10,"mostLikelyTagName":"Person","links":[{"id":"http://dbpedia.org/resource/Bill_Gates","type":"http://dbpedia.org/ontology/Person","name":"Bill Gates","description":"William Henry \"Bill\" Gates III (born October 28, 1955) is an American business magnate, philanthropist, author and chairman of Microsoft, the software company he founded with Paul Allen. He is consistently ranked among the world's wealthiest people and was the wealthiest overall from 1995 to 2009, excluding 2008, when he was ranked third.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Bill_Gates","type":"http://xmlns.com/foaf/0.1/Person","name":"比尔·盖茨","linkifierName":"sindice"},{"id":"http://dbpedia.org/page/William_H._Gates,_Sr.","type":"http://xmlns.com/foaf/0.1/Person","name":"About: William H. Gates, Sr.","linkifierName":"sindice"},{"id":"http://www.stiridebine.ro/tag/bill-gates","type":"http://xmlns.com/foaf/0.1/Person","name":"\"Bill Gates\"","linkifierName":"sindice"},{"id":"http://poststarboy.com/?tag=bill-gates","type":"http://xmlns.com/foaf/0.1/Person","name":"» bill gates","linkifierName":"sindice"},{"id":"http://www.atelier-us.com/tag/bill-gates","type":"http://xmlns.com/foaf/0.1/Person","name":"Bill Gates","linkifierName":"sindice"},{"id":"http://twitter.com/BillGates","type":"http://xmlns.com/foaf/0.1/Person","name":"Bill Gates (BillGates) on Twitter","linkifierName":"sindice"},{"id":"http://pregnancymiraclesblog.com/tag/bill-gates","type":"http://xmlns.com/foaf/0.1/Person","name":"\"Bill Gates\"","linkifierName":"sindice"},{"id":"http://blog.aaronmarks.com/?tag=bill-gates","type":"http://xmlns.com/foaf/0.1/Person","name":"Bill Gates","linkifierName":"sindice"},{"id":"http://doremixy.com/tag/bill-gates","type":"http://xmlns.com/foaf/0.1/Person","name":"\"Bill Gates\"","linkifierName":"sindice"},{"id":"http://www.youradfree.com/wordpress/tag/bill-gates","type":"http://xmlns.com/foaf/0.1/Person","name":"\"Bill Gates\"","linkifierName":"sindice"}]},{"entity":"Founder","offset":12,"length":7,"mostLikelyTagName":"Position","links":[{"id":"http://dbpedia.org/resource/Entrepreneur","type":"","name":"Entrepreneur","description":"An entrepreneur is a person who has possession of a new enterprise, venture or idea and assumes significant accountability for the inherent risks and the outcome. The term is originally a loanword from French and was first defined by the Irish economist Richard Cantillon. Entrepreneur in English is a term applied to the type of personality who is willing to take upon himself a new venture or enterprise and accepts full responsibility for the outcome.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Founders_Cup","type":"","name":"Founders Cup","description":"The Founders Cup is the championship trophy of Canada's Junior \"B\" lacrosse leagues. The custodial duties of this trophy fall upon the Canadian Lacrosse Association. The National Champions are determined through a round robin format with a playdown for the final in a host city. The host of the 2010 Founders Cup is the Mimico Mountaineers of Ontario. In 1972, the Founders Cup was awarded to the Junior \"C\" Champion instead of the Junior \"B\" Champion.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Founder_effect","type":"","name":"Founder effect","description":", the founder effect is the loss of genetic variation that occurs when a new population is established by a very small number of individuals from a larger population. It was first fully outlined by Ernst Mayr in 1952, using existing theoretical work by those such as Sewall Wright. As a result of the loss of genetic variation, the new population may be distinctively different, both genetically and phenotypically, from the parent population from which it is derived.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Founders_Cup_%28PIHA%29","type":"","name":"Founders Cup (PIHA)","description":"The Founders Cup is the championship trophy of the Professional Inline Hockey Association (PIHA), the major professional inline hockey league in the United States. It is similar to the National Hockey League's Stanley Cup. The cup was first presented in 2002 when the York Typhoon defeated the Delaware Blue Diamond Blades in the playoff finals.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Founder_and_International_President","type":"","name":"Founder and International President","description":"","linkifierName":"dbpedia"},{"id":"http://sw.opencyc.org/concept/Mx4rOib6_BlGQdiRA4xXUo_iPA","type":null,"name":"founder","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Founder","type":null,"name":"Founder","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/The_Founder","type":null,"name":"The Founder","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/word-founder","type":null,"name":"founder","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/synset-founder-noun-2","type":null,"name":"founder","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/synset-founder-noun-3","type":null,"name":"founder","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/wordsense-founder-noun-2","type":null,"name":"founder","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/wordsense-founder-verb-2","type":null,"name":"founder","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/wordsense-founder-verb-4","type":null,"name":"founder","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/synset-founder-verb-4","type":null,"name":"founder","linkifierName":"sindice"}]},{"entity":"Microsoft","offset":23,"length":9,"mostLikelyTagName":"Company","links":[{"id":"http://dbpedia.org/resource/Microsoft","type":"http://dbpedia.org/ontology/Company","name":"Microsoft","description":"Microsoft Corporation is a public multinational corporation headquartered in Redmond, Washington, USA that develops, manufactures, licenses, and supports a wide range of products and services predominantly related to computing through its various product divisions.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Microsoft_Dynamics","type":"http://dbpedia.org/ontology/Company","name":"Microsoft Dynamics","description":"Microsoft Dynamics is a line of ERP and CRM applications developed by the Microsoft Business Solutions group within Microsoft. Microsoft Dynamics applications are delivered through a network of reselling partners who provide specialized services. There are 300,000 businesses that use Microsoft Dynamics applications and 10,000 Microsoft Dynamics reselling partners worldwide.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Microsoft_Japan","type":"http://dbpedia.org/ontology/Company","name":"Microsoft Japan","description":"Microsoft Japan, officially Microsoft Company, Limited is a division of the United States-based computer technology corporation Microsoft based in Japan. The headquarters is in a skyscraper in the Shinjuku district of Tokyo.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Microsoft_Store","type":"http://dbpedia.org/ontology/Company","name":"Microsoft Store","description":"The Microsoft Store consists of four retail stores owned and operated by Microsoft, dealing in computers, computer software and consumer electronics. The stores aim to \"improve the PC and Microsoft retail purchase experience for consumers worldwide and help consumers make more informed decisions about their PC and software purchases. \" The first of the Microsoft Stores opened on October 22, 2009 (the same day that Windows 7 launched) at Scottsdale Fashion Square in Scottsdale, Arizona.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Microsoft_Dynamics_ERP","type":"http://dbpedia.org/ontology/Company","name":"Microsoft Dynamics ERP","description":"Microsoft Dynamics ERP applications are part of Microsoft Dynamics, a line of business management software owned and developed by Microsoft. Microsoft Dynamics includes both Microsoft Dynamics ERP and Microsoft Dynamics CRM applications that are delivered through a network of reselling partners who provide specialized services.","linkifierName":"dbpedia"},{"id":"http://sw.opencyc.org/2008/06/10/concept/en/MicrosoftComputerProgram","type":null,"name":"Microsoft computer program","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Microsoft_Word","type":null,"name":"Microsoft Word","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Microsoft_Project","type":null,"name":"Microsoft Office Project","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Microsoft_Exchange_Server","type":null,"name":"Microsoft Exchange","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Microsoft_Access","type":null,"name":"Microsoft Office Access","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Microsoft_Japan","type":null,"name":"Microsoft Co. Ltd.,","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Microsoft_office","type":null,"name":"Microsoft office","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Microsoft_PowerPoint","type":null,"name":"Microsoft PowerPoint","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Microsoft_XNA","type":null,"name":"XNA (Microsoft)","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Microsoft_Game_Studios","type":null,"name":"Microsoft Game Studios","linkifierName":"sindice"}]},{"entity":"Berlin","offset":74,"length":6,"mostLikelyTagName":"City","links":[{"id":"http://dbpedia.org/resource/Berlin","type":"http://dbpedia.org/ontology/City","name":"Berlin","description":"Berlin is the capital city of Germany and one of sixteen states of Germany. With a population of 3.4 million people, Berlin is Germany's largest city. It is the second most populous city proper and the eighth most populous urban area in the European Union. Located in northeastern Germany, it is the center of the Berlin-Brandenburg Metropolitan Area, comprising 5 million people from over 190 nations.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Berlin,_New_Hampshire","type":"http://dbpedia.org/ontology/City","name":"Berlin, New Hampshire","description":"Berlin (with stress on first syllable) is a city along the Androscoggin River in Coos County in northern New Hampshire, United States. The population was 10,331 at the 2000 Census. It includes the village of Cascade. Located on the edge of the White Mountains, the city's boundaries extend into the White Mountain National Forest.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Berlin,_Wisconsin","type":"http://dbpedia.org/ontology/City","name":"Berlin, Wisconsin","description":"Berlin is a city in Green Lake and Waushara Counties in the U.S. state of Wisconsin. The population was 5,305 at the 2000 census. The city is located mostly within the Town of Berlin in Green Lake County; only a small portion of the city extends into the Town of Aurora in Waushara County.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Berlin,_North_Dakota","type":"http://dbpedia.org/ontology/City","name":"Berlin, North Dakota","description":"Berlin is a city in LaMoure County, North Dakota in the United States. The population was 35 at the 2000 census. Berlin was founded in 1887.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Berlin,_Massachusetts__","type":"http://dbpedia.org/ontology/City","name":"Berlin, Massachusetts ","description":"","linkifierName":"dbpedia"},{"id":"http://www4.wiwiss.fu-berlin.de/eurostat/resource/regions/Berlin","type":null,"name":"Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/PeopleFromBerlin","type":null,"name":"person from Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/UniversitiesInBerlin","type":null,"name":"university in Berlin","linkifierName":"sindice"},{"id":"http://www4.wiwiss.fu-berlin.de/eurostat/data/regions/Berlin","type":null,"name":"RDF Description of Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Air_Berlin","type":null,"name":"柏林航空","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Berlin_Anhalter_Bahnhof","type":null,"name":"Stazione di Berlin Anhalter Bahnhof","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/SongsByIrvingBerlin","type":null,"name":"song by Irving Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Technical_University_of_Berlin","type":null,"name":"Technical University of Berlin","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Berlin_G%C3%B6rlitzer_Bahnhof","type":null,"name":"Görlitzer Bahnhof","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Berlin_Sportpalast","type":null,"name":"Berlin Sportpalast","linkifierName":"sindice"}]},{"entity":"Content Management System","offset":110,"length":25,"mostLikelyTagName":"Technology","links":[{"id":"http://dbpedia.org/resource/Content_management_system","type":"","name":"Content management system","description":"A content management system (CMS) is the collection of procedures used to manage work flow in a collaborative environment. These procedures can be manual or computer-based. The procedures are designed to: Allow for a large number of people to contribute to and share stored data Control access to data, based on user roles.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Content_management_systems_for_school_websites","type":"","name":"Content management systems for school websites","description":"","linkifierName":"dbpedia"},{"id":"http://semanticweb.org/id/Category-3ASemantic_content_management_system","type":null,"name":"Semantic content management system","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Enterprise_content_management","type":null,"name":"Enterprise-Content-Management-System","linkifierName":"sindice"},{"id":"http://faviki.com/tag/Content_management_system","type":null,"name":"Content management system  | Faviki. Tags that make sense.","linkifierName":"sindice"},{"id":"http://faviki.com/person/Janos.Haits/tag/Content_management_system","type":null,"name":"Janos.Haits: Content management system  | Faviki. Tags that make sense.","linkifierName":"sindice"},{"id":"http://faviki.com/tag/Web_content_management_system","type":null,"name":"Web content management system  | Faviki. Tags that make sense.","linkifierName":"sindice"},{"id":"http://dbpedia.org/page/Content_management_system","type":null,"name":"About: Content management system","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/Component_content_management_system","type":null,"name":"Component content management system","linkifierName":"sindice"},{"id":"http://www.slideshare.net/fishtech/vcgenius-content-management-system","type":null,"name":"VCGenius Content Management System","linkifierName":"sindice"},{"id":"http://dbpedia.org/resource/PHP_content_management_system","type":null,"name":"PHP content management system","linkifierName":"sindice"},{"id":"http://www.slideshare.net/jaspervangent/content-management-system-3048652","type":null,"name":"Content Management System","linkifierName":"sindice"}]},{"entity":"Kasper Skaarhoj","offset":156,"length":15,"mostLikelyTagName":"Person","links":[]},{"entity":"Denmark","offset":182,"length":7,"mostLikelyTagName":"Country","links":[{"id":"http://dbpedia.org/resource/Denmark","type":"http://dbpedia.org/ontology/Country","name":"Denmark","description":"Denmark, officially the Kingdom of Denmark together with Greenland and the Faroe Islands, is a Scandinavian country in Northern Europe. It is the southernmost of the Nordic countries, southwest of Sweden and south of Norway, and bordered to the south by Germany. Denmark borders both the Baltic and the North Sea.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Denmark%E2%80%93Norway","type":"http://dbpedia.org/ontology/Country","name":"Denmark–Norway","description":"Denmark–Norway is the historiographical name for a former political entity consisting of the kingdoms of Denmark and Norway, including the originally Norwegian dependencies of Iceland, Greenland and the Faroe Islands. Following the strife surrounding the break-up of its predecessor, the Kalmar Union, the two kingdoms entered into another personal union in 1536 which lasted until 1814. The corresponding adjective and demonym is Dano-Norwegian.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/Kingdom_of_Denmark","type":"http://dbpedia.org/ontology/Country","name":"Kingdom of Denmark","description":"The Kingdom of Denmark, or Danish Realm, is a constitutional monarchy and a community consisting of Denmark proper in northern Europe and two autonomous countries, the Faroe Islands in the North Atlantic and Greenland in North America, with Denmark as the hegemonial part, where the residual judicial, executive and legislative power rests. The relationship of the member states is referred to as Rigsfællesskabet.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/class/yago/GovernmentMinisterialOfficesOfDenmark","type":null,"name":"office of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/PrimeMinistersOfDenmark","type":null,"name":"minister of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/FootballVenuesInDenmark","type":null,"name":"venue in Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/UnitedStatesAmbassadorsToDenmark","type":null,"name":"ambassador to Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/OlympicFootballersOfDenmark","type":null,"name":"About: footballer of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/OlympicSailorsOfDenmark","type":null,"name":"sailor of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/LGBTPeopleFromDenmark","type":null,"name":"person from Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/FilmFestivalsInDenmark","type":null,"name":"About: festival in Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/OlympicWrestlersOfDenmark","type":null,"name":"wrestler of Denmark","linkifierName":"sindice"},{"id":"http://dbpedia.org/class/yago/LGBTWritersFromDenmark","type":null,"name":"writer from Denmark","linkifierName":"sindice"}]},{"entity":"Sebastian Kurfuerst","offset":214,"length":19,"mostLikelyTagName":"Person","links":[{"id":"http://sebastian.kurfuerst.eu/index.rdf","type":"http://xmlns.com/foaf/0.1/Person","name":"\"Sebastian Kurfürst, Dresden (FOAF Profile)\"","linkifierName":"sindice"}]},{"entity":"CFO","offset":242,"length":3,"mostLikelyTagName":"Position","links":[{"id":"http://dbpedia.org/resource/Chief_financial_officer","type":"","name":"Chief financial officer","description":"The chief financial officer (CFO) is a corporate officer primarily responsible for managing the financial risks of the corporation. This officer is also responsible for financial planning and record-keeping, as well as financial reporting to higher management. In some sectors the CFO is also responsible for analysis of data. The title is equivalent to finance director, a common title in the United Kingdom.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFOX-FM","type":"http://dbpedia.org/ontology/Organisation","name":"CFOX-FM","description":"CFOX-FM (identified on air and in print as 99.3 The Fox) is a Canadian radio station in the Greater Vancouver region of British Columbia. It broadcasts at 99.3 MHz on the FM band with an effective radiated power of 75,000 watts from a transmitter on Mount Seymour in the District of North Vancouver. Studios are located in Downtown Vancouver, in the TD Tower. The station is owned by Corus Entertainment. CFOX has an active rock format.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFOS","type":"http://dbpedia.org/ontology/Organisation","name":"CFOS","description":"CFOS is an AM radio station broadcasting from downtown Owen Sound, Ontario, Canada. They play oldies and news (plus an adult standards show, \"Remember When,\" seven nights a week from 9 p.m. -midnight), and are branded as 560 CFOS. 560 CFOS is owned and operated by Bayshore Broadcasting of Owen Sound.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFOM-FM","type":"http://dbpedia.org/ontology/Organisation","name":"CFOM-FM","description":"CFOM-FM is a French-language Canadian radio station located in Quebec City, Quebec. While the station's official city of license is and always has been Lévis, its studios are now in Quebec City, and it identifies itself as a Quebec City station. Owned and operated by Corus Entertainment, it broadcasts on 102.9 MHz using a directional antenna with an average effective radiated power of 16,800 watts and a peak effective radiated power of 32,800 watts.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFOB-FM","type":"http://dbpedia.org/ontology/Organisation","name":"CFOB-FM","description":"CFOB-FM, branded as B93, is a Canadian radio station, broadcasting at 93.1 FM in Fort Frances, Ontario. The station airs an adult contemporary format. The station was launched in 1944 as AM 1340 CKFI, a Dominion Network affiliate owned by local businessman J. G. McLaren. The station moved to AM 800 in 1952, and adopted the CFOB callsign in 1955. The station was acquired by Fawcett Broadcasting, in 1960. Fawcett sold the station to Border Broadcasting in 1966, but reacquired it in 1971.","linkifierName":"dbpedia"},{"id":"http://dbpedia.org/resource/CFO","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://chem2bio2rdf.org/pdb/resource/pdb_ligand/CFO","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://jobs.aol.com/articles/tag/cfo/","type":null,"name":"\"Cfo\"","linkifierName":"sindice"},{"id":"http://whitepapers.technologyevaluation.com/search/for/cfo.html","type":null,"name":"\"CFO\"","linkifierName":"sindice"},{"id":"http://www.keenbonusreview.com/tag/cfo","type":null,"name":"\"Cfo\"","linkifierName":"sindice"},{"id":"http://www.ifrsnewsandviews.com/tag/cfo/","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/word-CFO","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://antyweb.pl/tag/cfo/","type":null,"name":"\"CFO\"","linkifierName":"sindice"},{"id":"http://wordnet.rkbexplorer.com/id/wordsense-CFO-noun-1","type":null,"name":"CFO","linkifierName":"sindice"},{"id":"http://trak.in/Tags/Business/cfo/","type":null,"name":"\"cfo\"","linkifierName":"sindice"}]},{"entity":"Sandstorm Media UG","offset":249,"length":18,"mostLikelyTagName":"Company","links":[{"id":"http://sandstorm-media.de/","type":"http://dbpedia.org/ontology/Company","name":"Sandstorm Media UG","linkifierName":"learning"}]},{"entity":"Thomas Maroschik","offset":296,"length":16,"mostLikelyTagName":"Person","links":[{"id":"http://tmaroschik.dfau.de/index.rdf","type":"http://xmlns.com/foaf/0.1/Person","name":"Thomas Maroschik, DFAU Webentwickler aus Fürth in Bayern, Deutschland (FOAF Profile)","linkifierName":"sindice"},{"id":"http://twitter.com/WrYBiT","type":"http://xmlns.com/foaf/0.1/Person","name":"Jens vs. Hoffmann (WrYBiT) on Twitter","linkifierName":"sindice"}]}]});
				return;
				$.post('http://localhost:8080/semantifier/annotate', {
						text: $this.val()
					}, function(results) {
						//console.log(JSON.stringify(results));
						showAnnotationsInEnrichmentWidget(results);
					}
				)
			});

			$this.monitorTextChanges();
			$this.bind('textChangeWithDiff', function(diff) {
				// TODO implement
			});
		});
	};

	/**
	 * =====================
	 * Embedding in Document
	 * =====================
	 */
	$(document).ready(function() {
		$('.sm-semantic.externalReference').prev().semanticLookup();
		$('.sm-semantic.continuousText').prev().continuousTextEnrichment();
	})
})(jQuery);