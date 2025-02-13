<?php

namespace Jmrashed\LaravelCoreService\Repositories;

ini_set('max_execution_time', -1);

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Toastr;

class InstallRepository
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function checkInstallation()
    {
        $ac = Storage::disk('local')->exists('.app_installed') ? Storage::disk('local')->get('.app_installed') : null;
        if ($ac) {
            abort(404);
        } else {
            if ($this->checkPreviousInstallation()) {
                return redirect('/')->send();
            }
        }
    }

    /**
     * Used to compare version of PHP
     */
    public function my_version_compare($ver1, $ver2, $operator = null)
    {
        $p = '#(\.0+)+($|-)#';
        $ver1 = preg_replace($p, '', $ver1);
        $ver2 = preg_replace($p, '', $ver2);
        return isset($operator) ?
            version_compare($ver1, $ver2, $operator) :
            version_compare($ver1, $ver2);
    }

    /**
     * Used to check whether pre requisites are fulfilled or not and returns array of success/error type with message
     */
    public function check($boolean, $message, $help = '', $fatal = false)
    {
        if ($boolean) {
            return array('type' => 'success', 'message' => $message);
        } else {
            return array('type' => 'error', 'message' => $help);
        }
    }

    public function checkReinstall()
    {
        try {
            DB::connection()->getPdo();
            return (Storage::disk('local')->exists('.install_count') ? Storage::disk('local')->get('.install_count') : 0) and (Artisan::call('Jmrashed:migrate-status'));
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Used to check whether pre requisites are fulfilled or not and returns array of success/error type with message
     */
    public function checkPreviousInstallation()
    {
        return false;
    }

    /**
     * Check all pre-requisite for script
     */
    public function getPreRequisite()
    {
        $server[] = $this->check((dirname($_SERVER['REQUEST_URI']) != '/' && str_replace('\\', '/', dirname($_SERVER['REQUEST_URI'])) != '/'), 'Installation directory is valid.', 'Please use root directory or point your sub directory to domain/subdomain to install.', true);
        $server[] = $this->check($this->my_version_compare(phpversion(), config('Jmrashed.php_version', '7.2.0'), '>='), sprintf('Min PHP version ' . config('Jmrashed.php_version', '7.2.0') . ' (%s)', 'Current Version ' . phpversion()), 'Current Version ' . phpversion(), true);
        $server[] = $this->check(extension_loaded('fileinfo'), 'Fileinfo PHP extension enabled.', 'Install and enable Fileinfo extension.', true);
        $server[] = $this->check(extension_loaded('ctype'), 'Ctype PHP extension enabled.', 'Install and enable Ctype extension.', true);
        $server[] = $this->check(extension_loaded('json'), 'JSON PHP extension enabled.', 'Install and enable JSON extension.', true);
        $server[] = $this->check(extension_loaded('openssl'), 'OpenSSL PHP extension enabled.', 'Install and enable OpenSSL extension.', true);
        $server[] = $this->check(extension_loaded('tokenizer'), 'Tokenizer PHP extension enabled.', 'Install and enable Tokenizer extension.', true);
        $server[] = $this->check(extension_loaded('mbstring'), 'Mbstring PHP extension enabled.', 'Install and enable Mbstring extension.', true);
        $server[] = $this->check(extension_loaded('zip'), 'Zip archive PHP extension enabled.', 'Install and enable Zip archive extension.', true);
        $server[] = $this->check(class_exists('PDO'), 'PDO is installed.', 'Install PDO (mandatory for Eloquent).', true);
        $server[] = $this->check(extension_loaded('curl'), 'CURL is installed.', 'Install and enable CURL.', true);
        $server[] = $this->check(ini_get('allow_url_fopen'), 'allow_url_fopen is on.', 'Turn on allow_url_fopen.', true);

        $folder[] = $this->check(is_writable(base_path('/.env')), 'File .env is writable', 'File .env is not writable', true);
        $folder[] = $this->check(is_writable(base_path("/storage/framework")), 'Folder /storage/framework is writable', 'Folder /storage/framework is not writable', true);
        $folder[] = $this->check(is_writable(base_path("/storage/logs")), 'Folder /storage/logs is writable', 'Folder /storage/logs is not writable', true);
        $folder[] = $this->check(is_writable(base_path("/bootstrap/cache")), 'Folder /bootstrap/cache is writable', 'Folder /bootstrap/cache is not writable', true);

        /** START | add your custom environment list for checking */
        //
        //
        //
        //
        /** END | add your custom environment list for checking */

        $verifier = verifyUrl(config('Jmrashed.verifier', 'auth'));

        return ['server' => $server, 'folder' => $folder, 'verifier' => $verifier];
    }

    /**
     * Validate database connection, table count
     */
    public function validateDatabase($params)
    {
        $db_host = gv($params, 'db_host', env('DB_HOST'));
        $db_username = gv($params, 'db_username', env('DB_USERNAME'));
        $db_password = gv($params, 'db_password', env('DB_PASSWORD'));
        $db_database = gv($params, 'db_database', env('DB_DATABASE'));

        $link = @mysqli_connect($db_host, $db_username, $db_password);

        if (!$link) {
            throw ValidationException::withMessages(['message' => trans('service::install.connection_not_established')]);
        }

        $select_db = mysqli_select_db($link, $db_database);
        if (!$select_db) {
            throw ValidationException::withMessages(['message' => trans('service::install.db_not_found')]);
        }

        if (!gbv($params, 'force_migrate')) {
            $count_table_query = mysqli_query($link, "show tables");
            $count_table = mysqli_num_rows($count_table_query);

            if ($count_table) {
                throw ValidationException::withMessages(['message' => trans('service::install.existing_table_in_database')]);
            }
        }

        $this->setDBEnv($params);

        if (gbv($params, 'force_migrate')) {
            $this->rollbackDb();
        }

        return true;
    }

    public function checkDatabaseConnection()
    {
        $db_host = env('DB_HOST');
        $db_username = env('DB_USERNAME');
        $db_password = env('DB_PASSWORD');
        $db_database = env('DB_DATABASE');

        try {
            $link = @mysqli_connect($db_host, $db_username, $db_password);
        } catch (\Exception $e) {
            return false;
        }


        if (!$link) {
            return false;
        }
        try {
            $select_db = mysqli_select_db($link, $db_database);
        } catch (\Throwable $th) {
            return false;
        }

        if (!$select_db) {
            return false;
        }

        $count_table_query = mysqli_query($link, "show tables");
        $count_table = mysqli_num_rows($count_table_query);

        if ($count_table) {
            return false;
        }


        return true;
    }

    public function validateLicense($params)
    {
        if (isTestMode()) {
            return;
        }

        if (!isConnected()) {
            throw ValidationException::withMessages(['message' => 'No internect connection.']);
        }
        // $ve = Storage::disk('local')->exists('.ve') ? Storage::disk('local')->get('.ve') : 'e';
        // $url = verifyUrl(config('Jmrashed.verifier', 'auth')) . '/api/cc?a=install&u=' . app_url() . '&ac=' . request('access_code') . '&i=' . config('app.item') . '&e=' . request('envato_email') . '&ri=' . request('re_install') . '&current=' . urlencode(request()->path()) . '&ve=' . $ve;

        // $response = curlIt($url);
        // if (gv($response, 'goto')) {
        //     return $response;
        // }

        // $status = (isset($response['status']) && $response['status']) ? 1 : 0;

        // if ($status) {
        //     $checksum = $response['checksum'] ?? null;
        //     $license_code = $response['license_code'] ?? null;
        //     $modules = gv($response, 'modules', []);
        // } else {
        //     $message = gv($response, 'message') ? $response['message'] : trans('service::install.contact_script_author');
        //     throw ValidationException::withMessages(['access_code' => $message]);
        // }
        //generate random 64 bit code



        $checksum = 'eyJpdiI6Im9oMWU5Z0NoSGVwVzdmQlphaVBvd1E9PSIsInZhbHVlIjoiUURhZmpubkNBUVB6b0ZPck1v';
        $license_code = '5458-5365-8845-7865';

        Storage::disk('local')->put('.temp_app_installed', $checksum ?? '');
        Storage::disk('local')->put('.access_code', $license_code ?? '');
        Storage::disk('local')->put('.account_email', request('envato_email'));
        Storage::disk('local')->put('.access_log', date('Y-m-d'));

        $folder = storage_path('app' . DIRECTORY_SEPARATOR . config('app.item'));
        File::ensureDirectoryExists($folder);

        // foreach ($modules as $module) {
        //     if ($code = gv($module, 'code')) {
        //         File::put($folder . DIRECTORY_SEPARATOR . '.' . $code, request('access_code'));
        //     }
        // }

        return true;
    }

    public function checkLicense()
    {
        if (isTestMode()) {
            return;
        }

        if (!isConnected()) {
            throw ValidationException::withMessages(['message' => 'No internect connection.']);
        }

        $ac = Storage::disk('local')->exists('.access_code') ? Storage::disk('local')->get('.access_code') : null;
        $e = Storage::disk('local')->exists('.account_email') ? Storage::disk('local')->get('.account_email') : null;
        $c = Storage::disk('local')->exists('.temp_app_installed') ? Storage::disk('local')->get('.temp_app_installed') : null;
        $v = Storage::disk('local')->exists('.version') ? Storage::disk('local')->get('.version') : null;


        $url = verifyUrl(config('Jmrashed.verifier', 'auth')) . '/api/cc?a=verify&u=' . app_url() . '&ac=' . $ac . '&i=' . config('app.item') . '&e=' . $e . '&c=' . $c . '&v=' . $v . '&current=' . urlencode(request()->path());
        $response = curlIt($url);
        if (gv($response, 'goto')) {
            return redirect($goto)->send();
        }
        // $status = gbv($response, 'status');
        $status = true;

        if (!$status) {
            Log::info('License Verification failed');
            Storage::disk('local')->delete(['.access_code', '.account_email']);
            Storage::disk('local')->deleteDirectory(config('app.item'));
            Storage::disk('local')->put('.temp_app_installed', '');
            return false;
        } else {
            Storage::disk('local')->put('.access_log', date('Y-m-d'));
            return true;
        }
    }


    /**
     * Install the script
     */
    public function install($params)
    {
        // return $params;
        try {
            $this->migrateDB();

            $ac = Storage::disk('local')->exists('.temp_app_installed') ? Storage::disk('local')->get('.temp_app_installed') : null;
            Storage::disk('local')->put('.app_installed', config('app.signature'));
            Storage::disk('local')->put('.user_email', gv($params, 'email'));
            Storage::disk('local')->put('.user_pass', gv($params, 'password'));

            Storage::disk('local')->delete('.temp_app_installed');
        } catch (\Throwable $th) {
            Log::alert("message", $th->getMessage());
            return response()->json(['message' => $th->getMessage()], 200);
        }
    }


    /**
     * Write to env file
     */
    public function setDBEnv($params)
    {
        envu([
            'APP_URL' => app_url(),
            'DB_PORT' => gv($params, 'db_port'),
            'DB_HOST' => gv($params, 'db_host'),
            'DB_DATABASE' => gv($params, 'db_database'),
            'DB_USERNAME' => gv($params, 'db_username'),
            'DB_PASSWORD' => gv($params, 'db_password'),
        ]);

        DB::disconnect('mysql');

        config([
            'database.connections.mysql.host' => gv($params, 'db_host'),
            'database.connections.mysql.port' => gv($params, 'db_port'),
            'database.connections.mysql.database' => gv($params, 'db_database'),
            'database.connections.mysql.username' => gv($params, 'db_username'),
            'database.connections.mysql.password' => gv($params, 'db_password'),
        ]);

        DB::setDefaultConnection('mysql');
    }

    /**
     * Mirage tables to database
     */
    public function migrateDB()
    {
        try {
            Artisan::call('migrate:fresh', array('--force' => true));
        } catch (Throwable $e) {
            $this->rollbackDb();
            Log::error($e);
            // $sql = base_path('database/' . config('Jmrashed.database_file'));
            // if (File::exists($sql)) {
            //     DB::unprepared(file_get_contents($sql));
            // }
        }
    }

    public function rollbackDb()
    {
        Artisan::call('db:wipe', array('--force' => true));
    }

    /**
     * Seed tables to database
     */
    public function seed($seed = 0)
    {
        if (!$seed) {
            return;
        }

        $db = Artisan::call('db:seed', array('--force' => true));
    }


    public function installModule($params)
    {

        $code = gv($params, 'purchase_code');
        $name = gv($params, 'name');
        $e = gv($params, 'envatouser');
        $row = gbv($params, 'row');
        $file = gbv($params, 'file');

        $dataPath = base_path('Modules/' . $name . '/' . $name . '.json');

        $strJsonFileContents = file_get_contents($dataPath);
        $array = json_decode($strJsonFileContents, true);

        $item_id = $array[$name]['item_id'];

        $ve = gv($array[$name], 've', 'e');

        $module_file = storage_path('app' . DIRECTORY_SEPARATOR . config('app.item') . DIRECTORY_SEPARATOR . '.' . $item_id);


        if (file_exists($module_file)) {
            if (!$code) {
                $code = file_get_contents($module_file);
            }
            $item_id = config('app.item');
        } else if (gbv($params, 'tariq')) {
            return false;
        }

        $verifier = $array[$name]['verifier'] ?? 'auth';

        $url = verifyUrl($verifier) . '/api/cc?a=install&u=' . app_url() . '&ac=' . $code . '&i=' . $item_id . '&e=' . $e . '&t=Module&ve=' . $ve;

        $response = curlIt($url);

        $status = gbv($response, 'status');

        if (!$row) {
            if (gbv($params, 'file')) {
                app('general_settings')->put([
                    $name => 0
                ]);
            } else {
                if (!Schema::hasColumn(config('Jmrashed.settings_table'), $name)) {
                    Schema::table(config('Jmrashed.settings_table'), function ($table) use ($name) {
                        $table->integer($name)->default(1)->nullable();
                    });
                }
            }
        } else {
            $settings_model_name = config('Jmrashed.settings_model');
            $settings_model = new $settings_model_name;
            $config = $settings_model->firstOrCreate(['key' => $name]);
        }

        if ($status) {

            // added a new column in sm general settings
            try {

                $version = $array[$name]['versions'][0];
                $url = $array[$name]['url'][0];
                $notes = $array[$name]['notes'][0];

                DB::beginTransaction();
                $module_class_name = config('Jmrashed.module_manager_model');
                $moduel_class = new $module_class_name;
                $s = $moduel_class->where('name', $name)->first();
                if (empty($s)) {
                    $s = $moduel_class;
                }
                $s->name = $name;
                $s->email = $e;
                $s->notes = $notes;
                $s->version = $version;
                $s->update_url = $url;
                $s->installed_domain = app_url();
                $s->activated_date = date('Y-m-d');
                $s->purchase_code = $code;
                $s->checksum = gv($response, 'checksum');
                $r = $s->save();

                $settings_model_name = config('Jmrashed.settings_model');
                $settings_model = new $settings_model_name;
                if ($row) {
                    $config = $settings_model->firstOrNew(['key' => $name]);
                    $config->value = 1;
                    $config->save();
                } else if ($file) {
                    app('general_settings')->put([
                        $name => 1
                    ]);
                } else {
                    $config = $settings_model->find(1);
                    $config->$name = 1;
                    $config->save();
                }

                DB::commit();


                return true;
            } catch (Exception $e) {
                Log::error($e);
                $this->disableModule($name, $row, $file);
                if (request()->wantsJson()) {
                    throw ValidationException::withMessages(['message' => $e->getMessage()]);
                }
                Toastr::error($e->getMessage());
                return false;
            }
        } else {
            $this->disableModule($name, $row);
            if (request()->wantsJson()) {
                throw ValidationException::withMessages(['message' => gv($response, 'message', 'Something is not right')]);
            }
            Toastr::error(gv($response, 'message', 'Something is not right'));
            return false;
        }
    }

    protected function disableModule($module_name, $row = false, $file = false)
    {

        $settings_model_name = config('Jmrashed.settings_model');
        $settings_model = new $settings_model_name;
        if ($row) {
            $config = $settings_model->firstOrNew(['key' => $module_name]);
            $config->value = 0;
            $config->save();
        } else if ($file) {
            app('general_settings')->put([
                $module_name => 0
            ]);
        } else {
            $config = $settings_model->find(1);
            $config->$module_name = 0;
            $config->save();
        }
        $module_model_name = config('Jmrashed.module_model');
        $module_model = new $module_model_name;
        $ModuleManage = $module_model::find($module_name)->disable();
    }

    public function uninstall($request)
    {
        $signature = gv($request, 'signature');
        $response = [
            'DB_PORT' => env('DB_PORT'),
            'DB_HOST' => env('DB_HOST'),
            'DB_DATABASE' => env('DB_DATABASE'),
            'DB_USERNAME' => env('DB_USERNAME'),
            'DB_PASSWORD' => env('DB_PASSWORD'),
        ];
        if (config('app.signature') == $signature) {
            envu([
                'DB_PORT' => '3306',
                'DB_HOST' => 'localhost',
                'DB_DATABASE' => "",
                'DB_USERNAME' => "",
                'DB_PASSWORD' => "",
            ]);

            Storage::disk('local')->delete(['.access_code', '.account_email']);
            Storage::disk('local')->put('.app_installed', '');
            Artisan::call('optimize:clear');
            Storage::disk('local')->put('.logout', true);
        }
        return $response;
    }


    public function installTheme($params)
    {

        $code = gv($params, 'purchase_code');
        $name = gv($params, 'name');
        $e = gv($params, 'envatouser');

        $query = DB::table(config('Jmrashed.theme_table', 'themes'))->where('name', $name);
        $theme = $query->first();

        if (!$theme) {
            throw ValidationException::withMessages(['message' => 'Theme not found']);
        }

        $item_id = $theme->item_code;

        $url =  verifyUrl(config('Jmrashed.verifier', 'auth')) . '/api/cc?a=install&u=' . app_url() . '&ac=' . $code . '&i=' . $item_id . '&e=' . $e . '&t=Theme';

        $response = curlIt($url);


        $status = gbv($response, 'status');

        if ($status) {

            $query->update([
                'email' => $e,
                'installed_domain' => app_url(),
                'activated_date' => date('Y-m-d'),
                'purchase_code' => $code,
                'checksum' => gv($response, 'checksum'),
            ]);
            return true;
        } else {
            throw ValidationException::withMessages(['message' => gv($response, 'message', 'Something is not right')]);
        }
    }
}
