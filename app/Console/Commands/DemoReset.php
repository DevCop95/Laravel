<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DemoReset extends Command
{
    /**
     * @var string
     */
    protected $signature = 'demo:reset {--force : Ejecutar sin pedir confirmacion}';

    /**
     * @var string
     */
    protected $description = 'Reinicia la base de datos de la demo: borra todo y vuelve a sembrar datos de ejemplo.';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('No se puede reiniciar la demo en produccion sin --force.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Esto borrara TODOS los datos y resembrara la demo. Continuar?')) {
            $this->comment('Operacion cancelada.');

            return self::SUCCESS;
        }

        $this->info('Reiniciando base de datos de la demo...');

        Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true], $this->getOutput());

        $this->newLine();
        $this->info('Demo reiniciada. Acceso: admin@vet.com / password');

        return self::SUCCESS;
    }
}
