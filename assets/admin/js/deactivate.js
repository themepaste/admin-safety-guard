jQuery(document).ready(function ($) {
  $('tr[data-plugin="' + tpsaDeactivate.plugin_slug + '"] .deactivate a').on(
    'click',
    function (e) {
      e.preventDefault();

      var deactivateUrl = $(this).attr('href');

      if (confirm('Why are you deactivating?')) {
        // You can replace this with a modal form
      }

      // After form submit:
      window.location.href = deactivateUrl;
    },
  );
});
