(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.finnaviaFilters = {
    attach: function (context, settings) {

      $(".flights_container", context).once("finnaviaFilters").each(function () {
        function runAjax(selectedType, selectedAirport) {
          let urlendpoint = "";
          let path = `ajax_get_flight_data/${selectedType}/${selectedAirport}`;
          urlendpoint = Drupal.url(
            path
          );

          return $.ajax({
            url: urlendpoint,
            error: function(e) {
              console.log('error', e);
              return Drupal.t('No results found');
            },
            success: function(response) {
              // Move new content in.
              if (response.length > 0) {
                return response[0].data;
              } else {
                return Drupal.t('No results found');
              }

            },
            timeout: 20000
          });
        }

        function filterClick(event ,selectedType='all', selectedAirport='all') {
          const result = runAjax(selectedType, selectedAirport);

          result.done((data) => {
            let fligthContentElement = document.querySelectorAll(`.flight_content`)[0];
            if (fligthContentElement && data.length > 0 && data[0].data) {
              fligthContentElement.innerHTML = data[0].data;
            }
          });
          event.stopPropagation();
          event.preventDefault();
        }

        const filtersContainer = document.querySelector('.filters');
        let filterType = filtersContainer.getElementsByClassName('filters__type')[0];
        let filterAirport = filtersContainer.getElementsByClassName('filters__airport')[0];

        if (filterType && filterAirport) {
          filterType.onchange = function(e) {
            let selectedType = filterType.value;
            let selectedAirport = filterAirport.value;
            filterClick(e, selectedType, selectedAirport);
          };
          filterAirport.onchange = function(e) {
            let selectedType = filterType.value;
            let selectedAirport = filterAirport.value;
            filterClick(e, selectedType, selectedAirport);
          }
        }
      });
    },
  };

})(jQuery, Drupal);
