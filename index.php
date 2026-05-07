<?php
/**
 * index.php - Aplicación NaturES (CRUD Unificado + Tarjetas + Filtros)
 */
require_once 'config.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

$action = $_REQUEST['action'] ?? 'list';
$msg = $_GET['msg'] ?? '';

// --- LÓGICA DE CONTROL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'save') {
        $id      = $_POST['id_producto'] ?? null;
        $params = [
            ':nombre' => trim($_POST['nombre_producto']),
            ':cat'    => $_POST['id_categoria'],
            ':marca'  => trim($_POST['marca']),
            ':precio' => $_POST['precio'],
            ':stock'  => $_POST['cantidad_stock'],
            ':pres'   => trim($_POST['presentacion'] ?? ''),
            ':desc'   => trim($_POST['descripcion']),
            ':img'    => trim($_POST['imagen_url']),
            ':ben'    => trim($_POST['beneficios'] ?? ''),
            ':inst'   => trim($_POST['instrucciones_uso'] ?? '')
        ];

        try {
            if ($id) {
                $params[':id'] = $id;
                $sql = "UPDATE productos SET nombre_producto=:nombre, id_categoria=:cat, marca=:marca, precio=:precio, cantidad_stock=:stock, presentacion=:pres, descripcion=:desc, imagen_url=:img, beneficios=:ben, instrucciones_uso=:inst WHERE id_producto=:id";
                $pdo->prepare($sql)->execute($params);
                header("Location: index.php?msg=updated");
            } else {
                $sql = "INSERT INTO productos (nombre_producto, id_categoria, marca, precio, cantidad_stock, presentacion, descripcion, imagen_url, beneficios, instrucciones_uso) VALUES (:nombre, :cat, :marca, :precio, :stock, :pres, :desc, :img, :ben, :inst)";
                $pdo->prepare($sql)->execute($params);
                header("Location: index.php?msg=saved");
            }
            exit;
        } catch (PDOException $e) { $error = $e->getMessage(); }
    }
}

if ($action === 'delete') {
    $id_to_delete = $_REQUEST['id'] ?? null;
    if ($id_to_delete) {
        try {
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
            $stmt->execute([$id_to_delete]);
            if ($stmt->rowCount() > 0) {
                header("Location: index.php?msg=deleted");
                exit;
            } else {
                $error = "No se encontró el producto o no se pudo eliminar.";
            }
        } catch (PDOException $e) { 
            $error = "Error al eliminar: " . $e->getMessage(); 
        }
    } else {
        $error = "ID de producto no proporcionado.";
    }
}

// --- PREPARACIÓN DE DATOS ---
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll();
$productos = $pdo->query("SELECT p.*, c.nombre_categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id_categoria ORDER BY p.id_producto DESC")->fetchAll();
$stats = $pdo->query("SELECT COUNT(*) as total, SUM(cantidad_stock) as stock, COUNT(DISTINCT id_categoria) as cats, COUNT(DISTINCT marca) as marcas FROM productos")->fetch();

$producto_edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->execute([$_GET['id']]);
    $producto_edit = $stmt->fetch();
}

function getCatSlug($name) {
    $name = strtolower($name);
    if (strpos($name, 'aceite') !== false) return 'aceites';
    if (strpos($name, 'vela') !== false) return 'velas';
    if (strpos($name, 'herramienta') !== false) return 'herramientas';
    return 'otros';
}

function getEmoji($name) {
    if (strpos($name, 'Aceite') !== false) return '🧴';
    if (strpos($name, 'Vela') !== false) return '🕯️';
    if (strpos($name, 'Herramienta') !== false) return '🪨';
    return '📦';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NaturES — Gestión Avanzada</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet" />
  <style>
    :root { --verde-oscuro: #1a3c2b; --verde-medio: #2d6a4f; --verde-claro: #52b788; --verde-suave: #b7e4c7; --crema: #f8f4ef; --crema-oscura: #ede8e0; --dorado: #c9a84c; --dorado-claro: #f0d080; --texto: #1e2d24; --blanco: #ffffff; --sombra-md: 0 6px 24px rgba(0,0,0,0.12); --radio: 14px; --trans: .22s ease; }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: var(--crema); color: var(--texto); line-height: 1.6; }

    /* NAVBAR */
    .navbar { position: sticky; top: 0; z-index: 500; background: var(--verde-oscuro); height: 68px; display: flex; align-items: center; padding: 0 2rem; box-shadow: var(--sombra-md); }
    .logo { display: flex; align-items: center; gap: .6rem; text-decoration: none; }
    .logo__leaf { width: 34px; height: 34px; background: linear-gradient(135deg, var(--verde-claro), var(--dorado)); border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: grid; place-items: center; }
    .logo__leaf span { transform: rotate(45deg); font-size: .9rem; }
    .logo__text { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: white; }
    .logo__text em { color: var(--verde-claro); font-style: normal; }
    .nav-actions { margin-left: auto; display: flex; align-items: center; gap: 1rem; }
    .user-info { color: var(--verde-suave); font-size: .8rem; font-weight: 600; background: rgba(255,255,255,0.1); padding: .4rem .8rem; border-radius: 999px; }

    /* HERO */
    .hero { background: linear-gradient(135deg, var(--verde-oscuro) 0%, var(--verde-medio) 100%); padding: 4rem 2rem; text-align: center; color: white; position: relative; overflow: hidden; }
    .hero__badge { display: inline-block; background: rgba(255,255,255,0.15); padding: .3rem 1rem; border-radius: 999px; font-size: .75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 1rem; border: 1px solid rgba(255,255,255,0.1); }
    .hero h1 { font-family: 'Playfair Display', serif; font-size: 3.2rem; }
    .hero h1 span { color: var(--dorado-claro); }

    .stats { background: var(--verde-oscuro); border-top: 1px solid rgba(255,255,255,0.1); padding: 1.5rem; display: flex; justify-content: center; gap: 3rem; }
    .stat-item { text-align: center; }
    .stat-item strong { display: block; font-size: 1.5rem; color: var(--dorado-claro); }
    .stat-item span { font-size: .7rem; text-transform: uppercase; color: var(--verde-suave); font-weight: 700; }

    /* FILTERS */
    .controls { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
    .filter-bar { display: flex; gap: .5rem; flex-wrap: wrap; }
    .filter-btn { padding: .5rem 1.2rem; border-radius: 999px; border: 1.5px solid var(--crema-oscura); background: white; cursor: pointer; transition: all var(--trans); font-size: .8rem; font-weight: 600; }
    .filter-btn:hover, .filter-btn.active { background: var(--verde-medio); color: white; border-color: var(--verde-medio); }

    /* CARDS */
    .page-container { max-width: 1280px; margin: 0 auto; padding: 3rem 2rem; }
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem; }
    .product-card { background: white; border-radius: var(--radio); box-shadow: var(--sombra-md); overflow: hidden; display: flex; flex-direction: column; transition: transform .3s; }
    .product-card:hover { transform: translateY(-5px); }
    .card-img { width: 100%; aspect-ratio: 4/3; background: #eee; position: relative; overflow: hidden; display: grid; place-items: center; font-size: 3rem; }
    .card-img img { width: 100%; height: 100%; object-fit: cover; position: absolute; inset: 0; }
    .card-badge { position: absolute; top: .8rem; left: .8rem; background: var(--verde-oscuro); color: var(--verde-suave); font-size: .65rem; font-weight: 800; padding: .2rem .6rem; border-radius: 999px; }
    .card-body { padding: 1.2rem; flex: 1; }
    .card-price { font-size: 1.3rem; font-weight: 800; color: var(--verde-medio); margin-top: .5rem; }
    .card-footer { padding: 1rem; border-top: 1px dashed #eee; display: flex; gap: .5rem; }

    .btn { padding: .6rem 1.2rem; border-radius: 8px; border: none; font-weight: 700; cursor: pointer; text-decoration: none; font-size: .85rem; flex: 1; text-align: center; }
    .btn--edit { background: var(--crema-oscura); color: var(--verde-oscuro); }
    .btn--del { background: #fee2e2; color: #dc2626; }
    .btn--primary { background: var(--verde-medio); color: white; border-radius: 999px; padding: .6rem 1.5rem; }

    /* FORM (COPIADO DE FORM.HTML) */
    .form-card { background: white; border-radius: 20px; box-shadow: var(--sombra-md); max-width: 800px; margin: -2rem auto 4rem; overflow: hidden; }
    .form-header { background: linear-gradient(135deg, var(--verde-oscuro), var(--verde-medio)); padding: 2rem; color: white; display: flex; align-items: center; gap: 1rem; }
    .form-body { padding: 2.5rem; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .full { grid-column: span 2; }
    label { font-size: .7rem; font-weight: 800; text-transform: uppercase; color: var(--verde-medio); display: block; margin-bottom: .5rem; }
    input, select, textarea { width: 100%; padding: .8rem; border: 1.5px solid var(--crema-oscura); border-radius: 10px; outline: none; background: var(--crema); }
    input:focus { border-color: var(--verde-claro); background: white; }

    footer { background: var(--verde-oscuro); color: rgba(255,255,255,0.4); text-align: center; padding: 2rem; font-size: .8rem; }
  </style>
</head>
<body>

  <nav class="navbar">
    <a href="index.php" class="logo">
      <div class="logo__leaf"><span>🌿</span></div>
      <span class="logo__text">Natur<em>ES</em></span>
    </a>
    <div class="nav-actions">
      <span class="user-info">👤 <?= htmlspecialchars($_SESSION['usuario']) ?></span>
      <a href="index.php?action=logout" style="color: #fb7185; font-size: .8rem; text-decoration:none;" onclick="return confirm('¿Cerrar?')">Cerrar Sesión</a>
    </div>
  </nav>

  <?php if ($action === 'list'): ?>
    <section class="hero">
      <div class="hero__badge">NaturES — Panel de Gestión</div>
      <h1>Gestión de <span>Bienestar</span></h1>
      <p>Catálogo unificado y administración de existencias en tiempo real.</p>
    </section>

    <div class="stats">
      <div class="stat-item"><strong><?= $stats['total'] ?></strong><span>Productos</span></div>
      <div class="stat-item"><strong><?= $stats['stock'] ?></strong><span>Stock Total</span></div>
      <div class="stat-item"><strong><?= $stats['cats'] ?></strong><span>Categorías</span></div>
    </div>

    <main class="page-container">
      <?php if (isset($error) && $error): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; text-align: center; font-weight: 700; border: 1px solid #fecaca;">
          ❌ <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($msg): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; text-align: center; font-weight: 700;">
          <?= $msg === 'saved' ? '✅ Guardado' : ($msg === 'updated' ? '✅ Actualizado' : '🗑️ Eliminado') ?>
        </div>
      <?php endif; ?>

      <div class="controls">
        <div class="filter-bar">
          <button class="filter-btn active" onclick="filterProducts('all', this)">Todos</button>
          <?php foreach ($categorias as $cat): ?>
            <button class="filter-btn" onclick="filterProducts('<?= getCatSlug($cat['nombre_categoria']) ?>', this)">
              <?= getEmoji($cat['nombre_categoria']) ?> <?= $cat['nombre_categoria'] ?>
            </button>
          <?php endforeach; ?>
        </div>
        <a href="index.php?action=add" class="btn btn--primary">+ Añadir Producto</a>
      </div>

      <section class="products-grid" id="main-grid">
        <?php foreach ($productos as $p): ?>
          <article class="product-card" data-cat="<?= getCatSlug($p['nombre_categoria']) ?>">
            <div class="card-img">
              <?php if ($p['imagen_url']): ?>
                <img src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="Img">
              <?php else: ?>
                <?= getEmoji($p['nombre_categoria']) ?>
              <?php endif; ?>
              <span class="card-badge"><?= $p['nombre_categoria'] ?></span>
            </div>
            <div class="card-body">
              <h3 style="font-weight: 700; font-size: 1.1rem;"><?= htmlspecialchars($p['nombre_producto']) ?></h3>
              <p style="font-size: .8rem; color: #666; margin-top: .4rem;"><?= htmlspecialchars(mb_strimwidth($p['descripcion'],0,60,'...')) ?></p>
              <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
                <span class="card-price"><?= number_format($p['precio'], 2, ',', '.') ?>€</span>
                <span style="font-size:.7rem; color:#888;">Stock: <?= $p['cantidad_stock'] ?></span>
              </div>
            </div>
            <div class="card-footer">
              <a href="index.php?action=edit&id=<?= $p['id_producto'] ?>" class="btn btn--edit">Editar</a>
              <a href="index.php?action=delete&id=<?= $p['id_producto'] ?>" class="btn btn--del" onclick="return confirm('¿Eliminar?')">Eliminar</a>
            </div>
          </article>
        <?php endforeach; ?>
      </section>

      <!-- Vista tabla (complementaria) -->
      <div class="table-section" style="margin-top: 3rem;">
        <p style="font-weight: 700; color: var(--verde-oscuro); margin-bottom: 1rem; display: flex; align-items: center; gap: .5rem;">📋 Vista de tabla completa</p>
        <div style="overflow-x: auto; border-radius: var(--radio); box-shadow: var(--sombra-md); background: white;">
          <table style="width: 100%; border-collapse: collapse; font-size: .85rem;">
            <thead style="background: var(--verde-oscuro); color: white;">
              <tr>
                <th style="padding: 1rem; text-align: left;">ID</th>
                <th style="padding: 1rem; text-align: left;">Producto</th>
                <th style="padding: 1rem; text-align: left;">Categoría</th>
                <th style="padding: 1rem; text-align: left;">Marca</th>
                <th style="padding: 1rem; text-align: left;">Stock</th>
                <th style="padding: 1rem; text-align: left;">Precio</th>
                <th style="padding: 1rem; text-align: center;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productos as $p): ?>
                <tr class="product-row" data-cat="<?= getCatSlug($p['nombre_categoria']) ?>" style="border-bottom: 1px solid #eee;">
                  <td style="padding: 1rem;"><?= $p['id_producto'] ?></td>
                  <td style="padding: 1rem; font-weight: 600;"><?= htmlspecialchars($p['nombre_producto']) ?></td>
                  <td style="padding: 1rem;"><span style="background: var(--crema); color: var(--verde-medio); padding: .2rem .6rem; border-radius: 999px; font-size: .75rem; font-weight: 700;"><?= getEmoji($p['nombre_categoria']) ?> <?= $p['nombre_categoria'] ?></span></td>
                  <td style="padding: 1rem;"><?= htmlspecialchars($p['marca']) ?></td>
                  <td style="padding: 1rem; <?= $p['cantidad_stock'] < 10 ? 'color: red; font-weight: 700;' : '' ?>"><?= $p['cantidad_stock'] ?></td>
                  <td style="padding: 1rem; font-weight: 700;"><?= number_format($p['precio'], 2, ',', '.') ?>€</td>
                  <td style="padding: 1rem; text-align: center;">
                    <a href="index.php?action=edit&id=<?= $p['id_producto'] ?>" style="color: var(--verde-medio); text-decoration: none; font-weight: 700; margin-right: 0.5rem;">Editar</a>
                    <a href="index.php?action=delete&id=<?= $p['id_producto'] ?>" style="color: #dc2626; text-decoration: none; font-weight: 700;" onclick="return confirm('¿Eliminar?')">Borrar</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>

  <?php else: ?>
    <!-- VISTA FORMULARIO -->
    <div style="background: var(--verde-oscuro); height: 120px;"></div>
    <main class="page-container">
      <div class="form-card">
        <div class="form-header">
          <div style="font-size: 1.5rem;">🌿</div>
          <div>
            <h2 style="margin:0; font-size:1.4rem;"><?= $action==='edit'?'Editar':'Añadir' ?> Producto</h2>
            <p style="margin:0; font-size:.8rem; opacity:.8;">Formulario de gestión avanzada</p>
          </div>
        </div>
        <div class="form-body">
          <form action="index.php" method="POST">
            <input type="hidden" name="action" value="save">
            <?php if($producto_edit): ?><input type="hidden" name="id_producto" value="<?= $producto_edit['id_producto'] ?>"><?php endif; ?>
            
            <div class="form-grid">
              <!-- Información Básica -->
              <div class="form-group full">
                <label>Nombre del Producto *</label>
                <input type="text" name="nombre_producto" required value="<?= htmlspecialchars($producto_edit['nombre_producto']??'') ?>" placeholder="Ej: Aceite de Almendra Dulce">
              </div>
              
              <div class="form-group">
                <label>Categoría *</label>
                <select name="id_categoria" required>
                  <?php foreach($categorias as $cat): ?>
                    <option value="<?= $cat['id_categoria'] ?>" <?= ($producto_edit && $producto_edit['id_categoria']==$cat['id_categoria'])?'selected':'' ?>><?= $cat['nombre_categoria'] ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Marca</label>
                <input type="text" name="marca" value="<?= htmlspecialchars($producto_edit['marca']??'') ?>" placeholder="Ej: NaturalCare">
              </div>
              
              <div class="form-group">
                <label>Precio (€) *</label>
                <input type="number" step="0.01" name="precio" required value="<?= $producto_edit['precio']??'' ?>" placeholder="0.00">
              </div>
              <div class="form-group">
                <label>Stock</label>
                <input type="number" name="cantidad_stock" value="<?= $producto_edit['cantidad_stock']??'0' ?>">
              </div>
              
              <div class="form-group full">
                <label>Presentación</label>
                <input type="text" name="presentacion" value="<?= htmlspecialchars($producto_edit['presentacion']??'') ?>" placeholder="Ej: Botella 250ml">
              </div>

              <!-- Imagen y Descripción -->
              <div class="form-group full">
                <label>Imagen URL</label>
                <input type="url" name="imagen_url" value="<?= htmlspecialchars($producto_edit['imagen_url']??'') ?>" placeholder="https://example.com/imagen.jpg">
              </div>
              <div class="form-group full">
                <label>Descripción</label>
                <textarea name="descripcion" rows="2" placeholder="Breve descripción..."><?= htmlspecialchars($producto_edit['descripcion']??'') ?></textarea>
              </div>
              
              <div class="form-group">
                <label>Beneficios</label>
                <textarea name="beneficios" rows="2" placeholder="Principales beneficios..."><?= htmlspecialchars($producto_edit['beneficios']??'') ?></textarea>
              </div>
              <div class="form-group">
                <label>Instrucciones de Uso</label>
                <textarea name="instrucciones_uso" rows="2" placeholder="Cómo usar..."><?= htmlspecialchars($producto_edit['instrucciones_uso']??'') ?></textarea>
              </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem; border-top: 1px solid #eee; padding-top: 1.5rem;">
              <button type="submit" class="btn btn--primary" style="background:var(--verde-oscuro);">Guardar Producto</button>
              <a href="index.php" class="btn" style="background:#eee; color:#666; max-width: 150px;">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </main>
  <?php endif; ?>

  <footer>NaturES — Sistema Unificado &copy; 2026</footer>

  <script>
  function filterProducts(cat, btn) {
    // Buttons state
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Filter cards
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(card => {
      if (cat === 'all' || card.getAttribute('data-cat') === cat) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });

    // Filter table rows
    const rows = document.querySelectorAll('.product-row');
    rows.forEach(row => {
      if (cat === 'all' || row.getAttribute('data-cat') === cat) {
        row.style.display = 'table-row';
      } else {
        row.style.display = 'none';
      }
    });
  }
  </script>
</body>
</html>
