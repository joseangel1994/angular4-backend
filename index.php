<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$db = new mysqli('localhost','root','','curso_angular4');

//Cabeceras de configuracion PHP
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

$app->get("/pruebas", function() use($app, $db){
    echo "Hola mundo desde Slim PHP";
});

$app->get("/probando", function() use($app){
    echo "OTRO TEXTO CUALQUIERA";
});

//Guardar productos en base de datos
$app->post('/productos',function() use($app, $db){
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    if(!isset($data['imagen'])){
        $data['imagen']=null;
    }
    if(!isset($data['description'])){
        $data['description']=null;
    }
    if(!isset($data['nombre'])){
        $data['nombre']=null;
    }
    if(!isset($data['precio'])){
        $data['precio']=null;
    }

    $query = "INSERT INTO productos VALUES(NULL,".
        "'{$data['nombre']}',".
        "'{$data['description']}',".
        "'{$data['precio']}',".
        "'{$data['imagen']}'".
        ");";

    $insert = $db->query($query);

    $result = array(
      'status'  =>'error',
      'code'    => 404,
      'message' => 'Productro NO creado correctamente'
    );

    if($insert){
      $result = array(
        'status'  =>'success',
        'code'    => 200,
        'message' => 'Productro creado correctamente'
      );
    }
    echo json_encode($result);
});

//Listar todos los productos
$app->get('/productos', function() use($app, $db){
    $sql = 'SELECT * FROM productos ORDER BY id DESC;';
    $query = $db->query($sql);

    $productos = array();
    while ($producto = $query->fetch_assoc()) {
      $productos[] = $producto;
    }

    $result = array(
      'status'  =>'success',
      'code'    => 200,
      'data' => $productos
    );

    echo json_encode($result);

});

//Devolver un solo producto

$app->get('/producto/:id', function($id) use($app, $db){
  $sql = 'SELECT * FROM productos WHERE id = '.$id;
  $query = $db->query($sql);

  $result = array(
    'status' => 'error',
    'code'   => 404,
    'message' => 'Producto no disponible'
  );

  if($query->num_rows == 1){
    $producto = $query->fetch_assoc();
    $result = array(
      'status' => 'success',
      'code'   => 200,
      'data' => $producto
    );
  }
  echo json_encode($result);

});


//Eliminar un producto

$app ->get('/delete-producto/:id', function($id) use($db, $app){
    $sql = 'DELETE FROM productos WHERE id = '.$id;
    $query = $db->query($sql);

    if($query){
      $result = array(
        'status' => 'success',
        'code'   => 200,
        'message' => 'El producto se ha eleminiado correctamente'
      );
    }else{
      $result = array(
        'status' => 'error',
        'code'   => 404,
        'message' => 'El producto NO se ha eleminiado correctamente'
      );
    }
    echo json_encode($result);
});
//Actualizar un producto

$app->post('/update-producto/:id', function($id) use($db, $app){
  $json = $app->request->post('json');
  $data = json_decode($json,true);

  //El . se usa para concatenar datos
  $sql = "UPDATE productos SET ".
      "nombre = '{$data["nombre"]}',".
      "description = '{$data["description"]}',";

  if(isset($data['imagen'])){
    $sql .= "imagen = '{$data["imagen"]}',";
  }
  $sql .= "precio = '{$data["precio"]}' WHERE id = {$id}";

  $query = $db->query($sql);

  if($query){
    $result = array(
      'status' => 'success',
      'code'   => 200,
      'message' => 'El producto se ha actualizado correctamente'
    );
  }else{
    $result = array(
      'status' => 'error',
      'code'   => 404,
      'message' => 'El producto NO se ha actualizado correctamente'
    );
  }


  echo json_encode($result);
});

//Subir una imagen a un producto

$app->post('/upload-file', function() use($db, $app){

  $result = array(
    'status' => 'error',
    'code'   => 404,
    'message' => 'El archivo no ha podido subirse'
  );

  if(isset($_FILES['uploads'])){
    $piramideUploader = new PiramideUploader();

    $upload = $piramideUploader->upload('image',"uploads","uploads",array('image/jpeg','image/png','image/gif'));
    $file = $piramideUploader->getInfoFile();
    $file_name = $file['complete_name'];

    if(isset($upload) && $upload["uploaded"]== false){
      $result = array(
        'status' => 'error',
        'code'   => 404,
        'message' => 'El archivo no ha podido subirse'
      );
    }else{
      $result = array(
        'status' => 'success',
        'code'   => 200,
        'message' => 'El archivo ha podido subirse',
        'filename' => $file_name
      );
    }
  }
  echo json_encode($result);

});

$app->run();
