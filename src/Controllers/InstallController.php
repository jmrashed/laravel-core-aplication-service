<?php

namespace Jmrashed\LaravelCoreService\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Jmrashed\LaravelCoreService\Requests\UserRequest;
use Jmrashed\LaravelCoreService\Requests\LicenseRequest;
use Jmrashed\LaravelCoreService\Requests\DatabaseRequest;
use Jmrashed\LaravelCoreService\Requests\ThemeInstallRequest;
use Jmrashed\LaravelCoreService\Requests\ModuleInstallRequest;
use Jmrashed\LaravelCoreService\Repositories\InstallRepository;

class InstallController extends Controller{
    protected $repo, $request, $init, $path;

    public function __construct(
        InstallRepository $repo,
        Request $request
    )
    {
        $this->repo = $repo;
        $this->request = $request;
        $this->path = url('/'). '/vendor/jmrashed';
    }


    public function preRequisite(){

        $ac = Storage::disk('local')->exists('.app_installed') ? Storage::disk('local')->get('.app_installed') : null;
        if($ac){
            abort(404);
        }
        $checks = $this->repo->getPreRequisite();
		$server_checks = $checks['server'];
		$folder_checks = $checks['folder'];
        $verifier = $checks['verifier'];
        $has_false = in_array(false, $checks);

		envu(['APP_ENV' => 'production']);
		$name = env('APP_NAME');

        $data['asset_path'] =  $this->path;
		return view('service::install.preRequisite', compact('server_checks', 'folder_checks', 'name', 'verifier', 'has_false','data'));
    }

    public function license(){

        $checks = $this->repo->getPreRequisite();
        if(in_array(false, $checks)){
            return redirect()->route('service.preRequisite')->with(['message' => __('service::install.requirement_failed'), 'status' => 'error']);
        }

        $ac = Storage::disk('local')->exists('.app_installed') ? Storage::disk('local')->get('.app_installed') : null;
        if($ac){
            abort(404);
        }

        $reinstall = $this->repo->checkReinstall();

        $data['asset_path'] =  $this->path;
		return view('service::install.license', compact('reinstall','data'));
    }

    public function post_license(LicenseRequest $request){
        // return $request;
        $response = $this->repo->validateLicense($request->all());
        if($response && gv($response, 'goto')){
            $message = __('We can not verify your credentials, Please wait');
            $goto = $response['goto'];
        } else{
            session()->flash('license', 'verified');
            $goto = route('service.database');
            $message = __('service::install.valid_license');
            if (request('re_install') && $this->repo->checkReinstall()){
                Storage::disk('local')->put('.app_installed', Storage::disk('local')->get('.temp_app_installed'));
                Storage::disk('local')->delete('.temp_app_installed');
                Storage::disk('local')->put('.install_count', Storage::disk('local')->get('.install_count') + 1);
                $goto = url('/');
                $message = __('service::install.re_installation_process_complete');
            }
        }

		return response()->json(['message' => $message, 'goto' => $goto]);
    }

    public function database(){

        $ac = Storage::disk('local')->exists('.temp_app_installed') ? Storage::disk('local')->get('.temp_app_installed') : null;
        if(!$ac){
            abort(404);
        }
        // if ($this->repo->checkDatabaseConnection()) {
        //     return redirect()->route('service.user')->with(['message' => __('service::install.connection_established'), 'status' => 'success']);
        // }

        $data['asset_path'] =  $this->path;
		return view('service::install.database', compact('data'));
    }

    public function post_database(DatabaseRequest $request){
        $this->repo->validateDatabase($request->all());
		return response()->json(['message' => __('service::install.connection_established'), 'goto' => route('service.user')]);
    }


    public function done(){

        $data['user'] = Storage::disk('local')->exists('.user_email') ? Storage::disk('local')->get('.user_email') : null;
        $data['pass'] = Storage::disk('local')->exists('.user_pass') ? Storage::disk('local')->get('.user_pass') : null;

        if($data['user'] and $data['pass']){
            Log::info('done');
            Storage::disk('local')->delete(['.user_email', '.user_pass']);
            Storage::disk('local')->put('.install_count', 1);


        $data['asset_path'] =  $this->path;
            return view('service::install.done', compact('data'));
        } else{
            abort(404);
        }

    }

     public function ManageAddOnsValidation(ModuleInstallRequest $request){
        $response = $this->repo->installModule($request->all());
        if($response){
            if($request->wantsJson()){
                return response()->json(['message' => __('service::install.module_verify'), 'reload' => true]);
            }
            Toastr::success(__('service::install.module_verify'), 'Success');
        }
         return back();

    }

    public function uninstall(){
        $response = $this->repo->uninstall($this->request->all());
        $message = 'Uninstall by script author successfully';
        info($message);
        return response()->json(['message' => $message, 'response' => $response]);
    }

    public function installTheme(ThemeInstallRequest $request){
        $this->repo->installTheme($request->all());
        $message = __('service::install.theme_verify');
        if($request->ajax()){
            return response()->json(['message' => $message, 'reload' => true]);
        }

        Toastr::success($message);
        return redirect()->back();

    }



    public function reinstall(Request $request, $key){
        
        if($key == "sure"){
            // storage/app/.access_code

            // user_email
            // user_pass
            // app_installed
            // install_count

            // storage/app/30626608
            // storage/app/.access_code
            // storage/app/.access_log
            // storage/app/.account_email
            // storage/app/.app_installed
            // storage/app/.install_count
            // storage/app/.logout

            $list = [
                'storage/app/30626608',
                'storage/app/.access_code',
                'storage/app/.access_log',
                'storage/app/.account_email',
                'storage/app/.app_installed',
                'storage/app/.install_count',
                'storage/app/.logout'
            ];

            foreach ($list as $key => $value) {
               unlink($value);
            }

        }
    }




}
