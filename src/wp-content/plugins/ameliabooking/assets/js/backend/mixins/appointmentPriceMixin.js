export default {

  data () {
    return {}
  },

  methods: {
    getAppointmentPrice (serviceId, bookings) {
      let totalBookings = 0
      let $this = this

      let service = this.getServiceById(serviceId)

      bookings.forEach(function (booking) {
        // for old bookings use price from booking
        booking.payments.forEach(function () {
          if (['approved', 'pending'].includes(booking.status)) {
            totalBookings += $this.getBookingPrice(booking, false, booking.price, booking.aggregatedPrice)
          }
        })

        // for new bookings use price from service
        if (booking.payments.length === 0) {
          totalBookings += $this.getBookingPrice(booking, true, service.price, service.aggregatedPrice)
        }
      })

      return this.getFormattedPrice(totalBookings >= 0 ? totalBookings : 0)
    },

    getBookingPrice (booking, isNewBooking, servicePrice, aggregatedPrice) {
      let extrasPriceTotal = 0

      booking.extras.forEach(function (extra) {
        if (typeof extra.selected === 'undefined' || extra.selected === true) {
          extrasPriceTotal += extra.price * extra.quantity * (aggregatedPrice ? booking.persons : 1)
        }
      })

      let servicePriceTotal = (isNewBooking ? servicePrice : booking.price) * (aggregatedPrice ? booking.persons : 1)
      let subTotal = servicePriceTotal + extrasPriceTotal
      let discountTotal = (subTotal / 100 * (booking.coupon ? booking.coupon.discount : 0)) + (booking.coupon ? booking.coupon.deduction : 0)

      return subTotal - discountTotal
    }
  },

  watch: {}
}
