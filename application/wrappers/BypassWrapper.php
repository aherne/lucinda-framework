<?php
/**
 * Implements a view resolver that does nothing. Useful when we need a ResponseListener to start a fresh rendering.
 */
class BypassWrapper extends Wrapper {
	public function run() {
		echo "";
	}
}