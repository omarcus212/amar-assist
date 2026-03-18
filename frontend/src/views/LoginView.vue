<template>
  <div class="login-container">
    <!-- Lado Esquerdo -->
    <div class="left-side">
      <h1>Bem-vindo!</h1>
      <p>Sistema de Gerenciamento de Produtos</p>
      <div class="logo-icon">📦</div>
    </div>

    <!-- Lado Direito -->
    <div class="right-side">
      <div class="form-container">
        <!-- Cabeçalho -->
        <div class="form-header">
          <h2>Login</h2>
          <p>Acesse sua conta</p>
        </div>

        <!-- Formulário -->
        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label for="email">E-mail</label>
            <input
              id="email"
              type="email"
              placeholder="seu@email.com"
              v-model="email"
              required
            />
          </div>

          <div class="form-group">
            <label for="password">Senha</label>
            <input
              id="password"
              type="password"
              placeholder="••••••••"
              v-model="password"
              required
            />
          </div>

          <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

          <button type="submit" class="btn-login" :disabled="loading">
            {{ loading ? "Entrando..." : "Entrar" }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { authService } from "@/services/auth";

const router = useRouter();
const email = ref("admin@admin.com");
const password = ref("admin123");
const loading = ref(false);
const errorMessage = ref("");

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = "";

  try {
    await authService.login(email.value, password.value);
    router.push({ name: "products" });
  } catch (error) {
    errorMessage.value = error.message || "Falha no login.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-container {
  display: flex;
  min-height: 100vh;
  width: 100%;
  margin: 0;
  padding: 0;
}

/* Lado Esquerdo */
.left-side {
  flex: 1;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  padding: 40px;
}

.left-side h1 {
  font-size: 48px;
  margin-bottom: 20px;
  font-weight: 700;
  margin-top: 0;
}

.left-side p {
  font-size: 20px;
  opacity: 0.9;
  margin-bottom: 40px;
  margin-top: 0;
}

.logo-icon {
  width: 200px;
  height: 200px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 80px;
  margin-top: 20px;
}

/* Lado Direito */
.right-side {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f5f5f5;
  padding: 40px;
}

.form-container {
  width: 100%;
  max-width: 400px;
  background: white;
  padding: 40px;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.form-header {
  text-align: center;
  margin-bottom: 30px;
}

.form-header h2 {
  font-size: 32px;
  color: #333;
  margin-bottom: 10px;
  margin-top: 0;
}

.form-header p {
  color: #666;
  font-size: 14px;
  margin: 0;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
  font-size: 14px;
}

.form-group input {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-login {
  width: 100%;
  padding: 14px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: 10px;
}

.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-login:hover {
  background: #5568d3;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-login:active {
  transform: translateY(0);
}

.credentials-info {
  margin-top: 30px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  text-align: center;
}

.credentials-info p {
  font-size: 12px;
  color: #666;
  margin: 5px 0;
}

.credentials-info strong {
  color: #333;
}

.error-message {
  color: #dc3545;
  margin-bottom: 1rem;
  font-size: 0.9rem;
  text-align: center;
}

/* Responsivo */
@media (max-width: 768px) {
  .login-container {
    flex-direction: column;
  }

  .left-side {
    display: none;
  }

  .right-side {
    padding: 20px;
  }

  .form-container {
    padding: 30px 20px;
  }
}
</style>
