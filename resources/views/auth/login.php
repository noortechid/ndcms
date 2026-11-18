<div class="min-h-screen flex items-center justify-center px-5 py-10">

    <div class="w-full max-w-md">

        <div class="text-center mb-8">

            <div class="w-16 h-16 mx-auto rounded-2xl bg-primary
                        flex items-center justify-center shadow-sm">
                <span class="text-white text-2xl font-bold">N</span>
            </div>

            <h1 class="text-2xl font-bold mt-5">ND Coffee</h1>

            <p class="text-sm text-text-secondary mt-2">
                Management System
            </p>

        </div>

        <div class="bg-white border border-border rounded-2xl p-7 sm:p-9 shadow-sm">

            <div class="mb-7">
                <h2 class="text-xl font-bold">Welcome Back!</h2>

                <p class="text-sm text-text-secondary mt-2">
                    Sign in to access your workspace.
                </p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-5 p-3 rounded-xl bg-red-50 border border-red-100
                            text-sm text-red-700">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" class="space-y-5">

                <input
                    type="hidden"
                    name="_token"
                    value="<?= htmlspecialchars($csrfToken) ?>"
                >

                <div>
                    <label for="username"
                           class="block text-sm font-medium mb-2">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($oldUsername) ?>"
                        placeholder="Enter your username"
                        autocomplete="username"
                        required
                        autofocus
                        class="w-full px-4 py-3 rounded-xl border border-border
                               outline-none focus:ring-2 focus:ring-primary/30
                               focus:border-primary transition"
                    >
                </div>

                <div>
                    <label for="password"
                           class="block text-sm font-medium mb-2">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-border
                               outline-none focus:ring-2 focus:ring-primary/30
                               focus:border-primary transition"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3 rounded-xl bg-primary text-white
                           font-semibold hover:bg-primary-dark transition">
                    Sign In
                </button>

            </form>

        </div>

        <p class="text-center text-xs text-text-secondary mt-7">
            &copy; <?= date('Y') ?> ND Coffee. All rights reserved.
        </p>

    </div>

</div>
