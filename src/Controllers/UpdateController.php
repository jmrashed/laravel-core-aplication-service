<?php

namespace Jmrashed\LaravelCoreService\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Jmrashed\LaravelCoreService\Repositories\InitRepository;
use Jmrashed\LaravelCoreService\Repositories\UpdateRepository;

class UpdateController extends Controller
{
    protected $request;
    protected $repo;
    protected $init, $path;

    public function __construct(
        Request $request,
        UpdateRepository $repo,
        InitRepository $init
    ) {
        $this->request = $request;
        $this->repo = $repo;
        $this->init = $init;
        $this->path = url('/').'/vendor/jmrashed';
    }

    public function index()
    {
        $preRequisite = $this->init->product();
        $data['asset_path'] =  $this->path;
        return view('service::update.index', $preRequisite);
    }

    public function download()
    {
        $release = $this->repo->download();
        return response()->json(['message' => trans('service::install.updated'), 'goto' => route('service.update')]);
    }

    public function update()
    {
        $this->repo->update($this->request->all());
        return response()->json(['message' => trans('service::install.updated'), 'goto' => route('service.update')]);
    }
}
