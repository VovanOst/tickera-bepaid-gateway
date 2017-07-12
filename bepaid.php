<?php
/*
  BiPaid - Payment Gateway
 */

class TC_Gateway_BePaid extends TC_Gateway_API {

    var $plugin_name = 'bepaid';
    var $admin_name = '';
    var $public_name = '';
    var $method_img_url = '';
    var $admin_img_url = '';
    var $force_ssl = false;
    var $ipn_url;
    var $API_Username, $API_Password, $SandboxFlag, $returnURL, $API_Endpoint, $version, $currency, $locale;
    var $currencies = array();
    var $automatically_activated = false;
    var $skip_payment_screen = true;

    //Support for older payment gateway API
    function on_creation() {
        $this->init();
    }

    function init() {
        global $tc;

        $this->admin_name = __('bepaid', 'tc');
        $this->public_name = __('bepaid', 'tc');

        $this->method_img_url = apply_filters('tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/bepaid.png', $this->plugin_name);
        $this->admin_img_url = apply_filters('tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-bepaid.png', $this->plugin_name);

        $this->currency = $this->get_option('currency', 'BYN', 'bepaid');
        $this->API_Username = $this->get_option('sid', '', 'bepaid');
        $this->API_Password = $this->get_option('secret_word', '', 'bepaid');
        $this->SandboxFlag = $this->get_option('mode', 'sandbox', 'bepaid');

        $currencies = array(
            "AED" => __('AED - United Arab Emirates Dirham', 'tc'),
            "ARS" => __('ARS - Argentina Peso', 'tc'),
            "AUD" => __('AUD - Australian Dollar', 'tc'),
			"BYN" => __('BYN - Belorussion ruble', 'tc'),
            "BRL" => __('BRL - Brazilian Real', 'tc'),
            "CAD" => __('CAD - Canadian Dollar', 'tc'),
            "CHF" => __('CHF - Swiss Franc', 'tc'),
            "DKK" => __('DKK - Danish Krone', 'tc'),
            "EUR" => __('EUR - Euro', 'tc'),
            "GBP" => __('GBP - British Pound', 'tc'),
            "HKD" => __('HKD - Hong Kong Dollar', 'tc'),
            "INR" => __('INR - Indian Rupee', 'tc'),
            "ILS" => __('ILS - Israeli New Shekel', 'tc'),
            "LTL" => __('LTL - Lithuanian Litas', 'tc'),
            "JPY" => __('JPY - Japanese Yen', 'tc'),
            "MYR" => __('MYR - Malaysian Ringgit', 'tc'),
            "MXN" => __('MXN - Mexican Peso', 'tc'),
            "NOK" => __('NOK - Norwegian Krone', 'tc'),
            "NZD" => __('NZD - New Zealand Dollar', 'tc'),
            "PHP" => __('PHP - Philippine Peso', 'tc'),
            "RON" => __('RON - Romanian New Leu', 'tc'),
            "RUB" => __('RUB - Russian Ruble', 'tc'),
            "SEK" => __('SEK - Swedish Krona', 'tc'),
            "SGD" => __('SGD - Singapore Dollar', 'tc'),
            "TRY" => __('TRY - Turkish Lira', 'tc'),
            "USD" => __('USD - U.S. Dollar', 'tc'),
            "ZAR" => __('ZAR - South African Rand', 'tc'),
            "AFN" => __('AFN - Afghan Afghani', 'tc'),
            "ALL" => __('ALL - Albanian Lek', 'tc'),
            "AZN" => __('AZN - Azerbaijani an Manat', 'tc'),
            "BSD" => __('BSD - Bahamian Dollar', 'tc'),
            "BDT" => __('BDT - Bangladeshi Taka', 'tc'),
            "BBD" => __('BBD - Barbados Dollar', 'tc'),
            "BZD" => __('BZD - Belizean dollar', 'tc'),
            "BMD" => __('BMD - Bermudian Dollar', 'tc'),
            "BOB" => __('BOB - Bolivian Boliviano', 'tc'),
            "BWP" => __('BWP - Botswana Pula', 'tc'),
            "BND" => __('BND - Brunei Dollar', 'tc'),
            "BGN" => __('BGN - Bulgarian Lev', 'tc'),
            "CLP" => __('CLP - Chilean Peso', 'tc'),
            "CNY" => __('CNY - Chinese Yuan Renminbi', 'tc'),
            "COP" => __('COP - Colombian Peso', 'tc'),
            "CRC" => __('CRC - Costa Rican Colon', 'tc'),
            "HRK" => __('HRK - Croatian Kuna', 'tc'),
            "CZK" => __('CZK - Czech Republic Koruna', 'tc'),
            "DOP" => __('DOP - Dominican Peso', 'tc'),
            "XCD" => __('XCD - East Caribbean Dollar', 'tc'),
            "EGP" => __('EGP - Egyptian Pound', 'tc'),
            "FJD" => __('FJD - Fiji Dollar', 'tc'),
            "GTQ" => __('GTQ - Guatemala Quetzal', 'tc'),
            "HNL" => __('HNL - Honduras Lempira', 'tc'),
            "HUF" => __('HUF - Hungarian Forint', 'tc'),
            "IDR" => __('IDR - Indonesian Rupiah', 'tc'),
            "JMD" => __('JMD - Jamaican Dollar', 'tc'),
            "KZT" => __('KZT - Kazakhstan Tenge', 'tc'),
            "KES" => __('KES - Kenyan Shilling', 'tc'),
            "LAK" => __('LAK - Laosian kip', 'tc'),
            "MMK" => __('MMK - Myanmar Kyat', 'tc'),
            "LBP" => __('LBP - Lebanese Pound', 'tc'),
            "LRD" => __('LRD - Liberian Dollar', 'tc'),
            "MOP" => __('MOP - Macanese Pataca', 'tc'),
            "MVR" => __('MVR - Maldiveres Rufiyaa', 'tc'),
            "MRO" => __('MRO - Mauritanian Ouguiya', 'tc'),
            "MUR" => __('MUR - Mauritius Rupee', 'tc'),
            "MAD" => __('MAD - Moroccan Dirham', 'tc'),
            "NPR" => __('NPR - Nepalese Rupee', 'tc'),
            "TWD" => __('TWD - New Taiwan Dollar', 'tc'),
            "NIO" => __('NIO - Nicaraguan Cordoba', 'tc'),
            "PKR" => __('PKR - Pakistan Rupee', 'tc'),
            "PGK" => __('PGK - New Guinea kina', 'tc'),
            "PEN" => __('PEN - Peru Nuevo Sol', 'tc'),
            "PLN" => __('PLN - Poland Zloty', 'tc'),
            "QAR" => __('QAR - Qatari Rial', 'tc'),
            "WST" => __('WST - Samoan Tala', 'tc'),
            "SAR" => __('SAR - Saudi Arabian riyal', 'tc'),
            "SCR" => __('SCR - Seychelles Rupee', 'tc'),
            "SBD" => __('SBD - Solomon Islands Dollar', 'tc'),
            "KRW" => __('KRW - South Korean Won', 'tc'),
            "LKR" => __('LKR - Sri Lanka Rupee', 'tc'),
            "CHF" => __('CHF - Switzerland Franc', 'tc'),
            "SYP" => __('SYP - Syrian Arab Republic Pound', 'tc'),
            "THB" => __('THB - Thailand Baht', 'tc'),
            "TOP" => __('TOP - Tonga Pa&#x27;anga', 'tc'),
            "TTD" => __('TTD - Trinidad and Tobago Dollar', 'tc'),
            "UAH" => __('UAH - Ukraine Hryvnia', 'tc'),
            "VUV" => __('VUV - Vanuatu Vatu', 'tc'),
            "VND" => __('VND - Vietnam Dong', 'tc'),
            "XOF" => __('XOF - West African CFA Franc BCEAO', 'tc'),
            "YER" => __('YER - Yemeni Rial', 'tc'),
        );

        $this->currencies = $currencies;
    }

    function payment_form($cart) {

    }

    function process_payment($cart) {
        global $tc;

        $this->maybe_start_session();
        $this->save_cart_info();

        if ($this->SandboxFlag == 'sandbox') {
            $url = 'checkout.begateway.com';
        } else {
            $url = 'checkout.begateway.com';
        }

        $order_id = $tc->generate_order_id();

        //$params = array();
        //$params['total'] = $this->total();
        //$params['sid'] = $this->API_Username;
        //$params['cart_order_id'] = $order_id;
        //$params['merchant_order_id'] = $order_id;
        //$params['return_url'] = $tc->get_confirmation_slug(true, $order_id);
        //$params['x_receipt_link_url'] = $tc->get_confirmation_slug(true, $order_id);
        //$params['skip_landing'] = '1';
        //$params['fixed'] = 'Y';
        //$params['currency_code'] = $this->currency;
        //$params['mode'] = '2CO';
        //$params['card_holder_name'] = $this->buyer_info('full_name');
       // $params['email'] = $this->buyer_info('email');

        //if ($this->SandboxFlag == 'sandbox') {
        //    $params['demo'] = 'Y';
        //}

        //$params["li_0_type"] = "product";
        //$params["li_0_name"] = $this->cart_items();
        //$params["li_0_price"] = $this->total();
        //$params["li_0_tangible"] = 'N';

        //$param_list = array();

        //foreach ($params as $k => $v) {
        //    $param_list[] = "{$k}=" . rawurlencode($v);
        //}

        //$param_str = implode('&', $param_list);

        //$paid = false;

        //$payment_info = $this->save_payment_info();

        //$tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);

        //ob_start();
       // @wp_redirect("{$url}?{$param_str}");
        //tc_js_redirect("{$url}?{$param_str}");
        //exit(0);
		//check_ajax_referer( 'begateway-nonce', 'nonce' );
        //$shop_key=$this->API_Username;
		//var_dump($shop_key);
		
		$bgt_settings = get_option('bgt_settings');
        //$this->total();
        //$amount = isset($_POST['amount']) ? $_POST['amount'] : '|';
		//$amount = isset($this->total()) ? $this->total() : '|';
		$amount=$this->total();
		list($dsc, $amount) = explode("|", $amount);

		//$other_amount = isset($this->total()) ? $this->total() : '';
        $other_amount=$this->total();
		$amount = str_replace(',', '.', $amount);
		$amount = strval(floatval($amount));

		$other_amount = str_replace(',', '.', $other_amount);
		$other_amount = strval(floatval($other_amount));

		$currency = $_SESSION['currency'];
		if (empty($currency))
			$currency = (empty($currency) && isset($bgt_settings['currency'])) ? $bgt_settings['currency'] : 'USD';

		if ($other_amount)
			$amount = $other_amount;

		$payment_subject = $_SESSION['payment_subject'];
		if (empty($payment_subject))
		$payment_subject = isset($bgt_settings['payment_subject']) ? $bgt_settings['payment_subject'] : NULL;

		$bp_text = isset($_POST['bp_text'] ) ? ' '.$_POST['bp_text'] : NULL;
		$dsc = empty($dsc) ? NULL : $dsc;

		if($payment_subject || $bp_text || $dsc) {
			$bp_text = array($payment_subject, $bp_text, $dsc);
			$bp_text = array_filter( $bp_text, 'strlen' );
			$bp_text = implode('. ', $bp_text);
		} else {
			$bp_text = __('No Description');
		}

		$lang = $_SESSION['language'];
		if (empty($lang))
		$lang = substr(get_bloginfo('language'), 0, 2);

		if(!$lang) {$lang = 'en';}

		$shop_id = $_SESSION['shop_id'];
		if (empty($shop_id))
		$shop_id = $bgt_settings['shop_id'];

		$shop_key = $_SESSION['shop_key'];
		if (empty($shop_key))
		$shop_key = $bgt_settings['shop_key'];
         
		$checkout_base = $_SESSION['checkout_base'];
		if (empty($checkout_base))
		$checkout_base = $bgt_settings['checkout_base'];

		$notification_url = $_SESSION['notification_url'];
		$cancel_url = $_SESSION['cancel_url'];
		$fail_url = $_SESSION['fail_url'];
		$decline_url = $_SESSION['decline_url'];
		$success_url = $_SESSION['success_url'];
         
		if (empty($notification_url))
		$notification_url = esc_url($bgt_settings['notification_url']);

		if (empty($cancel_url))
		$cancel_url = $bgt_settings['cancel_url'] ? esc_url($bgt_settings['cancel_url']) : get_site_url();
		if (empty($fail_url))
			$fail_url = $bgt_settings['fail_url'] ? esc_url($bgt_settings['fail_url']) : get_site_url();
		if (empty($decline_url))
		$decline_url = $bgt_settings['decline_url'] ? esc_url($bgt_settings['decline_url']) : get_site_url();
		if (empty($success_url))
			$success_url = $bgt_settings['success_url'] ? esc_url($bgt_settings['success_url']) : get_site_url();

		$card = $_SESSION['card'];
		$erip = $_SESSION['erip'];
		$erip_service_no = $_SESSION['erip_service_no'];

		if (empty($card))
			$card = $bgt_settings['card'] ? $bgt_settings['card'] : '';
		if (empty($erip))
			$erip = $bgt_settings['erip'] ? $bgt_settings['erip'] : '';

		if (empty($erip_service_no))
			$erip_service_no = $bgt_settings['erip_service_no'] ? $bgt_settings['erip_service_no'] : '';
          // var_dump('shop_id:'.$shop_id.';shop_key:'.$shop_key.';amount:'.$amount);
		   //'\lib\beGateway\lib\beGateway.php');
		if (!class_exists('beGateway')) {
			require_once dirname(  __FILE__  ) . '/lib/beGateway.php';
		}

		\beGateway\Settings::$shopId  = $shop_id;
		\beGateway\Settings::$shopKey = $shop_key;
		\beGateway\Settings::$checkoutBase = 'https://' . $checkout_base;
         
		$transaction = new \beGateway\GetPaymentToken;
		$transaction->money->setAmount($amount);
		$transaction->money->setCurrency($currency);
		$transaction->setDescription($bp_text);
		$transaction->setLanguage($lang);
		
		$transaction->setTrackingId($order_id);//$order_id 
		var_dump($tc->get_confirmation_slug(true, $order_id));

		if ($notification_url)
			$transaction->setNotificationUrl($notification_url);

		$transaction->setSuccessUrl($tc->get_confirmation_slug(true, $order_id));
		$transaction->setDeclineUrl($tc->get_confirmation_slug(true, $order_id));
		$transaction->setFailUrl($tc->get_confirmation_slug(true, $order_id));
		$transaction->setCancelUrl($tc->get_confirmation_slug(true, $order_id));
		//var_dump($shop_id.'-'.$shop_key.'-'.$order_id);
		if (!isset($bgt_settings['personal_details']))
			$transaction->setAddressHidden();

		if (!empty($card)) {
			$cc = new \beGateway\PaymentMethod\CreditCard;
			$transaction->addPaymentMethod($cc);
		}

		if (!empty($erip)) {
			$order_id = rand(10000, 500000);
			$erip = new \beGateway\PaymentMethod\Erip(array(
			'order_id' => $order_id,
			'account_number' => strval($order_id),
			'service_no' => $erip_service_no,
        'service_info' => array($bp_text)
		));
		$transaction->addPaymentMethod($erip);
		}
		
		
		$response = $transaction->submit();

		if ($response->isSuccess() ) {
			echo json_encode(array(
				//'message' => $response->getMessage(),
				//'status' => 'ok',
				//'token' => $response->getToken(),
			//'gourl' => $response->getRedirectUrl()			
		));
		   $paid = false;

           $payment_info = $this->save_payment_info();

           $tc->create_order($order_id, $this->cart_contents(), $this->cart_info(), $payment_info, $paid);
		 @wp_redirect($response->getRedirectUrl());
		 tc_js_redirect($response->getRedirectUrl());
        exit(0);
		} else {
        echo json_encode(array(
                               'message' => '<div class="error">'. __('Error to acquire a payment token', 'begateway-payment').'</div>',
                               'status' => ''
                               ));
		}

		exit;
    }

    function order_confirmation($order, $payment_info = '', $cart_info = '') {
        global $tc;

        $total = $_REQUEST['total'];
          // var_dump('order_confirmation');
		  // var_dump('total'.$total);
        $hashSecretWord = $this->get_option('secret_word', '', 'bepaid'); //2Checkout Secret Word
        $hashSid = $this->get_option('sid', '', 'bepaid');
        $hashTotal = $total; //Sale total to validate against
        $hashOrder = $_REQUEST['order_number']; //2Checkout Order Number
         var_dump('hashSecretWord'.$hashSecretWord );
        if ($this->SandboxFlag == 'sandbox') {
            $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . 1 . $hashTotal));
        } else {
            $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));
        }

        if ($StringToHash != $_REQUEST['key']) {
            $tc->update_order_status($order->ID, 'order_fraud');
        } else {
            $paid = true;
            $order = tc_get_order_id_by_name($order);
            $tc->update_order_payment_status($order->ID, true);
        }

        $this->ipn();
    }

    function gateway_admin_settings($settings, $visible) {
        global $tc;
        ?>
        <div id="<?php echo esc_attr($this->plugin_name); ?>" class="postbox" <?php echo (!$visible ? 'style="display:none;"' : ''); ?>>
            <h3 class='hndle'><span><?php printf(__('%s Settings', 'tc'), $this->admin_name); ?></span></h3>
            <div class="inside">
                <span class="description">
                    <?php echo sprintf(__('Sell your tickets via <a target="_blank" href="%s">bepaid.by</a>', 'tc'), "bepaid.by"); ?>
                </span>

                <?php
                $fields = array(
                    'mode' => array(
                        'title' => __('Mode', 'tc'),
                        'type' => 'select',
                        'options' => array(
                            'sandbox' => __('Sandbox / Test', 'tc'),
                            'live' => __('Live', 'tc')
                        ),
                        'default' => 'sandbox',
                    ),
                    'sid' => array(
                        'title' => __('Seller ID', 'tc'),
                        'type' => 'text',
                        'description' => sprintf(__('Login to your bepaid dashboard to obtain the seller ID and secret word. <a target="_blank" href="%s">Instructions &raquo;</a>', 'tc'), "http://bepaid.by/"),
                    ),
                    'secret_word' => array(
                        'title' => __('Secret Word', 'tc'),
                        'type' => 'text',
                        'description' => '',
                        'default' => 'tango'
                    ),
                    'currency' => array(
                        'title' => __('Currency', 'tc'),
                        'type' => 'select',
                        'options' => $this->currencies,
                        'default' => 'USD',
                    ),
                );
                $form = new TC_Form_Fields_API($fields, 'tc', 'gateways', '2checkout');
                ?>
                <table class="form-table">
                    <?php $form->admin_options(); ?>
                </table>
            </div>
        </div>
        <?php
    }

    function ipn() {
        global $tc;
         
           
           
		 
       // if (isset($_REQUEST['message_type']) && $_REQUEST['message_type'] == 'INVOICE_STATUS_CHANGED') {
		   if (isset($_REQUEST['uid'])) {
			   //var_dump('ipn1'.$_REQUEST['vendor_order_id']);
            // $sale_id = $_REQUEST['sale_id']; //just for calculating hash
            // $tco_vendor_order_id = $_REQUEST['vendor_order_id']; //order "name"
            // $total = $_REQUEST['invoice_list_amount'];

            // $order_id = tc_get_order_id_by_name($tco_vendor_order_id); //get order id from order name
            // $order_id = $order_id->ID;
            // $order = new TC_Order($order_id);
			
			  //$query = new \beGateway\QueryByUid;
              //$query->setUid($response->getUid());

              //$query_response = $query->submit();

              //print_r($query_response);
			  if (!class_exists('beGateway')) {
					require_once dirname(  __FILE__  ) . '/lib/beGateway.php';
				}
				
				$query = new \beGateway\QueryByUid;
				//\beGateway\Settings::$shopId  = $bgt_settings['shop_id'];
				//\beGateway\Settings::$shopKey = $bgt_settings['shop_key'];
				//\beGateway\Settings::$checkoutBase = 'https://' . $checkout_base;
				\beGateway\Settings::$shopId = 361;
				\beGateway\Settings::$shopKey = 'b8647b68898b084b836474ed8d61ffe117c9a01168d867f24953b776ddcb134d';
				$query->setUid($_REQUEST['uid']);
             

             $query_response = $query->submit();
			 var_dump(intval($query_response->getAmount()));
			 //print_r($query_response->);
			 if ($query_response->isSuccess()) { //|| $response->isFailed()
               //print("Transaction UID: " . $response->getUid() . PHP_EOL);
		         $order_id = tc_get_order_id_by_name($query_response->getTrackingId());
		         $order_id = $order_id->ID;
				 $order = new TC_Order($order_id);
				}

            if (!$order) {
                header('HTTP/1.0 404 Not Found');
                header('Content-type: text/plain; charset=UTF-8');
                echo 'Invoice not found';
                exit;
            }

            //$hash = md5($sale_id . $this->get_option('sid', '', 'bepaid') . $_REQUEST['invoice_id'] . $this->get_option('sid', 'secret_word', 'bepaid'));

           // if ($_REQUEST['md5_hash'] != strtolower($hash)) {
			  // if ($_REQUEST['token'] != $query_response->getToken()) {
			   
                // header('HTTP/1.0 403 Forbidden');
                // header('Content-type: text/plain; charset=UTF-8');
                // echo "bePaid token doesn't match";
                // exit;
            // }

            // if (strtolower($_REQUEST['invoice_status']) != "deposited") {
                // header('HTTP/1.0 200 OK');
                // header('Content-type: text/plain; charset=UTF-8');
                // echo 'Waiting for deposited invoice status.';
                // exit;
            // }

            if (intval(round($query_response->getAmount(), 2)) >= round($order->details->tc_payment_info['total'], 2)) {
                $tc->update_order_payment_status($order_id, true);
                header('HTTP/1.0 200 OK');
                header('Content-type: text/plain; charset=UTF-8');
                echo 'Order completed and verified.';
                exit;
            } else {
                $tc->update_order_status($order_id, 'order_fraud');
                header('HTTP/1.0 200 OK');
                header('Content-type: text/plain; charset=UTF-8');
                echo 'Fraudulent order detected and changed status.';
                exit;
            }
        }
    }

}

tc_register_gateway_plugin('TC_Gateway_BePaid', 'bepaid', __('bepaid', 'tc'));
?>