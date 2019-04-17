<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();
$db = new mysqli('localhost', 'albert', 'root', 'curso_angular');

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

$app->get("/pruebas", function() use($app, $db){
    echo "hola mundo desde slim php";
    var_dump($db);
});

// LISTAR TODOS LOS PRODUCTOS
$app->get('/productos', function() use($app, $db){
    $sql = 'SELECT * FROM productos ORDER BY id DESC';
    $query = $db->query($sql);

    $productos = array();
    while ($producto = $query->fetch_assoc()){
        $productos[] = $producto;
    }

    $result = array(
        'status' => 'success',
        'code' => 200,
        'data' => $productos
    );
    echo json_encode($result);
});

// DEVOLVER UN SOLO PRODUCTO
$app->get('/producto/:id', function($id) use($app, $db){
    $sql = 'SELECT * FROM productos WHERE id = '.$id;
    $query = $db->query($sql);

    if ($query->num_rows == 0){
        $result = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'producto no encontrado'
        );
    }else {
        $producto = $query->fetch_assoc();

        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => $producto
        );
    }
    echo json_encode($result);
});

// ELIMINAR UN PRODUCTO
$app->get('/delete-producto/:id', function($id) use($app, $db){
   $sql = 'DELETE FROM productos WHERE id = '.$id;
   $query = $db->query($sql);

   if($query){
       $result = array(
           'status' => 'success',
           'code' => 200,
           'message' => 'El producto se ha eliminado correctamente'
       );
   }else{
       $result = array(
           'status' => 'error',
           'code' => 404,
           'message' => 'El producto NO se ha eliminado'
       );
   }
   echo json_encode($result);
});

// ACTUALIZAR UN PRODUCTO
$app->post('/update-producto/:id', function($id) use($app, $db){
   $json = $app->request->post('json');
   $data = json_decode($json, true);

   $sql = "UPDATE productos SET ".
            "nombre = '{$data["nombre"]}',".
            "descripcion = '{$data["descripcion"]}',";

   if(isset($data['imagen'])){
       $sql .= "imagen = '{$data["imagen"]}',";
   }
   $sql .= "precio = '{$data["precio"]}' WHERE id = {$id}";
   $query = $db->query($sql);

   if ($query){
        $result = array(
            'status' => 'success',
            'code' => 200,
            'data' => 'El producto se ha actualizado correctamente'
        );
   } else {
        $result = array(
            'status' => 'error',
            'code' => 404,
            'message' => 'El producto NO se ha actualizado'
        );
   }
    echo json_encode($result);
});


// SUBIR UNA IMAGEN A UN PRODUCTO
$app->post('/upload-file', function () use ($db, $app){
    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'El archivo no ha podido subirse'
    );

    if(isset($_FILES['uploads'])){
        $piramideUploader = new PiramideUploader();
        $upload = $piramideUploader->upload(
            'image',
            "uploads",
            "uploads",
            array('image/jpeg', 'image/png', 'image/gif'));
        $file = $piramideUploader->getInfoFile();
        $fileName = $file['complete_name'];

        if(isset($upload) && $upload["uploaded"]){
            $result = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El archivo ha podido subirse',
                'filename' => $fileName
            );
        }
    }

    echo json_encode($result);

});


// GUARDAR PRODUCTOS
$app-> post('/productos', function() use($app, $db){
    $json = $app->request->post('json');
    $data = json_decode($json, true);

    if(!isset($data['imagen']))
    {
        $data['imagen'] = null;
    }

    if(!isset($data['descripcion']))
    {
        $data['descripcion'] = null;
    }

    if(!isset($data['nombre']))
    {
        $data['nombre'] = null;
    }

    if(!isset($data['precio']))
    {
        $data['precio'] = null;
    }


    $query = "INSERT INTO productos VALUES (null, ".
             "'{$data['nombre']}',".
             "'{$data['descripcion']}',".
             "'{$data['precio']}',".
             "'{$data['imagen']}'".
             ");";
    $insert =  $db->query($query);

    $result = array(
        'status' => 'error',
        'code' => 404,
        'message' => 'el producto NO se ha creado correctamente'
    );

    if ($insert){
        $result = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'el producto se ha creado correctamente'
        );
    }
    echo json_encode($result);
});

$app->run();


