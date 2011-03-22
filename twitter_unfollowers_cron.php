<?php

header('Content-Type: text/plain; charset=utf-8');

require('twitter_unfollowers.php');

$tuiter = new Twitter_unfollowers('<id_usuario_twitter>');

if($unfollowers = $tuiter->check_unfollow())
{
	print_r($unfollowers);
}

echo 'fin';

?>
