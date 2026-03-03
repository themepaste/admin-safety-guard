jQuery(document).ready(function ($) {
  let deactivateUrl = '';

  // Append modal HTML once
  if ($('#tpsm-modal').length === 0) {
    $('body').append(`
            <div id="tpsm-modal">
                <div class="tpsm-modal-content">
                    <h2>We're sorry to see you go 😔</h2>
                    <p>Please tell us why you're deactivating:</p>

                    <form id="tpsm-feedback-form">
                        <label><input type="radio" name="reason" value="Found better plugin"> Found a better plugin</label>
                        <label><input type="radio" name="reason" value="Not working properly"> Not working properly</label>
                        <label><input type="radio" name="reason" value="Missing features"> Missing features</label>
                        <label><input type="radio" name="reason" value="Too expensive"> Premium too expensive</label>
                        <label><input type="radio" name="reason" value="Temporary"> Temporary deactivation</label>
                        <label><input type="radio" name="reason" value="Other"> Other</label>

                        <textarea name="details" class="tpsm-textarea" placeholder="Additional feedback (optional)"></textarea>

                        <div class="tpsm-modal-buttons">
                            <button type="button" class="tpsm-skip-btn">Skip</button>
                            <div>
                              <button type="button" class="tpsm-cancel-btn">Cancel</button>
                              <button type="submit" class="tpsm-submit-btn">Submit & Deactivate</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        `);
  }

  // YOUR ORIGINAL SELECTOR (unchanged)
  $('tr[data-plugin="' + tpsaDeactivate.plugin_slug + '"] .deactivate a').on(
    'click',
    function (e) {
      e.preventDefault();

      deactivateUrl = $(this).attr('href');

      $('#tpsm-modal').fadeIn();
    },
  );

  // Cancel button
  $(document).on('click', '.tpsm-cancel-btn', function () {
    $('#tpsm-modal').fadeOut();
  });

  // Skip button
  $(document).on('click', '.tpsm-skip-btn', function () {
    window.location.href = deactivateUrl;
  });

  // Submit with AJAX
  $(document).on('submit', '#tpsm-feedback-form', function (e) {
    e.preventDefault();

    const reason = $('input[name="reason"]:checked').val();
    const details = $('.tpsm-textarea').val();

    $.ajax({
      url: tpsaDeactivate.ajax_url, // or use localized script
      method: 'POST',
      data: {
        action: 'tpsa_deactivate_plugin',
        reason: reason,
        feedback: details,
      },
      success: function (response) {
        console.log(response);
        // window.location.href = deactivateUrl;
      },
      error: function () {
        // window.location.href = deactivateUrl;
      },
    });
  });
});
