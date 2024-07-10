<?php

namespace Fuelviews\Tailwindcss\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use JsonException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class TailwindCssCommand extends Command
{
    public $signature = 'tailwindcss:install {--force : Overwrite existing files without prompting for confirmation}';

    public $description = 'Install tailwindcss, postcss, and dependencies';

    /**
     * Execute the console command.
     *
     * Publishes Tailwind CSS and PostCSS configuration files, the application's main CSS file,
     * and installs specified npm packages that are not already installed.
     *
     * @return int Returns 0 if successful, or an error code on failure.
     *
     * @throws JsonException
     */
    public function handle(): int
    {
        $force = $this->option('force');

        $this->publishConfig('tailwind.config.js', $force);
        $this->publishConfig('postcss.config.js', $force);
        $this->publishAppCss($force);

        $devDependencies = [
            '@tailwindcss/forms',
            '@tailwindcss/typography',
            'autoprefixer',
            'postcss',
            'tailwindcss',
        ];

        $this->installNodePackages($devDependencies);

        return self::SUCCESS;
    }

    /**
     * Publishes a configuration file from the package's stubs to the project base path.
     * If the file already exists, it checks for the --force flag to overwrite.
     *
     * @param  string  $configFileName  The name of the config file to publish.
     * @param  bool  $force  Whether to force overwrite existing files.
     */
    protected function publishConfig(string $configFileName, bool $force): void
    {
        $stubPath = __DIR__."/../../stubs/$configFileName.stub";
        $destinationPath = base_path($configFileName);

        if (File::exists($destinationPath)) {
            if ($force) {
                File::copy($stubPath, $destinationPath);
                $this->info("$configFileName has been overwritten successfully.");
            } else {
                $this->warn("Skipping $configFileName installation because it already exists.");
            }
        } else {
            File::copy($stubPath, $destinationPath);
            $this->info("$configFileName has been installed successfully.");
        }
    }

    /**
     * Publishes the application's main CSS file from the package's stubs to the Laravel resource path.
     * If the CSS file already exists, it checks for the --force flag to overwrite.
     *
     * @param  bool  $force  Whether to force overwrite existing files.
     */
    protected function publishAppCss(bool $force): void
    {
        $stubPath = __DIR__ . '/../../stubs/css/app.css.stub';
        $destinationPath = resource_path('css/app.css');

        if (! File::exists(dirname($destinationPath))) {
            File::makeDirectory(dirname($destinationPath), 0755, true);
        }

        if (File::exists($destinationPath)) {
            if ($force) {
                File::copy($stubPath, $destinationPath);
                $this->info('css/app.css file has been overwritten successfully.');
            } else {
                $this->warn('Skipping css/app.css installation because it already exists.');
            }
        } else {
            File::copy($stubPath, $destinationPath);
            $this->info('css/app.css file has been installed successfully.');
        }
    }

    /**
     * Installs the specified npm packages if they are not already included in the project's package.json.
     * It consolidates the installation of all necessary packages into a single npm command for efficiency.
     *
     * @param  array  $packageNames  An array of npm package names to install.
     *
     * @throws JsonException
     */
    protected function installNodePackages(array $packageNames): void
    {
        $packageJsonPath = base_path('package.json');
        $packageJsonContent = File::get($packageJsonPath);
        $packageJson = json_decode($packageJsonContent, true, 512, JSON_THROW_ON_ERROR);

        $packagesToInstall = [];
        foreach ($packageNames as $packageName) {
            if (! isset($packageJson['devDependencies'][$packageName])) {
                $packagesToInstall[] = $packageName;
            }
        }

        if (! empty($packagesToInstall)) {
            $packageInstallString = implode(' ', $packagesToInstall);
            $command = "npm install $packageInstallString --save-dev";

            $process = Process::fromShellCommandline($command, null, null, STDIN, null);
            $process->setTty(Process::isTtySupported());
            $process->run(function ($type, $buffer) {
                $this->output->write($buffer);
            });

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->info('Node packages installed successfully.');
        }
    }
}
