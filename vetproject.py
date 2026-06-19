import subprocess
import sys
import os

# Configuración de rutas
PHP_PATH = r"C:\Users\Admin\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

def start():
    print("--- Iniciando Proyecto Veterinaria ---")
    
    # 1. Iniciar servidor de Laravel
    print("[1/2] Iniciando servidor de Laravel (http://127.0.0.1:8000)...")
    subprocess.Popen(
        [PHP_PATH, "artisan", "serve"],
        creationflags=subprocess.CREATE_NEW_CONSOLE
    )
    
    # 2. Iniciar Vite (Vue)
    print("[2/2] Iniciando Vite (Frontend)...")
    subprocess.Popen(
        ["npm.cmd", "run", "dev"],
        creationflags=subprocess.CREATE_NEW_CONSOLE
    )
    
    print("\n¡Todo listo! El proyecto se está ejecutando en ventanas separadas.")

def stop():
    print("--- Deteniendo Proyecto Veterinaria ---")
    try:
        print("Cerrando procesos de PHP y Node...")
        # /F = Fuerza el cierre, /T = Cierra procesos hijos, /IM = Nombre de la imagen
        subprocess.run(["taskkill", "/F", "/IM", "php.exe", "/T"], capture_output=True)
        subprocess.run(["taskkill", "/F", "/IM", "node.exe", "/T"], capture_output=True)
        print("¡Proyecto detenido correctamente!")
    except Exception as e:
        print(f"Error al detener los procesos: {e}")

def main():
    if len(sys.argv) < 2:
        print("Uso: python vetproject.py [start|stop]")
        return

    command = sys.argv[1].lower()

    if command == "start":
        start()
    elif command == "stop":
        stop()
    else:
        print(f"Comando desconocido: {command}")
        print("Uso: python vetproject.py [start|stop]")

if __name__ == "__main__":
    main()
