<?php

namespace Jmrashed\LaravelCoreService\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;  
use Jmrashed\LaravelCoreService\Repositories\InitRepository;

class CheckController extends Controller
{

    private $initRepo, $request;

    public function __construct(InitRepository $initRepo, Request $request)
    {
        $this->initRepo = $initRepo;
        $this->request = $request;
    }

    public function verify()
    {
        $requests = $this->request->all();
        $response = gv($requests, 'response', []);
        $params = gv($requests, 'params', []);
        try {
            if(gbv($response, 'status')){
                return $this->successAction($requests, $response, $params);
            }

            if(gv($params, 'a') == 'verify'){
                Log::info('Initial License Verification failed. Message: '. gv($response, 'message'));
                Storage::disk('local')->delete(['.access_code', '.account_email']);
                Storage::disk('local')->deleteDirectory(config('app.item'));
                Storage::disk('local')->put('.app_installed', '');
                Storage::disk('local')->put('.logout', 'true');
                Auth::logout();
                $goto = route('service.install');
                if (gv($requests, 'from') == 'browser') {
                    return redirect($goto)->send();
                }
                return [
                    'status' => false,
                    'goto' => $goto
                ];
            }

            return $response;

        } catch (\Exception $e) {

            $message = $e->getMessage();
            \Log::error($e);
            $goto = route('service.install');
            if(gv($params, 'a') == 'verify'){
                Storage::disk('local')->put('.access_log', date('Y-m-d'));
                $goto = gv($params, 'current', route('dashboard'));
            }


            if (gv($requests, 'from') == 'browser') {
                Toastr::error($message);
                return redirect($goto)->send();
            }
            return [
                'status' => false,
                'goto' => route('service.install'),
                'message' => $e->getMessage(),
            ];
        }
    }

    public function successAction($requests, $response, $params){

        if (gv($params, 'a') == 'install') {
            $checksum = $response['checksum'] ?? null;
            $license_code = $response['license_code'] ?? null;

            if (gbv($params, 'ri')) {
                Storage::disk('local')->put('.app_installed', $checksum);
                Storage::disk('local')->put('.install_count', Storage::disk('local')->get('.install_count') + 1);
                $goto = url('/');
                $message = __('service::install.re_installation_process_complete');
            } else {
                Storage::disk('local')->put('.temp_app_installed', $checksum ?? '');
                $goto = route('service.database');
                $message = gv($response, 'message', __('service::install.valid_license'));
            }

            Storage::disk('local')->put('.access_code', $license_code ?? '');
            Storage::disk('local')->put('.account_email', gv($params, 'e'));
            Storage::disk('local')->put('.access_log', date('Y-m-d'));
            Toastr::success($message);
        } elseif (gv($params, 'a') == 'verify') {
            Storage::disk('local')->put('.access_log', date('Y-m-d'));
            $goto = gv($params, 'current', route('dashboard'));
        }


        if (gv($requests, 'from') == 'browser') {
            return redirect($goto);
        }

        return [
            'status' => true,
            'goto' => $goto
        ];
    }

}
