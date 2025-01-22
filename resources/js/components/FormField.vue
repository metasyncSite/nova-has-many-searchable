<template>
  <DefaultField :field="field" :errors="errors">
    <template #field>
      <div class="tw-relative hasManySearchable">
        <div class="flex flex-col space-y-2">
          <div v-if="field.showCreateButton" class="mb-2">
            <button
                type="button"
                @click="showCreateModal = true"
                class="px-4 py-2 text-sm font-medium text-white bg-primary-500 rounded-lg hover:bg-primary-600"
            >
              {{ field.createButtonLabel }}
            </button>

            <CreateRelationModal
                :show="showCreateModal"
                :resource-name="field.resourceName"
                @create-cancelled="showCreateModal = false"
                @set-resource="handleResourceCreated"
            />
          </div>

          <div class="flex flex-wrap p-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900">
            <div v-for="selected in selectedItems"
                 :key="selected.value"
                 class="inline-flex items-center px-2 py-1 tw-m-1 text-sm rounded
                                    bg-gray-100 dark:bg-gray-800">
              <span class="text-gray-900 dark:text-white">{{ selected.label }}</span>
              <button @click.prevent="removeItem(selected)"
                      class="tw-ml-1 tw-text-gray-500 tw-hover:text-gray-700 tw-dark:text-gray-400 tw-dark:hover:text-gray-200">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <input type="text"
                   v-model="search"
                   @focus="showDropdown = true"
                   placeholder="Search..."
                   class="tw-w-full flex-grow min-w-[150px] p-1 outline-none border-none
                                      bg-transparent text-gray-900 dark:text-white
                                      placeholder-gray-400 dark:placeholder-gray-500" />
          </div>

          <div v-if="showDropdown"
               class="tw-absolute tw-top-full dropDownMenu tw-z-50 w-full mt-1 rounded-lg shadow-lg
                                bg-white dark:bg-gray-900
                                border border-gray-300 dark:border-gray-700">
            <div v-for="option in filteredOptions"
                 :key="option.value"
                 @click="selectItem(option)"
                 class="p-2 cursor-pointer text-gray-900 dark:text-white
                                    hover:bg-gray-100 dark:hover:bg-gray-800">
              {{ option.label }}
            </div>

            <div v-if="!filteredOptions.length && !loading"
                 class="p-2 text-gray-500 dark:text-gray-400">
              No options available
            </div>

            <div v-if="loading"
                 class="p-2 text-gray-500 dark:text-gray-400">
              Loading...
            </div>
          </div>
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import { ref, computed, onMounted, onUnmounted } from 'vue'

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  setup(props) {
    const search = ref('')
    const showDropdown = ref(false)
    const selectedItems = ref([])
    const loading = ref(false)
    const showCreateModal = ref(false)

    const filteredOptions = computed(() => {
      if (!props.field.options) return []

      return props.field.options.filter(option =>
          option.label.toLowerCase().includes(search.value.toLowerCase()) &&
          !selectedItems.value.some(selected => selected.value === option.value)
      )
    })

    const selectItem = (option) => {
      selectedItems.value.push(option)
      search.value = ''
      showDropdown.value = false
    }

    const removeItem = (option) => {
      selectedItems.value = selectedItems.value.filter(item => item.value !== option.value)
    }

    const handleClickOutside = (event) => {
      if (!event.target.closest('.relative')) {
        showDropdown.value = false
      }
    }

    const handleResourceCreated = (resource) => {
      const newOption = {
        value: resource.id.toString(),
        label: resource.title || `ID: ${resource.id}`
      }

      props.field.options = [...props.field.options, newOption]
      selectItem(newOption)
      showCreateModal.value = false
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)

      if (props.field.value) {
        selectedItems.value = props.field.value.map(value => {
          const option = props.field.options.find(opt => opt.value === value)
          return option || { value, label: `ID: ${value}` }
        }).filter(Boolean)
      }
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    return {
      search,
      showDropdown,
      selectedItems,
      filteredOptions,
      selectItem,
      removeItem,
      loading,
      showCreateModal,
      handleResourceCreated,
    }
  },

  methods: {
    fill(formData) {
      formData.append(this.field.attribute, JSON.stringify(
          this.selectedItems.map(item => item.value)
      ))
    },
  }
}
</script>

<style>
.multiselect-search input::placeholder {
  color: rgb(156, 163, 175) !important;
}

.dark .multiselect-search input::placeholder {
  color: rgb(107, 114, 128) !important;
}

.hasManySearchable {
  .dropDownMenu {
    max-height: 300px;
    overflow: scroll;
  }
}
</style>
