<?php

namespace Jmrashed\LaravelCoreService\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Jmrashed\LaravelCoreService\Repositories\LicenseRepository;
use Toastr;

class LicenseController extends Controller{
    protected $repo, $request, $path;

    public function __construct(LicenseRepository $repo, Request $request)
    {
         $this->middleware('auth');
        $this->repo = $repo;
        $this->request = $request;

        $this->path = url('/'). '/vendor/jmrashed';
    }


    public function revoke(){

        $ac = Storage::disk('local')->exists('.app_installed') ? Storage::disk('local')->get('.app_installed') : null;
        if(!$ac){ 
        $data['asset_path'] =  $this->path;
            return redirect()->route('service.install', compact('data'));
        }

        abort_if(auth()->user()->role_id != 1, 403);

        $this->repo->revoke();

        $data['asset_path'] =  $this->path;
        return redirect()->route('service.install', compact('data'));

    }

    public function revokeModule(Request $request){

        $ac = Storage::disk('local')->exists('.app_installed') ? Storage::disk('local')->get('.app_installed') : null;
        if(!$ac){
            $data['asset_path'] =  $this->path;
            return redirect()->route('service.install', compact('data'));
        }

        abort_if(auth()->user()->role_id != 1, 403);

        $this->repo->revokeModule($request->all());
        Toastr::success('Your module license revoke successfull');

        return redirect()->back();

    }

    public function revokeTheme(Request $request){

        $ac = Storage::disk('local')->exists('.app_installed') ? Storage::disk('local')->get('.app_installed') : null;
        if(!$ac){
            $data['asset_path'] =  $this->path;
            return redirect()->route('service.install', compact('data'));
            // return redirect()->route('service.install');
        }

        abort_if(auth()->user()->role_id != 1, 403);

        $this->repo->revokeTheme($request->all());
        Toastr::success('Your theme license revoke successfull');

        return redirect()->back();

    }
}
