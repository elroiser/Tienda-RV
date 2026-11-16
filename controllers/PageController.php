<?php
// controllers/PageController.php

require_once 'models/TiendaModel.php';

class PageController
{

    private $model;

    public function __construct()
    {
        $this->model = new TiendaModel();
    }

    public function home()
    {
        require_once 'views/home.php';
    }

    public function productos()
    {
        $productosRaw = $this->model->obtenerProductos();
        $productosAgrupados = [];

        foreach ($productosRaw as $prod) {
            $nombreOriginal = $prod['nombre'];

            // 1. Extraer presentación (lo que está entre paréntesis)
            $pos = strrpos($nombreOriginal, '(');
            if ($pos !== false) {
                $nombreSinPresentacion = trim(substr($nombreOriginal, 0, $pos));
                $presentacion = trim(substr($nombreOriginal, $pos + 1, -1));
            } else {
                $nombreSinPresentacion = trim($nombreOriginal);
                $presentacion = "Unidad";
            }

            // 2. Definir Familia Principal y Variante (Aroma/Color)
            $familia = $nombreSinPresentacion;
            $variante = "Estándar";

            // Detectar si es un AROMAFRESH
            if (strpos(strtoupper($nombreSinPresentacion), 'AROMAFRESH') === 0) {
                $familia = "AROMAFRESH Aromatizador de Ambientes";
                $varianteRaw = preg_replace('/^AROMAFRESH\s+/i', '', $nombreSinPresentacion);
                $varianteRaw = preg_replace('/\s+aromatizador de ambientes?$/i', '', $varianteRaw);
                $variante = trim($varianteRaw) !== '' ? trim($varianteRaw) : "Estándar";
            }
            // Detectar si es un DESINBACT
            elseif (strpos(strtoupper($nombreSinPresentacion), 'DESINBACT') === 0) {
                $familia = "DESINBACT Desinfectante de Pisos";
                $varianteRaw = preg_replace('/^DESINBACT\s+/i', '', $nombreSinPresentacion);
                $varianteRaw = preg_replace('/\s+Desinfectante de Pisos$/i', '', $varianteRaw);
                $variante = trim($varianteRaw) !== '' ? trim($varianteRaw) : "Estándar";
            }
            // Detectar si es un SUAVICHEM
            elseif (strpos(strtoupper($nombreSinPresentacion), 'SUAVICHEM') === 0) {
                $familia = "SUAVICHEM Suavizante Textil";
                $varianteRaw = preg_replace('/^SUAVICHEM\s+/i', '', $nombreSinPresentacion);
                $varianteRaw = preg_replace('/\s+Suavizante Textil$/i', '', $varianteRaw);
                $variante = trim($varianteRaw) !== '' ? trim($varianteRaw) : "Estándar";
            }
            // Detectar si es un SOAP CHEM
            elseif (strpos(strtoupper($nombreSinPresentacion), 'SOAP CHEM') === 0) {
                $familia = "SOAP CHEM Jabón Líquido";
                $varianteRaw = preg_replace('/^SOAP CHEM\s+/i', '', $nombreSinPresentacion);
                $varianteRaw = preg_replace('/\s+Jabón Líquido$/i', '', $varianteRaw);
                $variante = trim($varianteRaw) !== '' ? trim($varianteRaw) : "Estándar";
            }

            // CORRECCIÓN: Estandarizar el nombre de la variante (ej. "Manzana canela" a "Manzana Canela")
            // Esto evita que PHP los separe por culpa de una letra mayúscula o minúscula.
            $variante = ucwords(strtolower($variante));

            $key = strtolower($familia);

            // Crear el grupo del producto si no existe
            if (!isset($productosAgrupados[$key])) {
                $productosAgrupados[$key] = [
                    'nombre_base' => $familia,
                    'categoria' => $prod['categoria_nombre'],
                    'imagen' => $prod['imagen'],
                    'precio_min' => $prod['precio'],
                    'stock_total' => 0,
                    'variantes' => []
                ];
            }

            $stockActual = max(0, intval($prod['stock']));
            $productosAgrupados[$key]['stock_total'] += $stockActual;

            if ($prod['precio'] < $productosAgrupados[$key]['precio_min']) {
                $productosAgrupados[$key]['precio_min'] = $prod['precio'];
            }

            if (!isset($productosAgrupados[$key]['variantes'][$variante])) {
                $productosAgrupados[$key]['variantes'][$variante] = [];
            }

            $productosAgrupados[$key]['variantes'][$variante][] = [
                'id' => $prod['id'],
                'tipo' => $presentacion,
                'precio' => $prod['precio'],
                'stock' => $stockActual
            ];
        }

        $productosFinales = [];
        foreach ($productosAgrupados as $grupo) {
            $variantesFormateadas = [];
            foreach ($grupo['variantes'] as $nombreVariante => $presentaciones) {
                $variantesFormateadas[] = [
                    'aroma' => $nombreVariante,
                    'presentaciones' => $presentaciones
                ];
            }
            $grupo['variantes'] = $variantesFormateadas;
            $productosFinales[] = $grupo;
        }

        $productos = $productosFinales;
        require_once 'views/productos.php';
    }

    public function registro()
    {
        require_once 'views/registro.php';
    }

    public function procesarRegistro()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre    = trim(preg_replace('/\s+/', ' ', $_POST['nombre'] ?? ''));
            $email     = trim(strtolower($_POST['email'] ?? ''));
            $password  = $_POST['password'] ?? '';
            $telefono  = preg_replace('/[^0-9]/', '', $_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $redirect  = $_POST['redirect'] ?? 'home';

            // Guardar datos previos en sesión por si falla una validación y hay que devolverlos
            $_SESSION['form_registro'] = [
                'nombre'    => $nombre,
                'email'     => $email,
                'telefono'  => $telefono,
                'direccion' => $direccion
            ];

            // 1. Validar campos vacíos
            if ($nombre === '' || $email === '' || $password === '' || $telefono === '' || $direccion === '') {
                header("Location: index.php?action=registro&error=campos_vacios&redirect=" . urlencode($redirect));
                exit();
            }

            // 2. Validar nombre (solo letras/espacios y al menos 2 palabras: Nombre y Apellido)
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]{5,80}$/', $nombre) || count(explode(' ', $nombre)) < 2) {
                header("Location: index.php?action=registro&error=nombre_invalido&redirect=" . urlencode($redirect));
                exit();
            }

            // 3. Validar formato de correo electrónico
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header("Location: index.php?action=registro&error=email_invalido&redirect=" . urlencode($redirect));
                exit();
            }

            // 4. Validar contraseña mínima de 6 caracteres
            if (strlen($password) < 6) {
                header("Location: index.php?action=registro&error=password_corta&redirect=" . urlencode($redirect));
                exit();
            }

            // 5. Validar celular ecuatoriano (10 dígitos empezando con 09)
            if (!preg_match('/^09[0-9]{8}$/', $telefono)) {
                header("Location: index.php?action=registro&error=telefono_invalido&redirect=" . urlencode($redirect));
                exit();
            }

            // 6. Validar dirección mínima
            if (strlen($direccion) < 10) {
                header("Location: index.php?action=registro&error=direccion_corta&redirect=" . urlencode($redirect));
                exit();
            }

            require_once 'config/db.php';
            $db = new Database();
            $conn = $db->connect();

            // 7. Verificar si el correo ya existe
            $stmtCheck = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
            $stmtCheck->execute([$email]);
            if ($stmtCheck->fetch()) {
                header("Location: index.php?action=registro&error=email_existe&redirect=" . urlencode($redirect));
                exit();
            }

            // 8. Encriptar contraseña y registrar
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, password, telefono, direccion) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $email, $hash, $telefono, $direccion]);
            $nuevoId = $conn->lastInsertId();

            // Limpiar datos temporales del formulario
            unset($_SESSION['form_registro']);

            // AUTO LOGIN
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['cliente_id'] = $nuevoId;
            $_SESSION['cliente_nombre'] = $nombre;

            if ($redirect === 'checkout') {
                header("Location: index.php?action=checkout");
            } else {
                header("Location: index.php?action=productos");
            }
            exit();
        }
    }
    public function login()
    {
        // Capturamos a dónde quería ir el usuario (por defecto 'home')
        $redirect = $_GET['redirect'] ?? 'home';
        require_once 'views/login.php';
    }

    public function procesarLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $redirect = $_POST['redirect'] ?? 'home';

            require_once 'config/db.php';
            $db = new Database();
            $conn = $db->connect();

            $stmt = $conn->prepare("SELECT * FROM clientes WHERE email = ?");
            $stmt->execute([$email]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificamos si existe el cliente y la contraseña es correcta
            if ($cliente && password_verify($password, $cliente['password'])) {

                // IMPORTANTE: Al guardar esto, el carrito ($_SESSION['carrito']) NO se borra.
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nombre'] = $cliente['nombre'];

                // Lo enviamos de regreso a donde quería ir
                if ($redirect === 'checkout') {
                    header("Location: index.php?action=checkout");
                } else {
                    header("Location: index.php?action=productos");
                }
                exit();
            } else {
                header("Location: index.php?action=login&error=1&redirect=" . urlencode($redirect));
                exit();
            }
        }
    }

    public function logout()
    {
        // Destruye toda la sesión (carrito incluido) y cierra sesión
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
