/**
 * OmnifyWP eCommerce – Gutenberg block editor registrations.
 *
 * Plain ES5-style JavaScript (no build step) using the global wp.* APIs.
 * Improvements in this version:
 *  • RangeControl sliders for numeric inputs (padding, radius, font-size, etc.)
 *  • ButtonGroup visual selectors for layout / columns / orientation / alignment
 *  • Debounced ServerSideRender – no API spam on every keystroke
 *  • useBlockProps for proper editor integration (selection, alignment)
 *  • TabPanel Style panel: Colors | Typography & Spacing | Border & Shadow
 *  • Block `example` data for inserter thumbnail previews
 *  • ComboboxControl for product search (falls back to SelectControl)
 *  • Notice tips inline in panels
 *  • Spinner loading indicator in preview
 *  • Proper empty/placeholder states per block
 */
( function () {
	'use strict';

	/* ── Bootstrap ─────────────────────────────────────────────────────── */
	var wp          = window.wp    || {};
	var blocks      = wp.blocks;
	var element     = wp.element;
	var components  = wp.components;
	var blockEditor = wp.blockEditor;
	var i18n        = wp.i18n;

	if ( ! blocks || ! element || ! blocks.registerBlockType || ! element.createElement ) {
		return;
	}

	var el         = element.createElement;
	var Fragment   = element.Fragment;
	var useState   = element.useState;
	var useEffect  = element.useEffect;
	var useRef     = element.useRef;
	var useMemo    = element.useMemo;

	var __       = i18n && i18n.__ ? i18n.__ : function ( t ) { return t; };
	var _n       = i18n && i18n._n ? i18n._n : function ( s, p, n ) { return n === 1 ? s : p; };

	var ssrModule         = wp.serverSideRender;
	var ServerSideRender  = ssrModule && ( ssrModule.default || ssrModule );

	var data           = window.omnifyBlocks || {};
	var productOptions = Array.isArray( data.products ) ? data.products : [];
	var categories     = Array.isArray( data.categories ) ? data.categories : [];

	/* ── WP Components ─────────────────────────────────────────────────── */
	var InspectorControls  = blockEditor  && blockEditor.InspectorControls;
	var useBlockProps      = blockEditor  && blockEditor.useBlockProps;
	var BlockControls      = blockEditor  && blockEditor.BlockControls;

	var PanelBody          = components && components.PanelBody;
	var PanelRow           = components && components.PanelRow;
	var SelectControl      = components && components.SelectControl;
	var TextControl        = components && components.TextControl;
	var TextareaControl    = components && components.TextareaControl;
	var ToggleControl      = components && components.ToggleControl;
	var RangeControl       = components && components.RangeControl;
	var ColorPalette       = components && components.ColorPalette;
	var ColorIndicator     = components && components.ColorIndicator;
	var Button             = components && components.Button;
	var ButtonGroup        = components && components.ButtonGroup;
	var BaseControl        = components && components.BaseControl;
	var Placeholder        = components && components.Placeholder;
	var Spinner            = components && components.Spinner;
	var Notice             = components && components.Notice;
	var TabPanel           = components && components.TabPanel;
	var Tooltip            = components && components.Tooltip;
	var Icon               = components && components.Icon;
	var ComboboxControl    = components && components.ComboboxControl;
	var Flex               = components && components.Flex;
	var FlexItem           = components && components.FlexItem;
	var Separator          = components && components.Separator;
	var Modal              = components && components.Modal;

	/* ── Emerald design-token palette ──────────────────────────────────── */
	var stylePalette = [
		{ name: __( 'Emerald',      'omnifywp-ecommerce' ), color: '#0f766e' },
		{ name: __( 'Deep Teal',    'omnifywp-ecommerce' ), color: '#115e59' },
		{ name: __( 'Forest',       'omnifywp-ecommerce' ), color: '#059669' },
		{ name: __( 'Mint',         'omnifywp-ecommerce' ), color: '#ecfdf5' },
		{ name: __( 'Midnight',     'omnifywp-ecommerce' ), color: '#0D1B12' },
		{ name: __( 'Ink',          'omnifywp-ecommerce' ), color: '#0f172a' },
		{ name: __( 'Slate',        'omnifywp-ecommerce' ), color: '#334155' },
		{ name: __( 'Muted',        'omnifywp-ecommerce' ), color: '#64748b' },
		{ name: __( 'Silver',       'omnifywp-ecommerce' ), color: '#e2e8f0' },
		{ name: __( 'Cloud',        'omnifywp-ecommerce' ), color: '#f8fafc' },
		{ name: __( 'White',        'omnifywp-ecommerce' ), color: '#ffffff' },
		{ name: __( 'Indigo',       'omnifywp-ecommerce' ), color: '#4f46e5' },
		{ name: __( 'Amber',        'omnifywp-ecommerce' ), color: '#f59e0b' },
		{ name: __( 'Red',          'omnifywp-ecommerce' ), color: '#dc2626' }
	];

	/* ── Shared style attributes ────────────────────────────────────────── */
	var styleAttributes = {
		maxWidth:         { type: 'integer', default: 0    },
		blockBackground:  { type: 'string',  default: ''   },
		cardBackground:   { type: 'string',  default: ''   },
		blockTextColor:   { type: 'string',  default: ''   },
		blockAccentColor: { type: 'string',  default: ''   },
		blockFontSize:    { type: 'integer', default: 0    },
		blockPadding:     { type: 'integer', default: 0    },
		blockRadius:      { type: 'integer', default: -1   },
		cardRadius:       { type: 'integer', default: -1   },
		blockBorderWidth: { type: 'integer', default: 0    },
		blockBorderColor: { type: 'string',  default: ''   },
		blockShadow:      { type: 'string',  default: ''   },
		blockMarginTop:   { type: 'integer', default: 0    },
		blockMarginBottom:{ type: 'integer', default: 0    },
		blockAlign:       { type: 'string',  default: ''   }
	};

	/* ── Small helpers ─────────────────────────────────────────────────── */
	function opt( value, label ) { return { value: value, label: label }; }

	function triOptions() {
		return [
			opt( '',  __( 'Use store default', 'omnifywp-ecommerce' ) ),
			opt( '1', __( 'Show',              'omnifywp-ecommerce' ) ),
			opt( '0', __( 'Hide',              'omnifywp-ecommerce' ) )
		];
	}

	function setAttr( props, attr, value ) {
		var u = {};
		u[ attr ] = value;
		props.setAttributes( u );
	}

	function getVal( props, attr, fallback ) {
		var v = props.attributes[ attr ];
		return v !== undefined ? v : ( fallback !== undefined ? fallback : '' );
	}

	/* ── Control factory functions ─────────────────────────────────────── */

	// Plain text
	function textField( label, attr, props, help ) {
		if ( ! TextControl ) return null;
		return el( TextControl, {
			key: attr,
			label: label,
			value: String( getVal( props, attr, '' ) ),
			help: help,
			onChange: function ( v ) { setAttr( props, attr, v ); }
		} );
	}

	// Range slider (falls back to number text-input)
	function rangeField( label, attr, props, min, max, step, help ) {
		var cur = parseInt( getVal( props, attr, 0 ), 10 );
		if ( isNaN( cur ) ) cur = 0;

		if ( RangeControl ) {
			return el( RangeControl, {
				key: attr,
				label: label,
				value: cur,
				min:   min  !== undefined ? min  : 0,
				max:   max  !== undefined ? max  : 100,
				step:  step !== undefined ? step : 1,
				help:  help,
				onChange: function ( v ) { setAttr( props, attr, v !== undefined ? v : 0 ); }
			} );
		}

		// Fallback
		if ( ! TextControl ) return null;
		return el( TextControl, {
			key: attr,
			label: label,
			type: 'number',
			value: String( cur ),
			help: help,
			onChange: function ( v ) { setAttr( props, attr, parseInt( v, 10 ) || 0 ); }
		} );
	}

	// Select dropdown
	function selectField( label, attr, options, props, help ) {
		if ( ! SelectControl ) return null;
		return el( SelectControl, {
			key: attr,
			label: label,
			value: String( getVal( props, attr, '' ) ),
			options: options,
			help: help,
			onChange: function ( v ) { setAttr( props, attr, v ); }
		} );
	}

	// Toggle
	function toggleField( label, attr, props, help ) {
		if ( ! ToggleControl ) return null;
		return el( ToggleControl, {
			key: attr,
			label: label,
			checked: !! getVal( props, attr, false ),
			help: help,
			onChange: function ( v ) { setAttr( props, attr, !! v ); }
		} );
	}

	// Color picker row
	function colorField( label, attr, props ) {
		var current = getVal( props, attr, '' );
		var indicatorRow = ( ColorIndicator && current )
			? el( ColorIndicator, { colorValue: current, style: { marginLeft: 8, verticalAlign: 'middle' } } )
			: null;

		return el(
			'div',
			{ className: 'omnify-color-field', key: attr, style: { marginBottom: 20 } },
			el( 'p', { className: 'omnify-block-style-label', style: { display: 'flex', alignItems: 'center', gap: 6 } },
				label, indicatorRow
			),
			ColorPalette
				? el( ColorPalette, {
					value:               current || undefined,
					colors:              stylePalette,
					clearable:           true,
					disableCustomColors: false,
					onChange: function ( v ) { setAttr( props, attr, v || '' ); }
				} )
				: el( TextControl, {
					value: current,
					onChange: function ( v ) { setAttr( props, attr, v ); }
				} )
		);
	}

	// ButtonGroup visual selector (falls back to SelectControl)
	function buttonGroupField( label, attr, options, props, help ) {
		var current = String( getVal( props, attr, '' ) );

		if ( Button && ButtonGroup && BaseControl ) {
			return el( BaseControl, { key: attr, label: label, help: help, id: 'omnify-bg-' + attr },
				el( 'div', { style: { marginTop: 4 } },
					el( ButtonGroup, { style: { flexWrap: 'wrap', gap: 4 } },
						options.map( function ( o ) {
							return el( Button, {
								key: o.value,
								variant:   current === String( o.value ) ? 'primary' : 'secondary',
								isSmall:   true,
								style:     { marginRight: 4, marginBottom: 4 },
								onClick:   function () { setAttr( props, attr, o.value ); }
							}, o.label );
						} )
					)
				)
			);
		}

		return selectField( label, attr, options, props, help );
	}

	// Notice/tip strip
	function tipNotice( text ) {
		if ( Notice ) {
			return el( Notice, { status: 'info', isDismissible: false, style: { marginBottom: 12, fontSize: 12 } }, text );
		}
		return el( 'p', { style: { fontSize: 11, color: '#64748b', marginBottom: 12 } }, text );
	}

	/* ── Style panel (tabbed) ───────────────────────────────────────────── */
	function stylePanel( props ) {
		var title = __( 'Style & Appearance', 'omnifywp-ecommerce' );

		// Colors tab content
		var colorsContent = [
			colorField( __( 'Container background', 'omnifywp-ecommerce' ),     'blockBackground',  props ),
			colorField( __( 'Card surface background', 'omnifywp-ecommerce' ),   'cardBackground',   props ),
			colorField( __( 'Text color', 'omnifywp-ecommerce' ),                'blockTextColor',   props ),
			colorField( __( 'Accent / brand color', 'omnifywp-ecommerce' ),      'blockAccentColor', props ),
			colorField( __( 'Border color', 'omnifywp-ecommerce' ),              'blockBorderColor', props )
		];

		// Spacing tab content
		var spacingContent = [
			rangeField( __( 'Max width (px)', 'omnifywp-ecommerce' ),          'maxWidth',      props, 0, 2400, 1, __( '0 = full width.', 'omnifywp-ecommerce' ) ),
			rangeField( __( 'Font size (px)', 'omnifywp-ecommerce' ),          'blockFontSize', props, 0, 72,   1, __( '0 = inherit.', 'omnifywp-ecommerce' ) ),
			rangeField( __( 'Padding (px)', 'omnifywp-ecommerce' ),            'blockPadding',  props, 0, 120,  4 ),
			rangeField( __( 'Corner radius (px)', 'omnifywp-ecommerce' ),      'blockRadius',   props, 0, 48,   1, __( '0 = square.', 'omnifywp-ecommerce' ) ),
			rangeField( __( 'Card corner radius (px)', 'omnifywp-ecommerce' ), 'cardRadius',    props, 0, 48,   1 ),
			rangeField( __( 'Top margin (px)', 'omnifywp-ecommerce' ),         'blockMarginTop',    props, 0, 200, 4 ),
			rangeField( __( 'Bottom margin (px)', 'omnifywp-ecommerce' ),      'blockMarginBottom', props, 0, 200, 4 ),
			buttonGroupField( __( 'Text alignment', 'omnifywp-ecommerce' ), 'blockAlign', [
				opt( '',       __( 'Default', 'omnifywp-ecommerce' ) ),
				opt( 'left',   __( 'Left',    'omnifywp-ecommerce' ) ),
				opt( 'center', __( 'Center',  'omnifywp-ecommerce' ) ),
				opt( 'right',  __( 'Right',   'omnifywp-ecommerce' ) )
			], props )
		];

		// Border & Shadow tab
		var borderContent = [
			rangeField( __( 'Border width (px)', 'omnifywp-ecommerce' ), 'blockBorderWidth', props, 0, 20, 1 ),
			selectField( __( 'Box shadow', 'omnifywp-ecommerce' ), 'blockShadow', [
				opt( '',       __( 'Theme default', 'omnifywp-ecommerce' ) ),
				opt( 'none',   __( 'None',          'omnifywp-ecommerce' ) ),
				opt( 'soft',   __( 'Soft',          'omnifywp-ecommerce' ) ),
				opt( 'medium', __( 'Medium',        'omnifywp-ecommerce' ) ),
				opt( 'strong', __( 'Strong',        'omnifywp-ecommerce' ) )
			], props )
		];

		// Use TabPanel when available, otherwise plain panels
		if ( TabPanel && PanelBody ) {
			return el( PanelBody, { title: title, initialOpen: false },
				el( TabPanel, {
					className: 'omnify-style-tabs',
					tabs: [
						{ name: 'colors',  title: __( 'Colors',   'omnifywp-ecommerce' ) },
						{ name: 'spacing', title: __( 'Spacing',  'omnifywp-ecommerce' ) },
						{ name: 'border',  title: __( 'Border',   'omnifywp-ecommerce' ) }
					],
					children: function ( tab ) {
						if ( tab.name === 'colors'  ) return el( Fragment, null, colorsContent  );
						if ( tab.name === 'spacing' ) return el( Fragment, null, spacingContent );
						if ( tab.name === 'border'  ) return el( Fragment, null, borderContent  );
						return null;
					}
				} )
			);
		}

		// Fallback: flat panels
		return el( Fragment, null,
			PanelBody && el( PanelBody, { title: title, initialOpen: false }, colorsContent, spacingContent, borderContent )
		);
	}

	/* ── Debounced ServerSideRender ─────────────────────────────────────── */
	/**
	 * Wraps SSR with a 500 ms debounce so rapid control changes don't
	 * hammer the REST endpoint. Shows a spinner while the delay is pending.
	 */
	var DebouncedPreview = function ( p ) {
		var blockName  = p.blockName;
		var attributes = p.attributes;

		var timerRef  = useRef && useRef( null );
		var initState = useState && useState( attributes );
		var deferred  = initState ? initState[0] : attributes;
		var setDeferred = initState ? initState[1] : function () {};

		var pendingState = useState && useState( false );
		var isPending    = pendingState ? pendingState[0] : false;
		var setPending   = pendingState ? pendingState[1] : function () {};

		var attrsStr = JSON.stringify( attributes );

		if ( useEffect ) {
			useEffect( function () {
				if ( timerRef && timerRef.current ) clearTimeout( timerRef.current );
				setPending( true );
				if ( timerRef ) {
					timerRef.current = setTimeout( function () {
						setDeferred( attributes );
						setPending( false );
					}, 500 );
				}
				return function () {
					if ( timerRef && timerRef.current ) clearTimeout( timerRef.current );
				};
			}, [ attrsStr ] ); // eslint-disable-line react-hooks/exhaustive-deps
		}

		return el( 'div', { className: 'omnify-block-editor__preview', style: { position: 'relative' } },
			isPending && Spinner
				? el( 'div', { className: 'omnify-block-editor__loading' },
					el( Spinner )
				)
				: null,
			ServerSideRender
				? el( ServerSideRender, { block: blockName, attributes: deferred || attributes } )
				: el( 'div', { className: 'omnify-block-editor__notice' },
					__( 'Live preview unavailable – block renders on the front end.', 'omnifywp-ecommerce' )
				)
		);
	};

	/* ── renderPreview helper ───────────────────────────────────────────── */
	function renderPreview( name, attributes ) {
		// Use hooks-capable component for debouncing when hooks are available
		if ( useState && useEffect && useRef ) {
			return el( DebouncedPreview, { blockName: name, attributes: attributes } );
		}

		// Hooks not available – render directly
		if ( ! ServerSideRender ) {
			return el( 'div', { className: 'omnify-block-editor__notice' },
				__( 'Live preview unavailable.', 'omnifywp-ecommerce' )
			);
		}
		return el( 'div', { className: 'omnify-block-editor__preview' },
			el( ServerSideRender, { block: name, attributes: attributes || {} } )
		);
	}

	/* ── inspector / panel wrappers ─────────────────────────────────────── */
	function panel( title, children, open ) {
		if ( ! PanelBody ) return null;
		// Flatten arrays so React doesn't complain about nested arrays without keys
		var flat = [].concat( children ).filter( Boolean );
		return el( PanelBody, { title: title, initialOpen: !! open }, flat );
	}

	function inspector() {
		if ( ! InspectorControls ) return null;
		var panels = Array.prototype.slice.call( arguments ).filter( Boolean );
		return el( InspectorControls, null, panels );
	}

	/* ── makeEdit with useBlockProps ────────────────────────────────────── */
	function makeEdit( name, controlsFactory ) {
		return function ( props ) {
			var blockProps = useBlockProps ? useBlockProps() : {};
			var controls   = controlsFactory ? controlsFactory( props ) : null;
			var preview    = renderPreview( name, props.attributes );

			return el( Fragment, null,
				controls,
				el( 'div', blockProps, preview )
			);
		};
	}

	/* ── register helper ────────────────────────────────────────────────── */
	function register( name, settings ) {
		if ( blocks.getBlockType && blocks.getBlockType( name ) ) return;
		blocks.registerBlockType( name, settings );
	}

	function withStyle( specific ) {
		return Object.assign( {}, styleAttributes, specific || {} );
	}

	var common = {
		category: 'omnify',
		supports:  { html: false, align: [ 'wide', 'full' ] },
		save:      function () { return null; }
	};

	/* ══════════════════════════════════════════════════════════════════════
	   1. PRODUCT GRID
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/product-grid', Object.assign( {}, common, {
		title:       __( 'Omnify Product Grid', 'omnifywp-ecommerce' ),
		description: __( 'Interactive product grid with instant search, category filters, flash sale ticker, and promotions.', 'omnifywp-ecommerce' ),
		icon:        'grid-view',
		keywords:    [ 'shop', 'products', 'store', 'grid', 'ecommerce', 'catalog' ],

		example: {
			attributes: { columns: '3', showHero: '1', showFlashTicker: '1', showQuickFilters: '1' }
		},

		attributes: withStyle( {
			columns:          { type: 'string',  default: '' },
			perPage:          { type: 'integer', default: 0 },
			category:         { type: 'string',  default: '' },
			tag:              { type: 'string',  default: '' },
			brand:            { type: 'string',  default: '' },
			minPrice:         { type: 'number',  default: -1 },
			maxPrice:         { type: 'number',  default: -1 },
			stockStatus:      { type: 'string',  default: '' },
			gridRows:         { type: 'integer', default: -1 },
			layout:           { type: 'string',  default: '' },
			cardStyle:        { type: 'string',  default: '' },
			gap:              { type: 'string',  default: '' },
			filterPosition:   { type: 'string',  default: '' },
			paginationType:   { type: 'string',  default: '' },
			defaultSort:      { type: 'string',  default: '' },
			heroTitle:        { type: 'string',  default: '' },
			heroSubtitle:     { type: 'string',  default: '' },
			flashTickerText:  { type: 'string',  default: '' },
			flashCouponCode:  { type: 'string',  default: '' },
			showSearch:       { type: 'string',  default: '' },
			showFilters:      { type: 'string',  default: '' },
			showSort:         { type: 'string',  default: '' },
			showHero:         { type: 'string',  default: '' },
			showFlashTicker:  { type: 'string',  default: '' },
			showTrustBar:     { type: 'string',  default: '' },
			showQuickFilters: { type: 'string',  default: '' },
			showBreadcrumbs:  { type: 'string',  default: '' },
			showReviews:      { type: 'string',  default: '' },
			showWishlist:     { type: 'string',  default: '' },
			showAddToCart:    { type: 'string',  default: '' },
			showQuickView:    { type: 'string',  default: '' },
			showSaleBadge:    { type: 'string',  default: '' },
			showStockBadge:   { type: 'string',  default: '' },
			showFeaturePill:  { type: 'string',  default: '' },
			showRecentlyViewed:{ type: 'string', default: '' },
			featuredOnly:     { type: 'boolean', default: false }
		} ),

		edit: makeEdit( 'omnify/product-grid', function ( props ) {
			return inspector(

				/* ── Layout & Columns ── */
				panel( __( 'Layout & Columns', 'omnifywp-ecommerce' ), [
					buttonGroupField( __( 'Grid columns', 'omnifywp-ecommerce' ), 'columns', [
						opt( '',  __( 'Auto',      'omnifywp-ecommerce' ) ),
						opt( '2', __( '2 col',     'omnifywp-ecommerce' ) ),
						opt( '3', __( '3 col',     'omnifywp-ecommerce' ) ),
						opt( '4', __( '4 col',     'omnifywp-ecommerce' ) ),
						opt( '5', __( '5 col',     'omnifywp-ecommerce' ) )
					], props ),

					buttonGroupField( __( 'View style', 'omnifywp-ecommerce' ), 'layout', [
						opt( '',       __( 'Default', 'omnifywp-ecommerce' ) ),
						opt( '2',      __( '2-wide',  'omnifywp-ecommerce' ) ),
						opt( '4',      __( '4-dense', 'omnifywp-ecommerce' ) ),
						opt( 'list',   __( 'List',    'omnifywp-ecommerce' ) ),
						opt( 'compact',__( 'Compact', 'omnifywp-ecommerce' ) )
					], props ),

					buttonGroupField( __( 'Card style', 'omnifywp-ecommerce' ), 'cardStyle', [
						opt( '',          __( 'Default',  'omnifywp-ecommerce' ) ),
						opt( 'standard',  __( 'Standard', 'omnifywp-ecommerce' ) ),
						opt( 'compact',   __( 'Compact',  'omnifywp-ecommerce' ) ),
						opt( 'spacious',  __( 'Spacious', 'omnifywp-ecommerce' ) )
					], props ),

					rangeField( __( 'Products per page', 'omnifywp-ecommerce' ), 'perPage',   props, 0, 100, 1, __( '0 = store default.', 'omnifywp-ecommerce' ) ),
					rangeField( __( 'Max rows', 'omnifywp-ecommerce' ),          'gridRows',  props, 0, 20,  1, __( '0 = unlimited.', 'omnifywp-ecommerce' ) ),

					selectField( __( 'Pagination', 'omnifywp-ecommerce' ), 'paginationType', [
						opt( '',           __( 'Store default',     'omnifywp-ecommerce' ) ),
						opt( 'classic',    __( 'Classic numbers',   'omnifywp-ecommerce' ) ),
						opt( 'load_more',  __( 'Load more (AJAX)',  'omnifywp-ecommerce' ) )
					], props ),

					selectField( __( 'Default sort', 'omnifywp-ecommerce' ), 'defaultSort', [
						opt( '',           __( 'Store default',       'omnifywp-ecommerce' ) ),
						opt( 'latest',     __( 'Latest',              'omnifywp-ecommerce' ) ),
						opt( 'price_asc',  __( 'Price: low → high',   'omnifywp-ecommerce' ) ),
						opt( 'price_desc', __( 'Price: high → low',   'omnifywp-ecommerce' ) ),
						opt( 'name_asc',   __( 'Name: A → Z',         'omnifywp-ecommerce' ) ),
						opt( 'name_desc',  __( 'Name: Z → A',         'omnifywp-ecommerce' ) )
					], props )
				], true ),

				/* ── Promotions & Header ── */
				panel( __( 'Promotions & Hero Header', 'omnifywp-ecommerce' ), [
					selectField( __( 'Hero banner', 'omnifywp-ecommerce' ), 'showHero', triOptions(), props ),
					textField( __( 'Custom hero title',    'omnifywp-ecommerce' ), 'heroTitle',    props, __( 'Leave empty for store default.', 'omnifywp-ecommerce' ) ),
					textField( __( 'Custom hero subtitle', 'omnifywp-ecommerce' ), 'heroSubtitle', props ),
					selectField( __( 'Flash sale ticker', 'omnifywp-ecommerce' ), 'showFlashTicker', triOptions(), props ),
					textField( __( 'Ticker message',    'omnifywp-ecommerce' ), 'flashTickerText', props ),
					textField( __( 'Coupon code pill',  'omnifywp-ecommerce' ), 'flashCouponCode', props, __( '1-click copy. e.g. FLASH30', 'omnifywp-ecommerce' ) ),
					selectField( __( 'Trust badges bar', 'omnifywp-ecommerce' ), 'showTrustBar', triOptions(), props )
				], false ),

				/* ── Filters ── */
				panel( __( 'Filters, Search & Quick Pills', 'omnifywp-ecommerce' ), [
					selectField( __( 'Search bar',          'omnifywp-ecommerce' ), 'showSearch',       triOptions(), props ),
					selectField( __( 'Filter sidebar',      'omnifywp-ecommerce' ), 'showFilters',      triOptions(), props ),
					selectField( __( 'Quick category pills','omnifywp-ecommerce' ), 'showQuickFilters', triOptions(), props ),
					selectField( __( 'Sort dropdown',       'omnifywp-ecommerce' ), 'showSort',         triOptions(), props ),
					buttonGroupField( __( 'Filter position', 'omnifywp-ecommerce' ), 'filterPosition', [
						opt( '',      __( 'Default', 'omnifywp-ecommerce' ) ),
						opt( 'top',   __( 'Top',     'omnifywp-ecommerce' ) ),
						opt( 'left',  __( 'Left',    'omnifywp-ecommerce' ) ),
						opt( 'right', __( 'Right',   'omnifywp-ecommerce' ) )
					], props )
				], false ),

				/* ── Product Card Elements ── */
				panel( __( 'Product Card Elements', 'omnifywp-ecommerce' ), [
					selectField( __( 'Add to Cart button', 'omnifywp-ecommerce' ), 'showAddToCart',   triOptions(), props ),
					selectField( __( 'Star ratings',       'omnifywp-ecommerce' ), 'showReviews',     triOptions(), props ),
					selectField( __( 'Wishlist button',    'omnifywp-ecommerce' ), 'showWishlist',    triOptions(), props ),
					selectField( __( 'Quick view modal',   'omnifywp-ecommerce' ), 'showQuickView',   triOptions(), props ),
					selectField( __( 'Sale badge',         'omnifywp-ecommerce' ), 'showSaleBadge',   triOptions(), props ),
					selectField( __( 'Stock badge',        'omnifywp-ecommerce' ), 'showStockBadge',  triOptions(), props ),
					selectField( __( 'Feature pill',       'omnifywp-ecommerce' ), 'showFeaturePill', triOptions(), props ),
					selectField( __( 'Breadcrumbs',        'omnifywp-ecommerce' ), 'showBreadcrumbs', triOptions(), props ),
					selectField( __( 'Recently viewed',    'omnifywp-ecommerce' ), 'showRecentlyViewed', triOptions(), props )
				], false ),

				/* ── Query ── */
				panel( __( 'Query & Taxonomy Filter', 'omnifywp-ecommerce' ), [
					categories && categories.length > 1
						? selectField( __( 'Category', 'omnifywp-ecommerce' ), 'category', categories, props, __( 'Filter grid by category.', 'omnifywp-ecommerce' ) )
						: textField( __( 'Category slug', 'omnifywp-ecommerce' ), 'category', props, __( 'Single category slug. Leave empty for all.', 'omnifywp-ecommerce' ) ),
					textField( __( 'Tag slug',      'omnifywp-ecommerce' ), 'tag',      props ),
					textField( __( 'Brand slug',    'omnifywp-ecommerce' ), 'brand',    props ),
					rangeField( __( 'Min price ($)', 'omnifywp-ecommerce' ), 'minPrice', props, 0, 50000, 1 ),
					rangeField( __( 'Max price ($)', 'omnifywp-ecommerce' ), 'maxPrice', props, 0, 50000, 1 ),
					selectField( __( 'Stock status', 'omnifywp-ecommerce' ), 'stockStatus', [
						opt( '',            __( 'Any',         'omnifywp-ecommerce' ) ),
						opt( 'instock',     __( 'In stock',    'omnifywp-ecommerce' ) ),
						opt( 'outofstock',  __( 'Out of stock','omnifywp-ecommerce' ) ),
						opt( 'onbackorder', __( 'Backorder',   'omnifywp-ecommerce' ) ),
						opt( 'onsale',      __( 'On sale',     'omnifywp-ecommerce' ) )
					], props ),
					toggleField( __( 'Featured products only', 'omnifywp-ecommerce' ), 'featuredOnly', props )
				], false ),

				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   2. SHOPPING CART
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/cart', Object.assign( {}, common, {
		title:       __( 'Omnify Shopping Cart', 'omnifywp-ecommerce' ),
		description: __( 'Full shopping cart with coupon input, free-shipping meter, cross-sells and trust badges.', 'omnifywp-ecommerce' ),
		icon:        'cart',
		keywords:    [ 'cart', 'shopping', 'basket', 'ecommerce' ],

		example: { attributes: { showCoupon: '1', showShippingMeter: true } },

		attributes: withStyle( {
			showCoupon:        { type: 'string',  default: '' },
			showSteps:         { type: 'boolean', default: true },
			showNote:          { type: 'boolean', default: true },
			showShippingMeter: { type: 'boolean', default: true },
			shippingThreshold: { type: 'number',  default: 0 },
			showTrustBadges:   { type: 'boolean', default: true },
			showEmptyButton:   { type: 'boolean', default: true },
			showCrossSells:    { type: 'boolean', default: true }
		} ),

		edit: makeEdit( 'omnify/cart', function ( props ) {
			return inspector(
				panel( __( 'Cart Features', 'omnifywp-ecommerce' ), [
					selectField( __( 'Coupon field', 'omnifywp-ecommerce' ), 'showCoupon', triOptions(), props ),
					toggleField( __( 'Checkout progress steps', 'omnifywp-ecommerce' ),         'showSteps',         props ),
					toggleField( __( 'Order note textarea', 'omnifywp-ecommerce' ),              'showNote',          props ),
					toggleField( __( 'Free shipping progress meter', 'omnifywp-ecommerce' ),     'showShippingMeter', props ),
					rangeField(  __( 'Free shipping threshold ($)', 'omnifywp-ecommerce' ),      'shippingThreshold', props, 0, 500, 1, __( '0 = use store setting.', 'omnifywp-ecommerce' ) ),
					toggleField( __( 'Trust & payment icons strip', 'omnifywp-ecommerce' ),      'showTrustBadges',   props ),
					toggleField( __( 'Continue shopping back link', 'omnifywp-ecommerce' ),      'showEmptyButton',   props ),
					toggleField( __( 'Cross-sell recommendations', 'omnifywp-ecommerce' ),       'showCrossSells',    props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   3. MINI CART
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/mini-cart', Object.assign( {}, common, {
		title:       __( 'Omnify Mini Cart', 'omnifywp-ecommerce' ),
		description: __( 'Floating or embedded mini cart button with slide-out drawer and shipping progress.', 'omnifywp-ecommerce' ),
		icon:        'cart',
		keywords:    [ 'cart', 'mini', 'drawer', 'floating' ],

		example: { attributes: { label: 'Cart', showShippingMeter: true } },

		attributes: withStyle( {
			label:              { type: 'string',  default: 'Cart' },
			align:              { type: 'string',  default: 'right' },
			showShippingMeter:  { type: 'boolean', default: true },
			shippingThreshold:  { type: 'number',  default: 0 },
			showCount:          { type: 'boolean', default: true },
			showSubtotal:       { type: 'boolean', default: true },
			showCoupon:         { type: 'boolean', default: true },
			showViewCartButton: { type: 'boolean', default: true },
			showCheckoutButton: { type: 'boolean', default: true },
			showEmptyState:     { type: 'boolean', default: true }
		} ),

		edit: makeEdit( 'omnify/mini-cart', function ( props ) {
			return inspector(
				panel( __( 'Mini Cart Drawer', 'omnifywp-ecommerce' ), [
					textField( __( 'Button label', 'omnifywp-ecommerce' ), 'label', props ),

					buttonGroupField( __( 'Drawer direction', 'omnifywp-ecommerce' ), 'align', [
						opt( 'right', __( '← Slide from right', 'omnifywp-ecommerce' ) ),
						opt( 'left',  __( '→ Slide from left',  'omnifywp-ecommerce' ) )
					], props ),

					toggleField( __( 'Item count badge', 'omnifywp-ecommerce' ),          'showCount',          props ),
					toggleField( __( 'Subtotal on trigger', 'omnifywp-ecommerce' ),        'showSubtotal',       props ),
					toggleField( __( 'Shipping progress meter', 'omnifywp-ecommerce' ),    'showShippingMeter',  props ),
					rangeField(  __( 'Free shipping target ($)', 'omnifywp-ecommerce' ),   'shippingThreshold',  props, 0, 500, 1 ),
					toggleField( __( 'Coupon accordion in drawer', 'omnifywp-ecommerce' ), 'showCoupon',         props ),
					toggleField( __( 'View Cart button', 'omnifywp-ecommerce' ),           'showViewCartButton', props ),
					toggleField( __( 'Checkout CTA button', 'omnifywp-ecommerce' ),        'showCheckoutButton', props ),
					toggleField( __( 'Empty state illustration', 'omnifywp-ecommerce' ),   'showEmptyState',     props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   4. CHECKOUT
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/checkout', Object.assign( {}, common, {
		title:       __( 'Omnify Checkout', 'omnifywp-ecommerce' ),
		description: __( 'High-converting one-page checkout with 14+ gateways, order bump, and urgency countdown.', 'omnifywp-ecommerce' ),
		icon:        'money-alt',
		keywords:    [ 'checkout', 'payment', 'purchase', 'order', 'pay' ],

		example: { attributes: { showTrustBadges: '1', showOrderBump: '1', showScarcityCountdown: true } },

		attributes: withStyle( {
			layout:               { type: 'string',  default: '' },
			showTrustBadges:      { type: 'string',  default: '' },
			showOrderBump:        { type: 'string',  default: '' },
			orderBumpTitle:       { type: 'string',  default: '' },
			showCoupon:           { type: 'string',  default: '' },
			requireTerms:         { type: 'string',  default: '' },
			showOrderNotes:       { type: 'boolean', default: true },
			showScarcityCountdown:{ type: 'boolean', default: true },
			countdownMinutes:     { type: 'integer', default: 15 },
			showGuarantees:       { type: 'boolean', default: true },
			expressCheckout:      { type: 'boolean', default: true }
		} ),

		edit: makeEdit( 'omnify/checkout', function ( props ) {
			return inspector(
				panel( __( 'Form Layout', 'omnifywp-ecommerce' ), [
					buttonGroupField( __( 'Layout', 'omnifywp-ecommerce' ), 'layout', [
						opt( '',     __( 'Default', 'omnifywp-ecommerce' ) ),
						opt( '2col', __( '2-col',   'omnifywp-ecommerce' ) ),
						opt( '1col', __( '1-col',   'omnifywp-ecommerce' ) )
					], props ),
					selectField( __( 'Coupon field',          'omnifywp-ecommerce' ), 'showCoupon',    triOptions(), props ),
					selectField( __( 'Require T&C checkbox',  'omnifywp-ecommerce' ), 'requireTerms',  triOptions(), props ),
					toggleField( __( 'Order notes field',     'omnifywp-ecommerce' ), 'showOrderNotes', props ),
					toggleField( __( 'Express checkout row',  'omnifywp-ecommerce' ), 'expressCheckout', props, __( 'Apple Pay, Google Pay & PayPal one-click buttons.', 'omnifywp-ecommerce' ) )
				], true ),

				panel( __( 'Conversion Boosters', 'omnifywp-ecommerce' ), [
					selectField( __( 'Trust badge box',        'omnifywp-ecommerce' ), 'showTrustBadges', triOptions(), props ),
					toggleField( __( 'Money-back guarantee',   'omnifywp-ecommerce' ), 'showGuarantees',       props ),
					toggleField( __( 'Cart reservation timer', 'omnifywp-ecommerce' ), 'showScarcityCountdown', props ),
					rangeField(  __( 'Timer duration (mins)',  'omnifywp-ecommerce' ), 'countdownMinutes', props, 1, 60, 1 )
				], false ),

				panel( __( 'Order Bump Offer', 'omnifywp-ecommerce' ), [
					selectField( __( 'Order bump checkbox',   'omnifywp-ecommerce' ), 'showOrderBump',  triOptions(), props ),
					textField(   __( 'Custom bump heading',   'omnifywp-ecommerce' ), 'orderBumpTitle', props, __( 'Leave empty for store default.', 'omnifywp-ecommerce' ) )
				], false ),

				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   5. CUSTOMER PORTAL
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/customer-portal', Object.assign( {}, common, {
		title:       __( 'Omnify Customer Portal', 'omnifywp-ecommerce' ),
		description: __( 'Self-service portal: order history, downloads, invoices, wishlist, and profile management.', 'omnifywp-ecommerce' ),
		icon:        'id',
		keywords:    [ 'portal', 'account', 'customer', 'downloads', 'orders' ],

		example: { attributes: { showStats: true, defaultTab: '' } },

		attributes: withStyle( {
			defaultTab:      { type: 'string',  default: '' },
			showStats:       { type: 'boolean', default: true },
			showGreeting:    { type: 'boolean', default: true },
			showLogout:      { type: 'boolean', default: true },
			showInvoices:    { type: 'boolean', default: true },
			showWishlistTab: { type: 'boolean', default: true },
			showSearch:      { type: 'boolean', default: true }
		} ),

		edit: makeEdit( 'omnify/customer-portal', function ( props ) {
			return inspector(
				panel( __( 'Portal Sections', 'omnifywp-ecommerce' ), [
					selectField( __( 'Initial active tab', 'omnifywp-ecommerce' ), 'defaultTab', [
						opt( '',           __( 'Products (default)',     'omnifywp-ecommerce' ) ),
						opt( 'downloads',  __( 'Digital Downloads',     'omnifywp-ecommerce' ) ),
						opt( 'orders',     __( 'Order history',         'omnifywp-ecommerce' ) ),
						opt( 'invoices',   __( 'Invoices & Receipts',   'omnifywp-ecommerce' ) ),
						opt( 'wishlist',   __( 'Saved Wishlist',        'omnifywp-ecommerce' ) ),
						opt( 'history',    __( 'Download Audit Log',    'omnifywp-ecommerce' ) ),
						opt( 'settings',   __( 'Account Settings',      'omnifywp-ecommerce' ) )
					], props ),
					toggleField( __( 'KPI summary cards',        'omnifywp-ecommerce' ), 'showStats',       props, __( 'Total orders, active downloads, wishlist count.', 'omnifywp-ecommerce' ) ),
					toggleField( __( 'Greeting header',          'omnifywp-ecommerce' ), 'showGreeting',    props ),
					toggleField( __( 'Invoices tab',             'omnifywp-ecommerce' ), 'showInvoices',    props ),
					toggleField( __( 'Wishlist tab',             'omnifywp-ecommerce' ), 'showWishlistTab', props ),
					toggleField( __( 'Search orders & files',   'omnifywp-ecommerce' ), 'showSearch',      props ),
					toggleField( __( 'Log out button',           'omnifywp-ecommerce' ), 'showLogout',      props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   6. DIGITAL DOWNLOADS LOCKER
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/customer-downloads', Object.assign( {}, common, {
		title:       __( 'Omnify Downloads Locker', 'omnifywp-ecommerce' ),
		description: __( 'Standalone digital file locker with HMAC signed URLs, expiry notices, and download limits.', 'omnifywp-ecommerce' ),
		icon:        'download',
		keywords:    [ 'download', 'files', 'digital', 'license', 'software', 'locker' ],

		example: { attributes: { showVersion: true, showFileSize: true } },

		attributes: withStyle( {
			title:             { type: 'string',  default: '' },
			subtitle:          { type: 'string',  default: '' },
			showSearch:        { type: 'boolean', default: true },
			showVersion:       { type: 'boolean', default: true },
			showFileSize:      { type: 'boolean', default: true },
			showExpiryNotice:  { type: 'boolean', default: true },
			showDownloadCount: { type: 'boolean', default: true }
		} ),

		edit: makeEdit( 'omnify/customer-downloads', function ( props ) {
			return inspector(
				panel( __( 'Downloads Locker', 'omnifywp-ecommerce' ), [
					tipNotice( __( 'Displays only the digital downloads the logged-in customer has purchased.', 'omnifywp-ecommerce' ) ),
					textField( __( 'Section heading',          'omnifywp-ecommerce' ), 'title',    props, __( 'Leave empty for "My Digital Downloads".', 'omnifywp-ecommerce' ) ),
					textField( __( 'Subheading / description', 'omnifywp-ecommerce' ), 'subtitle', props ),
					toggleField( __( 'File search bar',              'omnifywp-ecommerce' ), 'showSearch',        props ),
					toggleField( __( 'Version tags (e.g. v2.1.0)',   'omnifywp-ecommerce' ), 'showVersion',       props ),
					toggleField( __( 'File size badges (e.g. 42 MB)','omnifywp-ecommerce' ), 'showFileSize',      props ),
					toggleField( __( 'Expiring link warnings',       'omnifywp-ecommerce' ), 'showExpiryNotice',  props ),
					toggleField( __( 'Remaining downloads counter',  'omnifywp-ecommerce' ), 'showDownloadCount', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   7. ORDER TRACKING
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/order-tracking', Object.assign( {}, common, {
		title:       __( 'Omnify Order Tracking', 'omnifywp-ecommerce' ),
		description: __( 'Guest-friendly order tracking form with shipment timeline and invoice download.', 'omnifywp-ecommerce' ),
		icon:        'location-alt',
		keywords:    [ 'track', 'tracking', 'order', 'shipment', 'delivery' ],

		example: { attributes: { showTimeline: true } },

		attributes: withStyle( {
			title:              { type: 'string',  default: '' },
			subtitle:           { type: 'string',  default: '' },
			showTimeline:       { type: 'boolean', default: true },
			showItemsSummary:   { type: 'boolean', default: true },
			showSupportButton:  { type: 'boolean', default: true },
			supportEmail:       { type: 'string',  default: '' }
		} ),

		edit: makeEdit( 'omnify/order-tracking', function ( props ) {
			return inspector(
				panel( __( 'Order Tracking', 'omnifywp-ecommerce' ), [
					textField( __( 'Heading',             'omnifywp-ecommerce' ), 'title',    props, __( 'Leave empty for "Track Your Order".', 'omnifywp-ecommerce' ) ),
					textField( __( 'Subheading',          'omnifywp-ecommerce' ), 'subtitle', props ),
					toggleField( __( 'Shipment timeline',         'omnifywp-ecommerce' ), 'showTimeline',      props ),
					toggleField( __( 'Items breakdown table',     'omnifywp-ecommerce' ), 'showItemsSummary',  props ),
					toggleField( __( 'Contact support link',      'omnifywp-ecommerce' ), 'showSupportButton', props ),
					textField(   __( 'Support email (optional)',  'omnifywp-ecommerce' ), 'supportEmail', props, __( 'Leave empty to use admin email.', 'omnifywp-ecommerce' ) )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   8. PRODUCT CARD
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/product-card', Object.assign( {}, common, {
		title:       __( 'Omnify Product Card', 'omnifywp-ecommerce' ),
		description: __( 'Individual high-converting product card with optional Buy Now direct-to-checkout action.', 'omnifywp-ecommerce' ),
		icon:        'tag',
		keywords:    [ 'product', 'card', 'buy', 'button', 'showcase' ],

		example: { attributes: { productId: 0, showPrice: true, showRating: true } },

		attributes: withStyle( {
			productId:       { type: 'integer', default: 0 },
			cardLayout:      { type: 'string',  default: 'vertical' },
			showPrice:       { type: 'boolean', default: true },
			showComparePrice:{ type: 'boolean', default: true },
			showRating:      { type: 'boolean', default: true },
			showBuyButton:   { type: 'boolean', default: true },
			buttonAction:    { type: 'string',  default: 'cart' },
			showSku:         { type: 'boolean', default: false },
			showExcerpt:     { type: 'boolean', default: false },
			showSaleBadge:   { type: 'boolean', default: true },
			showStockStatus: { type: 'boolean', default: false },
			showCategory:    { type: 'boolean', default: false },
			showFeaturePill: { type: 'boolean', default: false },
			imageRatio:      { type: 'string',  default: 'square' },
			buttonLabel:     { type: 'string',  default: 'Add to Cart' },
			badgeText:        { type: 'string',  default: '' },
			showGalleryFlip:  { type: 'boolean', default: true },
			hoverEffect:      { type: 'string',  default: 'lift' }
		} ),

		edit: function ( props ) {
			var blockProps = useBlockProps ? useBlockProps() : {};
			var productId  = parseInt( props.attributes.productId, 10 ) || 0;

			// Build ComboboxControl or SelectControl for product picker
			var productPickerOptions = useMemo
				? useMemo( function () {
					return productOptions.map( function ( o ) {
						return { value: String( o.value ), label: o.label };
					} );
				}, [] )
				: productOptions.map( function ( o ) {
					return { value: String( o.value ), label: o.label };
				} );

			var productPicker = null;
			if ( ComboboxControl ) {
				productPicker = el( ComboboxControl, {
					key: 'productCombo',
					label: __( 'Select product', 'omnifywp-ecommerce' ),
					value: String( productId || 0 ),
					options: productPickerOptions,
					help: __( 'Type to search published products.', 'omnifywp-ecommerce' ),
					onChange: function ( v ) { setAttr( props, 'productId', parseInt( v, 10 ) || 0 ); },
					onFilterValueChange: function () {}
				} );
			} else if ( SelectControl ) {
				productPicker = el( SelectControl, {
					key: 'productSelect',
					label: __( 'Select product', 'omnifywp-ecommerce' ),
					value: String( productId || 0 ),
					options: productPickerOptions,
					onChange: function ( v ) { setAttr( props, 'productId', parseInt( v, 10 ) || 0 ); }
				} );
			}

			var controls = inspector(
				panel( __( 'Product Card Display', 'omnifywp-ecommerce' ), [
					productPicker,

					buttonGroupField( __( 'Orientation', 'omnifywp-ecommerce' ), 'cardLayout', [
						opt( 'vertical',   __( 'Vertical',    'omnifywp-ecommerce' ) ),
						opt( 'horizontal', __( 'Horizontal',  'omnifywp-ecommerce' ) )
					], props ),

					buttonGroupField( __( 'Image ratio', 'omnifywp-ecommerce' ), 'imageRatio', [
						opt( 'square',    __( '1:1 Square',    'omnifywp-ecommerce' ) ),
						opt( 'landscape', __( '4:3 Landscape', 'omnifywp-ecommerce' ) ),
						opt( 'portrait',  __( '3:4 Portrait',  'omnifywp-ecommerce' ) )
					], props ),

					toggleField( __( 'Price',                 'omnifywp-ecommerce' ), 'showPrice',        props ),
					toggleField( __( 'Compare / strike price','omnifywp-ecommerce' ), 'showComparePrice', props ),
					toggleField( __( 'Star rating & count',   'omnifywp-ecommerce' ), 'showRating',       props ),
					toggleField( __( 'SKU code',              'omnifywp-ecommerce' ), 'showSku',          props ),
					toggleField( __( 'Short description',     'omnifywp-ecommerce' ), 'showExcerpt',      props ),
					toggleField( __( 'Category pill',         'omnifywp-ecommerce' ), 'showCategory',     props ),
					toggleField( __( 'Sale badge',            'omnifywp-ecommerce' ), 'showSaleBadge',    props ),
					toggleField( __( 'Stock status',          'omnifywp-ecommerce' ), 'showStockStatus',  props ),
					toggleField( __( 'Feature badge',         'omnifywp-ecommerce' ), 'showFeaturePill',  props ),
					toggleField( __( 'Hover secondary image flip', 'omnifywp-ecommerce' ), 'showGalleryFlip', props ),
					textField(   __( 'Custom ribbon badge text', 'omnifywp-ecommerce' ), 'badgeText', props ),
					buttonGroupField( __( 'Card hover effect', 'omnifywp-ecommerce' ), 'hoverEffect', [
						opt( 'lift',  __( 'Lift', 'omnifywp-ecommerce' ) ),
						opt( 'glow',  __( 'Glow', 'omnifywp-ecommerce' ) ),
						opt( 'none',  __( 'None', 'omnifywp-ecommerce' ) )
					], props )
				], true ),

				panel( __( 'Action Button', 'omnifywp-ecommerce' ), [
					toggleField( __( 'Show action button', 'omnifywp-ecommerce' ), 'showBuyButton', props ),
					textField(   __( 'Button label',       'omnifywp-ecommerce' ), 'buttonLabel',   props ),
					buttonGroupField( __( 'Click action', 'omnifywp-ecommerce' ), 'buttonAction', [
						opt( 'cart',     __( 'Add to Cart',   'omnifywp-ecommerce' ) ),
						opt( 'checkout', __( 'Buy Now (1-click)', 'omnifywp-ecommerce' ) )
					], props )
				], false ),

				stylePanel( props )
			);

			// Empty placeholder when no product selected
			if ( ! productId ) {
				var placeholder = Placeholder
					? el( Placeholder, {
						icon:         'tag',
						label:        __( 'Omnify Product Card', 'omnifywp-ecommerce' ),
						instructions: __( 'Select a product from the sidebar panel to show a live preview.', 'omnifywp-ecommerce' )
					} )
					: el( 'div', { className: 'omnify-block-editor__notice' },
						__( 'Select a product in the sidebar to preview.', 'omnifywp-ecommerce' )
					);

				return el( Fragment, null,
					controls,
					el( 'div', blockProps, placeholder )
				);
			}

			return el( Fragment, null,
				controls,
				el( 'div', blockProps, renderPreview( 'omnify/product-card', props.attributes ) )
			);
		}
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   9. CURRENCY SWITCHER
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/currency-switcher', Object.assign( {}, common, {
		title:       __( 'Omnify Currency Switcher', 'omnifywp-ecommerce' ),
		description: __( 'Interactive currency switcher (dropdown or pill strip) supporting 22 global currencies.', 'omnifywp-ecommerce' ),
		icon:        'translation',
		keywords:    [ 'currency', 'switcher', 'converter', 'money', 'multicurrency' ],

		example: { attributes: { displayStyle: 'dropdown', showSymbol: true } },

		attributes: withStyle( {
			label:        { type: 'string',  default: 'Currency:' },
			showLabel:    { type: 'boolean', default: true },
			showSymbol:   { type: 'boolean', default: true },
			showFlag:     { type: 'boolean', default: false },
			displayStyle: { type: 'string',  default: 'dropdown' },
			currencies:   { type: 'string',  default: '' },
			align:        { type: 'string',  default: '' }
		} ),

		edit: makeEdit( 'omnify/currency-switcher', function ( props ) {
			return inspector(
				panel( __( 'Currency Switcher', 'omnifywp-ecommerce' ), [
					textField( __( 'Prefix label', 'omnifywp-ecommerce' ), 'label', props ),
					toggleField( __( 'Show label',       'omnifywp-ecommerce' ), 'showLabel',  props ),
					toggleField( __( 'Show symbol',      'omnifywp-ecommerce' ), 'showSymbol', props ),

					buttonGroupField( __( 'Display style', 'omnifywp-ecommerce' ), 'displayStyle', [
						opt( 'dropdown', __( 'Dropdown', 'omnifywp-ecommerce' ) ),
						opt( 'pills',    __( 'Pills',    'omnifywp-ecommerce' ) )
					], props ),

					buttonGroupField( __( 'Alignment', 'omnifywp-ecommerce' ), 'align', [
						opt( '',       __( 'Default', 'omnifywp-ecommerce' ) ),
						opt( 'left',   __( 'Left',    'omnifywp-ecommerce' ) ),
						opt( 'center', __( 'Center',  'omnifywp-ecommerce' ) ),
						opt( 'right',  __( 'Right',   'omnifywp-ecommerce' ) )
					], props ),

					textField( __( 'Allowed currencies', 'omnifywp-ecommerce' ), 'currencies', props, __( 'Comma-separated e.g. USD, EUR, GBP – empty = all 22.', 'omnifywp-ecommerce' ) )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   10. ORDER BUMP (CHECKOUT UPSELL)
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/order-bump', Object.assign( {}, common, {
		title:       __( 'Omnify Order Bump', 'omnifywp-ecommerce' ),
		description: __( 'High-converting 1-click checkout upsell checkbox with instant AJAX cart addition.', 'omnifywp-ecommerce' ),
		icon:        'star-filled',
		keywords:    [ 'order bump', 'upsell', 'checkout', 'addon', 'cross-sell' ],

		example: { attributes: { title: 'Yes! Add This Exclusive Offer to My Order', badgeText: 'ONE-TIME OFFER', discountVal: 20 } },

		attributes: withStyle( {
			productId:      { type: 'integer', default: 0 },
			title:          { type: 'string',  default: 'Yes! Add This Exclusive Offer to My Order' },
			badgeText:      { type: 'string',  default: 'ONE-TIME OFFER' },
			description:    { type: 'string',  default: 'Add priority support and extended warranty with one single click.' },
			discountType:   { type: 'string',  default: 'percentage' },
			discountVal:    { type: 'number',  default: 20 },
			highlightColor: { type: 'string',  default: '#0f766e' }
		} ),

		edit: makeEdit( 'omnify/order-bump', function ( props ) {
			return inspector(
				panel( __( 'Order Bump Offer', 'omnifywp-ecommerce' ), [
					productSelector( props ),
					textField( __( 'Headline title', 'omnifywp-ecommerce' ), 'title', props ),
					textField( __( 'Badge pill text', 'omnifywp-ecommerce' ), 'badgeText', props ),
					textareaField( __( 'Pitch description', 'omnifywp-ecommerce' ), 'description', props ),
					buttonGroupField( __( 'Discount type', 'omnifywp-ecommerce' ), 'discountType', [
						opt( 'percentage', __( 'Percentage (%)', 'omnifywp-ecommerce' ) ),
						opt( 'fixed',      __( 'Fixed Amount', 'omnifywp-ecommerce' ) ),
						opt( 'none',       __( 'None (Regular Price)', 'omnifywp-ecommerce' ) )
					], props ),
					rangeField( __( 'Discount value', 'omnifywp-ecommerce' ), 'discountVal', 0, 100, 1, props ),
					colorPicker( __( 'Highlight border color', 'omnifywp-ecommerce' ), 'highlightColor', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   11. STICKY ADD TO CART BAR
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/sticky-add-to-cart', Object.assign( {}, common, {
		title:       __( 'Omnify Sticky Add to Cart', 'omnifywp-ecommerce' ),
		description: __( 'High-conversion floating bottom/top bar with product thumbnail, price, and instant checkout.', 'omnifywp-ecommerce' ),
		icon:        'arrow-down-alt2',
		keywords:    [ 'sticky', 'add to cart', 'floating', 'mobile', 'bar' ],

		example: { attributes: { position: 'bottom', deviceVisibility: 'all', buttonLabel: 'Add to Cart' } },

		attributes: withStyle( {
			productId:        { type: 'integer', default: 0 },
			position:         { type: 'string',  default: 'bottom' },
			deviceVisibility: { type: 'string',  default: 'all' },
			showThumbnail:    { type: 'boolean', default: true },
			showPrice:        { type: 'boolean', default: true },
			showQuantity:     { type: 'boolean', default: true },
			buttonAction:     { type: 'string',  default: 'cart' },
			buttonLabel:      { type: 'string',  default: 'Add to Cart' }
		} ),

		edit: makeEdit( 'omnify/sticky-add-to-cart', function ( props ) {
			return inspector(
				panel( __( 'Sticky Bar Settings', 'omnifywp-ecommerce' ), [
					productSelector( props ),
					buttonGroupField( __( 'Position', 'omnifywp-ecommerce' ), 'position', [
						opt( 'bottom', __( 'Bottom Fixed', 'omnifywp-ecommerce' ) ),
						opt( 'top',    __( 'Top Fixed',    'omnifywp-ecommerce' ) )
					], props ),
					buttonGroupField( __( 'Device visibility', 'omnifywp-ecommerce' ), 'deviceVisibility', [
						opt( 'all',     __( 'All Devices',    'omnifywp-ecommerce' ) ),
						opt( 'mobile',  __( 'Mobile Only',    'omnifywp-ecommerce' ) ),
						opt( 'desktop', __( 'Desktop Only',   'omnifywp-ecommerce' ) )
					], props ),
					toggleField( __( 'Show thumbnail', 'omnifywp-ecommerce' ), 'showThumbnail', props ),
					toggleField( __( 'Show price',     'omnifywp-ecommerce' ), 'showPrice',     props ),
					toggleField( __( 'Show quantity',  'omnifywp-ecommerce' ), 'showQuantity',  props ),
					buttonGroupField( __( 'Button action', 'omnifywp-ecommerce' ), 'buttonAction', [
						opt( 'cart',     __( 'Add to Cart', 'omnifywp-ecommerce' ) ),
						opt( 'checkout', __( 'Direct Buy Now (Checkout)', 'omnifywp-ecommerce' ) )
					], props ),
					textField( __( 'Button label', 'omnifywp-ecommerce' ), 'buttonLabel', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   12. FREE SHIPPING GOAL
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/free-shipping-goal', Object.assign( {}, common, {
		title:       __( 'Omnify Free Shipping Goal', 'omnifywp-ecommerce' ),
		description: __( 'Gamified dynamic progress bar displaying remaining amount for free shipping with live celebrations.', 'omnifywp-ecommerce' ),
		icon:        'car',
		keywords:    [ 'shipping', 'progress', 'free shipping', 'meter', 'goal' ],

		example: { attributes: { threshold: 50, showIcon: true, showConfetti: true, barColor: '#0f766e' } },

		attributes: withStyle( {
			threshold:    { type: 'number',  default: 50 },
			initialMsg:   { type: 'string',  default: 'Add %s more to unlock FREE Shipping!' },
			successMsg:   { type: 'string',  default: '🎉 Congratulations! You have unlocked FREE Shipping!' },
			showIcon:     { type: 'boolean', default: true },
			showConfetti: { type: 'boolean', default: true },
			barColor:     { type: 'string',  default: '#0f766e' }
		} ),

		edit: makeEdit( 'omnify/free-shipping-goal', function ( props ) {
			return inspector(
				panel( __( 'Shipping Goal Settings', 'omnifywp-ecommerce' ), [
					rangeField( __( 'Free shipping threshold ($)', 'omnifywp-ecommerce' ), 'threshold', 10, 500, 5, props ),
					textField( __( 'Initial message template (%s = amount)', 'omnifywp-ecommerce' ), 'initialMsg', props ),
					textField( __( 'Success unlock message', 'omnifywp-ecommerce' ), 'successMsg', props ),
					toggleField( __( 'Show delivery icon', 'omnifywp-ecommerce' ), 'showIcon', props ),
					toggleField( __( 'Celebrate with confetti unlock', 'omnifywp-ecommerce' ), 'showConfetti', props ),
					colorPicker( __( 'Progress bar fill color', 'omnifywp-ecommerce' ), 'barColor', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   13. TRUST BADGE BAR
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/trust-badge-bar', Object.assign( {}, common, {
		title:       __( 'Omnify Trust Badges & Guarantee Strip', 'omnifywp-ecommerce' ),
		description: __( 'Showcase SSL security, payment providers, fast shipping, and money-back guarantees.', 'omnifywp-ecommerce' ),
		icon:        'shield-alt',
		keywords:    [ 'trust', 'badges', 'guarantee', 'ssl', 'security', 'payments' ],

		example: { attributes: { layout: 'horizontal', showBorder: true, showGuarantees: true, showPayments: true } },

		attributes: withStyle( {
			layout:         { type: 'string',  default: 'horizontal' },
			badgeStyle:     { type: 'string',  default: 'emerald' },
			showBorder:     { type: 'boolean', default: true },
			showGuarantees: { type: 'boolean', default: true },
			showPayments:   { type: 'boolean', default: true },
			showSupport:    { type: 'boolean', default: true },
			showReturns:    { type: 'boolean', default: true }
		} ),

		edit: makeEdit( 'omnify/trust-badge-bar', function ( props ) {
			return inspector(
				panel( __( 'Trust Badges Layout', 'omnifywp-ecommerce' ), [
					buttonGroupField( __( 'Layout structure', 'omnifywp-ecommerce' ), 'layout', [
						opt( 'horizontal', __( 'Horizontal Strip', 'omnifywp-ecommerce' ) ),
						opt( 'grid-2x2',   __( '2x2 Grid',         'omnifywp-ecommerce' ) )
					], props ),
					toggleField( __( 'Show border container', 'omnifywp-ecommerce' ), 'showBorder', props ),
					toggleField( __( 'Show security & guarantee badges', 'omnifywp-ecommerce' ), 'showGuarantees', props ),
					toggleField( __( 'Show payment card icons', 'omnifywp-ecommerce' ), 'showPayments', props ),
					toggleField( __( 'Show 24/7 priority support', 'omnifywp-ecommerce' ), 'showSupport', props ),
					toggleField( __( 'Show hassle-free returns', 'omnifywp-ecommerce' ), 'showReturns', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   14. PRODUCT IMAGE (ATOMIC)
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/product-image', Object.assign( {}, common, {
		title:       __( 'Omnify Product Image', 'omnifywp-ecommerce' ),
		description: __( 'Product image thumbnail with aspect ratio and hover zoom.', 'omnifywp-ecommerce' ),
		icon:        'format-image',
		keywords:    [ 'product', 'image', 'photo' ],
		attributes: withStyle( {
			productId:    { type: 'integer', default: 0 },
			aspectRatio:  { type: 'string',  default: '1/1' },
			zoomOnHover:  { type: 'boolean', default: true },
			borderRadius: { type: 'integer', default: 10 }
		} ),
		edit: makeEdit( 'omnify/product-image', function ( props ) {
			return inspector(
				panel( __( 'Image Settings', 'omnifywp-ecommerce' ), [
					productSelector( props ),
					buttonGroupField( __( 'Aspect ratio', 'omnifywp-ecommerce' ), 'aspectRatio', [
						opt( '1/1',  __( 'Square (1:1)', 'omnifywp-ecommerce' ) ),
						opt( '4/3',  __( 'Standard (4:3)', 'omnifywp-ecommerce' ) ),
						opt( '16/9', __( 'Wide (16:9)', 'omnifywp-ecommerce' ) )
					], props ),
					rangeField( __( 'Border radius (px)', 'omnifywp-ecommerce' ), 'borderRadius', 0, 40, 2, props ),
					toggleField( __( 'Zoom on hover', 'omnifywp-ecommerce' ), 'zoomOnHover', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   15. PRODUCT TITLE (ATOMIC)
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/product-title', Object.assign( {}, common, {
		title:       __( 'Omnify Product Title', 'omnifywp-ecommerce' ),
		description: __( 'Dynamic product title with heading tag and link.', 'omnifywp-ecommerce' ),
		icon:        'heading',
		keywords:    [ 'product', 'title', 'heading' ],
		attributes: withStyle( {
			productId:  { type: 'integer', default: 0 },
			headingTag: { type: 'string',  default: 'h3' },
			linkToPost: { type: 'boolean', default: true },
			fontSize:   { type: 'integer', default: 18 }
		} ),
		edit: makeEdit( 'omnify/product-title', function ( props ) {
			return inspector(
				panel( __( 'Title Settings', 'omnifywp-ecommerce' ), [
					productSelector( props ),
					buttonGroupField( __( 'Heading tag', 'omnifywp-ecommerce' ), 'headingTag', [
						opt( 'h2', 'H2' ), opt( 'h3', 'H3' ), opt( 'h4', 'H4' ), opt( 'p', 'P' )
					], props ),
					rangeField( __( 'Font size (px)', 'omnifywp-ecommerce' ), 'fontSize', 12, 48, 1, props ),
					toggleField( __( 'Link to product page', 'omnifywp-ecommerce' ), 'linkToPost', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   16. PRODUCT PRICE (ATOMIC)
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/product-price', Object.assign( {}, common, {
		title:       __( 'Omnify Product Price', 'omnifywp-ecommerce' ),
		description: __( 'Dynamic product price with sale comparison.', 'omnifywp-ecommerce' ),
		icon:        'money-alt',
		keywords:    [ 'product', 'price', 'sale' ],
		attributes: withStyle( {
			productId:   { type: 'integer', default: 0 },
			showRegular: { type: 'boolean', default: true },
			showSavings: { type: 'boolean', default: true },
			fontSize:    { type: 'integer', default: 18 }
		} ),
		edit: makeEdit( 'omnify/product-price', function ( props ) {
			return inspector(
				panel( __( 'Price Settings', 'omnifywp-ecommerce' ), [
					productSelector( props ),
					rangeField( __( 'Font size (px)', 'omnifywp-ecommerce' ), 'fontSize', 14, 42, 1, props ),
					toggleField( __( 'Show strike-through regular price', 'omnifywp-ecommerce' ), 'showRegular', props ),
					toggleField( __( 'Show savings discount pill', 'omnifywp-ecommerce' ), 'showSavings', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   17. PRODUCT RATING (ATOMIC)
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/product-rating', Object.assign( {}, common, {
		title:       __( 'Omnify Product Rating', 'omnifywp-ecommerce' ),
		description: __( 'Star reviews summary rating and count.', 'omnifywp-ecommerce' ),
		icon:        'star-filled',
		keywords:    [ 'product', 'rating', 'stars' ],
		attributes: withStyle( {
			productId: { type: 'integer', default: 0 },
			starColor: { type: 'string',  default: '#f59e0b' },
			showCount: { type: 'boolean', default: true }
		} ),
		edit: makeEdit( 'omnify/product-rating', function ( props ) {
			return inspector(
				panel( __( 'Rating Settings', 'omnifywp-ecommerce' ), [
					productSelector( props ),
					colorPicker( __( 'Star icon color', 'omnifywp-ecommerce' ), 'starColor', props ),
					toggleField( __( 'Show review count text', 'omnifywp-ecommerce' ), 'showCount', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   18. PRODUCT BADGE (ATOMIC)
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/product-badge', Object.assign( {}, common, {
		title:       __( 'Omnify Product Badge', 'omnifywp-ecommerce' ),
		description: __( 'Highlight pill for Sale, New, or Custom label.', 'omnifywp-ecommerce' ),
		icon:        'tag',
		keywords:    [ 'product', 'badge', 'sale' ],
		attributes: withStyle( {
			productId:  { type: 'integer', default: 0 },
			customText: { type: 'string',  default: '' },
			badgeColor: { type: 'string',  default: '#0f766e' }
		} ),
		edit: makeEdit( 'omnify/product-badge', function ( props ) {
			return inspector(
				panel( __( 'Badge Settings', 'omnifywp-ecommerce' ), [
					productSelector( props ),
					textField( __( 'Custom badge text (empty = auto Sale/New)', 'omnifywp-ecommerce' ), 'customText', props ),
					colorPicker( __( 'Badge background color', 'omnifywp-ecommerce' ), 'badgeColor', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   19. PRODUCT BUTTON (ATOMIC)
	══════════════════════════════════════════════════════════════════════ */
	register( 'omnify/product-button', Object.assign( {}, common, {
		title:       __( 'Omnify Product Button', 'omnifywp-ecommerce' ),
		description: __( 'Interactive Add to Cart or Buy Now button.', 'omnifywp-ecommerce' ),
		icon:        'cart',
		keywords:    [ 'product', 'button', 'cart' ],
		attributes: withStyle( {
			productId:  { type: 'integer', default: 0 },
			action:     { type: 'string',  default: 'cart' },
			buttonText: { type: 'string',  default: 'Add to Cart' },
			showQty:    { type: 'boolean', default: false }
		} ),
		edit: makeEdit( 'omnify/product-button', function ( props ) {
			return inspector(
				panel( __( 'Button Settings', 'omnifywp-ecommerce' ), [
					productSelector( props ),
					buttonGroupField( __( 'Action', 'omnifywp-ecommerce' ), 'action', [
						opt( 'cart',     __( 'Add to Cart', 'omnifywp-ecommerce' ) ),
						opt( 'checkout', __( 'Buy Now (Direct Checkout)', 'omnifywp-ecommerce' ) )
					], props ),
					textField( __( 'Button label text', 'omnifywp-ecommerce' ), 'buttonText', props ),
					toggleField( __( 'Show quantity stepper input', 'omnifywp-ecommerce' ), 'showQty', props )
				], true ),
				stylePanel( props )
			);
		} )
	} ) );

	/* ══════════════════════════════════════════════════════════════════════
	   20. BLOCK STYLE VARIATIONS (registerBlockStyle)
	══════════════════════════════════════════════════════════════════════ */
	if ( wp.blocks && wp.blocks.registerBlockStyle ) {
		// Style variations for Product Card & Grid
		wp.blocks.registerBlockStyle( 'omnify/product-card', [
			{ name: 'default',        label: __( 'Default Emerald', 'omnifywp-ecommerce' ), isDefault: true },
			{ name: 'minimal-luxury', label: __( 'Minimalist Luxury', 'omnifywp-ecommerce' ) },
			{ name: 'neo-brutalist',  label: __( 'Neo-Brutalist', 'omnifywp-ecommerce' ) },
			{ name: 'dark-elegance',  label: __( 'Dark Elegance', 'omnifywp-ecommerce' ) }
		] );

		wp.blocks.registerBlockStyle( 'omnify/product-grid', [
			{ name: 'default',        label: __( 'Clean Grid', 'omnifywp-ecommerce' ), isDefault: true },
			{ name: 'masonry-fluid',  label: __( 'Fluid Masonry', 'omnifywp-ecommerce' ) },
			{ name: 'compact-dense',  label: __( 'Dense Catalog', 'omnifywp-ecommerce' ) }
		] );

		wp.blocks.registerBlockStyle( 'omnify/checkout', [
			{ name: 'default',        label: __( 'Standard Emerald', 'omnifywp-ecommerce' ), isDefault: true },
			{ name: 'split-hero',     label: __( 'Split Hero High-Focus', 'omnifywp-ecommerce' ) },
			{ name: 'compact-clean',  label: __( 'Compact Seamless', 'omnifywp-ecommerce' ) }
		] );

		wp.blocks.registerBlockStyle( 'omnify/cart', [
			{ name: 'default',        label: __( 'Standard Flow', 'omnifywp-ecommerce' ), isDefault: true },
			{ name: 'elevated-card',  label: __( 'Floating Card', 'omnifywp-ecommerce' ) }
		] );
	}

	/* ══════════════════════════════════════════════════════════════════════
	   21. CORE QUERY LOOP VARIATION ('Omnify Products')
	══════════════════════════════════════════════════════════════════════ */
	if ( wp.blocks && wp.blocks.registerBlockVariation ) {
		wp.blocks.registerBlockVariation( 'core/query', {
			name:        'omnify-products-query',
			title:       __( 'Omnify Products Query', 'omnifywp-ecommerce' ),
			description: __( 'Display and filter published Omnify products with native WordPress Core pagination and templates.', 'omnifywp-ecommerce' ),
			category:    'omnify',
			icon:        'cart',
			attributes: {
				namespace: 'omnify/products',
				query: {
					postType:  'omnify_product',
					perPage:   8,
					pages:     0,
					offset:    0,
					post__in:  [],
					order:     'desc',
					orderBy:   'date',
					author:    '',
					search:    '',
					exclude:   [],
					sticky:    '',
					inherit:   false
				}
			},
			innerBlocks: [
				[ 'core/post-template', {}, [
					[ 'omnify/product-image', {} ],
					[ 'omnify/product-title', { headingTag: 'h3', fontSize: 18 } ],
					[ 'omnify/product-price', {} ],
					[ 'omnify/product-button', { action: 'cart' } ]
				] ],
				[ 'core/query-pagination', {} ]
			],
			isActive: function ( blockAttributes ) {
				return blockAttributes.namespace === 'omnify/products';
			}
		} );
	}

	/* ══════════════════════════════════════════════════════════════════════
	   22. IN-EDITOR CLOUD TEMPLATE LIBRARY MODAL (omnify/template-cloud)
	══════════════════════════════════════════════════════════════════════ */
	var templatesList = Array.isArray( data.templateLibrary ) ? data.templateLibrary : [];

	register( 'omnify/template-cloud', Object.assign( {}, common, {
		title:       __( 'Omnify Template Cloud', 'omnifywp-ecommerce' ),
		description: __( 'Browse and insert pre-built, high-converting eCommerce starter layouts with 1-click.', 'omnifywp-ecommerce' ),
		icon:        'layout',
		keywords:    [ 'templates', 'starter', 'layout', 'cloud', 'funnel', 'store' ],

		attributes: {
			selectedId: { type: 'string', default: '' }
		},

		edit: function ( props ) {
			var blockProps = useBlockProps ? useBlockProps( { className: 'omnify-template-cloud-block' } ) : {};
			var isOpenState = useState( false );
			var isOpen      = isOpenState[0];
			var setIsOpen   = isOpenState[1];

			var filterCatState = useState( 'all' );
			var filterCat      = filterCatState[0];
			var setFilterCat   = filterCatState[1];

			var insertTemplate = function ( templateContent ) {
				if ( wp.data && wp.data.dispatch && wp.blocks.parse ) {
					var parsedBlocks = wp.blocks.parse( templateContent );
					if ( parsedBlocks && parsedBlocks.length ) {
						var clientId = props.clientId;
						var blockEditorDispatch = wp.data.dispatch( 'core/block-editor' );
						if ( blockEditorDispatch && blockEditorDispatch.replaceBlocks ) {
							blockEditorDispatch.replaceBlocks( clientId, parsedBlocks );
							setIsOpen( false );
							return;
						}
					}
				}
				setIsOpen( false );
			};

			var filtered = templatesList.filter( function ( t ) {
				if ( filterCat === 'all' ) return true;
				return t.category === filterCat;
			} );

			var modalContent = null;
			if ( isOpen && Modal ) {
				modalContent = el( Modal, {
					title:             __( 'Omnify eCommerce Template Library', 'omnifywp-ecommerce' ),
					onRequestClose:    function () { setIsOpen( false ); },
					className:         'omnify-template-library-modal',
					shouldCloseOnClickOutside: true
				},
					el( 'div', { style: { padding: '10px 0 20px', borderBottom: '1px solid #e2e8f0', display: 'flex', gap: '8px' } },
						el( Button, { isPrimary: filterCat === 'all',      isSecondary: filterCat !== 'all',      onClick: function () { setFilterCat( 'all' ); } }, __( 'All Templates', 'omnifywp-ecommerce' ) ),
						el( Button, { isPrimary: filterCat === 'funnels',  isSecondary: filterCat !== 'funnels',  onClick: function () { setFilterCat( 'funnels' ); } }, __( 'High-Converting Funnels', 'omnifywp-ecommerce' ) ),
						el( Button, { isPrimary: filterCat === 'stores',   isSecondary: filterCat !== 'stores',   onClick: function () { setFilterCat( 'stores' ); } }, __( 'Storefronts', 'omnifywp-ecommerce' ) ),
						el( Button, { isPrimary: filterCat === 'checkout', isSecondary: filterCat !== 'checkout', onClick: function () { setFilterCat( 'checkout' ); } }, __( 'Checkout Pages', 'omnifywp-ecommerce' ) ),
						el( Button, { isPrimary: filterCat === 'account',  isSecondary: filterCat !== 'account',  onClick: function () { setFilterCat( 'account' ); } }, __( 'Customer Account', 'omnifywp-ecommerce' ) )
					),
					el( 'div', { style: { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '20px', padding: '24px 0' } },
						filtered.map( function ( tmpl ) {
							return el( 'div', { key: tmpl.id, style: { border: '1px solid #e2e8f0', borderRadius: '12px', padding: '18px', background: '#ffffff', display: 'flex', flexDirection: 'column', justifyContent: 'space-between' } },
								el( 'div', null,
									el( 'div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' } },
										el( 'span', { style: { background: '#0f766e', color: '#ffffff', fontSize: '10.5px', fontWeight: '800', padding: '2px 8px', borderRadius: '999px' } }, tmpl.badge ),
										el( 'span', { style: { fontSize: '11px', color: '#94a3b8', textTransform: 'uppercase' } }, tmpl.category )
									),
									el( 'h3', { style: { margin: '0 0 8px', fontSize: '16px', fontWeight: '700', color: '#0f172a' } }, tmpl.title ),
									el( 'p', { style: { margin: '0 0 16px', fontSize: '13px', color: '#64748b', lineHeight: '1.5' } }, tmpl.description )
								),
								el( Button, {
									isPrimary: true,
									style: { width: '100%', justifyContent: 'center', background: '#0f766e', fontWeight: '700' },
									onClick: function () { insertTemplate( tmpl.content ); }
								}, __( 'Import & Insert Layout', 'omnifywp-ecommerce' ) )
							);
						} )
					)
				);
			}

			return el( 'div', blockProps,
				el( Placeholder, {
					icon:         'layout',
					label:        __( 'Omnify eCommerce Starter Kits & Template Cloud', 'omnifywp-ecommerce' ),
					instructions: __( 'Choose from 20+ pre-designed niche eCommerce layouts, sales funnels, and checkout flows.', 'omnifywp-ecommerce' )
				},
					el( Button, {
						isPrimary: true,
						style:     { background: '#0f766e', fontWeight: '700', padding: '10px 20px', height: 'auto' },
						onClick:   function () { setIsOpen( true ); }
					}, __( 'Open Template Cloud Library', 'omnifywp-ecommerce' ) )
				),
				modalContent
			);
		},

		save: function () {
			return null;
		}
	} ) );

} )();
