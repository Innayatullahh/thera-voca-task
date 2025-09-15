<template>
  <div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-md mx-auto">
      <div class="bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Submit Actor Information</h1>
        
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
              :class="{ 'border-red-500': errors.email }"
              
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email }}
            </p>
          </div>

          <!-- Description Field -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
              Actor Description
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
              :class="{ 'border-red-500': errors.description }"
              placeholder="Describe the actor..."
              
            ></textarea>
            <p class="mt-2 text-sm text-gray-600">
              Please enter first name, last name, address, height, weight, gender, and age.
            </p>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
              {{ errors.description }}
            </p>
          </div>

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              :disabled="processing"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="processing" class="flex items-center">
                <span class="animate-spin mr-3">⏳</span>
                Processing...
              </span>
              <span v-else>Submit</span>
            </button>
          </div>
        </form>

        <!-- Link to view submissions -->
        <div class="mt-6 text-center">
          <Link
            :href="route('actors.index')"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            View All Submissions
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { route } from 'ziggy-js'

const form = useForm({
  email: '',
  description: ''
})

const submit = () => {
  form.post(route('actors.store'))
}

defineProps<{
  errors?: Record<string, string>
}>()

const processing = computed(() => form.processing)
</script>
