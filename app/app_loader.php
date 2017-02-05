<?php
$base = __DIR__ . '/../app/';

$folders = [
    'lib',
    'app'
];


foreach($folders as $k => $f)
{
	if(!is_array($f)) {
		foreach ( glob( $base . "$f/*.php" ) as $k => $filename )
		{
			require $filename;
		}
	} else {
		foreach ($f as $value) {
			foreach ( glob($base . "$k/$value/*.php") as $key => $file )
			{
				require $file;
			}
		}		
	}
}