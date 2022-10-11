// phpcs:disable
function ce4wpNavigateToDashboard(element, linkReference, linkParameters, startCallback, finishCallback) {
  if (typeof startCallback === 'function') {
    startCallback(element)
  }
  jQuery.ajax({
    type   : "POST",
    url    : ce4wp_data.url,
    data   : {
      nonce: ce4wp_data.nonce,
      link_reference: linkReference || undefined,
      link_parameters: linkParameters || undefined,
      action: 'ce4wp_request_sso'
    },
    success: function(data){
      window.open(data, '_blank')
      if (typeof finishCallback === 'function') {
        finishCallback(element)
      }
    }
  });
}

function ce4wpDashboardStartCallback (element) {
  var skeleton = document.getElementById('ce4wpskeleton')
  var loaded = document.getElementById('ce4wploaded')
  if (skeleton && loaded) {
    skeleton.style.display = "block";
    loaded.style.display = "none";
  }
}
function ce4wpDashboardFinishCallback (element) {
  var skeleton = document.getElementById('ce4wpskeleton')
  var loaded = document.getElementById('ce4wploaded')
  if (skeleton && loaded) {
    skeleton.style.display = "none";
    loaded.style.display = "block";
  }
}

function ce4wpWidgetStartCallback (element) {
  if (element) {
    element.setAttribute('disabled', true)
  }
}
function ce4wpWidgetFinishCallback (element) {
  if (element) {
    element.removeAttribute('disabled')
  }
}