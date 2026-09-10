<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Checklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checklist:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $dataChecked = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $separator = '-----------------------------------------------';
        $this->info($separator);
        $this->info(str_pad('AMS Checklist', strlen($separator), ' ', STR_PAD_BOTH));
        $this->info($separator);
        $this->warn('[ be careful when running remotely (db and etc.) ]');

        $dataChecked = [];

        $checkName = 'php version ['.phpversion().']';
        $this->setDataChecked($checkName, 'ok');
        if (version_compare(PHP_VERSION, '8.1.1', '<')) {
            $this->setDataChecked($checkName, 'php version should be at least 7.3', 'error');
        }

        $this->checkEnvVar('APP_KEY', 'APP_KEY should be generated: php artisan key:generate');
        $this->checkEnvVar('APP_DEBUG', 'APP_DEBUG should be false on PRD', true);
        // $this->checkEnvVar('APP_DOMAIN');
        $this->checkEnvVar('AMS_ASSETS_URL');
        $this->checkEnvVar('AMS_DISK_ASSETS');
        $this->checkEnvVar('AMS_DISK_CACHE2');
        $this->checkEnvVar('AMS_DISK_CACHE3');
        $this->checkEnvVar('SANCTUM_STATEFUL_DOMAINS');
        $this->checkEnvVar('SESSION_DOMAIN');

        $checkName = 'DB connection';
        $this->setDataChecked($checkName, 'ok');
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            $this->setDataChecked($checkName, 'No DB connection', 'error');
        }

        $checkName = 'Redis connection';
        $this->setDataChecked($checkName, 'ok');
        try {
            \AMSRedis::set('checklist', 'check', 1);
            if (\AMSRedis::get('checklist', 'check') !== '1') {
                throw new \Exception('Redis seems to be not working');
            }
        } catch (\Throwable $e) {
            $this->setDataChecked($checkName, 'No Redis connection', 'error');
        } catch (\Exception|\LogicException $e) {
            $this->setDataChecked($checkName, 'No Redis connection', 'error');
        }

        // check php extensions
        $loadedExtensions = get_loaded_extensions();
        $extensionsRequired = [
            'bcmath',
            'ctype',
            'fileinfo',
            'json',
            'mbstring',
            'openssl',
            'pdo_mysql|pdo-mysql',
            'tokenizer',
            'xml',
            'libxml',
            'SimpleXML',
            'dom',
            'redis',
        ];
        foreach ($extensionsRequired as $extension) {
            $checkName = 'php.extension '.$extension;
            $this->setDataChecked($checkName, 'ok');
            $_extension = explode('|', $extension);
            $isExtEnabled = false;
            foreach ($_extension as $_ext) {
                if (in_array($_ext, $loadedExtensions)) {
                    $isExtEnabled = true;
                    break;
                }
            }
            if (! $isExtEnabled) {
                $this->setDataChecked($checkName, 'not enabled', 'error');
            }
        }

        // check folders permissions
        $dirsPermissionToCheck = ['storage', 'bootstrap'];
        foreach ($dirsPermissionToCheck as $dir) {
            $dirPath = base_path($dir);
            $checkName = 'dir permissions: '.$dir;
            $this->setDataChecked($checkName, 'ok');
            if (! is_writable($dirPath)) {
                $this->setDataChecked($checkName, 'not writable', 'error');
            }
        }

        $this->table(
            ['name', 'type', 'info'],
            $this->dataChecked
        );
    }

    /**
     * check env variable setting
     */
    private function checkEnvVar(string $varName, string $msg = '', bool|string $condition = ''): void
    {
        $checkName = 'env.'.$varName.' ['.env($varName, '').']';
        $this->setDataChecked($checkName, 'ok');
        if (empty($msg)) {
            $msg = $varName.' should be set';
        }
        if (is_null(env($varName)) || env($varName) === $condition) {
            $this->setDataChecked($checkName, $msg, 'error');
        }
    }

    private function setDataChecked(string $name, string $msg = '', string $type = 'info'): void
    {
        if (isset($this->dataChecked[$name])) {
            if (! empty($type)) {
                $this->dataChecked[$name]['type'] = $type;
            }
            if (! empty($msg)) {
                $this->dataChecked[$name]['info'] = $msg;
            }
        } else {
            $this->dataChecked[$name] = [
                'name' => $name,
                'type' => $type,
                'info' => $msg,
            ];
        }
    }
}
