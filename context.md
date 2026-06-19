# Contexto del Proyecto: Green Col

Este archivo funciona como referencia rapida del estado actual del proyecto.

## Tecnologias

- Backend: `Laravel 13` sobre `PHP 8.3`
- Frontend: `Vue 3` + `Inertia.js`
- Estilos: `Tailwind CSS`
- Base de datos: `SQLite`
- Utilidades locales: `Python` para `vetproject.py`

## Objetivo actual

Green Col es una aplicacion de gestion veterinaria centrada en operacion clinica:

- agenda de citas,
- responsables de mascotas,
- notas del doctor,
- seguimiento temporal por caso,
- enlace de WhatsApp al finalizar el servicio.

## Flujo principal

1. El administrador inicia sesion en `/login`.
2. Desde el dashboard crea o actualiza una cita.
3. El doctor registra motivo, estado y notas visibles para seguimiento.
4. Cada cita tiene un `public_token` unico.
5. Cuando el servicio finaliza, se puede abrir WhatsApp con un enlace temporal del caso.
6. El responsable ve solo la informacion de ese servicio en una vista publica.

## Estructura clave

### Controladores

- `app/Http/Controllers/VeterinaryController.php`
  Dashboard, CRUD de citas, actualizacion de responsables, seguimiento publico y generacion del enlace de WhatsApp.
- `app/Http/Controllers/SettingsController.php`
  Configuracion de nombre y logo de la clinica.

### Modelos

- `app/Models/Owner.php`
  Responsable de mascota con correo, celular y prefijo pais.
- `app/Models/Pet.php`
  Mascota relacionada con un responsable.
- `app/Models/Appointment.php`
  Cita medica con estado, notas, token publico y fecha de finalizacion.
- `app/Models/ClinicSetting.php`
  Marca de la clinica.

### Frontend

- `resources/js/Pages/Dashboard.vue`
  Vista principal del panel administrativo.
- `resources/js/Pages/Auth/Login.vue`
  Pantalla de login con branding e interaccion visual.
- `resources/js/Pages/Public/Tracking.vue`
  Vista publica del seguimiento por caso.
- `resources/js/Pages/Settings/Edit.vue`
  Edicion de nombre y logo.
- `resources/js/Layouts/AuthenticatedLayout.vue`
  Layout del panel interno.
- `resources/js/Layouts/GuestLayout.vue`
  Layout del acceso/login.
- `resources/js/Components/ApplicationLogo.vue`
  Logo por defecto cuando no hay logo cargado.

### Rutas importantes

- `GET /dashboard`
- `POST /appointments`
- `PATCH /appointments/{appointment}`
- `DELETE /appointments/{appointment}`
- `PATCH /owners/{owner}`
- `GET /seguimiento/{token}`
- `GET /seguimiento/{token}/data`

## Base de datos

Migraciones importantes:

- `2026_04_26_184200_create_owners_table.php`
- `2026_04_26_184200_create_pets_table.php`
- `2026_04_26_184201_create_appointments_table.php`
- `2026_04_26_191734_create_clinic_settings_table.php`
- `2026_04_26_230000_add_tracking_fields_to_appointments_table.php`
- `2026_04_26_231000_add_phone_country_code_to_owners_table.php`

Campos funcionales relevantes:

- `appointments.public_token`
- `appointments.doctor_notes`
- `appointments.service_finished_at`
- `owners.phone_country_code`

## WhatsApp y telefonia

- Todos los responsables usan `+57` por defecto.
- El prefijo pais puede cambiarse desde el dashboard.
- El enlace de WhatsApp se arma con `phone_country_code + phone`.
- El mensaje incluye el enlace temporal de seguimiento del caso.

## Acceso por defecto

- URL: `/login`
- Email: `admin@vet.com`
- Password: `password`

## Gestion local

- `vetproject.py`
  Script de ayuda para iniciar y detener Laravel + Vite en Windows.
- `composer run setup`
  Instalacion inicial.
- `composer run dev`
  Flujo completo de desarrollo.
- `npm run build`
  Compilacion frontend.

## Estado del proyecto

Lo que ya existe:

- branding dinamico,
- login personalizado,
- panel de citas,
- edicion de responsables,
- seguimiento publico,
- boton de finalizar y compartir por WhatsApp.

Lo que todavia conviene construir:

- CRUD completo de mascotas,
- CRUD completo de responsables fuera del dashboard,
- expiracion real de enlaces temporales,
- historial clinico mas profundo,
- validacion avanzada de telefonos por pais.
