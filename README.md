Creando un bundle de Symfony desde Cero
========

> Artículo completo en: https://medium.com/@conejerock/creando-un-bundle-de-symfony-desde-cero-5afa7669f830

Hace poco, trabajando en un proyecto que tiene dos instancias Symfony, se dio la necesidad crear un Bundle para tener aislada una funcionalidad y que fuera compartida por las dos instancias.

Así que me puse a leer [documentación oficial de Symfony](https://symfony.com/doc/current/bundles/best_practices.html) y me dispuse a montarlo. Como cometí algunos errores y algunos líos mentales mientras lo creaba, he aquí mi tutorial en Español a quien le pueda servir de ayuda.

Para no liar la madeja, vamos a crear un Bundle que inserte en cada _Response HTTP,_ una cabecera con una **fruta aleatoria** (sí, ya sé que no es muy útil. Pero es lo primero que se me ha ocurrido). El Bundle se llamará **FruitBundle** (y sí, soy muy original)

Requesitos
==========

*   [PHP](https://php.watch/articles/php-8.3-install-upgrade-on-fedora-rhel-el#quick-start) instalado (v8.3)
*   [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) instalado
*   [Symfony CLI](https://symfony.com/download#step-1-install-symfony-cli) instalado

Creando la estructura
=====================

Vamos a suponer que tenemos el siguiente árbol de ficheros de nuestro proyecto Symfony:

```
base  
  ├── symfony-project  
  │          ├── bin  
  │          ├── composer.json  
  │          ├── composer.lock  
  │          ├── config  
  │          ├── public  
  │          ├── src  
  │          ├── symfony.lock  
  │          └── vendor  
  └── symfony-project-2  
             ├── bin  
             ├── composer.json  
             ├── composer.lock  
             ├── config  
             ├── public  
             ├── src  
             ├── symfony.lock  
             └── vendor
```

En mi caso, como quería que el Bundle fuera **compartido por ambos proyectos** (_symfony-project_ y _symfony-project-2_) cree el Bundle en un directorio paralelo a estos dos. No obstante, si tu Bundle sólo va a ser utilizado por un proyecto, puedes crearlo junto al resto de carpetas (_bin, config, public, src…_).

Situándonos en la carpeta `base` haremos lo siguiente:

```bash
symfony new --debug --php=8.3 FruitBundle
```

Vale y dirás… ¡si esto crea otro proyecto de Symfony!  
¡Exacto! Un Bundle no deja de ser un **miniproyecto** de Symfony que fusiona las funcionalidades del Bundle con el proyecto principal.

Ahora bien, se han creado algunas cosas que no nos interesan en el Bundle. Vamos a **borrar** aquello que no nos haga falta y a **cambiar la rama de Git**:

```bash
cd FruitBundle  
rm -rf bin public config/\* src/\*  
git branch -M main
```

Ahora mismo nuestra estructura de ficheros tendría que ser algo tal que así:

```
base  
  ├── symfony-project  
  ├── symfony-project-2  
  └── FruitBundle       <---- Nuestro bundle  
      ├── composer.json  
      ├── composer.lock  
      ├── config  
      ├── src  
      ├── symfony.lock  
      ├── var  
      └── vendor
```

Ahora, crearemos el **archivo de entrada del Bundle**. Crearemos un archivo `FruitBundle.php`en la carpeta `src`. Ya lo configuraremos más adelante:

```php
// base/FruitBundle/src/FruitBundle.php  
<?php  
declare(strict\_types=1);  
  
namespace BasketFruit\FruitBundle;  
  
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;  
  
class FruitBundle extends AbstractBundle  
{  
  
}  

```

Como véis, he escogido `BasketFruit` como **namespace**, para poder seguir la convención [PSR-4](https://www.php-fig.org/psr/psr-4/) que es la que nos recomienda Symfony.

Y, finalmente, cambiaremos el `composer.json` del Bundle para, principalmente, dos cosas:

*   Que configure nuestro **_miniproyecto_ como Bundle**(ahora mismo es un proyecto de Symfony normal).
*   Que sepa como se **llaman los namespace y donde buscarlos.**

```json5
// base/FruitBundle/composer.json  
"type": "symfony-bundle",  
"name": "basket-fruit/fruit-bundle", // ¡¡Este nombre será el que use Composer para instalar el bundle!!  
"description": "Add random fruit to HTTP Response",  
"version": "1.0.0",  
  
// "replace": {  
//    "symfony/polyfill-php72": "\*",  
// ...  
// },  
  
// ...  
  
"autoload": {  
    "psr-4": {  
        "BasketFruit\\FruitBundle\\|": "src/"  
    }  
},  
"autoload-dev": {  
    "psr-4": {  
        "BasketFruit\\Tests\\FruitBundle\": "tests/"  
    }  
}
```

Le he dado el nombre de `basket-fruit/fruit-bundle` al módulo. Este nombre será el que utilice **Composer** para instalarlo.

**OJO: Elimina el atributo** `**replace**`**de** `**composer.json**` **o no te dejará instalar el bundle en el proyecto principal**

> Yeah! Bundle creado

Configurando el proyecto principal
==================================

Con nuestro Bundle creado, sólo nos queda instalarlo en nuestro proyecto principal.

Puesto que Composer busca los paquetes para instalar en [https://packagist.org/](https://packagist.org/) (y nuestro Bundle aún no está publicado), debemos indicar que además busque en otro sitio (concretamente en el directorio `/FruitBundle`)  
En mi caso, en `composer.json`del proyecto `symfony-project` incluiremos lo siguiente:

```json5
// base/symfony-project/composer.json  
"autoload": {  
    "psr-4": {  
        "App\\": "src/", //Tu aplicación principal  
        "BasketFruit\\FruitBundle\\": "../FruitBundle/src/"  
    }  
},  
"repositories": [  
    {  
      "type": "path",  
      "url": "../FruitBundle",  
      "canonical": true  
    }  
  ]
```

Una vez hecho esto, procedemos a instalar nuestro Bundle:

```bash
cd ../symfony-project  
composer require basket-fruit/fruit-bundle:1.0.0
```

Una vez instalado, añadimos el Bundle a nuestra lista de Bundles en `symfony-project/config/bundles.php` :

```php
//config/bundles.php  
<?php  
  
return [  
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],  
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],  
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],  
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],  
    // nuestro FruitBundle  
    BasketFruit\FruitBundle::class => ['all' => true],  
];
```

Ahora sí, nuestro bundle está integrado con el proyecto principal de Symfony. Si ejecutamos el siguiente comando en la consola de nuestro proyecto principal:

```bash
./bin/console config:dump-reference 
```

Veremos lo siguiente (te saldrán otros Bundles si ya lo tienes intalados):

```
 ------------------- -----------------   
  Bundle name         Extension alias    
 ------------------- -----------------   
  FrameworkBundle     framework          
  FruitBundle         fruit            <-- Nuestro Bundle integrado  
  MakerBundle         maker              
  TwigBundle          twig               
  WebProfilerBundle   web\_profiler       
 ------------------- -----------------
```

Añadiendo frutas a las respuestas
=================================

Vamos al turrón. Hemos dicho que queremos añadir una fruta aleatoria como cabecera a las _Responses HTTP_ que ofrece Symfony. Vamos allá:

Añadiendo listeners
-------------------

Para añadir esta simple cabecera, bastará con crear un listener que escuche el evento Response, y añadirle la cabecera antes de enviarla.

Según la documentación oficial, para seguir una buena estructura del Bundle, los _listeners_ deben ir en la carpeta `src/EventListener` de nuestro bundle.

Así bien, creamos la siguiente estructura de carpetas dentro de **FruitBundle**:

```
FruitBundle  
      ├── src  
      │   ├── EventListener <----------------------- NEW  
      │   │   └── AddFruitResponseListener.php  
      │   └── FruitBundle.php  
      └── tests
```

El contenido del Listener es muy simple:

```php
// ./FruitBundle/src/EventListener/AddFruitResponseListener.php  
<?php  
declare(strict\_types=1);  
  
namespace BasketFruit\FruitBundle\EventListener;  
  
use Symfony\Component\HttpKernel\Event\ResponseEvent;  
  
class AddFruitResponseListener  
{  
    const HEADER_KEY = 'X-Random-Fruit';  
    const FRUITS = [  
        "apple", "banana", "orange",  
        "grapes", "strawberry", "watermelon",  
        "pineapple", "mango", "blueberry"  
    ];  
  
    public function __invoke(ResponseEvent $event): void  
    {  
        $response = $event->getResponse();  
        $response->headers->set(self::HEADER_KEY, self::FRUITS[array_rand(self::FRUITS)]);  
    }  
}
```

Configurando el listener
------------------------

Para que Symfony fusione nuestro listener del bundle con el proyecto principal, hay que indicarle que nuestra clase es un listener y que se lanza con el evento _ResponseEvent (valga la redundancia):_

Así, crearemos el archivo `FruitBundle/config/services.yaml` con el siguiente contenido:

```yaml
# ./FruitBundle/config/services.yaml  
services:  
  BasketFruit\FruitBundle\EventListener\AddFruitResponseListener:  
    tags:  
      - { name: kernel.event\_listener, event: kernel.response }
```

Finalmente, esta configuración se tiene que cargar desde algún sitio. ¿Recordáis nuestra clase `FruitBundle.php`? Es la entrada principal del Bundle. En esa misma clase crearemos la función `loadExtension` que hereda de AbstractBundle, y es la encargada de cargar el archivo de configuración de nuestro bundle.

```php
// FruitBundle/src/FruitBundle.php  
<?php  
declare(strict\_types=1);  
  
namespace BasketFruit\FruitBundle;  
  
use Symfony\Component\DependencyInjection\ContainerBuilder;  
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;  
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;  
  
class FruitBundle extends AbstractBundle  
{  
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void  
    {  
        $container->import('../config/services.yaml');  
    }  
  
}
```

¡Listo! Ya deberíamos tener nuestro Bundle, añadiendo una cabecera `X-Random-Fruit` a las respuestas de nuestras peticiones al proyecto principal.
