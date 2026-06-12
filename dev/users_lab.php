<?php

require_once __DIR__ . '/../config/database.php';

if (!defined('BASE_URL')) {
    $scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/dev/users_lab.php'));
    $applicationDirectory = str_replace('\\', '/', dirname($scriptDirectory));
    define('BASE_URL', $applicationDirectory === '/' || $applicationDirectory === '.' ? '' : rtrim($applicationDirectory, '/'));
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';

$devToolsConfig = require __DIR__ . '/../config/dev_tools.php';
if (empty($devToolsConfig['enabled'])) {
    http_response_code(403);
    echo '403 Forbidden';
    exit;
}

$db = new Database();
$message = '';
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_csrf();

    $action = trim((string) ($_POST['action'] ?? ''));
    $userId = (int) ($_POST['user_id'] ?? 0);

    try {
        if ($action === 'create' || $action === 'update') {
            $nombre = trim((string) ($_POST['nombre'] ?? ''));
            $apellido = trim((string) ($_POST['apellido'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $rol = trim((string) ($_POST['rol'] ?? 'estudiante'));
            $instanciaId = (int) ($_POST['instancia_id'] ?? 0);
            $activo = isset($_POST['activo']) ? 1 : 0;
            $emailVerificado = isset($_POST['email_verificado']) ? 1 : 0;
            $isOfficial = isset($_POST['is_official']) ? 1 : 0;
            $billingPlan = trim((string) ($_POST['billing_plan'] ?? 'free'));
            $idiomaBase = trim((string) ($_POST['idioma_base'] ?? 'espanol'));
            $idiomaInterfaz = trim((string) ($_POST['idioma_interfaz'] ?? 'espanol'));
            $vistaDefault = trim((string) ($_POST['vista_default'] ?? 'estudiante'));
            $intentosFallidos = max(0, (int) ($_POST['intentos_fallidos'] ?? 0));

            if (!in_array($billingPlan, ['free', 'paid', 'lifetime'], true)) {
                throw new RuntimeException('Plan no valido.');
            }

            if (!in_array($rol, ['estudiante', 'profesor', 'admin'], true)) {
                throw new RuntimeException('Rol no valido.');
            }

            $isAdmin = $rol === 'admin' ? 1 : 0;
            $isProfessor = $rol === 'profesor' ? 1 : 0;
            $isStudent = $rol === 'estudiante' ? 1 : 0;

            if ($nombre === '' || $apellido === '' || $email === '') {
                throw new RuntimeException('Nombre, apellido y correo son obligatorios.');
            }

            if ($instanciaId <= 0) {
                throw new RuntimeException('La instancia es obligatoria.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Correo invalido.');
            }

            if ($action === 'create') {
                if (strlen($password) < 8) {
                    throw new RuntimeException('La contrasena debe tener al menos 8 caracteres.');
                }

                $db->query('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
                $db->bind(':email', $email);
                if ($db->single()) {
                    throw new RuntimeException('Ese correo ya existe.');
                }

                $db->query('
                    INSERT INTO usuarios (
                        instancia_id, email, password_hash, nombre, apellido,
                        idioma_base, idioma_interfaz,
                        es_estudiante, es_profesor, es_admin_institucion,
                        billing_plan, is_official, vista_default, activo, email_verificado, creado_por, creado_en
                    ) VALUES (
                        :instancia_id, :email, :password_hash, :nombre, :apellido,
                        :idioma_base, :idioma_interfaz,
                        :es_estudiante, :es_profesor, :es_admin_institucion,
                        :billing_plan, :is_official, :vista_default, :activo, :email_verificado, :creado_por, NOW()
                    )
                ');
                $db->bind(':instancia_id', $instanciaId);
                $db->bind(':email', $email);
                $db->bind(':password_hash', password_hash($password, PASSWORD_BCRYPT));
                $db->bind(':nombre', $nombre);
                $db->bind(':apellido', $apellido);
                $db->bind(':idioma_base', $idiomaBase);
                $db->bind(':idioma_interfaz', $idiomaInterfaz);
                $db->bind(':es_estudiante', $isStudent);
                $db->bind(':es_profesor', $isProfessor);
                $db->bind(':es_admin_institucion', $isAdmin);
                $db->bind(':billing_plan', $billingPlan);
                $db->bind(':is_official', $isOfficial);
                $db->bind(':vista_default', $vistaDefault);
                $db->bind(':activo', $activo);
                $db->bind(':email_verificado', $emailVerificado);
                $db->bind(':creado_por', Auth::getUserId());
                $db->execute();
                $message = 'Usuario creado.';
            } else {
                $db->query('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
                $db->bind(':id', $userId);
                $current = $db->single();
                if (!$current) {
                    throw new RuntimeException('Usuario no encontrado.');
                }

                $db->query('SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1');
                $db->bind(':email', $email);
                $db->bind(':id', $userId);
                if ($db->single()) {
                    throw new RuntimeException('Ese correo ya pertenece a otro usuario.');
                }

                $sql = '
                    UPDATE usuarios
                    SET instancia_id = :instancia_id,
                        nombre = :nombre,
                        apellido = :apellido,
                        email = :email,
                        es_estudiante = :es_estudiante,
                        es_profesor = :es_profesor,
                        es_admin_institucion = :es_admin_institucion,
                        billing_plan = :billing_plan,
                        is_official = :is_official,
                        activo = :activo,
                        email_verificado = :email_verificado,
                        idioma_base = :idioma_base,
                        idioma_interfaz = :idioma_interfaz,
                        vista_default = :vista_default,
                        intentos_fallidos = :intentos_fallidos
                ';
                if ($password !== '') {
                    if (strlen($password) < 8) {
                        throw new RuntimeException('La contrasena nueva debe tener al menos 8 caracteres.');
                    }
                    $sql .= ', password_hash = :password_hash';
                }
                $sql .= ' WHERE id = :id';

                $db->query($sql);
                $db->bind(':instancia_id', $instanciaId);
                $db->bind(':nombre', $nombre);
                $db->bind(':apellido', $apellido);
                $db->bind(':email', $email);
                $db->bind(':es_estudiante', $isStudent);
                $db->bind(':es_profesor', $isProfessor);
                $db->bind(':es_admin_institucion', $isAdmin);
                $db->bind(':billing_plan', $billingPlan);
                $db->bind(':is_official', $isOfficial);
                $db->bind(':activo', $activo);
                $db->bind(':email_verificado', $emailVerificado);
                $db->bind(':idioma_base', $idiomaBase);
                $db->bind(':idioma_interfaz', $idiomaInterfaz);
                $db->bind(':vista_default', $vistaDefault);
                $db->bind(':intentos_fallidos', $intentosFallidos);
                if ($password !== '') {
                    $db->bind(':password_hash', password_hash($password, PASSWORD_BCRYPT));
                }
                $db->bind(':id', $userId);
                $db->execute();
                $message = 'Usuario actualizado.';
            }
        } elseif ($action === 'toggle') {
            if (Auth::isLoggedIn() && $userId === (int) Auth::getUserId()) {
                throw new RuntimeException('No puedes cambiar tu propio acceso desde aqui.');
            }
            $db->query('UPDATE usuarios SET activo = CASE WHEN activo = 1 THEN 0 ELSE 1 END WHERE id = :id LIMIT 1');
            $db->bind(':id', $userId);
            $db->execute();
            $message = 'Estado alternado.';
        } elseif ($action === 'delete') {
            if (Auth::isLoggedIn() && $userId === (int) Auth::getUserId()) {
                throw new RuntimeException('No puedes eliminar tu propia cuenta.');
            }
            $db->query('DELETE FROM usuarios WHERE id = :id LIMIT 1');
            $db->bind(':id', $userId);
            $db->execute();
            $message = 'Usuario eliminado.';
        } else {
            throw new RuntimeException('Accion no valida.');
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$search = trim((string) ($_GET['search'] ?? ''));
$roleFilter = trim((string) ($_GET['role'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));

$usersSql = 'SELECT * FROM usuarios WHERE 1 = 1';
$userParams = [];
if ($search !== '') {
    $usersSql .= ' AND (nombre LIKE :search OR apellido LIKE :search OR email LIKE :search OR id = :exact_id)';
    $userParams[':search'] = '%' . $search . '%';
    $userParams[':exact_id'] = ctype_digit($search) ? (int) $search : 0;
}
if ($roleFilter === 'admin') {
    $usersSql .= ' AND es_admin_institucion = 1';
} elseif ($roleFilter === 'profesor') {
    $usersSql .= ' AND es_profesor = 1';
} elseif ($roleFilter === 'estudiante') {
    $usersSql .= ' AND es_estudiante = 1';
}
if ($statusFilter === 'active') {
    $usersSql .= ' AND activo = 1';
} elseif ($statusFilter === 'inactive') {
    $usersSql .= ' AND activo = 0';
}
$usersSql .= ' ORDER BY creado_en DESC, id DESC';

$db->query($usersSql);
foreach ($userParams as $param => $value) {
    $db->bind($param, $value);
}
$users = $db->resultSet();
$db->query('SELECT id FROM instancias ORDER BY id ASC LIMIT 1');
$firstInstance = $db->single();
$defaultInstanceId = Auth::getInstanciaId() ?: ($firstInstance->id ?? 1);

require_once __DIR__ . '/../views/partials/header.php';
?>
<div class="container py-4">
    <div class="page-hero content-hero mb-4">
        <span class="eyebrow"><i class="bi bi-flask"></i> Laboratorio</span>
        <h1 class="page-title">CRUD de usuarios para pruebas</h1>
        <p class="page-subtitle">Crea, consulta, edita y elimina usuarios desde una sola herramienta.</p>
        <div class="mt-3 d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="<?php echo url('/dev/'); ?>">
                <i class="bi bi-arrow-left"></i> Herramientas de desarrollo
            </a>
            <a class="btn btn-primary" href="#crear-usuario">
                <i class="bi bi-person-plus"></i> Crear usuario
            </a>
            <a class="btn btn-outline-primary" href="#usuarios">
                <i class="bi bi-people"></i> Ver usuarios
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card mb-4" id="crear-usuario">
        <div class="card-body">
            <h2 class="h5 mb-3">Crear usuario</h2>
            <form method="POST" class="row g-3">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="action" value="create">
                <div class="col-md-3"><input class="form-control" name="nombre" placeholder="Nombre"></div>
                <div class="col-md-3"><input class="form-control" name="apellido" placeholder="Apellido"></div>
                <div class="col-md-3"><input class="form-control" name="email" placeholder="email@ejemplo.com"></div>
                <div class="col-md-3"><input class="form-control" name="password" placeholder="Contrasena"></div>
                <div class="col-md-2"><input class="form-control" name="instancia_id" value="<?php echo (int) $defaultInstanceId; ?>" required></div>
                <div class="col-md-2">
                    <select class="form-select" name="rol">
                        <option value="estudiante">Estudiante</option>
                        <option value="profesor">Profesor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="billing_plan">
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
                <div class="col-md-2"><input class="form-control" name="idioma_base" value="espanol" placeholder="Idioma base"></div>
                <div class="col-md-2"><input class="form-control" name="idioma_interfaz" value="espanol" placeholder="Idioma interfaz"></div>
                <div class="col-md-2">
                    <select class="form-select" name="vista_default">
                        <option value="estudiante">Vista estudiante</option>
                        <option value="creador">Vista creador</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex flex-wrap gap-3 align-items-center">
                    <label class="form-check"><input class="form-check-input" type="checkbox" name="activo" checked> Activo</label>
                    <label class="form-check"><input class="form-check-input" type="checkbox" name="email_verificado"> Email verificado</label>
                    <label class="form-check"><input class="form-check-input" type="checkbox" name="is_official"> Oficial</label>
                    <button class="btn btn-primary" type="submit">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label" for="lab_search">Buscar todos los usuarios</label>
                    <input class="form-control" id="lab_search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ID, nombre o correo">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="lab_role">Rol</label>
                    <select class="form-select" id="lab_role" name="role">
                        <option value="">Todos</option>
                        <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="profesor" <?php echo $roleFilter === 'profesor' ? 'selected' : ''; ?>>Profesor</option>
                        <option value="estudiante" <?php echo $roleFilter === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="lab_status">Estado</label>
                    <select class="form-select" id="lab_status" name="status">
                        <option value="">Todos</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Activos</option>
                        <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3" id="usuarios">
        <h2 class="h4 mb-0">Usuarios</h2>
        <span class="badge text-bg-secondary"><?php echo count($users); ?> encontrados</span>
    </div>

    <div class="d-grid gap-3">
        <?php if (empty($users)): ?>
            <div class="alert alert-info">No hay usuarios que coincidan con los filtros.</div>
        <?php endif; ?>
        <?php foreach ($users as $user): ?>
            <?php $userRole = $user->es_admin_institucion ? 'admin' : ($user->es_profesor ? 'profesor' : 'estudiante'); ?>
            <details class="card">
                <summary class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2" style="cursor:pointer">
                    <span><strong>#<?php echo (int) $user->id; ?> <?php echo htmlspecialchars(trim($user->nombre . ' ' . $user->apellido)); ?></strong><br><small><?php echo htmlspecialchars($user->email); ?></small></span>
                    <span>
                        <span class="badge text-bg-<?php echo !empty($user->activo) ? 'success' : 'secondary'; ?>"><?php echo !empty($user->activo) ? 'Activo' : 'Inactivo'; ?></span>
                        <span class="badge text-bg-primary"><?php echo htmlspecialchars($userRole); ?></span>
                    </span>
                </summary>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="user_id" value="<?php echo (int) $user->id; ?>">
                                <input type="hidden" name="action" value="update">
                                <div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control" name="nombre" value="<?php echo htmlspecialchars($user->nombre); ?>" required></div>
                                <div class="col-md-3"><label class="form-label">Apellido</label><input class="form-control" name="apellido" value="<?php echo htmlspecialchars($user->apellido); ?>" required></div>
                                <div class="col-md-4"><label class="form-label">Correo</label><input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required></div>
                                <div class="col-md-2"><label class="form-label">Instancia</label><input type="number" class="form-control" name="instancia_id" value="<?php echo (int) $user->instancia_id; ?>" min="1" required></div>
                                <div class="col-md-3"><label class="form-label">Nueva contraseña</label><input type="password" class="form-control" name="password" placeholder="Dejar vacio para conservar"></div>
                                <div class="col-md-3"><label class="form-label">Rol</label><select class="form-select" name="rol">
                                    <option value="estudiante" <?php echo $userRole === 'estudiante' ? 'selected' : ''; ?>>estudiante</option>
                                    <option value="profesor" <?php echo !empty($user->es_profesor) ? 'selected' : ''; ?>>profesor</option>
                                    <option value="admin" <?php echo !empty($user->es_admin_institucion) ? 'selected' : ''; ?>>admin</option>
                                </select></div>
                                <div class="col-md-2"><label class="form-label">Plan</label><select class="form-select" name="billing_plan">
                                    <?php foreach (['free', 'paid', 'lifetime'] as $plan): ?><option value="<?php echo $plan; ?>" <?php echo ($user->billing_plan ?? 'free') === $plan ? 'selected' : ''; ?>><?php echo ucfirst($plan); ?></option><?php endforeach; ?>
                                </select></div>
                                <div class="col-md-2"><label class="form-label">Intentos fallidos</label><input type="number" class="form-control" name="intentos_fallidos" min="0" value="<?php echo (int) ($user->intentos_fallidos ?? 0); ?>"></div>
                                <div class="col-md-2"><label class="form-label">Idioma base</label><input class="form-control" name="idioma_base" value="<?php echo htmlspecialchars($user->idioma_base ?? 'espanol'); ?>"></div>
                                <div class="col-md-2"><label class="form-label">Interfaz</label><input class="form-control" name="idioma_interfaz" value="<?php echo htmlspecialchars($user->idioma_interfaz ?? 'espanol'); ?>"></div>
                                <div class="col-md-2"><label class="form-label">Vista</label><select class="form-select" name="vista_default">
                                    <option value="estudiante" <?php echo ($user->vista_default ?? '') === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
                                    <option value="creador" <?php echo ($user->vista_default ?? '') === 'creador' ? 'selected' : ''; ?>>Creador</option>
                                </select></div>
                                <div class="col-md-6 d-flex flex-wrap align-items-end gap-3">
                                    <label class="form-check"><input class="form-check-input" type="checkbox" name="activo" <?php echo !empty($user->activo) ? 'checked' : ''; ?>> Activo</label>
                                    <label class="form-check"><input class="form-check-input" type="checkbox" name="email_verificado" <?php echo !empty($user->email_verificado) ? 'checked' : ''; ?>> Verificado</label>
                                    <label class="form-check"><input class="form-check-input" type="checkbox" name="is_official" <?php echo !empty($user->is_official) ? 'checked' : ''; ?>> Oficial</label>
                                </div>
                                <div class="col-12"><button class="btn btn-primary" type="submit">Guardar todos los cambios</button></div>
                    </form>
                    <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                        <form method="POST">
                            <?php echo csrf_input(); ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="user_id" value="<?php echo (int) $user->id; ?>">
                            <button class="btn btn-outline-secondary" type="submit"><?php echo !empty($user->activo) ? 'Desactivar' : 'Activar'; ?></button>
                        </form>
                        <?php if (!Auth::isLoggedIn() || (int) $user->id !== (int) Auth::getUserId()): ?>
                            <form method="POST" onsubmit="return confirm('Eliminar permanentemente este usuario?');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo (int) $user->id; ?>">
                                <button class="btn btn-danger" type="submit">Eliminar usuario</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </details>
        <?php endforeach; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>
