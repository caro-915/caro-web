<section>
    <header class="mb-4">
        <h2 class="text-base font-semibold text-gray-900">
            Suppression du compte
        </h2>
        <p class="mt-1 text-xs text-gray-500">
            Cette action est définitive. Toutes vos annonces et données seront supprimées.
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="rounded-lg bg-red-600 text-white text-sm font-semibold px-6 py-2 hover:bg-red-700"
    >
        Supprimer mon compte
    </button>

    <div
        x-data="{ show: false }"
        x-on:open-modal.window="if ($event.detail === 'confirm-user-deletion') show = true"
        x-on:close.stop="show = false"
        x-on:keydown.escape.window="show = false"
        x-show="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="show = false"></div>

            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                    @csrf
                    @method('delete')

                    <h2 class="text-base font-semibold text-gray-900">
                        Êtes-vous sûr de vouloir supprimer votre compte ?
                    </h2>

                    <p class="mt-2 text-xs text-gray-500">
                        Une fois supprimé, toutes vos annonces et données seront définitivement effacées. Entrez votre mot de passe pour confirmer.
                    </p>

                    <div class="mt-4">
                        <label for="password" class="sr-only">Mot de passe</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Mot de passe"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800"
                        />
                        @if($errors->userDeletion->has('password'))
                            <p class="mt-1 text-xs text-red-600">{{ $errors->userDeletion->first('password') }}</p>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="show = false" class="rounded-lg border border-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" class="rounded-lg bg-red-600 text-white text-sm font-semibold px-4 py-2 hover:bg-red-700">
                            Supprimer mon compte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
