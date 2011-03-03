<?php

/**
 * Script para obtener los usuarios que te han hecho unfollow en Twitter
 * Detalles en http://www.usuariodeinternet.es/desarrollo/twitter-unfollowers
 * @author Fernando García <http://www.usuariodeinternet.es>
 * @version 1.0
 */

require('lib/twitteroauth.php');

// INICIO PERSONALIZACIÓN
// nombre del usuario de Twitter que se monitoriza
define('TWITTER_USER', 'xxxx');
// claves de la aplicación de Twitter
define('CONSUMER_KEY', 'yyyy');
define('CONSUMER_SECRET', 'zzzz');
define('OAUTH_TOKEN', 'uuuu');
define('OAUTH_TOKEN_SECRET', 'vvvv');
// fichero donde se guardan los followers para posteriormente compararlos con los actuales
define('FIC_GUARDADO', 'twitter_followers.db');
// poner a 0 si no quieres recibir un email con la lista de unfollowers
define('ENVIAR_EMAIL', '1');
define('EMAIL_DIRECCION', 'mmmm@nnnn.com');
define('EMAIL_ASUNTO', '[TWITTER] Te ha(n) desfollogüeado {NUM} usuario(s)');
// debería ser una dirección válida en el servidor donde se ejecuta el script para que el email no se vaya al spam
define('EMAIL_REMITENTE', 'iiii@jjjj.com');
// FIN PERSONALIZACIÓN
 
/**
 * Clase para obtener los followers e identificar los unfollowers de Twitter
 */
class Twitter_unfollowers
{
	private $conexion_twitter;
	  
	public function __construct()
	{
		$this->conexion_twitter = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);
		if(!$this->conexion_twitter)
		{
			exit('error de conexión con twitter');
		}
	}
	
	/**
	 * Comprueba los followers actuales con los almacenados para identificar si hay unfollows
	 * @return array|bool ids de unfollowers o false si no hay cambios
	 */
	public function check_unfollow()
	{
		$lista_followers_guardada = $this->get_followers_guardados();
		$lista_followers = $this->get_followers();
		
		$num_followers = count($lista_followers);
		$num_followers_guardados = count($lista_followers_guardada);
			
		$ultimo_follower = $lista_followers[0];
		$ultimo_follower_guardado = $lista_followers_guardada[0];
			
		if(($ultimo_follower != $ultimo_follower_guardado) || ($num_followers != $num_followers_guardados))
		{
			$lista_unfollowers = array_diff($lista_followers_guardada, $lista_followers);
			
			if(!empty($lista_unfollowers))
			{
				if(ENVIAR_EMAIL)
				{
					$this->enviar_email($lista_unfollowers);
				}
					
				return $lista_unfollowers;
			}
		}
		
		return false;
	}
	
	/**
	 * Obtiene mediante la API de Twitter los followers actuales
	 * @return array ids de los followers actuales
	 */
	private function get_followers()
	{
		$lista_followers = $this->conexion_twitter->get('http://api.twitter.com/1/followers/ids.json?screen_name=' . TWITTER_USER);

		// no lo comprobamos con empty u otros porque puede que hayas perdido todos los followers y se interprete como un error
		if(!is_null($lista_followers))
		{
			file_put_contents(FIC_GUARDADO, json_encode($lista_followers));
			return $lista_followers;
		}
		else
		{
			exit('error de parseo de followers');
		}
	}
	
	/**
	 * Obtiene del fichero los followers almacenados en una ejecución anterior del script
	 * @return array ids de los followers almacenados
	 */
	private function get_followers_guardados()
	{
		$lista_followers_json = @file_get_contents(FIC_GUARDADO);
		return json_decode($lista_followers_json);
	}
	
	/**
	 * Obtiene información del perfil de un usuario a través de la API de Twitter
	 * @param int $user_id
	 * @return array datos del usuario especificado 
	 */
	private function get_user_info($user_id)
	{
		$user_info = $this->conexion_twitter->get('http://api.twitter.com/1/users/show.json?user_id=' . $user_id);
		return array('nombre' => $user_info->screen_name,
			'imagen' => $user_info->profile_image_url,
			'following' => $user_info->following
			);
	}

	/**
	 * Envia un email con el listado de unfollowers
	 * @param array $lista_unfollowers ids de los usuarios
	 */
	private function enviar_email($lista_unfollowers)
	{
		$unfollowers_info = array();
		
		foreach($lista_unfollowers as $unfollower_id)
		{
			$unfollowers_info[] = $this->get_user_info($unfollower_id);
		}
		
		$email_asunto = str_replace('{NUM}', count($lista_unfollowers), EMAIL_ASUNTO);
		
		$email_cuerpo = 'Te han desfollogüeado brutalmente los siguientes usuarios:<br/><br/><table cellspancing="6" cellpadding="6" border="1">';
		foreach($unfollowers_info as $unfollower_info)
		{
			$email_cuerpo .= '<tr><td><img src="' . $unfollower_info['imagen'] . '" /></td>';
			$email_cuerpo .= '<td><a href="http://twitter.com/' . $unfollower_info['nombre'] . '">' . $unfollower_info['nombre'] . '</a></td>';
			if($unfollower_info['following'])
			{
				$email_cuerpo .= '<td>le estás siguiendo :(</td>';
			}
			$email_cuerpo .= '</tr>';
		}
		$email_cuerpo .= '</table>';
		
		$email_cabeceras = 'MIME-Version: 1.0' . "\r\n";
		$email_cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$email_cabeceras .= 'To: ' . EMAIL_DIRECCION . "\r\n";
		$email_cabeceras .= 'From: ' . EMAIL_REMITENTE . "\r\n";
		
		mail(EMAIL_DIRECCION, $email_asunto, $email_cuerpo, $email_cabeceras);
	}
}
  
?>
