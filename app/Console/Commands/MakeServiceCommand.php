<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        // Garante que o nome termine com "Service"
        if (!str_ends_with($name, 'Service')) {
            $name .= 'Service';
        }

        $path = app_path("Services/{$name}.php");
        
        // Cria o diretório Services se não existir
        if (!File::exists(app_path('Services'))) {
            File::makeDirectory(app_path('Services'));
        }

        $stub = <<<EOT
<?php

namespace App\Services;

class {$name}
{
    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        //
    }
}
EOT;

        // Cria o arquivo
        File::put($path, $stub);

        $this->info("Service [{$name}] criado com sucesso!");
    }
}
