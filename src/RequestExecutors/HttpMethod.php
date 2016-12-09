<?php
namespace OAuth2;

/**
 * Enum of http methods that may be used in an OAuth2 application.
 */
interface HttpMethod {
	const GET = 1;
	const POST = 2;
	const PUT = 3;
	const DELETE = 4;
}