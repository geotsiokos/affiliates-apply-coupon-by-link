<?php
/**
 * Plugin Name: Affiliates Apply Coupon by Link
 * Plugin URI: http://www.netpad.gr
 * Description: Adds affiliate coupon automatically when affiliate link is used
 * Version: 1.0.0
 * Author: gtsiokos
 * Author URI: http://www.netpad.gr
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (c) 2015-2016 "gtsiokos" George Tsiokos www.netpad.gr
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @author gtsiokos
 * @package affiliates-apply-coupon-by-link
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'plugins_loaded', 'affiliates_apply_coupon_by_link' );
function affiliates_apply_coupons_by_link() {
	if ( defined( 'AFFILIATES_CORE_VERSION' )  ) {
		if ( !class_exists( class_exists( 'Affiliates_Service' ) ) ) {
			require_once AFFILIATES_CORE_LIB . '/class-affiliates-service.php';
		}
		add_action( 'woocommerce_before_calculate_totals', 'apply_affiliate_coupon' );
	}
}

function apply_affiliate_coupon( $cart ) {
	global $affiliates_db;

	error_log( print_r( $cart, true ) );

	if ( Affiliates_Service::get_referrer_id() ) {
		$affiliate_id = Affiliates_Service::get_referrer_id();
		$affiliates_attributes_table = $affiliates_db->get_tablename( 'affiliates_attributes' );
		$affiliates_attributes_coupons = $affiliates_db->get_objects( "SELECT attr_value FROM $affiliates_attributes_table WHERE affiliate_id = %d AND attr_key = 'coupons' ", $affiliate_id );
		error_log( print_r( $affiliates_attributes_coupons, true ) );
		$coupon_codes = explode( ',', $affiliates_attributes_coupons[0]->attr_value );
		$applied_coupons = $cart->get_applied_coupons();

		if ( !in_array( $coupon_codes[0], $applied_coupons ) ) {
			$added_coupon = $cart->add_discount( $coupon_codes[0] );
			if ( $added_coupon ) {
				wc_clear_notices();
				$wc_notice = 'Affiliate Coupon code <strong>' . $coupon_codes[0] . '</strong> has been automatically added because you followed an affiliate link';
				wc_add_notice( $wc_notice, 'notice' );
			}
		}
	}
}