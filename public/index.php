<?php
session_start();
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';


$app = AppFactory::create();
// To help the built-in PHP dev server, check if the request was actually for
// something which should probably be served as a static file
if (PHP_SAPI == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) return false;
}

$app->get('/inicio', function (Request $request, Response $response, array $args) {
    $inicio = new Library\TemplateEngine('../templates/home.html');
    $response->getBody()->write($inicio->render());
    return $response;
});

$app->get('/productos', function (Request $request, Response $response, array $args) {
    
    if (!empty($_GET['id'])){
        if(empty($_SESSION['carrito'][$_GET['id']])){
            $_SESSION['carrito'][$_GET['id']] = 1;
        } else{
            $_SESSION['carrito'][$_GET['id']] += 1;
        }
    }
            

    $str="";
    $listaDeProductos = array(
        "01"=>array(
            "nombre"=>"Pasta Frola", "precio"=>"550"),
        "02"=>array(
            "nombre"=>"Budin", "precio"=>"650"),
        "03"=>array(
            "nombre"=>"Bownie", "precio"=>"750"),
        "04"=>array(
            "nombre"=>"Tarde de Frutos Secos", "precio"=>"750"),
        "05"=>array(
            "nombre"=>"Pio Nonno", "precio"=>"850"),
        );
    foreach ($listaDeProductos as $id => $valor) {
        $detalleProducto = new \Library\TemplateEngine("../templates/detalleProducto.html");
        $detalleProducto->addVariable("Id", $id);
        $detalleProducto->addVariable("Nombre", $valor["nombre"]);
        $detalleProducto->addVariable("Precio", $valor["precio"]);

        $str .= $detalleProducto->render();
    }

    $productos = new Library\TemplateEngine('../templates/listaProductos.html');
    $productos->addVariable("detalle", $str);
    $response->getBody()->write($productos->render());
    return $response;
});

$app->get('/login', function (Request $request, Response $response, array $args) {
    
    $login = new Library\TemplateEngine('../templates/login.html');
    $response->getBody()->write($login->render());
    return $response;
});

$app->post('/login', function (Request $request, Response $response, array $args) {
    $users = array(
        "gian"=>"123456",
        "eleana"=>"654321",
        "gianna"=>"qwerty",
    );

    if (array_key_exists($_POST["user"], $users) && $_POST["password"]==$users[$_POST["user"]]){
        $_SESSION["logeado"] = true;
        $response = $response->withStatus(302);
        $response = $response->withHeader("Location","/productos");

        return $response;
        
    }
    $response->withStatus(302);
    $response->withHeader("Location","/login");

    return $response;
});


$app->group('', function (\Slim\Routing\RouteCollectorProxy $group){
    
    $group->get('/logout', function (Request $request, Response $response, array $args) {
        session_destroy();
        $response = $response->withStatus(302);
        $response = $response->withHeader("Location","/login");
        return $response;
    });
    
    $group->get('/carrito', function (Request $request, Response $response, array $args) {
    
        $str="";
        $listaDeProductos = array(
            "01"=>array(
                "nombre"=>"Pasta Frola", "precio"=>"550"),
            "02"=>array(
                "nombre"=>"Budin", "precio"=>"650"),
            "03"=>array(
                "nombre"=>"Bownie", "precio"=>"750"),
            "04"=>array(
                "nombre"=>"Tarde de Frutos Secos", "precio"=>"750"),
            "05"=>array(
                "nombre"=>"Pio Nonno", "precio"=>"850"),
            );
        $subtotal=0;
        foreach ($_SESSION["carrito"] as $id => $valor) {
                $detalleCarrito = new \Library\TemplateEngine("../templates/detalleCarrito.html");
                $detalleCarrito->addVariable("Id", $id);
                $detalleCarrito->addVariable("Cantidad", $valor);
                $detalleCarrito->addVariable("Nombre", $listaDeProductos[$id]["nombre"]);
                $detalleCarrito->addVariable("Precio Uni", $listaDeProductos[$id]["precio"]);
                $detalleCarrito->addVariable("Precio Total", $valor*$listaDeProductos[$id]["precio"]);
                $subtotal=$subtotal+$valor*$listaDeProductos[$id]["precio"];
                
                $str .= $detalleCarrito->render();
            }
            
        $listadoDeCarrito = new Library\TemplateEngine('../templates/carrito.html');
        $listadoDeCarrito->addVariable("carrito", $str);
        $listadoDeCarrito->addVariable("total", $subtotal);
        $response->getBody()->write($listadoDeCarrito->render());
        
        return $response;
    });
    
    $group->get('/agregarAlCarrito/{Id}', function (Request $request, Response $response, array $args) {
        
        if(empty($_SESSION['carrito'][$args['Id']])){
            $_SESSION['carrito'][$args['Id']] = 1;
    
        } else{
            $_SESSION['carrito'][$args['Id']] += 1;
        }
        $response = $response->withStatus(302);
        $response = $response->withHeader("Location","/productos");
        
        return $response;
    });
    
    $group->get('/borrarItem/{Id}', function (Request $request, Response $response, array $args) {
        
        if (!empty($args['Id'])){
            if ($args['borrarCantIdad']='borrarCantIdad'){
                if ($_SESSION['carrito'][$args['Id']]>=2){
        
                    $_SESSION['carrito'][$args['Id']]-=1;
                    $response = $response->withStatus(302);
                    $response = $response->withHeader("Location","/carrito");
            
                    return $response;
                }
                
                if ($_SESSION['carrito'][$args['Id']]==1){
                
                    unset($_SESSION['carrito'][$args['Id']]);
                }
                $response = $response->withStatus(302);
                $response = $response->withHeader("Location","/carrito");
        
                return $response;
    
            }elseif ($args['borrarLinea']='borrarLinea'){
                unset($_SESSION['carrito'][$args['Id']]);
                $response = $response->withStatus(302);
                $response = $response->withHeader("Location","/carrito");
        
                return $response;
            }  
        }
    });
})->add(function(Request $request, RequestHandler $next){
    if (empty($_SESSION['logeado']) || $_SESSION['logeado'] !== True){
        $response = new \Slim\Psr7\Response();
        $response = $response->withStatus(302);
        $response = $response->withHeader("Location","/login");

        return $response;
    }

    return $next->handle($request);
});

$app->run();