(function (w, d, $) {
  function load() {
    colorPickers();
  }
  function colorPickers() {
    $(".gd-checkout-color-picker").wpColorPicker();
  }
  $(document).ready(load);
})(window, document, jQuery);
