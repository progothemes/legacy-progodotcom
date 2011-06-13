<?php
	/**
	 * The Transaction Results Theme.
	 *
	 * Displays everything within transaction results.  Hopefully much more useable than the previous implementation.
	 *
	 * @package WPSC
	 * @since WPSC 3.8
	 */

	global $wpsc_query, $purchase_log, $wpdb, $errorcode, $sessionid, $echo_to_screen, $cart, $message_html,$wpsc_purchlog_statuses;
?>
<div class="wrap">

<?php
//	echo wpsc_transaction_theme();
// overwrite the default wpsc_transaction_theme...
	global $user_ID, $nzshpcrt_gateways, $sessionid, $cart_log_id, $errorcode;
	$errorcode = '';
	$transactid = '';
	echo '<!-- check1 -->';
	$dont_show_transaction_results = false;
	if ( isset( $_GET['sessionid'] ) )
		$sessionid = $_GET['sessionid'];

	if ( !isset( $_GET['sessionid'] ) && isset( $_GET['ms'] ) )
		$sessionid = $_GET['ms'];

	if ( isset( $_GET['gateway'] ) && 'google' == $_GET['gateway'] ) {
		wpsc_google_checkout_submit();
		unset( $_SESSION['wpsc_sessionid'] );
	}

	if ( 'paypal_certified' == $_SESSION['wpsc_previous_selected_gateway'] )
		$sessionid = $_SESSION['paypalexpresssessionid'];

	if ( isset( $_REQUEST['eway'] ) && '1' == $_REQUEST['eway'] )
		$sessionid = $_GET['result'];
	elseif ( isset( $_REQUEST['eway'] ) && '0' == $_REQUEST['eway'] )
		echo $_SESSION['eway_message'];
	elseif ( isset( $_REQUEST['payflow'] ) && '1' == $_REQUEST['payflow'] ){
		echo $_SESSION['payflow_message'];
		$_SESSION['payflow_message'] = '';
	}
	// Replaces the ugly if else for gateways
	switch($_SESSION['wpsc_previous_selected_gateway']){
		case 'paypal_certified':
		case 'wpsc_merchant_paypal_express':
			echo $_SESSION['paypalExpressMessage'];
			if(isset($_SESSION['reshash']['TRANSACTIONTYPE']) && 'expresscheckout' == $_SESSION['reshash']['TRANSACTIONTYPE'])
				$dont_show_transaction_results = false;
			else
				$dont_show_transaction_results = true;		
		break;
		case 'dps':
			$sessionid = decrypt_dps_response();
		break;
	}
	echo '<!-- check2 -->';
	if(!$dont_show_transaction_results ) {
	echo '<!-- check3 -->';
		if ( !empty($sessionid) ){
	echo '<!-- check4 -->';
			$cart_log_id = $wpdb->get_var( "SELECT `id` FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= " . $sessionid . " LIMIT 1" );
			//return transaction_results( $sessionid, true );
			// Do we seriously need this many globals?
			global $wpsc_cart, $echo_to_screen, $order_url; 
			global $wpsc_gateways;
			
			$wpec_taxes_controller = new wpec_taxes_controller();
			$is_transaction = false;
			$errorcode = 0;
			$purchase_log = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= " . $sessionid . " LIMIT 1", ARRAY_A );
			$order_status = $purchase_log['processed'];
			$curgateway = $purchase_log['gateway'];
			//new variable to check whether function is being called from resen_email
			if(isset($_GET['email_buyer_id']))
				$resend_email = true;
			else
				$resend_email = false;
				
			if( !is_bool( $display_to_screen )  )
				$display_to_screen = true;
				
			$echo_to_screen = $display_to_screen;
		
	echo '<!-- check1 -->';
	echo '<pre style="display:none">'. print_r($purchase_log,true) .'</pre>';
			if ( is_numeric( $sessionid ) ) {
				if ( $echo_to_screen )
					echo apply_filters( 'wpsc_pre_transaction_results', '' );
				
				// New code to check whether transaction is processed, true if accepted false if pending or incomplete
				$is_transaction = wpsc_check_purchase_processed($purchase_log['processed']);
				$message_html = $message = stripslashes( get_option( 'wpsc_email_receipt' ) );
			
				if( $is_transaction ){
					$message = "<strong>Your order has been completed successfully</strong>\r\n".$message;
					$message_html = "<strong>Your order has been completed successfully</strong><br />This is your official order confirmation. You will also receive an email confirmation. ".$message_html;
				}
		
				if ( !empty($purchase_log['shipping_country']) ) {
					$billing_country = $purchase_log['billing_country'];
					$shipping_country = $purchase_log['shipping_country'];
				} else {
					$country = $wpdb->get_var( "SELECT `value` FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `log_id`=" . $purchase_log['id'] . " AND `form_id` = '" . get_option( 'country_form_field' ) . "' LIMIT 1" );
								
					$billing_country = $country;
					$shipping_country = $country;
				}
				// BILLING INFO
				
				// no SHIPPING here so skip to ORDER SUMMARY...
		
				$email = wpsc_get_buyers_email($purchase_log['id']);
				$previous_download_ids = array( );
				$product_list = $product_list_html = $report_product_list = '';
			
				$cart = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid` = '{$purchase_log['id']}'" , ARRAY_A );
				if ( ($cart != null) && ($errorcode == 0) ) {
					$total_shipping = '';
					$product_list = '<table class="wpsc_checkout_table"><tr class="firstrow"><td colspan="4">Order Summary</td></tr><tr class="subh product_row"><td width="10%">Qty</td><td width="60%">Product Title</td><td width="15%" align="right">Price</td><td width="15%" align="right">Total</td></tr>';
					$product_list_html = '<table class="wpsc_checkout_table"><tr class="firstrow"><td colspan="4"><h4>Order Summary #'. $sessionid .'</h4></td></tr><tr class="subh product_row"><td width="10%"><strong>Qty</strong></td><td width="60%"><strong>Product Title</strong></td><td width="15%" align="right"><strong>Price</strong></td><td width="15%" align="right"><strong>Total</strong></td></tr>';
					foreach ( $cart as $row ) {
						$link = array( );
						if ( $purchase_log['email_sent'] != 1 )
							$wpdb->update(WPSC_TABLE_DOWNLOAD_STATUS, array('active' => '1'), array('cartid' => $row['id'], 'purchid'=>$purchase_log['id']) );
						do_action( 'wpsc_transaction_result_cart_item', array( "purchase_id" => $purchase_log['id'], "cart_item" => $row, "purchase_log" => $purchase_log ) );
		
						if ( $is_transaction ) {
		
							$download_data = $wpdb->get_results( "SELECT *
							FROM `" . WPSC_TABLE_DOWNLOAD_STATUS . "`
							WHERE `active`='1'
							AND `purchid`='" . $purchase_log['id'] . "'
							AND `cartid` = '" . $row['id'] . "'", ARRAY_A );
		
							if ( count( $download_data ) > 0 ) {
								foreach ( $download_data as $single_download ) {
									$file_data = get_post( $single_download['product_id'] );
									// if the uniqueid is not equal to null, its "valid", regardless of what it is
									if ( $single_download['uniqueid'] == null )
										$link[] = array( "url" => site_url( "?downloadid=" . $single_download['id'] ), "name" => $file_data->post_title );
									else
										$link[] = array( "url" => site_url( "?downloadid=" . $single_download['uniqueid'] ), "name" => $file_data->post_title );
									
								}
							} else {
								$order_status = $purchase_log['processed'];
							}
							$previous_download_ids[] = $download_data['id'];
						}
		
						do_action( 'wpsc_confirm_checkout', $purchase_log['id'] );
		
						$total = 0;
						$shipping = $row['pnp'] * $row['quantity'];
						$total_shipping += $shipping;
		
						$total += ( $row['price'] * $row['quantity']);
						$message_price = wpsc_currency_display( $total, array( 'display_as_html' => false ) );
						$message_price_html = wpsc_currency_display( $total );
						$shipping_price = wpsc_currency_display( $shipping, array( 'display_as_html' => false ) );
		
						if ( isset( $purchase['gateway'] ) && 'wpsc_merchant_testmode' != $purchase['gateway'] ) {
							if ( $gateway['internalname'] == $purch_data[0]['gateway'] )
								$gateway_name = $gateway['name'];
						} else {
							$gateway_name = "Manual Payment";
						}
		
						$variation_list = '';
						/* HANDLEPROGOKEYHERE */
						$additional_content = apply_filters( 'wpsc_transaction_result_content', array( "purchase_id" => $purchase_log['id'], "cart_item" => $row, "purchase_log" => $purchase_log ) );
						if ( !is_string( $additional_content ) ) {
							$additional_content = '';
						}
						if ( !empty( $link ) ) {
							$product_list .= "<tr class=\"product_row\"><td>$row[quantity]</td><td>$row[name]<br />" . __( 'Click to download', 'wpsc' ) . ":";
							$product_list_html .= "<tr class=\"product_row\"><td>$row[quantity]</td><td>$row[name]<br />" . __( 'Click to download', 'wpsc' ) . ":\n\r";
							foreach ( $link as $single_link ) {
								$product_list .= "\n\r " . $single_link["name"] . ": " . $single_link["url"] . "\n\r";
								$product_list_html .= "<a href='" . $single_link["url"] . "'>" . $single_link["name"] . "</a>\n";
							}
							$product_list .= "$additional_content</td><td align='right'>$message_price</td><td align='right'>$message_price</td></tr>";
							$product_list_html .= "$additional_content</td><td align='right'>$message_price</td><td align='right'>$message_price</td></tr>";
						} else {
						
							$product_list.= "<tr class=\"product_row\"><td>$row[quantity]</td><td>$row[name]<br />$additional_content</td><td>$message_price</td><td>$total</td></tr>";
							$product_list_html.= "<tr class=\"product_row\"><td>$row[quantity]</td><td>$row[name]<br />$additional_content</td><td>$message_price</td><td>$total</td></tr>";
							/*
							if ( $shipping > 0 ) {
								$product_list .= sprintf(__( ' - Shipping: %s
		', 'wpsc' ), $shipping_price);
								$product_list_html .=  sprintf(__( ' &nbsp; Shipping: %s
		', 'wpsc' ), $shipping_price);
							}
		*/
						}
		/*
						//add tax if included
						if($wpec_taxes_controller->wpec_taxes_isenabled() && $wpec_taxes_controller->wpec_taxes_isincluded())
						{
							$taxes_text = ' - - '.__('Tax Included', 'wpsc').': '.wpsc_currency_display( $row['tax_charged'], array( 'display_as_html' => false ) )."\n\r";
							$taxes_text_html = ' - - '.__('Tax Included', 'wpsc').': '.wpsc_currency_display( $row['tax_charged'] );
							$product_list .= $taxes_text;
							$product_list_html .= $taxes_text_html;
						}// if
		*/
						$report = get_option( 'wpsc_email_admin' );
						$report_product_list.= " - " . $row['quantity'] . " " . $row['name'] . "  " . $message_price . "\n\r";
					} // closes foreach cart as row
		
							$product_list .= "</table>";
							$product_list_html .= "</table>";
					// Decrement the stock here
					if ( $is_transaction )
						wpsc_decrement_claimed_stock( $purchase_log['id'] );
		
					if ( !empty($purchase_log['discount_data'])) {
						$coupon_data = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_COUPON_CODES . "` WHERE coupon_code='" . $wpdb->escape( $purchase_log['discount_data'] ) . "' LIMIT 1", ARRAY_A );
						if ( $coupon_data['use-once'] == 1 ) {
							$wpdb->update(WPSC_TABLE_COUPON_CODES, array('active' => '0', 'is-used' => '1'), array('id' => $coupon_data['id']) );
						}
					}
		
					$total_shipping += $purchase_log['base_shipping'];
		
					$total = $purchase_log['totalprice'];
					
					$total_price_email = '';
					$total_price_html = '<table class="wpsc_checkout_table totals">';
					$total_tax_html = '';
					$total_tax = '';
					$total_shipping_html = '';
					$total_shipping_email = sprintf('Total Shipping: %s', wpsc_currency_display( $total_shipping, array( 'display_as_html' => false ) ) );
					if ( $purchase_log['discount_value'] > 0 ) {
						$discount_email.= __( 'Discount', 'wpsc' ) . "\n\r: ";
						$discount_email .=$purchase_log['discount_data'] . ' : ' . wpsc_currency_display( $purchase_log['discount_value'], array( 'display_as_html' => false ) ) . "\n\r"; 
						
						$report.= $discount_email . "\n\r";
						$total_shipping_email .= $discount_email;
						$total_shipping_html .= '<tr class="addon"><td width="70%"></td><td width="15%" align="right">'. __('Discount', 'wpsc') .'</td><td width="15%" align="right"><span id="coupons_amount" class="pricedisplay">'. wpsc_currency_display( $purchase_log['discount_value'] ) .'</span></td></tr>';
						//__( 'Discount', 'wpsc' ) . ": " . wpsc_currency_display( $purchase_log['discount_value'] ) . "\n\r";
					}
		
					//only show total tax if tax is not included
					if($wpec_taxes_controller->wpec_taxes_isenabled() && !$wpec_taxes_controller->wpec_taxes_isincluded()){
						$total_tax_html .= '<tr class="addon total_tax"><td width="70%"></td><td align="right" width="15%">'. __('Total Tax', 'wpsc').'</td><td width="15%" align="right"><span id="checkout_tax" class="pricedisplay checkout-tax">'. wpsc_currency_display( $purchase_log['wpec_taxes_total'] )."</span></td></tr>";
						$total_tax .= __('Total Tax', 'wpsc').': '. wpsc_currency_display( $purchase_log['wpec_taxes_total'] , array( 'display_as_html' => false ) )."\n\r"; 		
					}
					$total_shipping_html.= '<tr class="addon total_shipping"><td width="70%"></td><td width="15%" align="right">'. __( 'Total Shipping', 'wpsc' ) .'</td><td width="15%" align="right"><span id="checkout_shipping" class="pricedisplay checkout-shipping">'. wpsc_currency_display( $total_shipping ) .'</span></td></tr>';
					
					$total_price_email .= sprintf(__( 'Total: %s
		', 'wpsc' ), wpsc_currency_display( $total, array( 'display_as_html' => false ) ));
		
					$total_price_html .= $total_shipping_html . $total_tax_html;
					$total_price_html .= '<tr><td width="70%"></td><td width="15%" align="right">Grand Total</td><td width="15%" align="right"><span id="checkout_total" class="pricedisplay checkout-total">'. wpsc_currency_display( $total ) .'</span></td></tr></table>';
					
					// total_price includes shipping & tax so
					$total_shipping_email = $total_shipping_html = $total_tax_html = $total_tax = '';
					
					// fix this too #needswork
					$report_id = sprintf(__("Purchase # %s
		", 'wpsc'), $purchase_log['id']);
		
					
					
					if ( isset( $_GET['ti'] ) ) {
						$message.= "\n\r" . __( 'Your Transaction ID', 'wpsc' ) . ": " . $_GET['ti'];
						$message_html.= "\n\r" . __( 'Your Transaction ID', 'wpsc' ) . ": " . $_GET['ti'];
						$report.= "\n\r" . __( 'Transaction ID', 'wpsc' ) . ": " . $_GET['ti'];
					} 
					$message = str_replace( '%purchase_id%', $report_id, $message );
					$message = str_replace( '%product_list%', $product_list, $message );
					$message = str_replace( '%total_tax%', $total_tax, $message );
					$message = str_replace( '%total_shipping%', $total_shipping_email, $message );
					$message = str_replace( '%total_price%', $total_price_email, $message );
					$message = str_replace( '%shop_name%', get_option( 'blogname' ), $message );
					$message = str_replace( '%find_us%', $purchase_log['find_us'], $message );
		
					$report = str_replace( '%purchase_id%', $report_id, $report );
					$report = str_replace( '%product_list%', $report_product_list, $report );
		
					$report = str_replace( '%total_tax%', $total_tax, $report );
					$report = str_replace( '%total_shipping%', $total_shipping_email, $report );
					$report = str_replace( '%total_price%', $total_price_email, $report );
					$report = str_replace( '%shop_name%', get_option( 'blogname' ), $report );
					$report = str_replace( '%find_us%', $purchase_log['find_us'], $report );
		
					$message_html = str_replace( '%purchase_id%', $report_id, $message_html );
					$message_html = str_replace( '%product_list%', $product_list_html, $message_html );
					$message_html = str_replace( '%total_tax%', $total_tax_html, $message_html );
					$message_html = str_replace( '%total_shipping%', $total_shipping_html, $message_html );
					$message_html = str_replace( '%total_price%', $total_price_html, $message_html );
					$message_html = str_replace( '%shop_name%', get_option( 'blogname' ), $message_html );
					$message_html = str_replace( '%find_us%', $purchase_log['find_us'], $message_html );
		
					if ( !empty($email) && ($purchase_log['email_sent'] != 1 || $resend_email) ) {
						$wpdb->update(WPSC_TABLE_PURCHASE_LOGS, array('email_sent' => '1'), array('id' => $purchase_log['id']) );
						add_filter( 'wp_mail_from', 'wpsc_replace_reply_address', 0 );
						add_filter( 'wp_mail_from_name', 'wpsc_replace_reply_name', 0 );
		
						if ( !$is_transaction ) {
			
							$payment_instructions = strip_tags( stripslashes( get_option( 'payment_instructions' ) ) );
							if(!empty($payment_instructions))
								$payment_instructions .= "\n\r";					
							$message = __( 'Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc' ) . "\n\r" . $payment_instructions . $message;
							$message_html = __( 'Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc' ) . "\n\r" . $payment_instructions . $message_html;
							
							wp_mail( $email, __( 'Order Pending: Payment Required', 'wpsc' ), $message );
						} else {
							wp_mail( $email, __( 'Purchase Receipt', 'wpsc' ), $message );
						}
					}
		
					remove_filter( 'wp_mail_from_name', 'wpsc_replace_reply_name' );
					remove_filter( 'wp_mail_from', 'wpsc_replace_reply_address' );
		
					$report_user = __( 'Customer Details', 'wpsc' ) . "\n\r";
					$form_sql = "SELECT * FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `log_id` = '" . $purchase_log['id'] . "'";
					$form_data = $wpdb->get_results( $form_sql, ARRAY_A );
					
					if ( $form_data != null ) {
						foreach ( $form_data as $form_field ) {
							$form_data = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `id` = '" . $form_field['form_id'] . "' LIMIT 1", ARRAY_A );
				
							switch ( $form_data['type'] ) {
								case "country":
									$country_code = $form_field['value'];
									$report_user .= $form_data['name'] . ": " . wpsc_get_country( $country_code ) . "\n";
									//check if country has a state then display if it does.
									$country_data = wpsc_country_has_state($country_code);
									if(($country_data['has_regions'] == 1))
										$report_user .= __( 'Billing State', 'wpsc' ) . ": " . wpsc_get_region( $purchase_log['billing_region'] ) . "\n";
									break;
		
								case "delivery_country":
									$report_user .= $form_data['name'] . ": " . wpsc_get_country( $form_field['value'] ) . "\n";			
									break;
							
								default:
									if ($form_data['name'] == 'State' && is_numeric($form_field['value'])){
										$report_user .= __( 'Delivery State', 'wpsc' ) . ": " . wpsc_get_state_by_id( $form_field['value'], 'name' ) . "\n";
									}else{
									$report_user .= wp_kses( $form_data['name'], array( ) ) . ": " . $form_field['value'] . "\n";
									}
									break;
							}
						}
					}
		
					$report_user .= "\n\r";
					$report = $report_id . $report_user . $report;
		
					//echo '======REPORT======<br />'.$report.'<br />';
					//echo '======EMAIL======<br />'.$message.'<br />';
					if ( (get_option( 'purch_log_email' ) != null) && ($purchase_log['email_sent'] != 1) )
						wp_mail( get_option( 'purch_log_email' ), __( 'Purchase Report', 'wpsc' ), $report );
		
					/// Adjust stock and empty the cart
					$wpsc_cart->submit_stock_claims( $purchase_log['id'] );
					$wpsc_cart->empty_cart();
				}
			}
			
		}else
		_e( 'Sorry your transaction was not accepted.<br /><a href=' . get_option( "shopping_cart_url" ) . '>Click here to go back to checkout page.</a>' );
	}

	if ( ( true === $echo_to_screen ) && ( $cart != null ) && ( $errorcode == 0 ) && ( $sessionid != null ) ) {			
		
		// Code to check whether transaction is processed, true if accepted false if pending or incomplete
		
		
		echo "<br />" . nl2br(str_replace("$",'\$',$message_html));						
	}elseif ( true === $echo_to_screen && ( !isset($purchase_log) ) ) {
			_e('Oops, there is nothing in your cart.', 'wpsc') . "<a href=".get_option("product_list_url").">" . __('Please visit our shop', 'wpsc') . "</a>";
	}
?>	
	
</div>