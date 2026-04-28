(function ($) {
	window.dlckReplaceImageOpen = function () {
		var strings = window.dlckReplaceImage || {};
		var frame = wp.media({
			title: String(strings.title || 'Choose Replacement Image'),
			button: {
				text: String(strings.button || 'Replace Image')
			},
			multiple: false,
			library: {
				type: 'image'
			}
		});

		frame.on('select', function () {
			var selection = frame.state().get('selection').first();
			var attachment = selection ? selection.toJSON() : null;
			var $field = $('#dlck_replace_image_with_fld');

			if (!attachment || !$field.length) {
				return;
			}

			$field.val(String(attachment.id || ''));

			if ($field.closest('.media-modal').length) {
				$field.trigger('change');
				var saveStatusInterval = window.setInterval(function () {
					if ($field.closest('.attachment-details.save-ready').length) {
						window.clearInterval(saveStatusInterval);
						window.location.reload();
					}
				}, 250);
			} else {
				$field.closest('form').trigger('submit');
			}
		});

		var frameEl = $(frame.open().el);
		frameEl.find('.media-router > a:first-child').trigger('click');
	};
})(jQuery);
