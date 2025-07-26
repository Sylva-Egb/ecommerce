<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Lock, Mail, Eye, EyeOff } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const isHovering = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-indigo-50 to-blue-100">
            <div class="w-full max-w-md">
                <transition
                    enter-active-class="transition ease-out duration-500"
                    enter-from-class="opacity-0 translate-y-10"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden p-8 space-y-6 backdrop-blur-sm bg-opacity-90 border border-white/20">
                        <div class="text-center">
                            <h1 class="text-3xl font-bold text-gray-900 bg-gradient-to-r from-indigo-600 to-blue-500 bg-clip-text text-transparent">
                                Welcome Back
                            </h1>
                            <p class="mt-2 text-gray-500">Sign in to your account</p>
                        </div>

                        <transition
                            enter-active-class="transition ease-out duration-300"
                            enter-from-class="opacity-0 scale-95"
                            enter-to-class="opacity-100 scale-100"
                        >
                            <div v-if="status" class="p-3 rounded-lg bg-emerald-100 text-emerald-700 text-sm flex items-center justify-center">
                                {{ status }}
                            </div>
                        </transition>

                        <form @submit.prevent="submit" class="space-y-5">
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
                                        autofocus
                                        autocomplete="username"
                                        placeholder="your@email.com"
                                    />
                                </div>
                                <InputError class="mt-1 text-sm" :message="form.errors.email" />
                            </div>

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
                                        autocomplete="current-password"
                                        placeholder="••••••••"
                                    />
                                    <button
                                        type="button"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        @click="showPassword = !showPassword"
                                    >
                                        <component :is="showPassword ? EyeOff : Eye" class="h-5 w-5 text-gray-400 hover:text-gray-500" />
                                    </button>
                                </div>
                                <InputError class="mt-1 text-sm" :message="form.errors.password" />
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <Checkbox
                                        name="remember"
                                        v-model:checked="form.remember"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition"
                                    />
                                    <span class="text-sm text-gray-600">Remember me</span>
                                </label>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm text-indigo-600 hover:text-indigo-500 transition-colors duration-200"
                                    @mouseenter="isHovering = true"
                                    @mouseleave="isHovering = false"
                                >
                                    <span class="relative">
                                        Forgot password?
                                        <span
                                            class="absolute bottom-0 left-0 w-full h-0.5 bg-indigo-500 transition-all duration-300"
                                            :class="isHovering ? 'scale-x-100' : 'scale-x-0'"
                                        ></span>
                                    </span>
                                </Link>
                            </div>

                            <div>
                                <PrimaryButton
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:scale-[1.02]"
                                    :class="{ 'opacity-70': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="!form.processing">Sign in</span>
                                    <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </PrimaryButton>
                            </div>
                        </form>

                        <div class="text-center text-sm text-gray-500">
                            Don't have an account?
                            <Link :href="route('register')" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                                Sign up
                            </Link>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </GuestLayout>
</template>

<style>
/* Animation douce pour les entrées */
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(10px);
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
  animation: float 3s ease-in-out infinite;
}
</style>