<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/Auth.php';

class AboutController extends Controller {
    public function index() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get user info if logged in
        $userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
        $userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
        $isLoggedIn = isset($_SESSION['user_id']);
        
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Acerca de - ADAPA LMS</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                }
                .container { 
                    max-width: 1000px; 
                    margin: 0 auto; 
                    padding: 20px;
                }
                .header {
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 15px;
                    padding: 30px;
                    margin-bottom: 30px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                    text-align: center;
                }
                .header h1 { 
                    color: #2c3e50; 
                    font-size: 2.5em; 
                    margin-bottom: 10px;
                    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
                }
                .header p {
                    color: #7f8c8d;
                    font-size: 1.2em;
                    margin-bottom: 20px;
                }
                .nav-bar {
                    background: rgba(255, 255, 255, 0.9);
                    border-radius: 10px;
                    padding: 15px;
                    margin-bottom: 20px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                }
                .nav-bar a {
                    color: #3498db;
                    text-decoration: none;
                    margin: 0 15px;
                    font-weight: 500;
                    transition: color 0.3s ease;
                }
                .nav-bar a:hover {
                    color: #2980b9;
                }
                .content {
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 15px;
                    padding: 40px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                }
                .section {
                    margin-bottom: 30px;
                }
                .section h2 {
                    color: #2c3e50;
                    font-size: 1.8em;
                    margin-bottom: 15px;
                    border-bottom: 2px solid #3498db;
                    padding-bottom: 10px;
                }
                .section h3 {
                    color: #34495e;
                    font-size: 1.3em;
                    margin-bottom: 10px;
                    margin-top: 20px;
                }
                .section p {
                    color: #555;
                    font-size: 1.1em;
                    margin-bottom: 15px;
                    text-align: justify;
                }
                .features-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 20px;
                    margin-top: 20px;
                }
                .feature-card {
                    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
                    color: white;
                    padding: 25px;
                    border-radius: 10px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                }
                .feature-card h4 {
                    font-size: 1.3em;
                    margin-bottom: 10px;
                }
                .feature-card p {
                    color: rgba(255,255,255,0.9);
                    font-size: 1em;
                }
                .tech-stack {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    margin-top: 15px;
                }
                .tech-badge {
                    background: #3498db;
                    color: white;
                    padding: 8px 15px;
                    border-radius: 20px;
                    font-size: 0.9em;
                    font-weight: 500;
                }
                .footer {
                    text-align: center;
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid rgba(255,255,255,0.3);
                    color: rgba(255,255,255,0.8);
                }
                .btn {
                    display: inline-block;
                    padding: 12px 25px;
                    background: #3498db;
                    color: white;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
                }
                .btn:hover {
                    background: #2980b9;
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
                }
                .btn-success {
                    background: #27ae60;
                    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
                }
                .btn-success:hover {
                    background: #229954;
                }
                @media (max-width: 768px) {
                    .container {
                        padding: 10px;
                    }
                    .header h1 {
                        font-size: 2em;
                    }
                    .content {
                        padding: 20px;
                    }
                    .features-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎓 ADAPA LMS</h1>
                    <p>Sistema de Gestión de Aprendizaje</p>
                    
                    <div class="nav-bar">
                        <a href="<?php echo url('/'); ?>">🏠 Inicio</a>
                        <?php if ($isLoggedIn): ?>
                            <form method="POST" action="<?php echo url('/logout'); ?>" style="display: inline;">
                                <?php echo csrf_input(); ?>
                                <button type="submit" style="background: none; border: none; color: #3498db; margin: 0 15px; font-weight: 500; cursor: pointer; padding: 0;">🚪 Cerrar Sesión</button>
                            </form>
                            <span style="color: #7f8c8d;">|</span>
                            <span style="color: #27ae60;">👤 <?php echo htmlspecialchars($userName); ?> (<?php echo htmlspecialchars($userRole); ?>)</span>
                        <?php else: ?>
                            <a href="<?php echo url('/login'); ?>">🔐 Iniciar Sesión</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content">
                    <div class="section">
                        <h2>ℹ️ Acerca de ADAPA LMS</h2>
                        <p>
                            <strong>ADAPA LMS</strong> es un Sistema de Gestión de Aprendizaje (Learning Management System) diseñado específicamente 
                            para instituciones educativas que buscan ofrecer una experiencia de aprendizaje en línea moderna, intuitiva y efectiva.
                        </p>
                        <p>
                            Nuestro sistema proporciona una plataforma integral que conecta estudiantes y profesores, facilitando la creación, 
                            distribución y seguimiento de contenido educativo de manera eficiente y accesible.
                        </p>
                    </div>

                    <div class="section">
                        <h2>🚀 Características Principales</h2>
                        <div class="features-grid">
                            <div class="feature-card">
                                <h4>📚 Gestión de Cursos</h4>
                                <p>Los profesores pueden crear, editar y gestionar cursos completos con contenido multimedia, actividades y evaluaciones.</p>
                            </div>
                            <div class="feature-card">
                                <h4>👥 Gestión de Usuarios</h4>
                                <p>Sistema de roles diferenciados para estudiantes, profesores y administradores con permisos específicos.</p>
                            </div>
                            <div class="feature-card">
                                <h4>📊 Seguimiento de Progreso</h4>
                                <p>Los estudiantes pueden ver su progreso en tiempo real y los profesores pueden monitorear el rendimiento de sus alumnos.</p>
                            </div>
                            <div class="feature-card">
                                <h4>📱 Diseño Responsive</h4>
                                <p>Interfaz adaptativa que funciona perfectamente en computadoras, tablets y dispositivos móviles.</p>
                            </div>
                            <div class="feature-card">
                                <h4>🔐 Seguridad</h4>
                                <p>Sistema de autenticación seguro con gestión de sesiones y control de acceso basado en roles.</p>
                            </div>
                            <div class="feature-card">
                                <h4>⚡ Rendimiento</h4>
                                <p>Arquitectura optimizada para respuestas rápidas y manejo eficiente de múltiples usuarios simultáneos.</p>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h2>💻 Tecnologías Utilizadas</h2>
                        <p>ADAPA LMS está construido con tecnologías modernas y confiables:</p>
                        <div class="tech-stack">
                            <span class="tech-badge">PHP 8.2</span>
                            <span class="tech-badge">MySQL</span>
                            <span class="tech-badge">Apache</span>
                            <span class="tech-badge">HTML5</span>
                            <span class="tech-badge">CSS3</span>
                            <span class="tech-badge">JavaScript</span>
                            <span class="tech-badge">MVC Architecture</span>
                            <span class="tech-badge">PDO</span>
                        </div>
                    </div>

                    <div class="section">
                        <h2>👨‍💻 Desarrollo</h2>
                        <p>
                            Este sistema fue desarrollado como una solución educativa integral, implementando las mejores prácticas de 
                            desarrollo web y diseño de interfaces centrado en el usuario. El código está estructurado siguiendo el 
                            patrón MVC (Model-View-Controller) para mantener una arquitectura limpia y escalable.
                        </p>
                        <h3>Características Técnicas:</h3>
                        <ul style="color: #555; font-size: 1.1em; margin-left: 20px;">
                            <li>Sistema de enrutamiento dinámico con URL limpias</li>
                            <li>Gestión segura de bases de datos con prepared statements</li>
                            <li>Sistema de plantillas y vistas modulares</li>
                            <li>Autenticación y autorización robustas</li>
                            <li>Diseño responsive y accesible</li>
                        </ul>
                    </div>

                    <div class="section">
                        <h2>📞 Soporte</h2>
                        <p>
                            Para soporte técnico o consultas sobre el sistema, por favor contacta al equipo de desarrollo 
                            a través de los canales establecidos por la institución.
                        </p>
                        <div style="text-align: center; margin-top: 30px;">
                            <a href="<?php echo url('/'); ?>" class="btn">🏠 Volver al Inicio</a>
                            <?php if (!$isLoggedIn): ?>
                                <a href="<?php echo url('/login'); ?>" class="btn btn-success">🔐 Iniciar Sesión</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <p>&copy; <?php echo date('Y'); ?> ADAPA LMS - Sistema de Gestión de Aprendizaje</p>
                    <p>Desarrollado con ❤️ para la educación</p>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
