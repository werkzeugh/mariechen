<template>
  <div class="form-group"
       :class="{'has-error': hasErrors, 'has-success':(!hasErrors && validator.$dirty)}">
    <slot name="label">
      <label class="control-label"
             v-if="label">{{ label }} {{ validator.$params.required ? '*' : '' }}</label>
    </slot>
    <slot :errors="activeErrors"
          :has-errors="hasErrors"
          :first-error-message="firstErrorMessage"
    ></slot>
    <slot name="errors"
          :errors="activeErrors"
          :has-errors="hasErrors"
          :first-error-message="firstErrorMessage">
      <div class="help-block" v-if="hasErrors">
        <span v-if="showSingleError"
              :data-validation-attr="firstError.validationKey">
          {{ firstErrorMessage }}
        </span>
        <template v-if="!showSingleError">
          <span v-for="error in activeErrors"
                :key="error.validationKey"
                :data-validation-attr="error.validationKey">
            {{ getErrorMessage(error.validationKey, error.params) }}
          </span>
        </template>
      </div>
    </slot>
  </div>
</template>
<script>
  import {extractorMixin} from 'vuelidate-error-extractor'

  export default {
    mixins: [extractorMixin]
  }
</script>
