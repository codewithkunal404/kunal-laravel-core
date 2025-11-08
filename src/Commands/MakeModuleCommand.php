<?php

namespace KunalLaravel\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = 'Create a complete MVC module';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $path = base_path("modules/{$name}");

        if (File::exists($path)) {
            $this->error("Module [{$name}] already exists!");
            return;
        }

        // Folder structure
        File::makeDirectory($path.'/Http/Controllers', 0755, true);
        File::makeDirectory($path.'/Models', 0755, true);
        File::makeDirectory($path.'/Routes', 0755, true);
        File::makeDirectory($path.'/Views', 0755, true);
        File::makeDirectory($path.'/Database/migrations', 0755, true);

        // Controller
        File::put($path."/Http/Controllers/{$name}Controller.php", $this->getControllerContent($name));

        // Model
        File::put($path."/Models/{$name}.php", $this->getModelContent($name));

        // Routes
        File::put($path."/Routes/web.php", $this->getRouteContent($name));

        // View
        File::put($path."/Views/index.blade.php", "<h1>{$name} Module Loaded!</h1>");

        // Migration
        $migrationName = date('Y_m_d_His')."_create_".Str::snake(Str::plural($name))."_table.php";
        File::put($path."/Database/migrations/{$migrationName}", $this->getMigrationContent($name));

        // Module Service Provider
        File::put($path."/ModuleServiceProvider.php", $this->getProviderContent($name));

        $this->info("✅ Module [{$name}] created successfully!");
    }

    protected function getControllerContent($name)
    {
        return <<<PHP
<?php

namespace Modules\\{$name}\\Http\\Controllers;

use App\Http\Controllers\Controller;

class {$name}Controller extends Controller
{
    public function index()
    {
        return view('{$name}::index');
    }
}
PHP;
    }

    protected function getModelContent($name)
    {
        return <<<PHP
<?php

namespace Modules\\{$name}\\Models;

use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    protected \$guarded = [];
}
PHP;
    }

    protected function getRouteContent($name)
    {
        $slug = strtolower($name);
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;
use Modules\\{$name}\\Http\\Controllers\\{$name}Controller;

Route::get('{$slug}', [{$name}Controller::class, 'index']);
PHP;
    }

    protected function getMigrationContent($name)
    {
        $table = Str::snake(Str::plural($name));
        return <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
    }

    protected function getProviderContent($name)
    {
        return <<<PHP
<?php

namespace Modules\\{$name};

use Illuminate\\Support\\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Boot things here if needed
    }

    public function register(): void
    {
        // Register bindings or services
    }
}
PHP;
    }
}
