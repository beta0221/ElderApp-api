<template>
  <div class="login-page">
    <div class="login-card-wrap">
      <v-card class="login-card elevation-12">

        <div class="login-header">
          <v-icon size="48" color="white">supervised_user_circle</v-icon>
          <div class="login-title">銀髮學院</div>
          <div class="login-subtitle">中央管理平台</div>
        </div>

        <v-card-text class="login-body">
          <v-alert
            v-if="error"
            type="error"
            :value="true"
            outline
            class="mb-4"
          >{{ error }}</v-alert>

          <v-form @submit.prevent="submit" ref="form">
            <v-text-field
              v-model="form.email"
              label="帳號"
              type="email"
              prepend-icon="person"
              color="orange darken-2"
              required
              :disabled="loading"
            ></v-text-field>

            <v-text-field
              v-model="form.password"
              label="密碼"
              :type="showPassword ? 'text' : 'password'"
              prepend-icon="lock"
              :append-icon="showPassword ? 'visibility_off' : 'visibility'"
              :append-icon-cb="() => showPassword = !showPassword"
              color="orange darken-2"
              required
              :disabled="loading"
            ></v-text-field>

            <v-btn
              type="submit"
              color="orange darken-2"
              class="white--text mt-3"
              block
              large
              :loading="loading"
            >
              登入
            </v-btn>
          </v-form>
        </v-card-text>

      </v-card>
    </div>
  </div>
</template>

<script>
import AppStorage from '../../Helpers/AppStorage'
import Token from '../../Helpers/Token'

export default {
  props: ['from_url'],
  data() {
    return {
      form: {
        email: null,
        password: null,
      },
      loading: false,
      error: null,
      showPassword: false,
    }
  },
  created() {
    if (User.loggedIn()) {
      this.$router.push({ path: '/member' })
    }
  },
  methods: {
    submit() {
      this.error = null
      this.loading = true

      axios.post('/api/auth/login', this.form)
        .then(res => {
          const { access_token, name, role } = res.data
          if (Token.isValid(access_token)) {
            AppStorage.store(name, access_token, role)
            window.axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`
            this.$router.push({ path: this.from_url || '/member' })
          } else {
            this.error = '登入失敗，請再試一次。'
          }
        })
        .catch(() => {
          this.error = '帳號或密碼錯誤，請重新輸入。'
        })
        .finally(() => {
          this.loading = false
        })
    }
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #d57558 0%, #e4a28e 45%, #eebeb0 100%);
}

.login-card-wrap {
  width: 100%;
  max-width: 420px;
  padding: 0 16px;
}

.login-card {
  border-radius: 12px;
  overflow: hidden;
}

.login-header {
  background: linear-gradient(135deg, #e16741, #dd8064);
  padding: 40px 24px 32px;
  text-align: center;
  color: white;
}

.login-title {
  font-size: 28px;
  font-weight: 600;
  letter-spacing: 4px;
  margin-top: 14px;
}

.login-subtitle {
  font-size: 13px;
  letter-spacing: 2px;
  opacity: 0.85;
  margin-top: 6px;
}

.login-body {
  padding: 28px 32px 36px;
}
</style>
