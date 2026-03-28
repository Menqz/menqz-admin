<?php

namespace MenqzAdmin\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use MenqzAdmin\Admin\Facades\Admin;
use MenqzAdmin\Admin\Form;
use MenqzAdmin\Admin\Layout\Content;

class AuthController extends Controller
{
    /**
     * @var string
     */
    protected $loginView = 'admin::login';

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view($this->loginView);
    }

    /**
     * Handle a login request.
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $this->normalizeLoginField($request);

        $login = strtolower($request->input($this->username()));
        $rate_limit_key = 'login-tries-'.$login.'-'.$request->ip();

        session(['login_throttle_key' => $rate_limit_key]);

        $this->loginValidator($request->all())->validate();

        $credentials = $request->only([$this->username(), 'password']);

        $remember = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            RateLimiter::clear($rate_limit_key);
            session()->forget('login_throttle_key');

            return $this->sendLoginResponse($request);
        }

        if (config('admin.auth.throttle_logins')) {
            $throttle_timeout = config('admin.auth.throttle_timeout', 600);
            RateLimiter::hit($rate_limit_key, $throttle_timeout);
        }

        return back()->withInput()->withErrors([
            'login' => $this->getFailedLoginMessage(),
        ]);
    }

    public function getSocialRedirect(string $provider)
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        $provider = strtolower($provider);

        $this->ensureSocialProviderEnabled($provider);

        return $this->socialiteDriver($provider)->redirect();
    }

    public function getSocialCallback(Request $request, string $provider)
    {
        $provider = strtolower($provider);

        $this->ensureSocialProviderEnabled($provider);

        try {
            $socialUser = $this->socialiteDriver($provider)->user();
        } catch (\Throwable $e) {
            return redirect(admin_url('auth/login'))->withErrors([
                'social' => $this->getFailedLoginMessage(),
            ]);
        }

        $email = $socialUser->getEmail();

        if (! $email) {
            return redirect(admin_url('auth/login'))->withErrors([
                'social' => $this->getFailedLoginMessage(),
            ]);
        }

        $userModel = config('admin.database.users_model');
        $user = (new $userModel)->newQuery()->where('email', $email)->first();

        if (! $user) {
            return redirect(admin_url('auth/login'))->withErrors([
                'social' => $this->getFailedLoginMessage(),
            ]);
        }

        $socialId = $socialUser->getId();

        if ($socialId) {
            $user->forceFill([
                'social_id' => $socialId,
                'social_type' => $provider,
            ])->save();
        }

        $rate_limit_key = 'login-tries-'.strtolower($email).'-'.$request->ip();
        RateLimiter::clear($rate_limit_key);
        session()->forget('login_throttle_key');

        $this->guard()->login($user);

        return $this->sendLoginResponse($request);
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username() => $this->username() === 'email' ? 'required|email' : 'required',
            'password' => 'required',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->disableView();
            }
        );

        return $content
            ->title(trans('admin.user_setting'))
            ->body($form->edit(Admin::user()->id));
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class);

        $form->display('username', trans('admin.username'));
        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('confirmed|required');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->setAction(admin_url('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_url('auth/setting'));
        });

        return $form;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        $loginType = config('admin.auth.login_type', 'username');

        return in_array($loginType, ['username', 'email'], true) ? $loginType : 'username';
    }

    protected function normalizeLoginField(Request $request): void
    {
        $loginType = $this->username();

        if ($request->has($loginType)) {
            return;
        }

        $fallback = $loginType === 'email' ? 'username' : 'email';

        if ($request->has($fallback)) {
            $request->merge([$loginType => $request->input($fallback)]);
        }
    }

    protected function socialiteDriver(string $provider)
    {
        $providerConfig = $this->socialProviderConfig($provider);

        config(["services.{$provider}" => $providerConfig]);

        $driver = Socialite::driver($provider);

        if (! empty($providerConfig['scopes']) && is_array($providerConfig['scopes'])) {
            $driver->scopes($providerConfig['scopes']);
        }

        if ($provider === 'facebook' && ! empty($providerConfig['fields']) && is_array($providerConfig['fields'])) {
            $driver->fields($providerConfig['fields']);
        }

        return $driver;
    }

    protected function ensureSocialProviderEnabled(string $provider): void
    {
        if (! config('admin.auth.social.enabled', false)) {
            abort(404);
        }

        if (! class_exists(Socialite::class)) {
            abort(500);
        }

        if (! in_array($provider, ['google', 'facebook'], true)) {
            abort(404);
        }

        if (! config("admin.auth.social.providers.{$provider}.enabled", false)) {
            abort(404);
        }
    }

    protected function socialProviderConfig(string $provider): array
    {
        $providerConfig = (array) config("admin.auth.social.providers.{$provider}", []);

        $clientId = $providerConfig['client_id'] ?? null;
        $clientSecret = $providerConfig['client_secret'] ?? null;

        if (! $clientId || ! $clientSecret) {
            abort(500);
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect' => admin_url("auth/social/{$provider}/callback"),
            'scopes' => $providerConfig['scopes'] ?? [],
            'fields' => $providerConfig['fields'] ?? [],
        ];
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }
}
