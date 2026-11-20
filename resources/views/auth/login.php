<div class="min-h-screen relative flex items-center justify-center
            overflow-hidden bg-[#F5EFEB] p-4 sm:p-7 lg:p-10"
     style="background-image: repeating-linear-gradient(90deg, rgba(190,145,125,.07) 0px, rgba(190,145,125,.07) 3px, transparent 3px, transparent 25px);">

    <!-- Ambient background -->
    <div class="absolute -top-40 -left-32 w-[500px] h-[500px]
                rounded-full bg-[#E9C8BD]/50 blur-[100px]"></div>

    <div class="absolute -bottom-40 -right-32 w-[500px] h-[500px]
                rounded-full bg-[#E8E8B9]/60 blur-[100px]"></div>


    <!-- Main Login Container -->
    <main class="relative z-10 w-full max-w-[1120px]
                min-h-[650px] lg:min-h-[680px]
                grid lg:grid-cols-[1.05fr_0.95fr]
                bg-[#F9F5F1]/90 backdrop-blur-xl
                border border-white/90
                rounded-[28px] sm:rounded-[36px]
                shadow-[0_30px_90px_rgba(105,72,56,0.16)]
                overflow-hidden">


        <!-- LEFT: BRANDING -->
        <section class="relative flex flex-col justify-between
                        p-7 sm:p-10 lg:p-12
                        bg-gradient-to-br from-[#EAD0C7]
                        via-[#EBD7CE] to-[#E9E5D9]
                        overflow-hidden">

            <!-- Decorative circles -->
            <div class="absolute -right-28 top-20 w-80 h-80
                        border border-white/40 rounded-full"></div>

            <div class="absolute -right-16 top-32 w-56 h-56
                        border border-white/30 rounded-full"></div>


            <!-- Brand -->
            <div class="relative z-10 flex items-center gap-3">

                <div class="w-12 h-12 rounded-2xl
                            bg-[#A9715A] text-white
                            flex items-center justify-center
                            shadow-lg shadow-[#A9715A]/20">

                    <span class="font-serif text-3xl">N</span>

                </div>

                <div>
                    <h1 class="font-bold tracking-[0.16em]
                               text-sm text-[#533C32]">
                        ND COFFEE
                    </h1>

                    <p class="text-[10px] text-[#95796D] mt-1">
                        Management System
                    </p>
                </div>

            </div>


            <!-- Main Content -->
            <div class="relative z-10 my-12">

                <span class="inline-flex items-center gap-2
                             text-[10px] uppercase tracking-[0.22em]
                             font-semibold text-[#A16F58]">
                    <span class="w-6 h-px bg-[#A16F58]"></span>
                    Your daily coffee story
                </span>

                <h2 class="mt-6 font-serif text-5xl sm:text-6xl
                           xl:text-[68px] leading-[1.08]
                           tracking-tight text-[#49362D]">

                    Good coffee,
                    <span class="block italic text-[#AD795F]">
                        good moments.
                    </span>

                </h2>

                <p class="mt-6 max-w-sm text-sm leading-7
                          text-[#856F63]">
                    Thoughtfully managing every little detail
                    behind your favorite coffee experience.
                </p>


                <!-- Coffee Visual Card -->
                <div class="relative mt-10 max-w-[390px]">

                    <div class="absolute inset-0 bg-[#B17B65]/15
                                rounded-[24px] rotate-[-3deg]"></div>

                    <div class="relative h-[175px] sm:h-[195px]
                                rounded-[24px] overflow-hidden
                                border-4 border-white/60 shadow-lg">

                        <img
                            src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=1000&q=85"
                            alt="Coffee brewing"
                            class="w-full h-full object-cover"
                        >

                        <div class="absolute inset-0
                                    bg-gradient-to-t
                                    from-[#39251D]/65
                                    to-transparent"></div>

                        <div class="absolute bottom-5 left-5 text-white">

                            <p class="text-[9px] uppercase
                                      tracking-[0.25em] text-white/70">
                                A little coffee inspiration
                            </p>

                            <p class="font-serif text-2xl mt-1">
                                Brew something beautiful.
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Left Footer -->
            <div class="relative z-10 flex justify-between
                        items-center text-[10px]
                        text-[#967C70]">

                <span>© <?= date('Y') ?> ND Coffee</span>

                <span class="tracking-widest">
                    MADE WITH CARE.
                </span>

            </div>

        </section>


        <!-- RIGHT: LOGIN -->
        <section class="flex items-center justify-center
                        p-6 sm:p-10 lg:p-12 xl:p-16
                        bg-[#FBF9F6]">

            <div class="w-full max-w-[380px]">

                <!-- Form Heading -->
                <div class="mb-9">

                    <div class="w-12 h-12 rounded-2xl
                                bg-[#F2E5DE]
                                flex items-center justify-center
                                text-[#A9715A] mb-7">

                        <svg class="w-6 h-6" fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.6"
                                  d="M15 3h4a2 2 0 0 1 2 2v14a2 2
                                     0 0 1-2 2h-4M10 17l5-5-5-5m5
                                     5H3"/>
                        </svg>

                    </div>

                    <p class="text-[10px] font-semibold
                              tracking-[0.2em] uppercase
                              text-[#B17B65]">
                        Welcome back
                    </p>

                    <h3 class="mt-3 text-3xl sm:text-4xl
                               font-semibold tracking-tight
                               text-[#49372F]">
                        Let's get started.
                    </h3>

                    <p class="mt-3 text-sm leading-6
                              text-[#A49A92]">
                        Sign in to your account to continue
                        managing your coffee shop.
                    </p>

                </div>


                <!-- Error -->
                <?php if (!empty($error)): ?>
                    <div class="mb-5 p-4 rounded-xl
                                bg-red-50 border border-red-100
                                text-sm text-red-700">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>


                <!-- Form -->
                <form action="/login" method="POST" class="space-y-5">

                    <input
                        type="hidden"
                        name="_token"
                        value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
                    >


                    <!-- Username -->
                    <div>

                        <label for="username"
                               class="block text-xs font-semibold
                                      text-[#685349] mb-2.5">
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars($oldUsername, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Enter your username"
                            autocomplete="username"
                            required
                            autofocus
                            class="w-full h-[54px] px-4 rounded-xl
                                   bg-[#F5F1ED]
                                   border border-[#EAE2DC]
                                   text-sm text-[#49372F]
                                   placeholder:text-[#B6A9A0]
                                   outline-none
                                   transition-all duration-200
                                   focus:bg-white
                                   focus:border-[#B17B65]
                                   focus:ring-4
                                   focus:ring-[#B17B65]/10"
                        >

                    </div>


                    <!-- Password -->
                    <div>

                        <label for="password"
                               class="block text-xs font-semibold
                                      text-[#685349] mb-2.5">
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                            class="w-full h-[54px] px-4 rounded-xl
                                   bg-[#F5F1ED]
                                   border border-[#EAE2DC]
                                   text-sm text-[#49372F]
                                   placeholder:text-[#B6A9A0]
                                   outline-none
                                   transition-all duration-200
                                   focus:bg-white
                                   focus:border-[#B17B65]
                                   focus:ring-4
                                   focus:ring-[#B17B65]/10"
                        >

                    </div>


                    <!-- Submit -->
                    <button
                        type="submit"
                        class="group w-full h-[54px]
                               mt-3 rounded-xl
                               bg-[#A9715A] text-white
                               font-semibold text-sm
                               flex items-center justify-center gap-3
                               shadow-lg shadow-[#A9715A]/15
                               hover:bg-[#925D49]
                               hover:shadow-xl
                               transition-all duration-300
                               active:scale-[0.99]">

                        <span>Sign In</span>

                        <svg class="w-4 h-4 transition-transform
                                    group-hover:translate-x-1"
                             fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.8"
                                  d="M5 12h14m-6-6 6 6-6 6"/>

                        </svg>

                    </button>

                </form>

                <!-- Copyright -->
                <p class="mt-10 text-center text-[10px]
                          text-[#B4A9A0]">
                    © <?= date('Y') ?> ND Coffee. All rights reserved.
                </p>

            </div>

        </section>

    </main>

</div>
