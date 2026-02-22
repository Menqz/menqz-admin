<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>{{config('admin.title')}} | {{ __('admin.login') }}</title>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

		@if(!is_null($favicon = Admin::favicon()))
		<link rel="shortcut icon" href="{{$favicon}}">
		@endif

		<link rel="stylesheet" href="{{ Admin::asset("menqz-admin/css/styles.css")}}">
		<script src="{{ Admin::asset("bootstrap5/bootstrap.bundle.min.js")}}"></script>

	</head>
	<body class="bg-light" @if(config('admin.login_background_image'))style="background: url({{config('admin.login_background_image')}}) no-repeat center center;background-size: cover;"@endif>
		<div class="position-relative min-vh-100 d-flex align-items-center justify-content-center px-3">
			<div class="row w-100 justify-content-center" style="max-width: 960px;">
				<div class="col-lg-6 mb-4 mb-lg-0">
					<div class="h-100 rounded-4 text-white bg-semi-dark p-4 p-lg-5" style="box-shadow: 0 1.5rem 3rem rgba(15,23,42,.4);">
						<div class="d-flex flex-column h-100">
							<div class="mb-4">
								<h1 class="fs-2 fw-semibold mb-2">
									<a href="{{ admin_url('/') }}" class="text-white text-decoration-none">{{config('admin.name')}}</a>
								</h1>
								<p class="mb-0 opacity-75">
									{{config('admin.description', '')}}
								</p>
							</div>
							<div class="mt-auto small opacity-75">
								<span>{{config('admin.title')}}</span>
							</div>
						</div>
					</div>
				</div>

				<div class="col-lg-5">
					<div class="bg-white rounded-4 shadow-lg p-4 p-lg-5">
						<div class="mb-4 text-center">
							<h2 class="h4 fw-semibold mb-2">{{ __('admin.login') }}</h2>
							<p class="text-muted small mb-0">Insira suas credenciais para entrar.</p>
						</div>

						@if($errors->has('attempts'))
							<div class="alert alert-danger text-center mb-0">{{$errors->first('attempts')}}</div>
						@else

						<form action="{{ admin_url('auth/login') }}" method="post" class="mt-3">
							<input type="hidden" name="_token" value="{{ csrf_token() }}">

							<div class="mb-3">
								<label for="username" class="form-label small text-muted mb-1">{{ __('admin.username') }}</label>
								<input type="text" class="form-control form-control-lg" name="username" id="username" placeholder="{{ __('admin.username') }}" value="{{ old('username') }}" required autofocus>
								@if($errors->has('username'))
									<div class="text-danger small mt-1">{{$errors->first('username')}}</div>
								@endif
							</div>

							<div class="mb-3">
								<label for="password" class="form-label small text-muted mb-1">{{ __('admin.password') }}</label>
								<input type="password" class="form-control form-control-lg" name="password" id="password" placeholder="{{ __('admin.password') }}" required>
								@if($errors->has('password'))
									<div class="text-danger small mt-1">{{$errors->first('password') }}</div>
								@endif
							</div>

							@if(config('admin.auth.remember'))
							<div class="d-flex justify-content-between align-items-center mb-4">
								<div class="form-check">
									<input type="checkbox" class="form-check-input" name="remember" id="remember" value="1" {{ (old('remember')) ? 'checked="checked"' : '' }}>
									<label class="form-check-label small" for="remember">{{ __('admin.remember_me') }}</label>
								</div>
							</div>
							@endif

							<button type="submit" class="btn btn-primary btn-lg w-100">
								{{ __('admin.login') }}
							</button>
						</form>
						@endif
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
