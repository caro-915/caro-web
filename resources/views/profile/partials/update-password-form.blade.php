<section>
    <header class="mb-4">
        <h2 class="text-base font-semibold text-gray-900">
            Mot de passe
        </h2>
        <p class="mt-1 text-xs text-gray-500">
            Modifiez votre mot de passe pour sécuriser votre compte.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-3">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-xs font-semibold text-gray-600 mb-1">MOT DE PASSE ACTUEL</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800" />
            @if($errors->updatePassword->has('current_password'))
                <p class="mt-1 text-xs text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="block text-xs font-semibold text-gray-600 mb-1">NOUVEAU MOT DE PASSE</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800" />
            @if($errors->updatePassword->has('password'))
                <p class="mt-1 text-xs text-red-600">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-semibold text-gray-600 mb-1">CONFIRMER LE MOT DE PASSE</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800" />
            @if($errors->updatePassword->has('password_confirmation'))
                <p class="mt-1 text-xs text-red-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-gray-800 text-white text-sm font-semibold px-6 py-2 hover:bg-gray-900">
                Modifier le mot de passe
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-xs text-green-600">
                    Modifié !
                </p>
            @endif
        </div>
    </form>
</section>
