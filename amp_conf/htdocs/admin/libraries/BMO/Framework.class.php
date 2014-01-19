<?php
// vim: set ai ts=4 sw=4 ft=php:

/**
 * Framework built-in BMO Class.
 */

class Framework extends FreePBX_Helpers implements BMO {
	/** BMO Required Interfaces */
	public function install() {
	}
	public function uninstall() {
	}
	public function backup() {
	}
	public function restore($backup) {
	}
	public function runTests($db) {
		return true;
	}
	public function doConfigPageInit() {
	}

	public function ajaxRequest($req, &$setting) {
		if ($req == "unset" || $req == "pubkey") {
			$setting['authenticate'] = false;
			$setting['allowremote'] = true;
			return true;
		}
		return false; // Returning false, or anything APART from (bool) true will abort the request
	}

	public function ajaxHandler() {
		print "Wibble\n";
	}
}

