(function() {
	tinymce.PluginManager.add('mcelb', function(editor, url) {
		var sh_tag = 'mcelb';

		// helper functions
		function getAttr(s, n) {
			n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
			return n ? window.decodeURIComponent(n[1]) : '';
		};

		function html(cls, data) {
			var text	= getAttr(data, 'text');
			var data	= window.encodeURIComponent(data);

			return '<a class="mcelb button mceItem ' + cls + '" ' + 'data-sh-attr="' + data + '" data-mce-resize="false" data-mce-placeholder="0" style="display: inline-block; cursor: pointer;">' + text + '</a>';
		}

		function replaceShortcodes(content) {
			return content.replace( /\[mcelb([^\]]*)\]/g, function(all, attr) {
				return html('mce-link-button', attr);
			});
		}

		function restoreShortcodes(content) {
			return content.replace( /(?:<p(?:[^>]+)?>)*(<a class="mcelb [^>]+>[^<]+<\/a>)(?:<\/p>)*/g, function(match, anchor) {
				var data = getAttr(anchor, 'data-sh-attr');

				if (data) {
					return '<p>[' + sh_tag + data + ']</p>';
				}
				return match;
			});
		}

		// add popup
		editor.addCommand('mce-link-button-popup', function(ui, v) {
			// setup defaults
			var text = '';
			if (v.text)
				text = v.text;
			var link = 'http://';
			if (v.link)
				link = v.link;
			var target = 'self';
			if (v.target)
				target = v.target;

			// updated element will be removed first
			elem = '';
			if (typeof v.elem != 'undefined')
				elem = v.elem;

			editor.windowManager.open({
				title	: 'MCE Link Button',
				body	: [
					{
						type		: 'textbox',
						name		: 'text',
						label		: 'Text',
						value		: text,
						tooltip		: ''
					},
					{
						type		: 'textbox',
						name		: 'link',
						label		: 'Link',
						value		: link,
						tooltip		: ''
					},
					{
						type		: 'listbox',
						name		: 'target',
						label		: 'Target',
						value		: target,
						'values'	: [
							{text: 'Same Page',	value: 'self'},
							{text: 'New Page',	value: 'blank'}
						],
						tooltip: 'Select link target'
					}
				],
				onsubmit: function(e) {
					// remove updated element
					if (elem)
						elem.parentNode.removeChild(elem);

					var shortcode_str = '[' + sh_tag;
					// check for text
					if (typeof e.data.text != 'undefined' && e.data.text.length)
						shortcode_str += ' text="' + e.data.text + '"';
					// check for link
					if (typeof e.data.link != 'undefined' && e.data.link.length)
						shortcode_str += ' link="' + e.data.link + '"';
					// check for target
					if (typeof e.data.target != 'undefined' && e.data.target.length)
						shortcode_str += ' target="' + e.data.target + '"';

					// close shortcode
					shortcode_str += ']';
					//insert shortcode to tinymce
					editor.insertContent(shortcode_str);
				}
			});
		});

		// add button
		editor.addButton('mcelb', {
			icon	: 'mce-button-shortcode',
			tooltip	: 'MCE Link Button',
			onclick	: function() {
				editor.execCommand('mce-link-button-popup', '', {
					text	: 'Download',
					link	: 'https://',
					target	: 'self'
				});
			}
		});

		// replace from shortcode to an image placeholder
		editor.on('BeforeSetcontent', function(event) {
			event.content = replaceShortcodes(event.content);
		});

		// replace from image placeholder to shortcode
		editor.on('GetContent', function(event) {
			event.content = restoreShortcodes(event.content);
		});

		// open popup on placeholder double click
		editor.on('Click', function(e) {
			var cls = e.target.className.indexOf('mcelb');
			if ( e.target.nodeName == 'A' && e.target.className.indexOf('mce-link-button') > -1 ) {
				var title = e.target.attributes['data-sh-attr'].value;
				title = window.decodeURIComponent(title);

				editor.execCommand('mce-link-button-popup', '', {
					text	: getAttr(title, 'text'),
					link	: getAttr(title, 'link'),
					target	: getAttr(title, 'target'),
					elem	: e.target
				});
			}
		});
	});
})();