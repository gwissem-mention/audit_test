<?
function removeDirectory($path){
        $files = glob($path . '/*');
	foreach ($files as $file) {
		is_dir($file) ? removeDirectory($file) : unlink($file);
	}
	rmdir($path);
 	return;
}

$path = "/var/www/html/virtualdomains/15124/monhopitalnumerique.fr/www/preprod/www/app/logs";

removeDirectory($path);


?>
