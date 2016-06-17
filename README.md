Twitter Unfollowers
===================

Pequeña clase de PHP para obtener los unfollowers de una cuenta de Twitter.

Por su naturaleza está pensado para ser ejecutado periódicamente como una tarea de `cron`, como en el ejemplo `twitter_unfollowers_cron.php`.

Limitaciones
------------

* Está limitada a 5000 followers (por defecto el límite de la API de Twitter si no se utiliza paginación), cuando sobrepase esa cifra ya lo modificaré ;)
* Utiliza un fichero para almacenar los followers y arrays para comparar, no testado con un número elevado de ellos

Más información
---------------

En el siguiente post se detallan los pasos dados para el desarrollo: [Twitter Unfollowers][blog]

[blog]: http://ferjgar.rocks/desarrollo/twitter-unfollowers
[1]: https://github.com/abraham/twitteroauth
[2]: https://github.com/abraham
