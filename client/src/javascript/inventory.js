var prepareBase = function (url) {
        return url.split("?")[0].split("#")[0];
    },
    fetchBase = function () {
        var base = window.location.toString();

        if (window.location.href.replace(/\/$/, "") === window.location.origin) {
            if (base.substr(-1) === '/') {
                base = base.substr(0, base.length - 1) + '/home';
            } else {
                base = base + '/home';
            }
        } else {
            base = window.location.toString();
        }

        if (base.indexOf('//home')) {
            base = base.replace('//home', '/home');
        }

        return prepareBase(base);
    };


var FC = FC || {};
FC.onLoad = (function (_super) {
    return function () {
        if (typeof _super !== 'undefined') {
            _super.apply(this, arguments);
        }

        function updateReservations() {
          FC.client.request('https://' + FC.settings.storedomain + '/cart?output=json').done(function (dataJSON) {
            jQuery.each(dataJSON.items, function (key, product) {
              if (product.expires > 0) {
                var code = product.parent_code === '' ? product.code : product.parent_code;

                jQuery.ajax({
                  url: fetchBase() + '/reserveproduct/?code=' + code + '&id=' + product.id +
                    '&quantity=' + product.quantity + '&expires=' + product.expires +
                    '&cart=' + FC.json.session_id,
                  success: function (data) {
                    //console.log(data);
                  },
                });
              }
            });
          });
        }

        FC.client.on('cart-submit.done', function () {
          updateReservations();
        });

      FC.client.wrap("cart-quantity-updated", function() {
        updateReservations();
      });
    };
})(FC.onLoad);
