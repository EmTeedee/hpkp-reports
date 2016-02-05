<?php

class Utils {

	public static function getCanonicalIPv6($address) {
		// Known prefix
		$v4mapped_prefix_hex = '00000000000000000000ffff';
		$v4mapped_prefix_bin = pack("H*", $v4mapped_prefix_hex);

		// Or more readable when using PHP >= 5.4
		# $v4mapped_prefix_bin = hex2bin($v4mapped_prefix_hex); 

		// Parse
		$addr_bin = inet_pton($address);
		if( $addr_bin === FALSE ) {
		  // Unparsable? How did they connect?!?
		  die('Invalid IP address');
		}

		// Check prefix
		if( substr($addr_bin, 0, strlen($v4mapped_prefix_bin)) == $v4mapped_prefix_bin) {
		  // Strip prefix
		  $addr_bin = substr($addr_bin, strlen($v4mapped_prefix_bin));
		}

		// Convert back to printable address in canonical form
		return inet_ntop($addr_bin);
	}
}