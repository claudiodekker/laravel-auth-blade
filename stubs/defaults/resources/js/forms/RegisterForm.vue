<script>
import webauthn from '@/webauthn.js'

import ButtonComponent from '@/components/ButtonComponent.vue'
import KeyIcon from '@/components/icons/KeyIcon.vue'
import PasswordIcon from '@/components/icons/PasswordIcon.vue'
import InputComponent from '@/components/InputComponent.vue'
import InputErrorComponent from '@/components/InputErrorComponent.vue'
import LoadingIndicatorComponent from '@/components/LoadingIndicatorComponent.vue'

export default {
  components: {
    ButtonComponent,
    KeyIcon,
    PasswordIcon,
    InputComponent,
    InputErrorComponent,
    LoadingIndicatorComponent,
  },
  props: {
    endpoint: {
      type: String,
      required: true,
    },
  },
  data: () => ({
    flow: 'none',
    state: 'default',
    form: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    },
    passkeySupported: false,
    publicKeyRequestOptions: null,
    passkeyCredential: null,
    errors: {},
  }),
  computed: {
    hasMethodSelector() {
      return this.passkeySupported && this.flow === 'none'
    },
    inPasswordFlow() {
      return !this.passkeySupported || this.flow === 'password'
    },
    inPasskeyFlow() {
      return this.passkeySupported && this.flow === 'passkey'
    },
    hasStandardFields() {
      return this.hasMethodSelector || this.state === 'default'
    },
  },
  created() {
    this.passkeySupported = webauthn.supported()
  },
  methods: {
    submit() {
      this.errors = {}

      if (!this.inPasskeyFlow && !this.inPasswordFlow) {
        return this.selectPasskeyFlow()
      }

      if (this.inPasswordFlow) {
        return this.submitPasswordFlow()
      }

      return this.submitPasskeyFlow()
    },
    async submitForm(data) {
      let response = null

      try {
        response = await window.axios.post(this.endpoint, data)
      } catch (e) {
        if (!e.response || !e.response.data) {
          console.error(e)
          this.state = 'error'
          return
        }

        response = e.response
      }

      // When the session has expired, reload the page.
      if (response.status === 419) {
        window.location.reload()
        return
      }

      // If the server returned a validation error, we'll want to display it to the user.
      if (response.status === 422) {
        this.state = 'default'
        this.errors = response.data.errors
        this.$nextTick(() => this.adjustFocus())
        return
      }

      return response
    },
    async cancelFlow() {
      if (this.inPasswordFlow) {
        this.form.password = ''
        this.form.password_confirmation = ''
      }

      if (this.inPasskeyFlow && this.publicKeyRequestOptions) {
        await this.releaseClaimedAccount()
      }

      this.flow = 'none'
      this.state = 'default'
      this.$nextTick(() => this.adjustFocus())
    },
    adjustFocus() {
      if (!this.form.name) {
        this.$refs.name.focus()
      } else if (!this.form.email) {
        this.$refs.email.focus()
      } else if (this.inPasswordFlow) {
        this.$refs.password.focus()
      } else {
        this.$refs.email.focus()
      }
    },
    // Password Flow Methods
    selectPasswordFlow() {
      this.flow = 'password'
      this.resetPasswordFlow()
    },
    resetPasswordFlow() {
      this.form.password = ''
      this.form.password_confirmation = ''
      this.state = 'default'
      this.$nextTick(() => this.adjustFocus())
    },
    submitPasswordFlow() {
      if (this.state === 'error') {
        return this.resetPasswordFlow()
      }

      return this.registerPasswordAccount()
    },
    async registerPasswordAccount() {
      if (this.state === 'submitting') {
        return
      }

      this.state = 'submitting'

      const payload = {
        type: 'password',
        name: this.form.name,
        email: this.form.email,
        password: this.form.password,
        password_confirmation: this.form.password_confirmation,
      }

      this.form.password = ''
      this.form.password_confirmation = ''

      const response = await this.submitForm(payload)
      if (!response) {
        return
      }

      if (response.status !== 201) {
        this.state = 'error'
        console.error(response)
        return
      }

      window.location.href = response.data.redirect_url
    },
    // Passkey Flow Methods
    async selectPasskeyFlow() {
      this.flow = 'passkey'

      if (this.state === 'default' && this.form.name && this.form.email) {
        this.submit()
      }
    },
    submitPasskeyFlow() {
      if (!this.publicKeyRequestOptions) {
        return this.claimPasskeyAccount()
      }

      if (!this.passkeyCredential) {
        return this.createPasskeyCredential()
      }

      this.submitPasskeyCredential()
    },
    async claimPasskeyAccount() {
      if (this.state === 'claiming') {
        return
      }

      this.state = 'claiming'

      const payload = {
        type: 'passkey',
        name: this.form.name,
        email: this.form.email,
      }

      const response = await this.submitForm(payload)
      if (!response) {
        return
      }

      if (response.status !== 200) {
        this.state = 'error'
        console.error(response)
        return
      }

      this.publicKeyRequestOptions = response.data
      this.createPasskeyCredential()
    },
    async createPasskeyCredential() {
      this.state = 'waiting'

      let credential = null
      try {
        credential = await webauthn.register({
          publicKey: this.publicKeyRequestOptions,
        })
      } catch (e) {
        if (e.name === 'SecurityError') {
          this.state = 'security-error'
          this.silentReleaseClaimedAccount()
          return
        }

        if (e.name === 'NotAllowedError') {
          this.state = 'not-allowed'
          console.error(e)
          return
        }

        if (e.name === 'AbortError') {
          this.state = 'aborted'
          return
        }

        this.state = 'error'
        console.error(e)
        return
      }

      this.passkeyCredential = credential
      this.submitPasskeyCredential()
    },
    async submitPasskeyCredential() {
      if (this.state === 'submitting') {
        return
      }

      this.state = 'submitting'
      const payload = {
        type: 'passkey',
        credential: this.passkeyCredential,
      }

      const response = await this.submitForm(payload)
      if (!response) {
        return
      }

      if (response.status !== 201) {
        this.state = 'error'
        console.error(response)
        return
      }

      window.location.href = response.data.redirect_url
    },
    async releaseClaimedAccount() {
      if (!this.publicKeyRequestOptions) {
        return
      }

      this.state = 'releasing'
      await this.silentReleaseClaimedAccount()
    },
    async silentReleaseClaimedAccount() {
      let response = null
      try {
        response = await window.axios.delete(this.endpoint)
      } catch (e) {
        if (!e.response || !e.response.data) {
          console.error(e)
          this.state = 'error'
          return
        }

        response = e.response
      }

      // When the session has expired, reload the page.
      if (response.status === 419) {
        window.location.reload()
        return
      }

      // Otherwise, we'll assume the account was released.
      this.publicKeyRequestOptions = null
    },
    async switchToPasswordFlow() {
      // Normally we'd await this, but we don't want to block the user from switching to the password flow.
      // If the user auto-fill generates and signs up too quickly, they'll just have to try again.
      await this.releaseClaimedAccount()
      this.selectPasswordFlow()
    },
  },
}
</script>

<template>
  <form @submit.prevent="submit">
    <div v-if="hasStandardFields">
      <div>
        <InputComponent ref="name" v-model="form.name" type="text" name="name" required autofocus autocomplete="name">Name</InputComponent>
        <InputErrorComponent :error="errors.name" />
      </div>
      <div class="mt-4">
        <InputComponent ref="email" v-model="form.email" type="email" name="email" required autocomplete="email webauthn">Email</InputComponent>
        <InputErrorComponent :error="errors.email" />
      </div>
    </div>
    <template v-if="hasMethodSelector">
      <div class="relative mt-6">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
          <div class="w-full border-t border-gray-300" />
        </div>
        <div class="relative flex justify-center">
          <span class="bg-white px-2 text-sm text-gray-500">Select your preferred authentication method</span>
        </div>
      </div>
      <div class="flex items-center mt-4 -mx-2">
        <ButtonComponent type="submit" flavor="secondary" class="w-full mx-2 justify-center">
          <KeyIcon class="w-6 h-6 mr-4" />
          <span>Using a passkey</span>
        </ButtonComponent>
        <ButtonComponent type="button" flavor="primary" class="w-full mx-2 justify-center" @click="selectPasswordFlow">
          <PasswordIcon class="w-6 h-6 mr-4" />
          <span>Using a password</span>
        </ButtonComponent>
      </div>
    </template>
    <div v-else-if="inPasswordFlow">
      <template v-if="state === 'default'">
        <div>
          <div class="mt-4">
            <InputComponent ref="password" v-model="form.password" type="password" name="password" required autocomplete="new-password" :error="errors.password">Password</InputComponent>
            <InputErrorComponent :error="errors.password" />
          </div>
          <div class="mt-4">
            <InputComponent v-model="form.password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">Confirm Password</InputComponent>
          </div>
          <div class="mt-4">
            <ButtonComponent type="submit" flavor="primary" class="w-full justify-center"> Sign up </ButtonComponent>
          </div>
          <div v-if="passkeySupported" class="mt-2">
            <ButtonComponent type="button" flavor="secondary" class="w-full justify-center" @click="cancelFlow"> Cancel </ButtonComponent>
          </div>
        </div>
      </template>
      <div v-if="state === 'submitting'" class="py-4">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Please wait...</h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
          <p>Your account is being registered. This should only take a few seconds.</p>
        </div>
        <LoadingIndicatorComponent class="mt-8 flex justify-center" />
      </div>
      <div v-if="state === 'error'">
        <h3 class="text-lg font-medium leading-6 text-red-500">An unexpected error occurred</h3>
        <div class="mt-2 max-w-xl text-sm text-gray-500">
          <p>We're sorry, but we were unable to register your account. Please check your internet connection and try again.</p>
          <p class="mt-2">If the problem persists, it's likely on our end and we're already working on a solution. Thank you for your patience.</p>
        </div>
        <div class="mt-4">
          <ButtonComponent type="submit" flavor="primary" class="w-full justify-center"> Retry </ButtonComponent>
        </div>
        <div v-if="passkeySupported" class="mt-2">
          <ButtonComponent type="button" flavor="secondary" class="w-full justify-center" @click="cancelFlow"> Cancel </ButtonComponent>
        </div>
      </div>
    </div>
    <template v-else-if="inPasskeyFlow">
      <div>
        <div v-if="state === 'default'">
          <div class="mt-4">
            <ButtonComponent type="submit" flavor="primary" class="w-full justify-center"> Sign up </ButtonComponent>
          </div>
          <div class="mt-2">
            <ButtonComponent type="button" flavor="secondary" class="w-full justify-center" @click="cancelFlow"> Cancel </ButtonComponent>
          </div>
        </div>
        <div v-if="state === 'claiming'" class="py-4">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Please wait...</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>We're claiming your account details, and are setting up it's registration process. This should only take a few seconds.</p>
          </div>
          <LoadingIndicatorComponent class="mt-8 flex justify-center" />
        </div>
        <div v-if="state === 'waiting'" class="py-4">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Register your passkey</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>Follow the instructions provided by your browser to register your passkey.</p>
          </div>
        </div>
        <div v-if="state === 'submitting'" class="py-4">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Please wait..</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>Your passkey has been successfully created, and is being associated with your account. This should only take a few seconds.</p>
          </div>
          <LoadingIndicatorComponent class="mt-8 flex justify-center" />
        </div>
        <div v-if="state === 'security-error'">
          <h3 class="text-lg font-medium leading-6 text-red-500">Security Key Error</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>We were unable to register your security key because your browser is unable to trust the current connection.</p>
            <p class="mt-2">Please make sure you are using a secure connection (https) and that the website's domain matches what you were expecting.</p>
          </div>
          <div class="mt-4">
            <ButtonComponent type="button" flavor="danger" class="w-full justify-center" @click="cancelFlow"> I understand </ButtonComponent>
          </div>
        </div>
        <div v-if="state === 'aborted'">
          <h3 class="text-lg font-medium leading-6 text-red-500">Passkey registration cancelled</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>You have cancelled the passkey registration process.</p>
          </div>
          <div class="mt-4">
            <ButtonComponent type="submit" flavor="primary" class="w-full justify-center"> Retry </ButtonComponent>
          </div>
          <div class="mt-2">
            <ButtonComponent type="button" flavor="secondary" class="w-full justify-center" @click="switchToPasswordFlow">
              <span>Register using a password</span>
            </ButtonComponent>
          </div>
        </div>
        <div v-if="state === 'not-allowed'">
          <h3 class="text-lg font-medium leading-6 text-red-500">Passkey registration cancelled</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>You have cancelled the passkey registration process, or your browser's request has timed out.</p>
            <p class="mt-2">If you would like to register a security key for your account, please try again.</p>
          </div>
          <div class="mt-4">
            <ButtonComponent type="submit" flavor="primary" class="w-full justify-center"> Retry </ButtonComponent>
          </div>
          <div class="mt-2">
            <ButtonComponent type="button" flavor="secondary" class="w-full justify-center" @click="cancelFlow"> Cancel </ButtonComponent>
          </div>
        </div>
        <div v-if="state === 'error'">
          <h3 class="text-lg font-medium leading-6 text-red-500">An unexpected error occurred</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>We're sorry, but we were unable to register your account using a passkey. Please check your internet connection and try again.</p>
            <p class="mt-2">If the problem persists, it's likely on our end and we're already working on a solution. Thank you for your patience.</p>
          </div>
          <div class="mt-4">
            <ButtonComponent type="submit" flavor="primary" class="w-full justify-center"> Retry </ButtonComponent>
          </div>
          <div class="mt-2">
            <ButtonComponent type="button" flavor="secondary" class="w-full justify-center" @click="cancelFlow"> Cancel </ButtonComponent>
          </div>
        </div>
        <div v-if="state === 'releasing'" class="py-4">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Please wait...</h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>We're resetting the registration process for the provided account details. This should only take a few seconds.</p>
          </div>
          <LoadingIndicatorComponent class="mt-8 flex justify-center" />
        </div>
      </div>
    </template>
  </form>
</template>
