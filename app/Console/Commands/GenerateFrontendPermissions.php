<?php

namespace App\Console\Commands;

use App\Enums\Roles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class GenerateFrontendPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gh:generate-frontend-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $accessPath = resource_path('js/Access');
        $outputPermissions = "{$accessPath}/Permissions";
        $outputRoles = "{$accessPath}/Roles.ts";

        File::ensureDirectoryExists($outputPermissions);
        File::cleanDirectory($outputPermissions);

        $permissionsIndex = [];
        collect(glob(app_path('Enums/Permissions').'/*.php'))
            ->map(function ($file) {
                $className = pathinfo($file, PATHINFO_FILENAME);
                $class = "App\Enums\Permissions\\$className";

                return $class;
            })
            ->filter(fn ($class) => enum_exists($class))
            ->flatten()
            ->each(function ($class) use ($outputPermissions, &$permissionsIndex) {
                $name = str_replace('Permissions', '', (new ReflectionClass($class))->getShortName());
                $content = $this->generateContent($class, $name);

                File::put($outputPermissions."/{$name}.ts", $content);
                $permissionsIndex[] = "export * from './{$name}'";
            });

        $permissionsIndex[] = '';
        File::put($outputPermissions.'/index.ts', collect($permissionsIndex)->implode(PHP_EOL, ''));

        $rolesContent = $this->generateContent(Roles::class, 'Roles');
        File::put($outputRoles, $rolesContent);

        return Command::SUCCESS;
    }

    private function generateContent(string $class, string $name): string
    {
        $caseList = [];
        foreach ($class::cases() as $enum) {
            $caseList[] = str_repeat(' ', 2)."{$enum->name} = '".$enum->value."'";
        }

        return collect([
            "export enum {$name} {",
            collect($caseList)->implode(','.PHP_EOL, ''),
            '}',
            '',
            "export default {$name}",
            '',
        ])->implode(PHP_EOL);
    }
}
