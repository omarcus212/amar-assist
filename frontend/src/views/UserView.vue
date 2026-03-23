<template>
  <div class="page-wrapper">
    <Sidebar currentRoute="users" />

    <main class="main-content">
      <!-- Header -->
      <header class="header">
        <h1>Gerenciamento de Usuários</h1>
        <div class="header-actions">
          <button class="btn-filter" @click="toggleFilters">Filtros</button>
          <button class="btn-create" @click="openModal('create')">
            Novo Usuário
          </button>
        </div>
      </header>

      <div class="container">
        <!-- Filtros -->
        <div class="filters-card" :class="{ show: showFilters }">
          <div class="filters-header">
            <h3>Filtros</h3>
            <button class="btn-close-filters" @click="toggleFilters">✕</button>
          </div>
          <div class="filters-grid">
            <div class="filter-group">
              <label>Nome</label>
              <input
                type="text"
                v-model="filters.name"
                placeholder="Buscar por nome..."
              />
            </div>
            <div class="filter-group">
              <label>E-mail</label>
              <input
                type="text"
                v-model="filters.email"
                placeholder="Buscar por e-mail..."
              />
            </div>
            <div class="filter-group">
              <label>Status</label>
              <select v-model="filters.active">
                <option :value="undefined">Todos</option>
                <option :value="true">Ativos</option>
                <option :value="false">Inativos</option>
              </select>
            </div>
          </div>
          <div class="filters-footer">
            <button class="btn-clear-filters" @click="clearFilters">
              Limpar Filtros
            </button>
            <button class="btn-apply-filters" @click="applyFilters">
              Aplicar Filtros
            </button>
          </div>
        </div>

        <!-- Tabela de Usuários -->
        <div class="users-card">
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nome</th>
                  <th>E-mail</th>
                  <th>Status</th>
                  <th>Criado em</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id">
                  <td>{{ user.id }}</td>
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </div>
                      <span>{{ user.name }}</span>
                    </div>
                  </td>
                  <td>{{ user.email }}</td>
                  <td>
                    <span
                      class="badge"
                      :class="user.active ? 'badge-active' : 'badge-inactive'"
                    >
                      {{ user.active ? "Ativo" : "Inativo" }}
                    </span>
                  </td>
                  <td>{{ formatDate(user.created_at) }}</td>
                  <td>
                    <div class="action-buttons">
                      <button
                        v-if="user.active"
                        class="btn-edit"
                        @click="openModal('edit', user)"
                      >
                        Editar
                      </button>
                      <button
                        v-if="user.active"
                        class="btn-deactivate"
                        @click="deactivateUser(user.id)"
                      >
                        Desativar
                      </button>
                      <button v-else class="btn-activate">Ativar</button>
                    </div>
                  </td>
                </tr>
                <tr v-if="users.length === 0 && !loading">
                  <td colspan="6" class="empty-state">
                    Nenhum usuário encontrado.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginação -->
          <div class="pagination" v-if="pagination.last_page > 1">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="pagination-arrow"
            >
              &lt;
            </button>
            <div class="pagination-status">
              <input
                type="number"
                :value="pagination.current_page"
                @keyup.enter="goToPage($event.target.value)"
                class="pagination-input"
                min="1"
                :max="pagination.last_page"
              />
              <span>/ {{ pagination.last_page }}</span>
            </div>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="pagination-arrow"
            >
              &gt;
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Create/Update -->
    <div class="modal-overlay" v-if="showModal" @click.self="closeModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2>{{ isEdit ? "Editar Usuário" : "Novo Usuário" }}</h2>
          <button class="btn-close-modal" @click="closeModal">✕</button>
        </div>

        <div class="modal-body">
          <div class="form-content">
            <div class="form-group">
              <label>Nome *</label>
              <input
                type="text"
                v-model="form.name"
                placeholder="Nome completo"
              />
            </div>

            <div class="form-group">
              <label>E-mail *</label>
              <input
                type="email"
                v-model="form.email"
                placeholder="email@exemplo.com"
              />
            </div>

            <div class="form-group" v-if="!isEdit">
              <label>Senha *</label>
              <input
                type="password"
                v-model="form.password"
                placeholder="Mínimo 6 caracteres"
              />
            </div>

            <div class="form-group" v-if="isEdit">
              <label>Nova Senha (deixe em branco para manter)</label>
              <input
                type="password"
                v-model="form.password"
                placeholder="Mínimo 6 caracteres"
              />
            </div>

            <div class="form-group">
              <label>Status</label>
              <select v-model="form.active">
                <option :value="true">Ativo</option>
                <option :value="false">Inativo</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-cancel" @click="closeModal">
            Cancelar
          </button>
          <button
            type="button"
            class="btn-save"
            @click="handleSubmit"
            :disabled="loading || isFormInvalid"
          >
            {{ loading ? "Salvando..." : isEdit ? "Atualizar" : "Cadastrar" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from "vue";
import Sidebar from "../components/Sidebar.vue";
import { userService } from "@/services/user";
import Swal from "sweetalert2";

// --- Estados ---
const users = ref([]);
const loading = ref(false);
const showModal = ref(false);
const showFilters = ref(false);
const isEdit = ref(false);

// --- Filtros ---
const filters = reactive({
  name: "",
  email: "",
  active: undefined,
  page: 1,
});

// --- Paginação ---
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
});

// --- Formulário ---
const form = reactive({
  id: null,
  name: "",
  email: "",
  password: "",
  active: true,
});

// --- Computed ---
const isFormInvalid = computed(() => {
  if (isEdit.value) {
    return !form.name || !form.email;
  }
  return (
    !form.name || !form.email || !form.password || form.password.length < 6
  );
});

const visiblePages = computed(() => {
  const current = pagination.current_page;
  const total = pagination.last_page;
  const pages = [];

  let start = Math.max(1, current - 2);
  let end = Math.min(total, start + 4);

  if (end - start < 4) {
    start = Math.max(1, end - 4);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

// --- Métodos de Filtro ---
const toggleFilters = () => (showFilters.value = !showFilters.value);

const clearFilters = () => {
  filters.name = "";
  filters.email = "";
  filters.active = undefined;
  applyFilters();
};

const applyFilters = async () => {
  // UX: Sempre que aplicar filtro novo, voltar para página 1
  filters.page = 1;
  await loadUsers();
  // showFilters.value = false;
};

// --- Carregar Usuários ---
const loadUsers = async () => {
  loading.value = true;
  try {
    const response = await userService.getAll({
      name: filters.name,
      email: filters.email,
      active: filters.active,
      page: filters.page,
    });

    const paginationObj = response;

    users.value = paginationObj.data || [];
    pagination.current_page = paginationObj.current_page || 1;
    pagination.last_page = paginationObj.last_page || 1;
    pagination.total = paginationObj.total || 0;
  } catch (error) {
    console.error("Erro ao carregar usuários:", error);
    showToast("error", "Erro ao carregar usuários");
  } finally {
    loading.value = false;
  }
};

const changePage = async (page) => {
  if (page < 1 || page > pagination.last_page) return;
  filters.page = page;
  await loadUsers();
};

// Navegação direta via input
const goToPage = (page) => {
  const pageNum = parseInt(page, 10);
  // SEGURANÇA (FRONT): Valida se está dentro dos limites antes de chamar a API
  if (!isNaN(pageNum) && pageNum >= 1 && pageNum <= pagination.last_page) {
    changePage(pageNum);
  }
  // O input será re-renderizado com o valor correto de `pagination.current_page`
  // graças à reatividade do Vue quando o loadUsers atualizar o estado.
};

// --- Modal ---
const openModal = (mode, user = null) => {
  isEdit.value = mode === "edit";
  if (isEdit.value && user) {
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = "";
    form.active = user.active;
  } else {
    resetForm();
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

// Reseta o estado do formulário para evitar dados "fantasmas" ao reabrir o modal
const resetForm = () => {
  form.id = null;
  form.name = "";
  form.email = "";
  form.password = "";
  form.active = true;
};

// --- Submit ---
const handleSubmit = async () => {
  loading.value = true;
  try {
    // Prepara payload apenas com campos necessários
    const data = {
      name: form.name,
      email: form.email,
      active: form.active,
    };

    // LOGICA: Só envia senha se ela foi preenchida (no caso de edição)
    if (form.password) {
      data.password = form.password;
    }

    if (isEdit.value) {
      await userService.update(form.id, data);
      showToast("success", "Usuário atualizado!");
    } else {
      await userService.create(data);
      showToast("success", "Usuário cadastrado!");
    }

    closeModal();
    await loadUsers();
  } catch (error) {
    console.error("Erro no submit:", error);
    showToast("error", error.message);
  } finally {
    loading.value = false;
  }
};

// --- Ações ---
const deactivateUser = async (id) => {
  const result = await Swal.fire({
    title: "Desativar usuário?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sim",
    cancelButtonText: "Cancelar",
  });

  if (result.isConfirmed) {
    try {
      await userService.deactivate(id);
      showToast("success", "Usuário desativado!");
      await loadUsers();
    } catch (error) {
      showToast("error", error || "Erro ao desativar usuário");
    }
  }
};

const activateUser = async (id) => {
  const result = await Swal.fire({
    title: "Ativar usuário?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sim",
    cancelButtonText: "Cancelar",
  });

  if (result.isConfirmed) {
    try {
      await userService.reactivate(id);
      showToast("success", "Usuário ativado!");
      await loadUsers();
    } catch (error) {
      showToast("error", "Erro ao ativar usuário");
    }
  }
};

// --- Utilitários ---
const formatDate = (dateString) => {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("pt-BR");
};

const showToast = (icon, message) => {
  Swal.fire({
    toast: true,
    position: "top-end",
    icon,
    title: message,
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
  });
};

onMounted(() => {
  loadUsers();
});
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.page-wrapper {
  display: flex;
  min-height: 100vh;
  background: #f5f7fa;
}

.main-content {
  flex: 1;
  margin-left: 260px;
  min-height: 100vh;
}

.header {
  background: white;
  padding: 25px 30px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header h1 {
  font-size: 24px;
  color: #1f2937;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-filter {
  padding: 10px 20px;
  background: white;
  color: #667eea;
  border: 2px solid #667eea;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-filter:hover {
  background: #667eea;
  color: white;
}

.btn-create {
  padding: 10px 24px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-create:hover {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.container {
  padding: 30px;
}

/* Filtros */
.filters-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 25px;
  display: none;
}

.filters-card.show {
  display: block;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filters-header h3 {
  font-size: 18px;
  color: #1f2937;
  margin: 0;
}

.btn-close-filters {
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  font-size: 20px;
  padding: 5px;
  transition: all 0.2s;
}

.btn-close-filters:hover {
  color: #ef4444;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.filter-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.filter-group input,
.filter-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
}

.filter-group input:focus,
.filter-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filters-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.btn-clear-filters {
  padding: 10px 20px;
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-clear-filters:hover {
  background: #f9fafb;
}

.btn-apply-filters {
  padding: 10px 20px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-apply-filters:hover {
  background: #5568d3;
}

.users-card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: #f9fafb;
}

th {
  padding: 15px 12px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #e5e7eb;
}

td {
  padding: 15px 12px;
  border-bottom: 1px solid #f3f4f6;
  font-size: 14px;
  color: #6b7280;
}

tbody tr {
  transition: all 0.2s;
}

tbody tr:hover {
  background: #f9fafb;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
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
  font-size: 16px;
}

.badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge-active {
  background: #d1fae5;
  color: #065f46;
}

.badge-inactive {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-edit {
  padding: 6px 12px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-edit:hover {
  background: #2563eb;
}

.btn-deactivate {
  padding: 6px 12px;
  background: #f59e0b;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-deactivate:hover {
  background: #d97706;
}

.btn-activate {
  padding: 6px 12px;
  background: #a8a8a8;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  margin-top: 25px;
}

.pagination-arrow {
  padding: 6px 12px;
  border: 1px solid #d1d5db;
  background: white;
  cursor: pointer;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
  color: #6b7280;
  line-height: 1;
}

.pagination-arrow:hover:not(:disabled) {
  border-color: #667eea;
  color: #667eea;
}

.pagination-arrow:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-status {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #6b7280;
}

.pagination-input {
  width: 45px;
  padding: 6px 8px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  text-align: center;
  font-size: 14px;
  margin-right: 8px;
}

.pagination-input::-webkit-outer-spin-button,
.pagination-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.pagination-input:focus {
  outline: none;
  border-color: #667eea;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #9ca3af;
  font-size: 14px;
}

/* ==================== MODAL ==================== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-container {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 25px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  font-size: 20px;
  color: #1f2937;
  margin: 0;
}

.btn-close-modal {
  background: none;
  border: none;
  font-size: 24px;
  color: #9ca3af;
  cursor: pointer;
  transition: color 0.2s;
}

.btn-close-modal:hover {
  color: #ef4444;
}

.modal-body {
  padding: 25px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 25px;
  border-top: 1px solid #e5e7eb;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 8px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-cancel {
  padding: 10px 20px;
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-cancel:hover {
  background: #f9fafb;
}

.btn-save {
  padding: 10px 24px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s;
}

.btn-save:hover:not(:disabled) {
  background: #5568d3;
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ==================== RESPONSIVO ==================== */
@media (max-width: 1024px) {
  .main-content {
    margin-left: 70px;
  }
}

@media (max-width: 768px) {
  .filters-grid {
    grid-template-columns: 1fr;
  }

  .table-container {
    overflow-x: auto;
  }

  table {
    min-width: 700px;
  }
}
</style>
