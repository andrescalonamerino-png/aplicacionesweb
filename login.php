<?php
/**
 * login.php - Fusión de Página de Inicio + Login NaturES
 */
require_once 'config.php';

if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if ($user === 'alumno13' && $pass === 'Andres1t0') {
        $_SESSION['usuario'] = $user;
        header("Location: index.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="NaturES — Bienestar natural. Acceso al panel de gestión." />
  <title>NaturES — Inicio y Acceso</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap"
    rel="stylesheet"
  />

  <style>
    /* VARIABLES UNIFICADAS */
    :root {
      --verde-oscuro:  #1a3c2b;
      --verde-medio:   #2d6a4f;
      --verde-claro:   #52b788;
      --verde-suave:   #b7e4c7;
      --crema:         #f8f4ef;
      --crema-oscura:  #ede8e0;
      --dorado:        #c9a84c;
      --dorado-claro:  #f0d080;
      --texto:         #1e2d24;
      --blanco:        #ffffff;
      --radio:         16px;
      --t:             .28s ease;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: var(--verde-oscuro); color: var(--blanco); min-height: 100vh; overflow-x: hidden; display: flex; flex-direction: column; }

    /* FONDO ANIMADO */
    .bg-layer {
      position: fixed; inset: 0; z-index: 0;
      background:
        radial-gradient(ellipse 80% 60% at 15% 20%, rgba(82,183,136,0.2) 0%, transparent 60%),
        radial-gradient(ellipse 60% 50% at 85% 80%, rgba(201,168,76,0.14) 0%, transparent 60%),
        radial-gradient(ellipse 50% 70% at 50% 50%, rgba(45,106,79,0.3) 0%, transparent 70%),
        linear-gradient(160deg, #0d2318 0%, #1a3c2b 40%, #243d30 100%);
      animation: bg-pulse 10s ease-in-out infinite alternate;
    }
    @keyframes bg-pulse { 0% { opacity: 1; } 100% { opacity: .85; } }

    .particle { position: fixed; border-radius: 50%; opacity: 0; animation: float-up linear infinite; z-index: 1; }
    @keyframes float-up { 0% { transform: translateY(100vh) scale(0); opacity: 0; } 10% { opacity: .6; } 90% { opacity: .2; } 100% { transform: translateY(-20vh) scale(1); opacity: 0; } }

    /* NAVBAR */
    .navbar { position: relative; z-index: 100; padding: 1.5rem 3rem; display: flex; align-items: center; justify-content: space-between; }
    .logo { display: flex; align-items: center; gap: .7rem; text-decoration: none; color: white; }
    .logo__leaf { width: 44px; height: 44px; background: linear-gradient(135deg, var(--verde-claro), var(--dorado)); border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: grid; place-items: center; }
    .logo__leaf span { transform: rotate(45deg); font-size: 1.2rem; }
    .logo__text { font-family: 'Playfair Display', serif; font-size: 1.8rem; letter-spacing: .02em; }
    .logo__text em { color: var(--verde-claro); font-style: normal; }

    /* MAIN CONTENT */
    .container { position: relative; z-index: 10; flex: 1; display: grid; grid-template-columns: 1.2fr 1fr; align-items: center; padding: 2rem 5rem; max-width: 1400px; margin: 0 auto; gap: 4rem; }

    /* LADO IZQUIERDO: HERO */
    .hero { text-align: left; }
    .hero__badge { display: inline-flex; align-items: center; gap: .5rem; background: rgba(82,183,136,0.15); border: 1px solid rgba(82,183,136,0.3); color: var(--verde-suave); font-size: .8rem; font-weight: 700; text-transform: uppercase; padding: .4rem 1rem; border-radius: 999px; margin-bottom: 2rem; }
    .hero__title { font-family: 'Playfair Display', serif; font-size: 4rem; line-height: 1.1; margin-bottom: 1.5rem; }
    .hero__title span { color: var(--dorado-claro); }
    .hero__desc { font-size: 1.1rem; color: rgba(183,228,199,0.8); max-width: 500px; line-height: 1.7; margin-bottom: 2.5rem; }

    .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; }
    .feat { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 1.2rem; border-radius: 12px; backdrop-filter: blur(5px); }
    .feat h3 { font-size: .95rem; margin-bottom: .3rem; color: var(--verde-suave); }
    .feat p { font-size: .8rem; color: rgba(255,255,255,0.5); }

    /* LADO DERECHO: LOGIN */
    .login-card { background: rgba(255,255,255,0.04); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.12); padding: 3rem; border-radius: 28px; box-shadow: 0 30px 80px rgba(0,0,0,0.3); animation: fade-in .8s ease; }
    @keyframes fade-in { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
    
    .login-card h2 { font-family: 'Playfair Display', serif; margin-bottom: .5rem; font-size: 1.8rem; text-align: center; }
    .login-card p.subtitle { text-align: center; font-size: .85rem; color: var(--verde-suave); margin-bottom: 2rem; opacity: 0.7; }

    .form-group { margin-bottom: 1.5rem; }
    label { display: block; font-size: .75rem; font-weight: 700; text-transform: uppercase; color: var(--verde-claro); margin-bottom: .6rem; letter-spacing: .05em; }
    input { width: 100%; padding: 1rem; border-radius: 12px; border: 1.5px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white; outline: none; transition: all var(--t); font-family: inherit; }
    input:focus { border-color: var(--verde-claro); background: rgba(0,0,0,0.3); box-shadow: 0 0 0 4px rgba(82,183,136,0.1); }

    .btn-submit { width: 100%; padding: 1.1rem; border: none; border-radius: 12px; background: linear-gradient(135deg, var(--verde-claro), var(--dorado)); color: var(--verde-oscuro); font-weight: 800; font-size: 1rem; cursor: pointer; transition: all .3s; margin-top: 1rem; box-shadow: 0 10px 30px rgba(82,183,136,0.25); }
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(82,183,136,0.4); opacity: .95; }

    .error { background: rgba(220, 38, 38, 0.15); border: 1px solid rgba(220, 38, 38, 0.4); color: #fecaca; padding: 0.8rem; border-radius: 8px; font-size: 0.85rem; text-align: center; margin-bottom: 1.5rem; }

    footer { text-align: center; padding: 2rem; font-size: .75rem; color: rgba(255,255,255,0.2); position: relative; z-index: 10; }

    @media (max-width: 1100px) { .container { grid-template-columns: 1fr; padding: 2rem; text-align: center; } .hero { text-align: center; } .hero__desc { margin: 0 auto 2.5rem; } .features { display: none; } }
  </style>
</head>
<body>

  <div class="bg-layer" aria-hidden="true"></div>

  <nav class="navbar">
    <a href="index.php" class="logo">
      <div class="logo__leaf"><span>🌿</span></div>
      <span class="logo__text">Natur<em>ES</em></span>
    </a>
  </nav>

  <main class="container">
    <div class="hero">
      <span class="hero__badge">
        <span>🌱</span> Gestión de productos naturales
      </span>
      <h1 class="hero__title">
        Bienestar natural,<br>
        <span>en tus manos</span>
      </h1>
      <p class="hero__desc">
        <strong>NaturES</strong> es tu aplicación para gestionar el catálogo de productos de masaje.
        Explora, añade y organiza aceites, velas aromáticas y herramientas de bienestar.
      </p>
      
      <div class="features">
        <div class="feat">
          <span style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;">🧴</span>
          <h3>Catálogo completo</h3>
          <p>Visualiza todos los productos organizados por categoría con stock y precio en tiempo real.</p>
        </div>
        <div class="feat">
          <span style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;">➕</span>
          <h3>Alta de productos</h3>
          <p>Formulario guiado para registrar nuevos productos con categoría, marca, y beneficios.</p>
        </div>
        <div class="feat">
          <span style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;">🔒</span>
          <h3>Base de datos segura</h3>
          <p>Protección contra inyección SQL en todas las operaciones del catálogo.</p>
        </div>
      </div>
    </div>

    <div class="login-card">
      <h2>Acceso Seguro</h2>
      <p class="subtitle">Identifícate para gestionar el catálogo</p>

      <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <div class="form-group">
          <label>Usuario</label>
          <input type="text" name="user" required placeholder="Introduce tu usuario" autofocus />
        </div>
        <div class="form-group">
          <label>Contraseña</label>
          <input type="password" name="pass" required placeholder="••••••••" />
        </div>
        <button type="submit" class="btn-submit">Entrar al Sistema →</button>
      </form>
    </div>
  </main>

  <footer>
    <strong>NaturES</strong> — Bienestar Natural &copy; 2026
  </footer>

  <script>
    const colors = ['#52b788', '#b7e4c7', '#c9a84c', '#f0d080'];
    for (let i = 0; i < 20; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 6 + 3;
        p.style.cssText = `
            width: ${size}px;
            height: ${size}px;
            left: ${Math.random() * 100}vw;
            background: ${colors[Math.floor(Math.random() * colors.length)]};
            animation-duration: ${Math.random() * 15 + 10}s;
            animation-delay: ${Math.random() * 10}s;
        `;
        document.body.appendChild(p);
    }
  </script>
</body>
</html>
