<?php

require('twitter_unfollowers.php');

$tuiter = new Twitter_unfollowers('<id_usuario_twitter>');

$tuiter->check_unfollow();

echo 'fin';

?>
