<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Auth.php';

$router = new Router();

// Auth routes
$router->add('GET', '/login', 'shared/AuthController', 'showLoginForm');
$router->add('POST', '/login', 'shared/AuthController', 'login');
$router->add('POST', '/logout', 'shared/AuthController', 'logout');

// Home route
$router->add('GET', '/', 'shared/HomeController', 'index');

// About route
$router->add('GET', '/about', 'shared/AboutController', 'index');

// Profesor routes
$router->add('GET', '/profesor/cursos', 'profesor/CursoController', 'index');
$router->add('GET', '/profesor/cursos/create', 'profesor/CursoController', 'create');
$router->add('POST', '/profesor/cursos/create', 'profesor/CursoController', 'create');
$router->add('GET', '/profesor/cursos/edit/{id}', 'profesor/CursoController', 'edit');
$router->add('POST', '/profesor/cursos/edit/{id}', 'profesor/CursoController', 'edit');
$router->add('POST', '/profesor/cursos/delete/{id}', 'profesor/CursoController', 'delete');

// Rutas de lecciones
$router->add('GET', '/profesor/cursos/{id}/lecciones', 'profesor/LeccionController', 'index');
$router->add('GET', '/profesor/cursos/{id}/lecciones/create', 'profesor/LeccionController', 'create');
$router->add('POST', '/profesor/cursos/{id}/lecciones/create', 'profesor/LeccionController', 'create');
$router->add('GET', '/profesor/lecciones/edit/{id}', 'profesor/LeccionController', 'edit');
$router->add('POST', '/profesor/lecciones/edit/{id}', 'profesor/LeccionController', 'edit');
$router->add('POST', '/profesor/lecciones/delete/{id}', 'profesor/LeccionController', 'delete');

// Rutas de teoría
$router->add('GET', '/profesor/lecciones/{id}/teoria', 'profesor/TeoriaController', 'index');
$router->add('GET', '/profesor/lecciones/{id}/teoria/create', 'profesor/TeoriaController', 'create');
$router->add('POST', '/profesor/lecciones/{id}/teoria/create', 'profesor/TeoriaController', 'create');
$router->add('GET', '/profesor/teoria/edit/{id}', 'profesor/TeoriaController', 'edit');
$router->add('POST', '/profesor/teoria/edit/{id}', 'profesor/TeoriaController', 'edit');
$router->add('POST', '/profesor/teoria/delete/{id}', 'profesor/TeoriaController', 'delete');

// Rutas de actividades
$router->add('GET', '/profesor/lecciones/{id}/actividades', 'profesor/ActividadController', 'index');
$router->add('GET', '/profesor/lecciones/{id}/actividades/create', 'profesor/ActividadController', 'create');
$router->add('POST', '/profesor/lecciones/{id}/actividades/create', 'profesor/ActividadController', 'create');
$router->add('GET', '/profesor/actividades/config/{tipo}/{id}', 'profesor/ActividadController', 'config');
$router->add('POST', '/profesor/actividades/config/{tipo}/{id}', 'profesor/ActividadController', 'config');
$router->add('GET', '/profesor/actividad/edit/{id}', 'profesor/ActividadController', 'edit');
$router->add('POST', '/profesor/actividad/edit/{id}', 'profesor/ActividadController', 'edit');
$router->add('POST', '/profesor/actividad/delete/{id}', 'profesor/ActividadController', 'delete');
$router->add('GET', '/profesor/actividad/{id}/preview', 'profesor/ActividadController', 'preview');
$router->add('GET', '/profesor/actividad/{id}/configurar', 'profesor/ActividadController', 'configurar');

// Rutas de actividades para estudiantes
$router->add('GET', '/estudiante/actividad/{id}', 'estudiante/EstudianteActividadController', 'index');
$router->add('POST', '/estudiante/actividad/guardar-respuesta', 'estudiante/EstudianteActividadController', 'guardarRespuesta');

// Rutas de calificaciones (Profesor)
$router->add('GET', '/profesor/calificaciones', 'profesor/CalificacionesController', 'index');
$router->add('GET', '/profesor/calificaciones/curso/{id}', 'profesor/CalificacionesController', 'curso');
$router->add('GET', '/profesor/calificaciones/revisar/{id}', 'profesor/CalificacionesController', 'revisar');
$router->add('POST', '/profesor/calificaciones/calificar/{id}', 'profesor/CalificacionesController', 'calificar');

// Placeholder: profesor estudiantes
$router->add('GET', '/profesor/estudiantes', 'profesor/EstudiantesController', 'index');

// Estudiante routes
$router->add('GET', '/estudiante', 'estudiante/EstudianteController', 'index');
$router->add('GET', '/estudiante/cursos', 'estudiante/EstudianteController', 'index');
$router->add('POST', '/estudiante/inscribir/{id}', 'estudiante/EstudianteController', 'inscribir');
$router->add('GET', '/estudiante/cursos/{id}/continuar', 'estudiante/EstudianteController', 'continuarCurso');
$router->add('GET', '/estudiante/cursos/{id}/lecciones', 'estudiante/EstudianteController', 'lecciones');
$router->add('GET', '/estudiante/lecciones/{id}/contenido', 'estudiante/EstudianteController', 'contenidoLeccion');
$router->add('GET', '/estudiante/actividades/{id}', 'estudiante/EstudianteController', 'realizarActividad');
$router->add('POST', '/estudiante/actividades/{id}/responder', 'estudiante/EstudianteController', 'responderActividad');
$router->add('POST', '/estudiante/teoria/{id}/leer', 'estudiante/EstudianteController', 'marcarTeoria');
// $router->add('GET', '/estudiante/curso/{id}', 'estudiante/EstudianteController', 'curso'); // Removed as redundant
// Placeholder: estudiante progreso y calificaciones
$router->add('GET', '/estudiante/progreso', 'estudiante/ProgresoController', 'index');
$router->add('GET', '/estudiante/calificaciones', 'estudiante/CalificacionesController', 'index');

// Admin routes (placeholders)
$router->add('GET', '/admin', 'admin/AdminController', 'index');
$router->add('GET', '/admin/usuarios', 'admin/AdminController', 'usuarios');
$router->add('GET', '/admin/usuarios/edit/{id}', 'admin/AdminController', 'editUsuario');
$router->add('POST', '/admin/usuarios/edit/{id}', 'admin/AdminController', 'editUsuario');
$router->add('POST', '/admin/usuarios/delete/{id}', 'admin/AdminController', 'deleteUsuario');
$router->add('GET', '/admin/cursos', 'admin/AdminController', 'cursos');

// Register routes (placeholders)
$router->add('GET', '/register', 'shared/RegisterController', 'showRegisterForm');
$router->add('POST', '/register', 'shared/RegisterController', 'register');

$uri = strtok($_SERVER['REQUEST_URI'], '?');
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);
