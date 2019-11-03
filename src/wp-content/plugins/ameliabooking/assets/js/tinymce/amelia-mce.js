/* eslint-disable */
(function () {
  tinymce.create('tinymce.plugins.ameliaBookingPlugin', {

    init: function (editor) {
      let win = null

      let entities = null
      let categories = null
      let services = null
      let employees = null
      let locations = null
      let servicesList = null
      let catalogView = null

      let setAndOpenEditor = function (view) {
        editor.windowManager.close()

        let viewBody = [{
          type: 'listbox',
          name: 'am_view_type',
          label: wpAmeliaLabels.select_view,
          values: !wpAmeliaLiteVersion ? [
            {value: 'search', text: 'Search'},
            {value: 'catalog', text: 'Catalog'},
            {value: 'booking', text: 'Booking'},
            {value: 'events', text: 'Events'}
          ] : [
            {value: 'booking', text: 'Booking'},
            {value: 'events', text: 'Events'}
          ],
          value: view,
          onSelect: function () {
            setAndOpenEditor(this.value())
          }
        },
        ]

        let filterItems = null

        // set view
        switch (view) {
          case ('booking'):

            // Filter
            filterItems = [
              {
                type: 'listbox',
                name: 'am_booking_category',
                label: wpAmeliaLabels.select_category,
                classes: 'am-booking-categories',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_categories
                }].concat(categories),
              },
              {
                type: 'listbox',
                name: 'am_booking_service',
                label: wpAmeliaLabels.select_service,
                classes: 'am-booking-services',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_services
                }].concat(services),
              },
              {
                type: 'listbox',
                name: 'am_booking_employee',
                label: wpAmeliaLabels.select_employee,
                classes: 'am-booking-employees',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_employees
                }].concat(employees),
              },
            ]

            if (locations.length) {
              filterItems.push({
                type: 'listbox',
                name: 'am_booking_location',
                label: wpAmeliaLabels.select_location,
                classes: 'am-booking-locations',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_locations
                }].concat(locations),
              })
            }

            if (!wpAmeliaLiteVersion) {
              viewBody.push({
                type: 'checkbox',
                name: 'am_booking_filter',
                label: wpAmeliaLabels.filter,
                classes: 'am-booking-filter',
                onChange: function () {
                  let filterForm = win.find('#am_booking_panel')
                  filterForm.visible(!filterForm.visible())
                }
              })

              viewBody.push({
                type: 'form',
                name: 'am_booking_panel',
                classes: 'am-booking-panel',
                items: filterItems,
                visible: false,
              })
            }

            break
          case ('search'):
            // Filter
            viewBody.push({
              type: 'checkbox',
              name: 'am_search_date',
              label: wpAmeliaLabels.search_date,
              classes: 'am-search-date',
            })

            break
          case ('catalog'):
            viewBody.push({
              type: 'listbox',
              name: 'am_catalog_view_type',
              label: wpAmeliaLabels.select_catalog_view,
              values: [
                {value: 'catalog', text: wpAmeliaLabels.show_catalog},
                {value: 'category', text: wpAmeliaLabels.show_category},
                {value: 'service', text: wpAmeliaLabels.show_service}
              ],
              classes: 'am-catalog-view-type',
              onSelect: function () {
                catalogView = this.value()

                let categoryElement = win.find('#am_category')
                let serviceElement = win.find('#am_service')

                if (catalogView === 'category') {
                  categoryElement.visible(true)
                  serviceElement.visible(false)
                } else if (catalogView === 'service') {
                  categoryElement.visible(false)
                  serviceElement.visible(true)
                } else {
                  categoryElement.visible(false)
                  serviceElement.visible(false)
                }
              },
            })

            // Category
            viewBody.push({
                type: 'listbox',
                name: 'am_category',
                values: categories,
                classes: 'am-categories',
              }
            )

            // Service
            viewBody.push({
              type: 'listbox',
              name: 'am_service',
              values: services,
              classes: 'am-services',
            })

            // Filter
            filterItems = [
              {
                type: 'listbox',
                name: 'am_booking_employee',
                label: wpAmeliaLabels.select_employee,
                classes: 'am-booking-employees',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_employees
                }].concat(employees),
              },
            ]

            if (locations.length) {
              filterItems.push({
                type: 'listbox',
                name: 'am_booking_location',
                label: wpAmeliaLabels.select_location,
                classes: 'am-booking-locations',
                values: [{
                  value: 0,
                  text: wpAmeliaLabels.show_all_locations
                }].concat(locations),
              })
            }

            viewBody.push({
              type: 'checkbox',
              name: 'am_booking_filter',
              label: wpAmeliaLabels.filter,
              classes: 'am-booking-filter',
              style: '',
              onChange: function () {
                let filterForm = win.find('#am_booking_panel')
                filterForm.visible(!filterForm.visible())
              }
            })

            viewBody.push({
              type: 'form',
              name: 'am_booking_panel',
              classes: 'am-booking-panel',
              items: filterItems,
              visible: false,
            })

            break
        }

        // open editor
        win = editor.windowManager.open({
          title: 'Amelia Booking',
          body: [],
          width: 500,
          height: 350,
          body: viewBody,
          onSubmit: function (e) {
            let shortCodeString = ''

            switch (view) {
              case ('booking'):
                if (e.data.am_booking_service) {
                  shortCodeString += ' service=' + e.data.am_booking_service
                } else if (e.data.am_booking_category) {
                  shortCodeString += ' category=' + e.data.am_booking_category
                }

                if (e.data.am_booking_employee) {
                  shortCodeString += ' employee=' + e.data.am_booking_employee
                }

                if (e.data.am_booking_location) {
                  shortCodeString += ' location=' + e.data.am_booking_location
                }

                editor.insertContent('[ameliabooking' + shortCodeString + ']')

                break

              case ('search'):
                if (e.data.am_search_date) {
                  shortCodeString += ' today=1'
                }

                editor.insertContent('[ameliasearch' + shortCodeString + ']')

                break

              case ('catalog'):
                if (e.data.am_booking_employee) {
                  shortCodeString += ' employee=' + e.data.am_booking_employee
                }

                if (e.data.am_booking_location) {
                  shortCodeString += ' location=' + e.data.am_booking_location
                }

                if (catalogView === 'category') {
                  editor.insertContent('[ameliacatalog category=' + e.data.am_category + shortCodeString + ']')
                } else if (catalogView === 'service') {
                  editor.insertContent('[ameliacatalog service=' + e.data.am_service + shortCodeString + ']')
                } else {
                  editor.insertContent('[ameliacatalog' + shortCodeString + ']')
                }

                break

              case ('events'):
                if (e.data.am_search_date) {
                  shortCodeString += ' today=1'
                }

                editor.insertContent('[ameliaevents' + shortCodeString + ']')

                break
            }
          },
          onOpen: function () {
            categoryElement = win.find('#am_category')
            serviceElement = win.find('#am_service')

            categoryElement.visible(false)
            serviceElement.visible(false)
          },
        })
      }

      // Add new button
      editor.addButton('ameliaButton', {
        title: wpAmeliaLabels.insert_amelia_shortcode,
        cmd: 'ameliaButtonCommand',
        image: window.wpAmeliaPluginURL + 'public/img/amelia-logo-admin-icon.svg'
      })

      // Button functionality
      editor.addCommand('ameliaButtonCommand', function () {
        jQuery.ajax({
          url: ajaxurl + '?action=wpamelia_api&call=/entities&types[]=categories&types[]=employees&types[]=locations',
          dataType: 'json',
          success: function (response) {
            entities = response.data
            categories = []
            services = []
            employees = []
            locations = []
            servicesList = []

            for (let i = 0; i < response.data.categories.length; i++) {
              categories.push({
                value: response.data.categories[i].id,
                text: response.data.categories[i].name + ' (id: ' + response.data.categories[i].id + ')'
              })
            }

            // Add all services to one array
            response.data.categories.map(category => category.serviceList).forEach(function (serviceList) {
              servicesList = servicesList.concat(serviceList)
            })

            // Create array of services objects
            for (let i = 0; i < servicesList.length; i++) {
              if (servicesList[i].show) {
                services.push({
                  value: servicesList[i].id,
                  text: servicesList[i].name + ' (id: ' + servicesList[i].id + ')'
                })
              }
            }

            // Create array of employees objects
            for (let i = 0; i < response.data.employees.length; i++) {
              employees.push({
                value: response.data.employees[i].id,
                text: response.data.employees[i].firstName + ' ' + response.data.employees[i].lastName + ' (id: ' + response.data.employees[i].id + ')'
              })
            }

            // Create array of locations objects
            for (let i = 0; i < response.data.locations.length; i++) {
              locations.push({
                value: response.data.locations[i].id,
                text: response.data.locations[i].name + ' (id: ' + response.data.locations[i].id + ')'
              })
            }

            // set and open editor
            setAndOpenEditor('booking')
          }
        })
      })
    }
  })

  tinymce.PluginManager.add('ameliaBookingPlugin', tinymce.plugins.ameliaBookingPlugin)
})()