<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	// identifier: missingType.generics
	'message' => '#^Interface BlitzPHP\\\\Socialite\\\\Contracts\\\\UserInterface extends generic interface ArrayAccess but does not specify its types\\: TKey, TValue$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Contracts/UserInterface.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<socialite\\>, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Facades/Socialite.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<request\\>, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/SocialiteManager.php',
];
$ignoreErrors[] = [
	// identifier: argument.type
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<session\\>, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Two/AbstractProvider.php',
];
$ignoreErrors[] = [
	// identifier: cast.string
	'message' => '#^Cannot cast phpseclib3\\\\Crypt\\\\Common\\\\PrivateKey\\|phpseclib3\\\\Crypt\\\\Common\\\\PublicKey to string\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Two/FacebookProvider.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
