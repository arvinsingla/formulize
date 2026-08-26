// elementId is the hidden element we're interacting with - can be a series of elements with [] which would be the case if multiple is set
// value is the value we're setting
// change is a flag to indicate if we trigger a change on the element when we do this
// multiple indicates if this is a multi-select autocomplete
function setAutocompleteValue(elementId, value, change, multiple) {
  if(multiple) {
		var targetElementId = 'last_selected_'+elementId;
	} else {
		var targetElementId = elementId;
	}
	jQuery('#'+targetElementId).val(value);
	if(change && !multiple) {
		jQuery('#'+elementId).trigger('change');
	}
	formulizechanged=1;
}

function removeFromMultiValueAutocomplete(value, elementId) {
	jQuery('#'+elementId+'_defaults input[value="'+value+'"]').remove();
	jQuery('.auto_multi_'+elementId+'[target="'+value+'"]').remove();
	triggerChangeOnMultiValueAutocomplete(elementId);
}

// if there are no items selected (such as if the user just deleted the last one), make a fake one just to trigger the change event
function triggerChangeOnMultiValueAutocomplete(elementId) {
	var triggerElements = jQuery('[name="'+elementId+'[]"]');
	if(triggerElements.length == 0) {
		jQuery('#'+elementId+'_defaults').append("<input type='hidden' name='"+elementId+"[]' jquerytag='"+elementId+"' id='"+elementId+"_0509' value='' />");
	}
	jQuery('[name="'+elementId+'[]"]').first().trigger('change');
	jQuery('#'+elementId+'_0509').remove();
	formulizechanged=1;
}

// ---------------------------------------------------------------------------
// UI text. PHP overwrites window.formulizeAutocompleteLang with the localized
// strings when it renders an autocomplete (see selectElement.php). The defaults
// below keep the component working if the script loads on its own.
// ---------------------------------------------------------------------------
window.formulizeAutocompleteLang = window.formulizeAutocompleteLang || {
	remove: 'Remove',
	removeItem: 'Remove %s',
	confirmTitle: 'Remove selection',
	confirmMessage: 'Remove %s from the selected items?',
	confirmMessageGeneric: 'Remove this item from the selected items?',
	confirmOk: 'Remove',
	confirmCancel: 'Cancel'
};

function formulizeAutocompleteText(key, substitution) {
	var lang = window.formulizeAutocompleteLang || {};
	var text = typeof lang[key] === 'string' ? lang[key] : '';
	if(typeof substitution !== 'undefined' && substitution !== null) {
		text = text.replace('%s', substitution);
	}
	return text;
}

// ---------------------------------------------------------------------------
// Chip (selected value) markup. Built here rather than in the per-element inline
// JS so that every theme, and both the initial server-side render and values
// added after the page has loaded, produce exactly the same structure.
// Returns a jQuery object for the chip.
// ---------------------------------------------------------------------------
function formulizeBuildAutocompleteChip(elementId, value, label) {
	var chip = jQuery('<p></p>')
		.addClass('auto_multi auto_multi_'+elementId)
		.attr('target', value);
	jQuery('<span></span>').addClass('auto_multi_label').text(label).appendTo(chip);
	jQuery('<button></button>')
		.attr('type', 'button')
		.addClass('auto_multi_remove')
		.attr('aria-label', formulizeAutocompleteText('removeItem', label))
		.attr('title', formulizeAutocompleteText('remove'))
		.html('&times;')
		.appendTo(chip);
	return chip;
}

// ---------------------------------------------------------------------------
// Confirmation modal. Deliberately a small self-contained widget rather than a
// jQuery-UI dialog, so it carries no widget-library chrome and every theme can
// skin it with the .formulize-confirm-modal* classes. Base appearance lives in
// modules/formulize/templates/css/formulize.css.
//
// message  - text shown in the body of the modal
// onConfirm - callback run when the user confirms
// ---------------------------------------------------------------------------
function formulizeConfirmModal(message, onConfirm) {

	var previouslyFocused = document.activeElement;

	var overlay = jQuery('<div></div>').addClass('formulize-confirm-modal-overlay');
	var modal = jQuery('<div></div>')
		.addClass('formulize-confirm-modal')
		.attr('role', 'dialog')
		.attr('aria-modal', 'true')
		.attr('aria-label', formulizeAutocompleteText('confirmTitle'));
	jQuery('<p></p>').addClass('formulize-confirm-modal-message').text(message).appendTo(modal);

	var actions = jQuery('<div></div>').addClass('formulize-confirm-modal-actions').appendTo(modal);
	var cancelButton = jQuery('<button></button>')
		.attr('type', 'button')
		.addClass('formulize-confirm-modal-button formulize-confirm-modal-cancel')
		.text(formulizeAutocompleteText('confirmCancel'))
		.appendTo(actions);
	var confirmButton = jQuery('<button></button>')
		.attr('type', 'button')
		.addClass('formulize-confirm-modal-button formulize-confirm-modal-confirm')
		.text(formulizeAutocompleteText('confirmOk'))
		.appendTo(actions);

	overlay.append(modal);

	function close() {
		jQuery(document).off('keydown.formulizeConfirmModal');
		overlay.remove();
		if(previouslyFocused && typeof previouslyFocused.focus === 'function') {
			previouslyFocused.focus();
		}
	}

	cancelButton.on('click', function(event) {
		event.preventDefault();
		close();
	});
	confirmButton.on('click', function(event) {
		event.preventDefault();
		close();
		if(typeof onConfirm === 'function') {
			onConfirm();
		}
	});
	// clicking the backdrop, but not the modal itself, cancels
	overlay.on('click', function(event) {
		if(event.target === overlay[0]) {
			close();
		}
	});
	// escape cancels, tab is trapped inside the two buttons
	jQuery(document).on('keydown.formulizeConfirmModal', function(event) {
		if(event.key === 'Escape' || event.keyCode === 27) {
			event.preventDefault();
			close();
		} else if(event.key === 'Tab' || event.keyCode === 9) {
			event.preventDefault();
			if(document.activeElement === confirmButton[0]) {
				cancelButton.focus();
			} else {
				confirmButton.focus();
			}
		}
	});

	jQuery('body').append(overlay);
	// cancel is the default focus, so that a stray enter/space does not remove anything
	cancelButton.focus();
}

// ---------------------------------------------------------------------------
// Removing a selected value. The only way to remove one is the chip's X button,
// and the X always asks for confirmation first. (The old behaviour was that
// clicking anywhere on the chip removed it immediately, with a strikethrough on
// hover as the only warning - see issue #92.)
//
// One delegated handler on the document covers every autocomplete on the page,
// including chips added after load.
// ---------------------------------------------------------------------------
jQuery(document).ready(function() {
	if(window.formulizeAutocompleteRemovalBound) {
		return;
	}
	window.formulizeAutocompleteRemovalBound = true;
	jQuery(document).on('click', '.formulize_autocomplete_selections .auto_multi_remove', function(event) {
		event.preventDefault();
		event.stopPropagation();
		var chip = jQuery(this).closest('.auto_multi');
		var container = chip.closest('.formulize_autocomplete_selections');
		var containerId = container.attr('id') || '';
		var elementId = containerId.replace(/_formulize_autocomplete_selections$/, '');
		var value = chip.attr('target');
		if(!elementId || typeof value === 'undefined') {
			return;
		}
		var label = jQuery.trim(chip.find('.auto_multi_label').text());
		var message = label
			? formulizeAutocompleteText('confirmMessage', label)
			: formulizeAutocompleteText('confirmMessageGeneric');
		formulizeConfirmModal(message, function() {
			removeFromMultiValueAutocomplete(value, elementId);
		});
	});
});
