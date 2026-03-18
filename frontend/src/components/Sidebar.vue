<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo">📦</div>
      <h2>Sistema</h2>
    </div>

    <nav class="sidebar-nav">
      <a
        href="/products"
        class="nav-item"
        :class="{ active: currentRoute === 'products' }"
      >
        <span class="icon">📊</span>
        <span>Produtos</span>
      </a>
      <a
        href="/users"
        class="nav-item"
        :class="{ active: currentRoute === 'users' }"
      >
        <span class="icon">👥</span>
        <span>Usuários</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="user-profile">
        <div class="user-avatar">A</div>
        <div class="user-info">
          <p class="user-name">Administrador</p>
          <p class="user-email">admin@admin.com</p>
        </div>
      </div>
      <button class="btn-logout" @click="handleLogout">
        <span class="icon">🚪</span>
        <span>Sair</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRouter } from "vue-router";
import { authService } from "@/services/auth";

// Define qual rota está ativa (products ou users)
const props = defineProps({
  currentRoute: {
    type: String,
    default: "products",
  },
});

const router = useRouter();

const handleLogout = async () => {
  await authService.logout();
  router.push({ name: "login" });
};
</script>

<style scoped>
/* Sidebar */
.sidebar {
  width: 15%;
  background: white;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  height: 100%;
}

.sidebar-header {
  padding: 25px 20px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo {
  font-size: 32px;
}

.sidebar-header h2 {
  font-size: 20px;
  color: #1f2937;
  margin: 0;
}

.sidebar-nav {
  flex: 1;
  padding: 20px 0;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #6b7280;
  text-decoration: none;
  transition: all 0.2s;
  font-size: 14px;
  font-weight: 500;
}

.nav-item .icon {
  font-size: 20px;
}

.nav-item:hover {
  background: #f3f4f6;
  color: #667eea;
}

.nav-item.active {
  background: #eef2ff;
  color: #667eea;
  border-right: 3px solid #667eea;
}

.sidebar-footer {
  padding: 20px;
  border-top: 1px solid #e5e7eb;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 15px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: #667eea;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
}

.user-info {
  flex: 1;
}

.user-name {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.user-email {
  font-size: 12px;
  color: #6b7280;
  margin: 2px 0 0 0;
}

.btn-logout {
  width: 100%;
  padding: 10px;
  background: #fee;
  color: #dc2626;
  border: 1px solid #fecaca;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn-logout:hover {
  background: #fecaca;
}

/* Responsivo */
@media (max-width: 1024px) {
  .sidebar {
    width: 70px;
  }

  .sidebar-header h2,
  .nav-item span:not(.icon),
  .user-info,
  .btn-logout span:not(.icon) {
    display: none;
  }

  .nav-item {
    justify-content: center;
  }
}
</style>
