/**
 * Markdown View for AI Agents - Admin live preview
 *
 * Updates the button preview in real-time as the user
 * changes settings on the admin page.
 */

/* global mdForAgentsAdmin */

( function () {
	'use strict';

	var ICON_SVG =
		'<svg class="md-agent-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" ' +
		'width="16" height="16" fill="currentColor" aria-hidden="true">' +
		'<path d="M14.85 3c.63 0 1.15.52 1.14 1.15v7.7c0 .63-.51 1.15-1.15 1.15H1.15C.52 ' +
		'13 0 12.48 0 11.84V4.15C0 3.52.52 3 1.15 3zM9 11V5H7L5.5 7 4 5H2v6h2V8l1.5 ' +
		'1.92L7 8v3zm2.99.5L14.5 8H13V5h-2v3H9.5z"/></svg>';

	var HIDDEN_HTML =
		'<p class="md-agent-admin-preview-hidden">The button is hidden.<br>The <code>?format=markdown</code> endpoint remains active.</p>';

	/**
	 * Gather current field values.
	 */
	function getValues() {
		var textInput    = document.getElementById( 'md_for_agents_button_text' );
		var iconCheckbox = document.getElementById( 'md_for_agents_show_icon' );
		var classesInput = document.getElementById( 'md_for_agents_custom_css_classes' );
		var positionSelect = document.getElementById( 'md_for_agents_button_position' );

		return {
			text:     textInput ? textInput.value : '',
			showIcon: iconCheckbox ? iconCheckbox.checked : true,
			classes:  classesInput ? classesInput.value.trim() : '',
			position: positionSelect ? positionSelect.value : 'before',
		};
	}

	/**
	 * Build the preview HTML from current field values.
	 */
	function buildPreview( values ) {
		if ( values.position === 'none' ) {
			return HIDDEN_HTML;
		}

		var buttonText = values.text || mdForAgentsAdmin.defaultButtonText;
		var extraClass = values.classes ? ' ' + values.classes : '';
		var iconHtml   = values.showIcon ? ICON_SVG + ' ' : '';

		return (
			'<div class="md-agent-button-wrapper">' +
				'<a href="#" class="md-agent-button' + extraClass + '" rel="nofollow" onclick="return false;">' +
					iconHtml +
					'<span class="md-agent-button-label">' + escapeHtml( buttonText ) + '</span>' +
				'</a>' +
			'</div>'
		);
	}

	/**
	 * Minimal HTML escaping.
	 */
	function escapeHtml( str ) {
		var div       = document.createElement( 'div' );
		div.appendChild( document.createTextNode( str ) );
		return div.innerHTML;
	}

	/**
	 * Re-render the preview container.
	 */
	function updatePreview() {
		var container = document.getElementById( 'md-agent-preview-container' );
		if ( ! container ) {
			return;
		}
		container.innerHTML = buildPreview( getValues() );
	}

	/**
	 * Attach listeners to the settings fields.
	 */
	function init() {
		var ids = [
			'md_for_agents_button_text',
			'md_for_agents_show_icon',
			'md_for_agents_custom_css_classes',
			'md_for_agents_button_position',
		];

		ids.forEach( function ( id ) {
			var el = document.getElementById( id );
			if ( el ) {
				el.addEventListener( 'input', updatePreview );
				el.addEventListener( 'change', updatePreview );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
