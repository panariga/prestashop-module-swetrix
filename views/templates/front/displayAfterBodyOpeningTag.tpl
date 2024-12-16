<div>
  <script src="https://swetrix.org/swetrix.js" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var swetrixCreatedEvent = new Event('swetrixCreated');
      swetrix.init('{$swetrix_project_id}', {
      devMode: false,
        disabled: false,
        respectDNT: false,
        apiURL: '{$swetrix_api_address}/log',
    })
    swetrix.trackViews()
    window.dispatchEvent(swetrixCreatedEvent);
    })
  </script>
  <noscript>
    <img src="{$swetrix_api_address}/log/noscript?pid={$swetrix_project_id}" alt=""
      referrerpolicy="no-referrer-when-downgrade" />
  </noscript>

</div>