/* Icon Picker by QueryLoop
 * Author: @eliorivero
 * URL: http://queryloop.com/
 * License: GPLv2
 */

;(function ( $ ) {

	'use strict';

	var defaults = {
		'mode'       : 'dialog',// show overlay 'dialog' panel or slide down 'inline' panel
		'closeOnPick': true,   // whether to close panel after picking or 'no'
		'save'       : 'class', // save icon 'class' or 'code'
		'size'       : '',
		'classes'    : {
			'launcher' : '', // extra classes for launcher buttons
			'clear'    : 'remove-times', // extra classes for button that removes preview and clears field
			'highlight': '', // extra classes when highlighting an icon
			'close'    : ''  // extra classes for close button
		},
		'iconSets' : {          // example data structure. Used to specify which launchers will be created
			'genericon' : 'Genericon', // create a launcher to pick genericon icons
			'fa' : 'FontAwesome' // create a launcher to pick fontawesome icons
		}
	};

	function QL_Icon_Picker ( element, options ) {
		this.element = element;
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this.init();
	}

	QL_Icon_Picker.prototype = {

		iconSet: '',
		iconSetName: '',
		$field: '',

		init: function(){

			var $brick = $(this.element),
				pickerId = $brick.data('pickerid'),
				$preview = $('<div class="jicon-preview jicon-preview-' + pickerId + '" />');

			this.$field = $brick.find('input');

			// Add preview area
			this.makePreview( $brick, pickerId, $preview );

			// Make button to clear field and remove preview
			this.makeClear( pickerId, $preview );

			// Make buttons that open the panel of icons
			this.makeLaunchers( $brick, pickerId );

			// Prepare display styles, inline and dialog
			this.makeDisplay( $brick );
		},

		makePreview: function( $brick, pickerId, $preview ) {
			var $icon = $('<i />'),
				iconValue = this.$field.val();
			$preview.prependTo($brick);
			$icon.prependTo($preview);
			if ( '' != iconValue ) {
				$preview.addClass('jicon-preview-on');
				$icon.addClass(iconValue);
			}
		},

		makeClear: function( pickerId, $preview ) {
			var base = this,
				$clear = $('<a class="remove-icon ' + base.settings.classes.clear + '" />');

			// Hide button to remove icon and preview and append it to preview area
			$clear.hide().prependTo($preview);
			// If there's a icon saved in the field, show remove icon button
			if ( '' != base.$field.val() ) {
				$clear.show();
			}

			$preview.on('click', '.remove-icon', function(e){
				e.preventDefault();
				base.$field.val('');
				$preview.removeClass('jicon-preview-on').find('i').removeClass();
				$(this).hide();
			});
		},

		makeDisplay: function( $brick ) {
			var base = this,
				close = base.settings.classes.close,
				$body = $('body'),
				$close = $('<a href="#" class="jicon-picker-close"/>');

			if ( 'inline' == base.settings.mode ) {
				$brick.find('.jicon-set').append($close).removeClass('jicon-dialog').addClass('inline ' + base.settings.size).parent().addClass('jicon-set-wrap');
			} else if ( 'dialog' == base.settings.mode ) {
				$('.jicon-set').addClass('jicon-dialog ' + base.settings.size);
				if ( $('.jicon-picker-overlay').length <= 0 ) {
					$body.append('<div class="jicon-picker-overlay"/>').append($close);
				}
			}
			$body
				.on('click', '.jicon-picker-close, .jicon-picker-overlay', function(e){
					e.preventDefault();
					base.closePicker( $brick, $(base.iconSet), base.settings.mode);
				})
				.on('mouseenter mouseleave', '.jicon-picker-close', function(e){
					if( 'mouseenter' == e.type ) {
						$(this).addClass(close);
					} else {
						$(this).removeClass(close);
					}
				});
		},

		makeLaunchers: function( $brick ) {
			var base = this,
				dataIconSets = $brick.data('iconsets'),
				iconSet;

			if ( 'undefined' == typeof dataIconSets ) {
				dataIconSets = base.settings.iconSets;
			}
			for ( iconSet in dataIconSets ) {
				if( dataIconSets.hasOwnProperty( iconSet ) ) {
					$brick.append('<a class="launch-icons ' + base.settings.classes.launcher + '" data-icons="' + iconSet + '">' + dataIconSets[iconSet] + '</a>');
				}
			}

			$brick.find('.launch-icons').on('click', function(e){
				e.preventDefault();
				var $self = $(this),
					theseIcons = $self.data('icons');
				base.iconSetName = theseIcons;
				base.iconSet = '.' + theseIcons + '-set';

				// Initialize picker
				base.iconPick( $brick );

				// Show icon picker
				base.showPicker( $brick, $(base.iconSet), base.settings.mode );
			});
		},

		iconPick:function( $brick ){
			var base = this,
				highlight = 'jicon-highlight ' + base.settings.classes.highlight;
			$(base.iconSet).on('click', 'li', function(e){
				e.preventDefault();
				var $icon = $(this),
					icon = $icon.data( base.settings.save );

				// Mark as selected
				$('.jicon-selected').removeClass('jicon-selected');
				$icon.addClass('jicon-selected');

				// Save icon value to field
				base.$field.val( icon );

				// Close icon picker
				if ( base.settings.closeOnPick ) {
					base.closePicker( $brick, $icon.closest(base.iconSet), base.settings.mode );
				}

				// Set preview
				base.setPreview( $icon.data( 'class' ) );

				// Broadcast event passing the selected icon.
				$('body').trigger('iconselected.queryloop', icon);
			});
			$(base.iconSet).on('mouseenter mouseleave', 'li', function(e){
				if( 'mouseenter' == e.type ) {
					$(this).addClass(highlight);
				} else {
					$(this).removeClass(highlight);
				}
			});
		},

		showPicker: function( $brick, $icons, mode ){
			if ( 'inline' == mode ) {
				$('.jicon-set').removeClass('inline-open');
				$brick.find($icons).toggleClass('inline-open');
			} else if ( 'dialog' == mode ) {
				$('.jicon-picker-close, .jicon-picker-overlay').addClass('make-visible');
				$icons.addClass('jicon-dialog-open');
			}

			$icons.find('.jicon-selected').removeClass('jicon-selected');
			var selectedIcon = this.$field.val().replace(' ', '.');
			if ( '' != selectedIcon ) {
				if ( 'class' == this.settings.save ) {
					$icons.find('.' + selectedIcon).addClass('jicon-selected');
				} else {
					$icons.find('[data-code="' + selectedIcon + '"]').addClass('jicon-selected');
				}
			}
			// Broadcast event when the picker is shown passing the picker mode.
			$('body').trigger('iconpickershow.queryloop', mode);
		},

		closePicker: function( $brick, $icons, mode ){
			// Remove event so they don't fire from a different picker
			$(this.iconSet).off('click', 'li');

			if ( 'inline' == mode ) {
				$brick.find($icons).removeClass('inline-open');
			} else if ( 'dialog' == mode ) {
				$('.jicon-picker-close, .jicon-picker-overlay').removeClass('make-visible');
				$icons.removeClass('jicon-dialog-open');
				$('.jicon-dialog').removeClass('jicon-dialog-open');
				
			}
			// Broadcast event when the picker is closed passing the picker mode.
			$('body').trigger('iconpickerclose.queryloop', mode);
		},

		setPreview: function( preview ){
			var $preview = $(this.element).find('.jicon-preview');
			$preview.addClass('jicon-preview-on').find('i').removeClass()
				.addClass( this.iconSetName )
				.addClass( preview );
			$preview.find('a').show();
		}
	};

	$.fn.qlIconPicker = function ( options ) {
		this.each(function() {
			if ( !$.data( this, 'plugin_qlIconPicker' ) ) {
				$.data( this, 'plugin_qlIconPicker', new QL_Icon_Picker( this, options ) );
			}
		});
		return this;
	};

})( jQuery );