export default {

  data: () => ({
    colors: [
      '1788FB',
      '4BBEC6',
      'FBC22D',
      'FA3C52',
      'D696B8',
      '689BCA',
      '26CC2B',
      'FD7E35',
      'E38587',
      '774DFB',
      '31CDF3',
      '6AB76C',
      'FD5FA1',
      'A697C5'
    ],
    usedColors: []
  }),

  methods: {
    inlineSVG () {
      let inlineSVG = require('inline-svg')
      inlineSVG.init({
        svgSelector: 'img.svg',
        initClass: 'js-inlinesvg'
      })
    },

    imageFromText (name) {
      let initials = this.getNameInitials(name)
      let colorIndex = Math.floor(Math.random() * this.colors.length)
      let colorHex = this.colors[colorIndex]

      this.usedColors.push(this.colors[colorIndex])
      this.colors.splice(colorIndex, 1)
      if (this.colors.length === 0) {
        this.colors = this.usedColors
        this.usedColors = []
      }
      return location.protocol + '//via.placeholder.com/120/' + colorHex + '/fff?text=' + initials
    },

    pictureLoad: function (entity, isPerson) {
      if (entity !== null) {
        let name = isPerson === true ? entity.firstName + ' ' + entity.lastName : entity.name
        if (typeof name !== 'undefined') {
          entity.pictureThumbPath = entity.pictureThumbPath || this.imageFromText(name)
          return entity.pictureThumbPath
        }
      }
    },

    imageLoadError: function (entity, isPerson) {
      let name = isPerson === true ? entity.firstName + ' ' + entity.lastName : entity.name
      if (typeof name !== 'undefined') {
        entity.pictureThumbPath = this.imageFromText(name)
      }
    },

    getNameInitials (name) {
      return name.split(' ').map((s) => s.charAt(0)).join('').toUpperCase().substring(0, 3).replace(/[^\w\s]/g, '')
    }

  }

}
