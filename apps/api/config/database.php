<?php
return [
	'default'     => 'mongodb',
	'connections' => [
		'mongodb' => [
			'driver'   => 'mongodb',
			'host'     => env( 'DB_HOST_MONGO', 'localhost' ),
			'port'     => env( 'DB_PORT_MONGO', 27017 ),
			'database' => env( 'DB_DATABASE_MONGO' ),
			'username' => env( 'DB_USERNAME_MONGO' ),
			'password' => env( 'DB_PASSWORD_MONGO' ),
			'options'  => [ 'connectTimeoutMS' => 50 ],
		],
	],
	'migrations'  => 'migrations',
];
