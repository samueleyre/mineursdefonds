(function (wp) {

  var el = wp.element.createElement
  var components = wp.components
  var blockControls = wp.editor.BlockControls
  var inspectorControls = wp.editor.InspectorControls

  // Registering the Block for events shortcode
  wp.blocks.registerBlockType('amelia/events-gutenberg-block', {
    title: wpAmeliaLabels.events_gutenberg_block.title,
    description: wpAmeliaLabels.events_gutenberg_block.description,
    icon: el('svg', {width: '25', height: '27', viewBox: '0 0 25 27'},
      el('path', {
        style: {fill: '#1A84EE'},
        d: 'M11.4937358,10.7089033 L11.4937358,2.00347742 C11.4937358,0.463573647 9.83438046,-0.498899048 8.50694847,0.271096622 L0.995613218,4.6279253 C0.379532759,4.98520749 1.74329502e-05,5.64565414 1.74329502e-05,6.36030609 L1.74329502e-05,15.0243117 C1.74329502e-05,16.5606252 1.65222529,17.5235357 2.97965728,16.7608958 L10.4910797,12.4454874 C11.1110826,12.0891685 11.4937358,11.4265326 11.4937358,10.7089033'
      }),
      el('path', {
        style: {fill: '#005AEE'},
        d: 'M13.4849535,2.00346866 L13.4849535,10.7088945 C13.4849535,11.4265238 13.8676068,12.0891597 14.4876097,12.4453911 L21.9991193,16.7608871 C23.3265512,17.5235269 24.9787591,16.5606164 24.9787591,15.024303 L24.9787591,6.36029734 C24.9787591,5.64564538 24.5992438,4.98519874 23.9831633,4.62791654 L16.4717409,0.271000296 C15.1443089,-0.498907805 13.4849535,0.46356489 13.4849535,2.00346866'
      }),
      el('path', {
        style: {fill: '#3BA6FF'},
        transform: 'translate(2.876437, 13.843371)',
        d: 'M8.62445527,0.32630898 L1.0701478,4.66641195 C-0.263647222,5.43264214 -0.267569636,7.36354223 1.06300029,8.13537686 L8.61730776,12.5170752 C9.23338822,12.8744449 9.99241887,12.8744449 10.6084993,12.5170752 L18.162894,8.13537686 C19.4934639,7.36354223 19.4895415,5.43264214 18.1557465,4.66641195 L10.601439,0.32630898 C9.98893228,-0.0256314947 9.23687481,-0.0256314947 8.62445527,0.32630898'
      })
    ),
    category: 'amelia-blocks',
    keywords: [
      'amelia',
      'events'
    ],
    supports: {
      customClassName: false,
      html: false
    },
    attributes: {
      short_code: {
        type: 'string',
        default: '[ameliaevents]'
      }
    },
    edit: function (props) {
      var inspectorElements = []
      var attributes = props.attributes

      function getShortCode(props, attributes) {
        var shortCode = '[ameliaevents]'

        props.setAttributes({short_code: shortCode})

        return shortCode;
      }

      return [
        el(blockControls, {key: 'controls'}),
        el(inspectorControls, {key: 'inspector'},
          el(components.PanelBody, {initialOpen: true},
            inspectorElements
          )
        ),
        el('div', {},
          getShortCode(props, props.attributes)
        )
      ]

    },

    save: function (props) {
      return (
        el('div', {},
          props.attributes.short_code
        )
      )
    }
  })

})(
  window.wp
)