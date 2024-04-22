<?php
/*
 * WPGraphQL customizations
 */

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;

use WPGraphQL\WooCommerce\Mutation;
use GoDaddy\MWC\WordPress\HeadlessCheckout\Resolvers;
use GoDaddy\WordPress\MWC\Core\Payments\Poynt;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Commerce;

class GraphQL {
  public function __construct() {
    $this->load();
  }


  public function load() {
    // disable wp-graphql tracking notice
    update_option('enhanced-checkout-woocommerce_tracking_notice', 'hide');
    update_option('enhanced-checkout-woocommerce_allow_tracking', 'no');

    add_action(
      'graphql_register_types',
      [$this, 'graphqlRegister'],
      10
    );
  }

  /**
   * Register mutation and queries with wpgraphql
   */
  public function graphqlRegister(): void {
    register_graphql_mutation(
      'gdlogin',
      [
        'description'         => __('Login a user.', 'gd-checkout'),
        'inputFields'         => [
          'username' => [
            'type'        => ['non_null' => 'String'],
            'description' => __('The username used for login. Typically a unique or email address depending on specific configuration', 'gd-checkout'),
          ],
          'password' => [
            'type'        => ['non_null' => 'String'],
            'description' => __('The plain-text password for the user logging in.', 'gd-checkout'),
          ],
        ],
        'outputFields'        => [
          'result'    => [
            'type'        => 'String',
            'description' => __('Result of login, either success or error.', 'gd-checkout'),
          ],
          'user_id'    => [
            'type'        => 'String',
            'description' => __('Logged in user id.', 'gd-checkout'),
          ],
        ],
        'mutateAndGetPayload' => function ($input) {

          // Login the user
          return Resolvers::loginSetCookie(sanitize_user($input['username']), trim($input['password']));
        },
      ]
    );

    // create a draft order with all the same data as the checkout. Can't use the checkout mutation for this.

    register_graphql_mutation(
      'createOrderFromSession',
      [
        'description'         => __('Create a pending order from the current user session.', 'gd-checkout'),
        'inputFields'         => array_merge(Mutation\Checkout::get_input_fields(), [
          'orderStatus' => [
            'type'        => 'String',
            'description' => __('The order status.', 'gd-checkout'),
          ],
          'orderNote' => [
            'type'        => 'String',
            'description' => __('The order note.', 'gd-checkout'),
          ],
        ]),
        'outputFields'        => [
          'orderId'    => [
            'type'        => 'Int',
            'description' => __('ID of the order.', 'gd-checkout'),
          ],
          'redirectUrl' => [
            'type'        => 'String',
            'description' => __('Redirect URL.', 'gd-checkout'),
          ],
        ],
        'mutateAndGetPayload' => function ($input) {
          return Resolvers::createOrderFromSession($input);
        },
      ]
    );

    register_graphql_mutation(
      'completeOrder',
      [
        'description'         => __('Complete a pending order.', 'gd-checkout'),
        'inputFields'         => [
          'orderId' => [
            'type'        => ['non_null' => 'Int'],
            'description' => __('The order id.', 'gd-checkout'),
          ],
          'transactionId' => [
            'type'        => ['non_null' => 'String'],
            'description' => __('The transaction id.', 'gd-checkout'),
          ],
        ],
        'outputFields'        => [
          'orderId'    => [
            'type'        => 'Int',
            'description' => __('ID of the order.', 'gd-checkout'),
          ],
          'redirectUrl'    => [
            'type'        => 'String',
            'description' => __('Redirect URL.', 'gd-checkout'),
          ],
          'orderStatus'    => [
            'type'        => 'String',
            'description' => __('Order status.', 'gd-checkout'),
          ],
        ],
        'mutateAndGetPayload' => function ($input) {
          return Resolvers::completeOrder($input);
        },
      ]
    );

    register_graphql_mutation(
      'stripeSavedPaymentMethods',
      [
        'description'         => __('Get saved payment methods for a user.', 'gd-checkout'),
        'inputFields'         => [
          'userId' => [
            'type'        => ['non_null' => 'Int'],
            'description' => __('The WP user id.', 'gd-checkout'),
          ],
        ],
        'outputFields'        => [
          'paymentMethods'    => [
            'type'        => 'String',
            'description' => __('JSON list of payment methods.', 'gd-checkout'),
          ],
        ],
        'mutateAndGetPayload' => function ($input) {
          return Resolvers::stripePaymentMethods($input);
        },

      ]
    );

    register_graphql_mutation(
      'stripePaymentIntent',
      [
        'description'         => __('Get a payment intent from Stripe.', 'gd-checkout'),
        'inputFields'         => [
          'currency' => [
            'type'        => 'String',
            'description' => __('The currency of the transaction.', 'gd-checkout'),
          ],
          'id' => [
            'type'        => 'String',
            'description' => __('An ID to update a payment intent.', 'gd-checkout'),
          ],
          'customerId' => [
            'type'        => 'String',
            'description' => __('The Stripe customer id.', 'gd-checkout'),
          ],
          'userEmail' => [
            'type'        => 'String',
            'description' => __('Email of Stripe customer to attach to payment Intent.', 'gd-checkout'),
          ],
          'setupFutureUsage' => [
            'type'        => 'String',
            'description' => __('Setup future usage for payment intent.', 'gd-checkout'),
          ],
        ],
        'outputFields'        => [
          'id'    => [
            'type'        => 'String',
            'description' => __('ID of the payment intent.', 'gd-checkout'),
          ],
          'amount' => [
            'type'        => 'Integer',
            'description' => __('The amount of the transaction.', 'gd-checkout'),
          ],
          'clientSecret'    => [
            'type'        => 'String',
            'description' => __('Client secret from the payment intent.', 'gd-checkout'),
          ],
          'status'    => [
            'type'        => 'String',
            'description' => __('Payment intent status.', 'gd-checkout'),
          ],
          'paymentMethodTypes'    => [
            'type'        => ['list_of' => 'String'],
            'description' => __('Payment intent payment method types, such as card.', 'gd-checkout'),
          ],
          'customer' => [
            'type'        => 'String',
            'description' => __('Stripe customer id from payment intent response.', 'gd-checkout'),
          ],
        ],
        'mutateAndGetPayload' => function ($input) {
          return Resolvers::stripePaymentIntent($input);
        },
      ]
    );

    register_graphql_mutation(
      'confirmStripePaymentIntent',
      [
        'description'         => __('Confirm a Stripe payment intent.', 'gd-checkout'),
        'inputFields'         => [
          'id' => [
            'type'        => 'String',
            'description' => __('An ID to update a payment intent.', 'gd-checkout'),
          ],
          'paymentMethodId' => [
            'type'        => 'String',
            'description' => __('The Stripe payment method id.', 'gd-checkout'),
          ],
          'setupFutureUsage' => [
            'type'        => 'String',
            'description' => __('Save the payment method to the Stripe customer.', 'gd-checkout'),
          ],
        ],
        'outputFields'        => [
          'id'    => [
            'type'        => 'String',
            'description' => __('ID of the payment intent.', 'gd-checkout'),
          ],
          'amount' => [
            'type'        => 'Integer',
            'description' => __('The amount of the transaction.', 'gd-checkout'),
          ],
          'clientSecret'    => [
            'type'        => 'String',
            'description' => __('Client secret from the payment intent.', 'gd-checkout'),
          ],
          'status'    => [
            'type'        => 'String',
            'description' => __('Payment intent status.', 'gd-checkout'),
          ],
          'customer' => [
            'type'        => 'String',
            'description' => __('Stripe customer id from payment intent response.', 'gd-checkout'),
          ],
        ],
        'mutateAndGetPayload' => function ($input) {
          return Resolvers::confirmStripePaymentIntent($input);
        },
      ]
    );

    register_graphql_object_type('StripeSettings', [
      'description' => __('An array of stripe settings like keys.', 'gd-checkout'),
      'fields' => [
        'testMode' => [
          'type' => 'String',
          'description' => __('Returns yes if test mode enabled.', 'gd-checkout'),
        ],
        'testPublishableKey' => [
          'type' => 'String',
          'description' => __('The test publishable key.', 'gd-checkout'),
        ],
        'publishableKey' => [
          'type' => 'String',
          'description' => __('The live publishable key.', 'gd-checkout'),
        ],
        'paymentRequestButtons' => [
          'type' => 'String',
          'description' => __('Returns yes if payment request buttons enabled.', 'gd-checkout'),
        ],
        'capture' => [
          'type' => 'String',
          'description' => __('If no, authorization only payments are allowed, and other payment methods are disabled.', 'gd-checkout'),
        ],
        'enableTokenization' => [
          'type' => 'String',
          'description' => __('Returns yes if tokenization enabled.', 'gd-checkout'),
        ],
      ],
    ]);

    // keys for displaying stripe elements on front end
    register_graphql_field('RootQuery', 'stripeSettings', [
      'type' => 'StripeSettings',
      'description' => __('Get Stripe settings.', 'gd-checkout'),
      'resolve' => function () {
        return Stripe::getStripeSettings();
      },
    ]);

    register_graphql_object_type('PayPalKeys', [
      'description' => __('An array of PayPal keys.', 'gd-checkout'),
      'fields' => [
        'sandboxMode' => [
          'type' => 'String',
          'description' => __('Returns yes if sandbox mode enabled.', 'gd-checkout'),
        ],
        'clientIdSandbox' => [
          'type' => 'String',
          'description' => __('The client id for sandbox mode.', 'gd-checkout'),
        ],
        'clientIdLive' => [
          'type' => 'String',
          'description' => __('The live client id.', 'gd-checkout'),
        ],
      ],
    ]);

    // client ID to handle PayPal button on front end
    register_graphql_field('RootQuery', 'paypalSettings', [
      'type' => 'PayPalKeys',
      'description' => __('Get PayPal keys.', 'gd-checkout'),
      'resolve' => function () {
        $paypalSettings = get_option('woocommerce-ppcp-settings');
        return
          [
            'sandboxMode' => !empty($paypalSettings['sandbox_on']) ? 'yes' : 'no',
            'clientIdSandbox' =>  !empty($paypalSettings['client_id_sandbox']) ? $paypalSettings['client_id_sandbox'] : '',
            'clientIdLive' =>  !empty($paypalSettings['client_id']) ? $paypalSettings['client_id'] : '',
          ];
      }
    ]);


    register_graphql_object_type('GdPaySettings', [
      'description' => __('GoDaddy payments settings.', 'gd-checkout'),
      'fields' => [
        'businessId' => [
          'type' => 'String',
          'description' => __('Poynt business id.', 'gd-checkout'),
        ],
        'storeId' => [
          'type' => 'String',
          'description' => __('Poynt store id.', 'gd-checkout'),
        ],
        'appId' => [
          'type' => 'String',
          'description' => __('Poynt app id.', 'gd-checkout'),
        ],
        'applePayEnabled' => [
          'type' => 'String',
          'description' => __('Returns yes if Apple Pay enabled.', 'gd-checkout'),
        ],
        'googlePayEnabled' => [
          'type' => 'String',
          'description' => __('Returns yes if Google Pay enabled.', 'gd-checkout'),
        ],
      ],
    ]);

    register_graphql_object_type('SquareSettings', [
      'description' => __('An array of Square payment settings.', 'gd-checkout'),
      'fields' => [
        'sandboxMode' => [
          'type' => 'String',
          'description' => __('Returns yes if test mode enabled.', 'gd-checkout'),
        ],
        'applicationId' => [
          'type' => 'String',
          'description' => __('Application ID.', 'gd-checkout'),
        ],
        'locationId' => [
          'type' => 'String',
          'description' => __('Location ID.', 'gd-checkout'),
        ],
      ],
    ]);

    register_graphql_field('RootQuery', 'gdPaySettings', [
      'type' => 'GdPaySettings',
      'description' => __('Get GoDaddy payments settings.', 'gd-checkout'),
      'resolve' => function () {



        $appleSettings = get_option('woocommerce_godaddy-payments-apple-pay_settings');
        $googleSettings = get_option('woocommerce_godaddy-payments-google-pay_settings');

        $googlePayEnabled = $googleSettings ? $googleSettings['enabled'] : 'no';
        $applePayEnabled = $appleSettings ? $appleSettings['enabled'] : 'no';

        // support GoDaddy Payments plugin
        if (class_exists('GD_Poynt_For_WooCommerce_Loader')) {

          // see if gateway is enabled
          $gateways = \WC()->payment_gateways->get_available_payment_gateways();

          foreach ($gateways as $gateway) {
            if ($gateway->id == 'poynt_credit_card' && $gateway->enabled == 'yes') {
              return
                [
                  'businessId' => get_option('wc_poynt_businessId'),
                  'storeId' =>  get_option('wc_poynt_storeId'),
                  'appId' => get_option('wc_poynt_appId'),
                  'applePayEnabled' => $applePayEnabled,
                  'googlePayEnabled' => $googlePayEnabled,
                ];
            }
          }
        }

        return
          [
            'businessId' => Poynt::getBusinessId(),
            'storeId' =>  Commerce::getStoreId(),
            'appId' => Poynt::getAppId(),
            'applePayEnabled' => $applePayEnabled,
            'googlePayEnabled' => $googlePayEnabled,
          ];
      }
    ]);



    register_graphql_field('RootQuery', 'squareSettings', [
      'type' => 'SquareSettings',
      'description' => __('Get Square settings.', 'gd-checkout'),
      'resolve' => function () {
        return Resolvers::getSquareSettings();
      }
    ]);

    register_graphql_object_type('WooPaymentToken', [
      'description' => __('A WooCommerce payment token.', 'gd-checkout'),
      'fields' => [
        'id' => [
          'type' => ['non_null' => 'String'],
          'description' => __('The database id of the token object.', 'gd-checkout'),
        ],
        'token' => [
          'type' => ['non_null' => 'String'],
          'description' => __('The raw token from the payment processor.', 'gd-checkout'),
        ],
        'last4' => [
          'type' => 'String',
          'description' => __('The last 4 digits of the card.', 'gd-checkout'),
        ],
        'type' => [
          'type' => 'String',
          'description' => __('The brand name of the card.', 'gd-checkout'),
        ],
        'expiryMonth' => [
          'type' => 'Int',
          'description' => __('The card expiration month.', 'gd-checkout'),
        ],
        'expiryYear' => [
          'type' => 'Int',
          'description' => __('The card expiration year.', 'gd-checkout'),
        ],
      ],
    ]);

    register_graphql_mutation(
      'getPaymentTokensForUser',
      [
        'description'         => __('Get saved payment methods (using token API) for a user.', 'gd-checkout'),
        'inputFields'         => [
          'userId' => [
            'type'        => ['non_null' => 'Int'],
            'description' => __('The WP user id.', 'gd-checkout'),
          ],
          'gatewayId' => [
            'type'        => ['non_null' => 'String'],
            'description' => __('ID of the payment gateway.', 'gd-checkout'),
          ],
        ],
        'outputFields'        => [
          'paymentTokens'    => [
            'type'        => ['list_of' => 'WooPaymentToken'],
            'description' => __('List of payment methods.', 'gd-checkout'),
          ],
        ],
        'mutateAndGetPayload' => function ($input) {
          return Resolvers::getPaymentTokensForUser($input);
        },

      ]
    );

    register_graphql_object_type('Country', [
      'description' => __('A country object with a code and name.', 'gd-checkout'),
      'fields'        => [
        'code'    => [
          'type'        => ['non_null' => 'String'],
        ],
        'name'    => [
          'type'        => ['non_null' => 'String'],
        ],
      ],
    ]);

    register_graphql_object_type('AllowedCountryResponse', [
      'description' => __('The response with shipping and sell to countries.', 'gd-checkout'),
      'fields'        => [
        'sellToCountries'    => [
          'type'        => ['list_of' => 'Country']
        ],
        'shipToCountries'    => [
          'type'        => ['list_of' => 'Country']
        ],
      ],
    ]);

    register_graphql_field(
      'RootQuery',
      'getAllowedCountries',
      [
        'type' => 'AllowedCountryResponse',
        'resolve' => function () {
          return Resolvers::getAllowedCountries();
        },
      ]
    );

    register_graphql_object_type('CheckoutData', [
      'description' => __('The checkout data.', 'gd-checkout'),
      'fields' => [
        'checkoutData' => [
          'type' => 'String',
          'description' => __('The checkout data.', 'gd-checkout'),
        ],
      ],
    ]);

    // client ID to handle PayPal button on front end
    register_graphql_field('RootQuery', 'getCheckoutData', [
      'type' => 'CheckoutData',
      'description' => __('Get checkout fields.', 'gd-checkout'),
      'resolve' => [__NAMESPACE__ . '\Resolvers', 'getCheckoutData']
    ]);

    // add a fee via checkout add ons plugin
    register_graphql_mutation(
      'addCheckoutAddonFee',
      [
        'description'         => __('Add a fee via Checkout Addons plugin.', 'gd-checkout'),
        'inputFields'         => [
          'id' => [
            'type'        => ['non_null' => 'String'],
            'description' => __('ID of the addon.', 'gd-checkout'),
          ],
          'value' => [
            'type'        => ['non_null' => 'String'],
            'description' => __('The value of the adjustment.', 'gd-checkout'),
          ],
        ],
        'outputFields'        => [
          'result'    => [
            'type'        => 'String',
            'description' => __('Result of fee action.', 'gd-checkout'),
          ],
          'fees' => [
            'type' => ['list_of' => 'CartFee'],
            'description' => __('List of fees.', 'gd-checkout'),
          ],

        ],
        'mutateAndGetPayload' => function ($input) {
          return Resolvers::addCheckoutAddonFee($input);
        },
      ]
    );
  }
}

new GraphQL();
