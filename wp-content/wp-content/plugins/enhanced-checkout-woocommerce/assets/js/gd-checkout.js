(function (window, document, $) {
  const gdCheckout = {};

  gdCheckout.checkoutEl = document.getElementById("gd-checkout");

  gdCheckout.init = function () {
    gdCheckout.resetHash();
    gdCheckout.addListeners();
    gdCheckout.misc();
  };

  gdCheckout.misc = function () {
    if (window.gdCheckoutVars?.isCheckout === "1") {
      setTimeout(() => {
        gdCheckout.sendMessage("gd_checkout_is_checkout");
      }, 1000);
    }
  };

  gdCheckout.resetHash = function () {
    if (window.location.hash === "#gd-checkout") {
      window.location.hash = "";
    }
  };

  gdCheckout.addListeners = function () {
    $(".gd-checkout-open").on("click", function (e) {
      e.preventDefault();
      gdCheckout.openCheckout();
    });

    window.addEventListener("message", function (e) {
      if (e.data === "close_modal") {
        gdCheckout.resetHash();
        if (gdCheckout.scrollListener) {
          gdCheckout.checkoutEl.removeEventListener(
            "touchmove",
            gdCheckout.scrollListener1
          );
        }
        if (window.gdCheckoutVars.isCheckout === "1") {
          // TODO: fix bug with tab hash navigation history
          history.back();
          history.back();
        } else {
          gdCheckout.checkoutEl?.classList.toggle("checkout-open");
        }
      }

      if (typeof e.data === "string") {
        if (e.data.indexOf("checkoutredirect") > -1) {
          let url = e.data.split("|").pop();
          if (url && url.indexOf("http") === 0) {
            window.location = url;
          } else if (url && url.indexOf("#") === 0) {
            // payment confirmation
            window.location = window.location.href + url;
          }
        } else if (e.data === "checkout_failed_to_load") {
          window.location.href =
            window.gdCheckoutVars.wooCheckoutUrl + "?gd-disable-checkout=1";
        } else if (e.data === "checkout_return_to_shop") {
          window.location.href = window.gdCheckoutVars.wooShopUrl;
        } else if (
          e.data.indexOf("gd_") > -1 &&
          JSON.parse(e.data)?.data?.length > 0
        ) {
          const data = JSON.parse(e.data)?.data;
          gdCheckout.sendMessage(`gd_customize|${data[0]}|${data[1]}`);
        }
      }
    });

    window.addEventListener("hashchange", function () {
      if (window.location.hash === "#gd-checkout") {
        gdCheckout.openCheckout();
      }
    });
  };

  gdCheckout.openCheckout = function () {
    gdCheckout.checkoutEl.classList.add("checkout-open");
    gdCheckout.disableScrolling();
    gdCheckout.sendMessage("open_checkout");
  };

  gdCheckout.disableScrolling = function () {
    gdCheckout.scrollListener1 = gdCheckout.checkoutEl.addEventListener(
      "touchmove",
      (e) => {
        e.preventDefault();
      }
    );
  };

  gdCheckout.sendMessage = function (message) {
    const frame = document.getElementById("gd-checkout");
    frame.contentWindow.postMessage(message, "*");
  };

  $(document).ready(gdCheckout.init);
})(window, document, jQuery);
