<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { User, Mail, Lock, Eye, EyeOff, ArrowRight } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const isHovering = ref(false);

// AJOUT DE LA MÉTHODE SUBMIT (comme dans Login)
const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-purple-50 to-indigo-100">
            <div class="w-full max-w-md">
                <transition
                    appear
                    enter-active-class="transition-all duration-500 ease-out"
                    enter-from-class="opacity-0 translate-y-10"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-8 space-y-6 backdrop-blur-sm bg-opacity-90 border border-white/20">
                        <div class="text-center">
                            <h1 class="text-3xl font-bold text-gray-900 bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                                Create Account
                            </h1>
                            <p class="mt-2 text-gray-500">Join our community today</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- Name Field -->
                            <div>
                                <InputLabel for="name" value="Full Name" class="text-gray-700" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <User class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="block w-full pl-10 pr-3 py-3 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-200"
                                        v-model="form.name"
                                        required
                                        autofocus
                                        autocomplete="name"
                                        placeholder="John Doe"
                                    />
                                </div>
                                <InputError class="mt-1 text-sm" :message="form.errors.name" />
                            </div>

                            <!-- Email Field -->
                            <div>
                                <InputLabel for="email" value="Email" class="text-gray-700" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Mail class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="block w-full pl-10 pr-3 py-3 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-200"
                                        v-model="form.email"
                                        required
                                        autocomplete="email"
                                        placeholder="your@email.com"
                                    />
                                </div>
                                <InputError class="mt-1 text-sm" :message="form.errors.email" />
                            </div>

                            <!-- Password Field -->
                            <div>
                                <InputLabel for="password" value="Password" class="text-gray-700" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Lock class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <TextInput
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="block w-full pl-10 pr-10 py-3 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-200"
                                        v-model="form.password"
                                        required
                                        autocomplete="new-password"
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        @click="showPassword = !showPassword"
                                    >
                                        <component :is="showPassword ? EyeOff : Eye" class="h-5 w-5 text-gray-400 hover:text-gray-500 transition-colors" />
                                    </button>
                                </div>
                                <InputError class="mt-1 text-sm" :message="form.errors.password" />
                            </div>

                            <!-- Confirm Password Field -->
                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" class="text-gray-700" />
                                <div class="relative mt-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <Lock class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <TextInput
                                        id="password_confirmation"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        class="block w-full pl-10 pr-10 py-3 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 transition duration-200"
                                        v-model="form.password_confirmation"
                                        required
                                        autocomplete="new-password"
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                    >
                                        <component :is="showConfirmPassword ? EyeOff : Eye" class="h-5 w-5 text-gray-400 hover:text-gray-500 transition-colors" />
                                    </button>
                                </div>
                                <InputError class="mt-1 text-sm" :message="form.errors.password_confirmation" />
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <Link
                                    :href="route('login')"
                                    class="text-sm text-indigo-600 hover:text-indigo-500 transition-colors duration-200 flex items-center"
                                    @mouseenter="isHovering = true"
                                    @mouseleave="isHovering = false"
                                >
                                    <span class="relative">
                                        Already registered?
                                        <span
                                            class="absolute bottom-0 left-0 w-full h-0.5 bg-indigo-500 transition-all duration-300"
                                            :class="isHovering ? 'scale-x-100' : 'scale-x-0'"
                                        ></span>
                                    </span>
                                    <ArrowRight class="w-4 h-4 ml-1 transition-transform" :class="{ 'translate-x-1': isHovering }" />
                                </Link>

                                <PrimaryButton
                                    class="flex justify-center py-3 px-6 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:scale-[1.02]"
                                    :class="{ 'opacity-70': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="!form.processing">Register Now</span>
                                    <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </PrimaryButton>
                            </div>
                        </form>

                        <div class="text-center text-xs text-gray-400 mt-6">
                            By registering, you agree to our
                            <a href="#" class="text-indigo-500 hover:text-indigo-600">Terms</a> and
                            <a href="#" class="text-indigo-500 hover:text-indigo-600">Privacy Policy</a>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </GuestLayout>
</template>

<style>
/* Animation douce pour les erreurs */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Effet de flottement léger */
@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}
.float {
  animation: float 4s ease-in-out infinite;
}
</style>