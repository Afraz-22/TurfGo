// TurfGo - basic front-end interactivity

// Ask for confirmation before delete / cancel actions
document.addEventListener("DOMContentLoaded", function () {
  var confirmLinks = document.querySelectorAll("[data-confirm]");
  confirmLinks.forEach(function (link) {
    link.addEventListener("click", function (e) {
      var message = link.getAttribute("data-confirm") || "Are you sure?";
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // Auto-hide alert boxes after 4 seconds
  var alerts = document.querySelectorAll(".alert");
  alerts.forEach(function (box) {
    setTimeout(function () {
      box.style.display = "none";
    }, 4000);
  });
});
