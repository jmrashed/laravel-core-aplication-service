<?php

namespace HonestTraders\CoreService\Repositories;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InitRepository {

    public function init() {
        config(['app.verifier' => 'http://auth.test.com']);
        config(['app.ux_verifier' => 'http://uxauth.test.com']);
        // config(['app.verifier' => '']);
        // config(['app.ux_verifier' => '']);
        config(['app.signature' => 'eyJpdiI6Im9oMWU5Z0NoSGVwVzdmQlphaVBvd1E9PSIsInZhbHVlIjoiUURhZmpubkNBUVB6b0ZPck1v']);
    }

    public function checkDatabase(){

        try {
            if (!Storage::disk('local')->has('settings.json')) {
                DB::connection()->getPdo();
                if (!Schema::hasTable('users')){
                    return false;
                }
            }
        } catch(\Exception $e){
            $error = $e->getCode();
            if($error == 2002){
                abort(403, 'No connection could be made because the target machine actively refused it');
            } else if($error == 1045){
                $c = Storage::disk('local')->exists('.app_installed') && Storage::disk('local')->get('.app_installed');
                if($c){
                    abort(403, 'Access denied for user. Please check your database username and password.');
                }

            }
        }

        return true;
    }

    public function check() {
        return;
        if (isTestMode()) {
            return;
        }

        if (Storage::disk('local')->exists('.access_log') && Storage::disk('local')->get('.access_log') == date('Y-m-d')) {
            return;
        }

        if (!isConnected()) {
            return;
        }

        $ac = Storage::disk('local')->exists('.access_code') ? Storage::disk('local')->get('.access_code') : null;
        $e = Storage::disk('local')->exists('.account_email') ? Storage::disk('local')->get('.account_email') : null;
        $c = Storage::disk('local')->exists('.app_installed') ? Storage::disk('local')->get('.app_installed') : null;
        $v = Storage::disk('local')->exists('.version') ? Storage::disk('local')->get('.version') : null;

        if (!$ac) {
            Log::info('Activation code not found from init');
            return false;
        }
        $ve = Storage::disk('local')->exists('.ve') ? Storage::disk('local')->get('.ve') : 'e';
        $url = verifyUrl(config('honesttraders.verifier', 'auth')) . '/api/cc?a=verify&u=' . app_url() . '&ac=' . $ac . '&i=' . config('app.item') . '&e=' . $e . '&c=' . $c . '&v=' . $v.'&current='.urlencode(request()->path()).'&ve='.$ve;
        $response = curlIt($url);


        if ($goto = gv($response, 'goto')){
            return redirect($goto)->send();
        }

        if($response){
            $status = gbv($response, 'status');

            if (!$status) {
                Log::info('Initial License Verification failed. Message: '. gv($response, 'message'));

                Storage::disk('local')->delete(['.access_code', '.account_email']);
                Storage::disk('local')->deleteDirectory(config('app.item'));
                Storage::disk('local')->put('.app_installed', '');
                Auth::logout();
                return redirect()->route('service.install')->send();
            }
        }
        Storage::disk('local')->put('.access_log', date('Y-m-d'));
    }

    public function apiCheck(){
        return true;
        $ac = Storage::disk('local')->exists('.access_code') ? Storage::disk('local')->get('.access_code') : null;
        $e = Storage::disk('local')->exists('.account_email') ? Storage::disk('local')->get('.account_email') : null;
        $c = Storage::disk('local')->exists('.app_installed') ? Storage::disk('local')->get('.app_installed') : null;
        $v = Storage::disk('local')->exists('.version') ? Storage::disk('local')->get('.version') : null;

        if (!$ac) {
            Log::info('Activation code not found from apicheck');
            return false;
        }
        $ve = Storage::disk('local')->exists('.ve') ? Storage::disk('local')->get('.ve') : 'e';
        $url = verifyUrl(config('honesttraders.verifier', 'auth')) . '/api/cc?a=verify&u=' . app_url() . '&ac=' . $ac . '&i=' . config('app.item') . '&e=' . $e . '&c=' . $c . '&v=' . $v.'&ve='.$ve;
        $response = curlIt($url);

        if($response){
            $status = gbv($response, 'status');
            if (!$status) {
                Log::info('Api License Verification failed. Message: '. gv($response, 'message'));
                return false;
            } else {
                return true;
            }
        } else{
            return true;
        }
    }

    public function product() {
        if (!isConnected()) {
            throw ValidationException::withMessages(['message' => 'No internect connection.']);
        }

        $ac = Storage::disk('local')->exists('.access_code') ? Storage::disk('local')->get('.access_code') : null;
        $e = Storage::disk('local')->exists('.account_email') ? Storage::disk('local')->get('.account_email') : null;
        $c = Storage::disk('local')->exists('.app_installed') ? Storage::disk('local')->get('.app_installed') : null;
        $v = Storage::disk('local')->exists('.version') ? Storage::disk('local')->get('.version') : null;

        $about = file_get_contents(verifyUrl(config('honesttraders.verifier', 'auth')) . '/about');
        $update_tips = file_get_contents(verifyUrl(config('honesttraders.verifier', 'auth')) . '/update-tips');
        $support_tips = file_get_contents(verifyUrl(config('honesttraders.verifier', 'auth')) . '/support-tips');

        $url = verifyUrl(config('honesttraders.verifier', 'auth')) . '/api/cc?a=product&u=' .  app_url() . '&ac=' . $ac . '&i=' . config('app.item') . '&e=' . $e . '&c=' . $c . '&v=' . $v;


        $response = curlIt($url);

        $status = gbv($response, 'status');

        if (!$status) {

            abort(404);
        }

        $product = gv($response, 'product', []);

        $next_release_build = gv($product, 'next_release_build');

        $is_downloaded = 0;
        if ($next_release_build) {
            if (File::exists( $next_release_build)) {
                $is_downloaded = 1;
            }
        }

        if (isTestMode()) {
            $product['purchase_code'] = config('system.hidden_field');
            $product['email'] = config('system.hidden_field');
            $product['access_code'] = config('system.hidden_field');
            $product['checksum'] = config('system.hidden_field');

            $is_downloaded = 0;
        }

        return compact('about', 'product', 'update_tips', 'support_tips', 'is_downloaded');
    }

}
