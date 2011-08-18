describe("Text similarity calculation", function() {
	var $;

	beforeEach(function() {
		$ = window.jQuery;
	});
	describe("Prefix", function() {
		it("Example 1", function() {
			var oldText = 'Hello, this is Sebastian.';
			var newText = 'Yello, this is Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.commonPrefix).toEqual(0);
		});

		it("Example 2", function() {
			var oldText = 'Hello, this is Sebastian.';
			var newText = 'Hello, This is Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.commonPrefix).toEqual(7);
		});
	});

	describe("Suffix", function() {
		it("Example 1", function() {
			var oldText = 'Hello, this is SebastianX';
			var newText = 'Yello, this is Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.commonSuffix).toEqual(0);
		});

		it("Example 2", function() {
			var oldText = 'Hello, this is SebastXan.';
			var newText = 'Hello, This is Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.commonSuffix).toEqual(3);
		});
	});

	it("Equal Texts specification", function() {
		var oldText = 'Hello, this is Sebastian.';
		var newText = 'Hello, this is Sebastian.';

		var result = $.semantic_helper_calculateChanges(oldText, newText);
		expect(result.commonPrefix).toEqual(25);
		expect(result.commonSuffix).toEqual(0);
	});

	describe("Removal", function() {
		it("Removal of a single character", function() {
			var oldText = 'Hello, this is Sebastian.';
			var newText = 'Hello this is Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.action).toEqual("remove");
			expect(result.position).toEqual(5);
			expect(result.lengthBefore).toEqual(1);
			expect(result.lengthAfter).toEqual(0);
		});
		it("Removal of contingous region, marking it and then deleting", function() {
			var oldText = 'Hello, this is Sebastian.';
			var newText = 'Hello Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.action).toEqual("remove");
			expect(result.position).toEqual(5);
			expect(result.lengthBefore).toEqual(9);
			expect(result.lengthAfter).toEqual(0);
		});
	});

	describe("Insertion", function() {
		it("Insertion of a single character", function() {
			var oldText = 'Hello this is Sebastian.';
			var newText = 'Hello, this is Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.action).toEqual("insert");
			expect(result.position).toEqual(5);
			expect(result.lengthBefore).toEqual(0);
			expect(result.lengthAfter).toEqual(1);
		});
		it("Insertion of contingous region, f.e. via copy / paste", function() {
			var oldText = 'Hello Sebastian.';
			var newText = 'Hello, this is Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.action).toEqual("insert");
			expect(result.position).toEqual(5);
			expect(result.lengthBefore).toEqual(0);
			expect(result.lengthAfter).toEqual(9);
		});
	});

	describe("Modification", function() {
		it("Modification of a single character, marking and changing", function() {
			var oldText = 'Hello, this is Sebastian.';
			var newText = 'Hello; this is Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.action).toEqual("modify");
			expect(result.position).toEqual(5);
			expect(result.lengthBefore).toEqual(1);
			expect(result.lengthAfter).toEqual(1);
		});
		it("Modification of a contingous region, f.e. via copy / paste", function() {
			var oldText = 'Hello, this is Sebastian.';
			var newText = 'Hello, i am so Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.action).toEqual("modify");
			expect(result.position).toEqual(7);
			expect(result.lengthBefore).toEqual(7);
			expect(result.lengthAfter).toEqual(7);
		});

		it("1 Complex Modification of a contingous region, f.e. via copy / paste", function() {
			var oldText = 'Hello, this is Sebastian.';
			var newText = 'Hello, sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.action).toEqual("remove");
			expect(result.position).toEqual(7);
			expect(result.lengthBefore).toEqual(9);
			expect(result.lengthAfter).toEqual(1);
		});

		it("2 Complex Modification of a contingous region, f.e. via copy / paste", function() {
			var oldText = 'Hello, sebastian.';
			var newText = 'Hello, this is Sebastian.';

			var result = $.semantic_helper_calculateChanges(oldText, newText);
			expect(result.action).toEqual("insert");
			expect(result.position).toEqual(7);
			expect(result.lengthBefore).toEqual(1);
			expect(result.lengthAfter).toEqual(9);
		});
	});

});