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
                    'variantes' => []
                ];
            }

            if ($prod['precio'] < $productosAgrupados[$key]['precio_min']) {
                $productosAgrupados[$key]['precio_min'] = $prod['precio'];
            }

            if (!isset($productosAgrupados[$key]['variantes'][$variante])) {
                $productosAgrupados[$key]['variantes'][$variante] = [];
            }

            $productosAgrupados[$key]['variantes'][$variante][] = [
                'id' => $prod['id'],
                'tipo' => $presentacion,
                'precio' => $prod['precio']
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

    public function procesarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $telefono = trim($_POST['telefono'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $redirect = $_POST['redirect'] ?? 'home';

            require_once 'config/db.php';
            $db = new Database();
            $conn = $db->connect();

            // Verificar si el correo ya existe
            $stmtCheck = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
            $stmtCheck->execute([$email]);
            if ($stmtCheck->fetch()) {
                header("Location: index.php?action=registro&error=email_existe");
                exit();
            }

            // Encriptar contraseña y registrar
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, password, telefono, direccion) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $email, $hash, $telefono, $direccion]);
            $nuevoId = $conn->lastInsertId();

            // 🔥 AUTO LOGIN: Iniciamos la sesión automáticamente
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['cliente_id'] = $nuevoId;
            $_SESSION['cliente_nombre'] = $nombre;

            // Redirigir según de dónde venía
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
